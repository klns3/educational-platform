@php
    $material = $material ?? null;
    $rawContent = old('content', $material?->content ?? '');
    $initialEditorHtml = old('content') !== null
        ? nl2br(e($rawContent))
        : ($material ? (string) $material->rendered_content : '');

    $labels = [
        'title' => 'Название',
        'content' => 'Содержимое материала',
        'formatting' => 'Форматирование',
        'toolbar_hint' => 'Текст редактируется визуально. При сохранении материал останется отформатированным с заголовками, списками, ссылками и цитатами.',
        'images' => 'Изображения',
        'images_hint' => 'JPG, PNG, WEBP, GIF. До 4 МБ каждое. Можно загрузить несколько файлов.',
        'existing_images' => 'Текущие изображения',
        'remove_image' => 'Удалить',
        'new_images' => 'Новые изображения',
        'publish' => 'Опубликовать материал',
        'editor_placeholder' => 'Введите текст материала...',
    ];

    $toolbarButtons = [
        ['label' => 'Заголовок', 'action' => 'h2', 'hint' => 'Сделать заголовок раздела'],
        ['label' => 'Подзаголовок', 'action' => 'h3', 'hint' => 'Сделать подзаголовок'],
        ['label' => 'Жирный', 'action' => 'bold', 'hint' => 'Выделить текст жирным'],
        ['label' => 'Курсив', 'action' => 'italic', 'hint' => 'Выделить текст курсивом'],
        ['label' => 'Список', 'action' => 'unordered', 'hint' => 'Маркированный список'],
        ['label' => 'Нумерация', 'action' => 'ordered', 'hint' => 'Нумерованный список'],
        ['label' => 'Цитата', 'action' => 'quote', 'hint' => 'Оформить как цитату'],
        ['label' => 'Ссылка', 'action' => 'link', 'hint' => 'Добавить ссылку'],
    ];
@endphp

<style>
    .material-editor {
        min-height: 22rem;
        white-space: normal;
        overflow-wrap: anywhere;
    }

    .material-editor:empty::before {
        content: attr(data-placeholder);
        color: #64748b;
    }

    .material-editor h1,
    .material-editor h2,
    .material-editor h3 {
        color: #f8fafc;
        font-weight: 800;
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
    }

    .material-editor h1 { font-size: 1.875rem; }
    .material-editor h2 { font-size: 1.5rem; }
    .material-editor h3 { font-size: 1.25rem; }

    .material-editor p,
    .material-editor li,
    .material-editor blockquote {
        color: #e2e8f0;
        line-height: 1.8;
    }

    .material-editor p,
    .material-editor ul,
    .material-editor ol,
    .material-editor blockquote {
        margin-bottom: 1rem;
    }

    .material-editor ul,
    .material-editor ol {
        padding-left: 1.5rem;
    }

    .material-editor ul { list-style: disc; }
    .material-editor ol { list-style: decimal; }

    .material-editor a {
        color: #93c5fd;
        text-decoration: underline;
    }

    .material-editor blockquote {
        border-left: 4px solid #3b82f6;
        padding-left: 1rem;
        color: #94a3b8;
    }
</style>

<form id="materialForm"
      action="{{ $action }}"
      method="POST"
      enctype="multipart/form-data"
      class="overflow-hidden rounded-[1.7rem] border border-white bg-white shadow-sm shadow-slate-200/70 dark:border-white/10 dark:bg-white/[0.04] dark:shadow-none">
    @csrf

    @if(($method ?? 'POST') !== 'POST')
        @method($method)
    @endif

    <div class="border-b border-slate-100 px-6 py-5 dark:border-white/10">
        <h2 class="text-xl font-black text-slate-950 dark:text-white">Основная информация</h2>
    </div>

    <div class="grid gap-6 p-6">
        <div>
            <label class="mb-2 block text-sm font-black text-slate-700 dark:text-slate-200">
                {{ $labels['title'] }}
            </label>

            <input type="text"
                   name="title"
                   value="{{ old('title', $material?->title) }}"
                   class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-950 outline-none transition focus:border-blue-400 focus:bg-white focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/[0.04] dark:text-white dark:focus:border-blue-400 dark:focus:bg-white/[0.07]">

            @error('title')
                <p class="mt-2 text-sm font-bold text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="mb-3 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <label class="block text-sm font-black text-slate-700 dark:text-slate-200">
                    {{ $labels['content'] }}
                </label>

                <div class="flex flex-wrap gap-2">
                    @foreach($toolbarButtons as $button)
                        <button type="button"
                                data-editor-action="{{ $button['action'] }}"
                                title="{{ $button['hint'] }}"
                                class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-black text-slate-600 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300 dark:hover:border-blue-400 dark:hover:text-blue-300">
                            {{ $button['label'] }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div id="materialEditor"
                 contenteditable="true"
                 data-placeholder="{{ $labels['editor_placeholder'] }}"
                 class="material-editor w-full rounded-[1.4rem] border border-slate-200 bg-slate-950 px-5 py-4 text-white outline-none transition focus:border-blue-400 focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-[#07111f]">{!! $initialEditorHtml !!}</div>

            <textarea id="materialContent"
                      name="content"
                      required
                      class="hidden">{{ $rawContent }}</textarea>


            @error('content')
                <p class="mt-2 text-sm font-bold text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-5 dark:border-white/10 dark:bg-white/[0.03]">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-black text-slate-700 dark:text-slate-200">
                        {{ $labels['images'] }}
                    </p>
                    <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                        {{ $labels['images_hint'] }}
                    </p>
                </div>

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-violet-50 text-xl dark:bg-violet-500/15">
                    🖼
                </div>
            </div>

            <input id="materialImages"
                   type="file"
                   name="images[]"
                   accept=".jpg,.jpeg,.png,.webp,.gif"
                   multiple
                   class="block w-full rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm font-bold text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-black file:text-white hover:border-blue-400 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300 dark:file:bg-blue-500">

            @error('images')
                <p class="mt-2 text-sm font-bold text-red-500">{{ $message }}</p>
            @enderror

            @error('images.*')
                <p class="mt-2 text-sm font-bold text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-[1.4rem] border border-slate-200 bg-slate-50 p-5 dark:border-white/10 dark:bg-white/[0.03]">
            <div class="mb-4 flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-black text-slate-700 dark:text-slate-200">
                        Файл материала
                    </p>
                    <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                        Можно прикрепить один файл до 100 МБ.
                    </p>
                </div>

                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 dark:bg-blue-500/15 dark:text-blue-300">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7l-5-5Zm0 0v5h5M9 15h6M9 18h4"/>
                    </svg>
                </div>
            </div>

            @if($material?->has_file)
                <div class="mb-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 dark:border-white/10 dark:bg-white/[0.04]">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-slate-800 dark:text-slate-100">
                                {{ $material->file_original_name }}
                            </p>
                            @if($material->formatted_file_size)
                                <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    {{ $material->formatted_file_size }}
                                </p>
                            @endif
                        </div>

                        <label class="inline-flex items-center gap-2 text-sm font-bold text-red-600 dark:text-red-300">
                            <input type="checkbox"
                                   name="remove_file"
                                   value="1"
                                   class="rounded border-slate-300 bg-white text-red-500 focus:ring-red-500 dark:border-white/10 dark:bg-slate-900">
                            Удалить файл
                        </label>
                    </div>
                </div>
            @endif

            <input id="materialFile"
                   type="file"
                   name="file"
                   class="block w-full rounded-2xl border border-dashed border-slate-300 bg-white px-4 py-4 text-sm font-bold text-slate-500 file:mr-4 file:rounded-xl file:border-0 file:bg-blue-600 file:px-4 file:py-2 file:text-sm file:font-black file:text-white hover:border-blue-400 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300 dark:file:bg-blue-500">

            @error('file')
                <p class="mt-2 text-sm font-bold text-red-500">{{ $message }}</p>
            @enderror
        </div>

        @if($material && !empty($material->image_urls))
            <div>
                <p class="mb-3 text-sm font-black text-slate-700 dark:text-slate-200">
                    {{ $labels['existing_images'] }}
                </p>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($material->image_urls as $index => $imageUrl)
                        <label class="overflow-hidden rounded-[1.4rem] border border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/[0.03]">
                            <img src="{{ $imageUrl }}"
                                 alt="material image"
                                 class="h-44 w-full object-cover">

                            <div class="flex items-center justify-between gap-3 px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-200">
                                <span>{{ $labels['remove_image'] }}</span>

                                <input type="checkbox"
                                       name="remove_images[]"
                                       value="{{ $material->images[$index] }}"
                                       class="rounded border-slate-300 bg-white text-red-500 focus:ring-red-500 dark:border-white/10 dark:bg-slate-900">
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div id="newImagesPreviewWrap" class="hidden">
            <p class="mb-3 text-sm font-black text-slate-700 dark:text-slate-200">
                {{ $labels['new_images'] }}
            </p>

            <div id="newImagePreviews" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3"></div>
        </div>

        <div class="mb-8">
            <label class="flex items-center justify-between gap-4 rounded-[1.4rem] border border-emerald-500/20 bg-emerald-500/10 px-5 py-4 transition hover:bg-emerald-500/15">
                <div class="flex items-center gap-3">
                    <input type="checkbox"
                        name="is_published"
                        value="1"
                        @checked(old('is_published', $material?->is_published ?? true))
                        class="h-5 w-5 rounded-lg border-emerald-400 bg-slate-900 text-emerald-500 focus:ring-emerald-500">

                    <span class="text-sm font-bold text-emerald-300">
                        {{ $labels['publish'] }}
                    </span>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20 text-lg">
                    ✔
                </div>
            </label>
        </div>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between dark:border-white/10">
        <a href="{{ $cancelUrl }}"
           class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 transition hover:border-blue-400 hover:text-blue-600 dark:border-white/10 dark:text-slate-200">
            {{ $cancelLabel }}
        </a>

        <button class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-6 py-3 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-400">
            {{ $submitLabel }}
        </button>
    </div>
</form>

<script>
    (function () {
        const form = document.getElementById('materialForm');
        const editor = document.getElementById('materialEditor');
        const hiddenInput = document.getElementById('materialContent');
        const buttons = document.querySelectorAll('[data-editor-action]');
        const imageInput = document.getElementById('materialImages');
        const previewWrap = document.getElementById('newImagesPreviewWrap');
        const previewGrid = document.getElementById('newImagePreviews');

        const syncEditorContent = () => {
            if (!editor || !hiddenInput) {
                return;
            }

            const normalizedHtml = editor.innerHTML
                .replace(/<div><br><\/div>/gi, '')
                .replace(/&nbsp;/g, ' ')
                .trim();

            hiddenInput.value = normalizedHtml;
        };

        const applyFormat = (action) => {
            if (!editor) {
                return;
            }

            editor.focus();

            switch (action) {
                case 'bold':
                    document.execCommand('bold');
                    break;
                case 'italic':
                    document.execCommand('italic');
                    break;
                case 'unordered':
                    document.execCommand('insertUnorderedList');
                    break;
                case 'ordered':
                    document.execCommand('insertOrderedList');
                    break;
                case 'quote':
                    document.execCommand('formatBlock', false, 'blockquote');
                    break;
                case 'h2':
                    document.execCommand('formatBlock', false, 'h2');
                    break;
                case 'h3':
                    document.execCommand('formatBlock', false, 'h3');
                    break;
                case 'link': {
                    const selection = window.getSelection();
                    const selectedText = selection ? selection.toString().trim() : '';
                    const url = window.prompt('Введите ссылку', 'https://');

                    if (!url) {
                        return;
                    }

                    if (!selectedText) {
                        document.execCommand('insertHTML', false, '<a href="' + url + '">' + url + '</a>');
                    } else {
                        document.execCommand('createLink', false, url);
                    }

                    break;
                }
                default:
                    break;
            }

            syncEditorContent();
        };

        if (editor && hiddenInput) {
            editor.addEventListener('input', syncEditorContent);
            editor.addEventListener('blur', syncEditorContent);
            syncEditorContent();
        }

        buttons.forEach((button) => {
            button.addEventListener('click', () => {
                applyFormat(button.dataset.editorAction || '');
            });
        });

        if (form) {
            form.addEventListener('submit', syncEditorContent);
        }

        if (!imageInput || !previewWrap || !previewGrid) {
            return;
        }

        imageInput.addEventListener('change', () => {
            previewGrid.innerHTML = '';
            const files = Array.from(imageInput.files || []);

            previewWrap.classList.toggle('hidden', files.length === 0);

            files.forEach((file) => {
                const card = document.createElement('div');
                card.className = 'overflow-hidden rounded-[1.4rem] border border-slate-200 bg-slate-50 dark:border-white/10 dark:bg-white/[0.03]';

                const image = document.createElement('img');
                image.src = URL.createObjectURL(file);
                image.alt = file.name;
                image.className = 'h-44 w-full object-cover';
                image.onload = () => URL.revokeObjectURL(image.src);

                const meta = document.createElement('div');
                meta.className = 'px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-300';
                meta.textContent = file.name;

                card.appendChild(image);
                card.appendChild(meta);
                previewGrid.appendChild(card);
            });
        });
    })();
</script>
