<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 34px 38px;
        }

        body {
            color: #111827;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 13px;
            line-height: 1.55;
        }

        h1 {
            color: #0f172a;
            font-size: 25px;
            line-height: 1.2;
            margin: 0 0 12px;
        }

        h2,
        h3 {
            color: #0f172a;
            margin: 22px 0 8px;
        }

        p {
            margin: 0 0 10px;
        }

        ul,
        ol {
            margin: 0 0 12px 22px;
            padding: 0;
        }

        li {
            margin-bottom: 5px;
        }

        blockquote {
            border-left: 3px solid #2563eb;
            color: #475569;
            margin: 12px 0;
            padding: 4px 0 4px 12px;
        }

        a {
            color: #1d4ed8;
        }

        .meta {
            border-bottom: 1px solid #e5e7eb;
            color: #64748b;
            font-size: 11px;
            margin-bottom: 22px;
            padding-bottom: 14px;
        }

        .badge {
            background: #eff6ff;
            border-radius: 999px;
            color: #1d4ed8;
            display: inline-block;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 5px 9px;
            text-transform: uppercase;
        }

        .content {
            margin-top: 8px;
        }

        .images {
            page-break-inside: avoid;
        }

        .image {
            margin: 0 0 14px;
            max-height: 360px;
            max-width: 100%;
        }

        .attachment {
            border-top: 1px solid #e5e7eb;
            color: #475569;
            font-size: 11px;
            margin-top: 22px;
            padding-top: 12px;
        }
    </style>
</head>
<body>
    <div class="badge">Материал курса</div>

    <h1>{{ $material->title }}</h1>

    <div class="meta">
        Курс: {{ $material->course->title }}<br>
        Автор: {{ $material->author->name ?? 'Не указан' }}<br>
        Статус: {{ $material->is_published ? 'Опубликован' : 'Черновик' }}
    </div>

    @if(!empty($imagePaths))
        <div class="images">
            @foreach($imagePaths as $imagePath)
                <img class="image" src="{{ $imagePath }}" alt="">
            @endforeach
        </div>
    @endif

    <div class="content">
        {!! $material->rendered_content !!}
    </div>

    @if($material->has_file)
        <div class="attachment">
            Прикрепленный файл: {{ $material->file_original_name ?: basename($material->file_path) }}
            @if($material->formatted_file_size)
                ({{ $material->formatted_file_size }})
            @endif
        </div>
    @endif
</body>
</html>
