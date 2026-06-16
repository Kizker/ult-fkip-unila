@foreach($posts as $p)
    @php
        $postTitle = $isEn ? $p->title_en ?? $p->title_id : $p->title_id;
        $postHtml = $isEn ? $p->content_html_en ?? $p->content_html_id : $p->content_html_id;
        $postDesc = filled($postHtml)
            ? $excerpt($postHtml, 130)
            : ($isEn
                ? 'Official announcement from ULT FKIP Unila.'
                : 'Pengumuman resmi dari ULT FKIP Unila.');
        $postImage = filled($p->image_path ?? null)
            ? asset('storage/' . ltrim((string) $p->image_path, '/'))
            : $normalizeImage($extractImage($postHtml));
        $postSearchText = trim(
            implode(
                ' ',
                array_filter([
                    $p->title_id,
                    $p->title_en,
                    $p->slug,
                    $excerpt($p->content_html_id, 220),
                    $excerpt($p->content_html_en, 220),
                ]),
            ),
        );
    @endphp
    <x-card class="card-item services-catalog-card" data-services-reveal data-realtime-search-item
        data-realtime-search-text="{{ $postSearchText }}">
        <div class="card-cover">
            @if ($postImage)
                <img class="card-cover__img" src="{{ $postImage }}" alt="{{ $postTitle }}"
                    loading="lazy">
            @else
                <span class="card-cover__fallback"
                    aria-hidden="true">{{ $isEn ? 'Announcement' : 'Pengumuman' }}</span>
            @endif
        </div>
        <div class="services-catalog-card__head">
            <span
                class="services-catalog-card__category">{{ $isEn ? 'Announcement' : 'Pengumuman' }}</span>
            <span
                class="services-catalog-card__index">{{ optional($p->published_at)->format('d M Y') }}</span>
        </div>
        <h3 class="services-catalog-card__title">
            <a href="{{ route('announcements.show', $p) }}">{{ $postTitle }}</a>
        </h3>
        <p class="services-catalog-card__desc">{{ $postDesc }}</p>
        <div class="services-catalog-card__actions">
            <x-button variant="secondary" class="services-catalog-card__detail-btn"
                href="{{ route('announcements.show', $p) }}">
                {{ $isEn ? 'Read' : 'Baca' }}
            </x-button>
        </div>
    </x-card>
@endforeach
