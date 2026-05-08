<?php

namespace App\Http\Controllers;

use App\Helpers\ActionLogger;
use App\Models\Course;
use App\Models\Material;
use DOMDocument;
use DOMElement;
use DOMNode;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MaterialController extends Controller
{
    private function checkCourseViewAccess(Course $course): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'teacher' && $course->teacher_id === $user->id) {
            return;
        }

        if ($user->role === 'student') {
            $isEnrolled = $user->courses()
                ->where('courses.id', $course->id)
                ->exists();

            if ($isEnrolled) {
                return;
            }
        }

        abort(403);
    }

    private function checkMaterialViewAccess(Material $material): void
    {
        $user = Auth::user();
        $course = $material->course;

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role === 'teacher' && $course->teacher_id === $user->id) {
            return;
        }

        if ($user->role === 'student') {
            $isEnrolled = $user->courses()
                ->where('courses.id', $course->id)
                ->exists();

            if ($isEnrolled && $material->is_published) {
                return;
            }
        }

        abort(403);
    }

    private function checkManageAccess(Course|Material $target): void
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return;
        }

        if ($user->role !== 'teacher') {
            abort(403);
        }

        $course = $target instanceof Material ? $target->course : $target;

        if ($course->teacher_id === $user->id) {
            return;
        }

        abort(403);
    }

    public function index(Course $course)
    {
        $this->checkCourseViewAccess($course);

        $query = $course->materials()
            ->with('author')
            ->latest();

        if (Auth::user()->role === 'student') {
            $query->where('is_published', true);
        }

        $materials = $query->get();

        return view('materials.index', compact('course', 'materials'));
    }

    public function create(Course $course)
    {
        $this->checkManageAccess($course);

        return view('materials.create', compact('course'));
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->checkManageAccess($course);

        $validated = $this->validateMaterial($request);

        $material = Material::create([
            'course_id' => $course->id,
            'author_id' => Auth::id(),
            'title' => $validated['title'],
            'content' => $this->normalizeContent($validated['content']),
            'images' => $this->storeImages($request),
            ...$this->storeAttachment($request),
            'is_published' => $request->boolean('is_published'),
        ]);

        ActionLogger::log(
            'Создание материала',
            'Создан материал: ' . $material->title . ' в курсе: ' . $course->title,
            $request
        );

        return redirect()
            ->route('materials.index', $course)
            ->with('success', 'Материал создан');
    }

    public function show(Material $material)
    {
        $this->checkMaterialViewAccess($material);

        $material->load(['course', 'author']);

        return view('materials.show', compact('material'));
    }

    public function edit(Material $material)
    {
        $this->checkManageAccess($material);

        return view('materials.edit', compact('material'));
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $this->checkManageAccess($material);

        $validated = $this->validateMaterial($request);
        $oldTitle = $material->title;
        $course = $material->course;
        $existingImages = $material->images ?? [];
        $imagesToRemove = collect($request->input('remove_images', []))
            ->filter(fn ($path) => is_string($path) && in_array($path, $existingImages, true))
            ->values()
            ->all();

        foreach ($imagesToRemove as $path) {
            Storage::disk('public')->delete($path);
        }

        $remainingImages = array_values(array_diff($existingImages, $imagesToRemove));
        $attachmentData = [];

        if ($request->boolean('remove_file') || $request->hasFile('file')) {
            $this->deleteAttachment($material);

            $attachmentData = [
                'file_path' => null,
                'file_original_name' => null,
                'file_mime_type' => null,
                'file_size' => null,
            ];
        }

        if ($request->hasFile('file')) {
            $attachmentData = $this->storeAttachment($request);
        }

        $material->update([
            'title' => $validated['title'],
            'content' => $this->normalizeContent($validated['content']),
            'images' => array_values(array_merge($remainingImages, $this->storeImages($request))),
            ...$attachmentData,
            'is_published' => $request->boolean('is_published'),
        ]);

        ActionLogger::log(
            'Обновление материала',
            'Обновлён материал: ' . $oldTitle . ' -> ' . $material->title . ' в курсе: ' . $course->title,
            $request
        );

        return redirect()
            ->route('materials.index', $material->course)
            ->with('success', 'Материал обновлён');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $this->checkManageAccess($material);

        $course = $material->course;
        $title = $material->title;

        foreach ($material->images ?? [] as $imagePath) {
            Storage::disk('public')->delete($imagePath);
        }

        $this->deleteAttachment($material);

        $material->delete();

        ActionLogger::log(
            'Удаление материала',
            'Удалён материал: ' . $title . ' из курса: ' . $course->title,
            request()
        );

        return redirect()
            ->route('materials.index', $course)
            ->with('success', 'Материал удалён');
    }

    public function downloadAttachment(Material $material)
    {
        $this->checkMaterialViewAccess($material);

        if (!$material->file_path || !Storage::disk('public')->exists($material->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->download(
            $material->file_path,
            $material->file_original_name ?: basename($material->file_path)
        );
    }

    public function downloadPdf(Material $material)
    {
        $this->checkMaterialViewAccess($material);

        $material->loadMissing(['course', 'author']);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->setChroot([
            public_path(),
            storage_path('app/public'),
        ]);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('materials.pdf', [
            'material' => $material,
            'imagePaths' => $this->pdfImagePaths($material),
        ])->render(), 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $this->pdfFileName($material) . '"',
        ]);
    }

    private function validateMaterial(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'file' => ['nullable', 'file', 'max:102400'],
            'remove_file' => ['nullable'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['string'],
            'is_published' => ['nullable'],
        ]);
    }

    private function normalizeContent(string $content): string
    {
        $content = trim(str_replace(["\r\n", "\r"], "\n", $content));

        if ($content === '') {
            return '';
        }

        if (! $this->looksLikeHtml($content)) {
            return $content;
        }

        return $this->sanitizeHtml($content);
    }

    private function looksLikeHtml(string $content): bool
    {
        return preg_match('/<\s*(p|div|br|h[1-6]|ul|ol|li|blockquote|strong|b|em|i|a)\b/i', $content) === 1;
    }

    private function sanitizeHtml(string $html): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $wrappedHtml = '<div>' . $html . '</div>';

        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>' . $wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $root = $document->documentElement;

        if (! $root instanceof DOMElement) {
            return '';
        }

        $this->sanitizeNode($root, $document);

        $html = '';

        foreach ($root->childNodes as $childNode) {
            $html .= $document->saveHTML($childNode);
        }

        return trim($html);
    }

    private function sanitizeNode(DOMNode $node, DOMDocument $document): void
    {
        $allowedTags = [
            'div', 'p', 'br', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'blockquote', 'strong', 'b', 'em', 'i', 'a',
        ];

        if ($node instanceof DOMElement) {
            $tagName = strtolower($node->tagName);

            if (! in_array($tagName, $allowedTags, true)) {
                $this->unwrapNode($node);

                return;
            }

            $allowedAttributes = $tagName === 'a' ? ['href'] : [];

            for ($i = $node->attributes->length - 1; $i >= 0; $i--) {
                $attribute = $node->attributes->item($i);

                if ($attribute === null) {
                    continue;
                }

                if (! in_array(strtolower($attribute->nodeName), $allowedAttributes, true)) {
                    $node->removeAttributeNode($attribute);
                }
            }

            if ($tagName === 'a') {
                $href = trim((string) $node->getAttribute('href'));

                if ($href === '' || ! filter_var($href, FILTER_VALIDATE_URL)) {
                    $this->unwrapNode($node);

                    return;
                }

                $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));

                if (! in_array($scheme, ['http', 'https', 'mailto'], true)) {
                    $this->unwrapNode($node);

                    return;
                }
            }
        }

        foreach (iterator_to_array($node->childNodes) as $childNode) {
            $this->sanitizeNode($childNode, $document);
        }
    }

    private function unwrapNode(DOMElement $node): void
    {
        $parent = $node->parentNode;

        if ($parent === null) {
            return;
        }

        while ($node->firstChild !== null) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    private function storeImages(Request $request): array
    {
        if (!$request->hasFile('images')) {
            return [];
        }

        return collect($request->file('images'))
            ->filter()
            ->map(fn ($file) => $file->store('materials', 'public'))
            ->values()
            ->all();
    }

    private function storeAttachment(Request $request): array
    {
        if (!$request->hasFile('file')) {
            return [];
        }

        $file = $request->file('file');

        return [
            'file_path' => $file->store('materials/files', 'public'),
            'file_original_name' => $file->getClientOriginalName(),
            'file_mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    private function deleteAttachment(Material $material): void
    {
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }
    }

    private function pdfImagePaths(Material $material): array
    {
        return collect($material->images ?? [])
            ->filter(fn (string $path) => Storage::disk('public')->exists($path))
            ->map(fn (string $path) => 'file:///' . str_replace('\\', '/', Storage::disk('public')->path($path)))
            ->values()
            ->all();
    }

    private function pdfFileName(Material $material): string
    {
        $name = Str::slug($material->title);

        if ($name === '') {
            $name = 'material-' . $material->id;
        }

        return $name . '.pdf';
    }
}
