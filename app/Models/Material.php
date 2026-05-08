<?php

namespace App\Models;

use DOMDocument;
use DOMElement;
use DOMNode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Material extends Model
{
    protected $fillable = [
        'course_id',
        'author_id',
        'title',
        'content',
        'images',
        'file_path',
        'file_original_name',
        'file_mime_type',
        'file_size',
        'is_published',
    ];

    protected $casts = [
        'images' => 'array',
        'is_published' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getImageUrlsAttribute(): array
    {
        return collect($this->images ?? [])
            ->filter()
            ->map(fn (string $path) => Storage::url($path))
            ->values()
            ->all();
    }

    public function getHasFileAttribute(): bool
    {
        return filled($this->file_path);
    }

    public function getFormattedFileSizeAttribute(): ?string
    {
        if ($this->file_size === null) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = (float) $this->file_size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, $unitIndex === 0 ? 0 : 1) . ' ' . $units[$unitIndex];
    }

    public function getRenderedContentAttribute(): HtmlString
    {
        $content = $this->content ?? '';

        if ($this->looksLikeHtml($content)) {
            return new HtmlString($this->sanitizeHtml($content));
        }

        return new HtmlString((string) Str::markdown($content, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]));
    }

    public function getExcerptAttribute(): string
    {
        return (string) Str::of(strip_tags((string) $this->rendered_content))
            ->squish()
            ->limit(160);
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

        $this->sanitizeNode($root);

        $html = '';

        foreach ($root->childNodes as $childNode) {
            $html .= $document->saveHTML($childNode);
        }

        return trim($html);
    }

    private function sanitizeNode(DOMNode $node): void
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
            $this->sanitizeNode($childNode);
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
}
