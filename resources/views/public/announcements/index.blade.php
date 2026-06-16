@extends('layouts.public')
@section('title', __('app.announcements'))
@section('content')
    @php
        $isEn = app()->getLocale() === 'en';
        $heroTitle = $isEn ? 'Announcements and Important Updates' : 'Pengumuman dan Informasi Penting';
        $heroSubtitle = $isEn
            ? 'Official notices from ULT FKIP Unila for students, lecturers, and staff.'
            : 'Pengumuman resmi dari ULT FKIP Unila untuk mahasiswa, dosen, dan tenaga kependidikan.';
        $searchTitle = $isEn ? 'Search Announcements' : 'Cari Pengumuman';
        $searchPlaceholder = $isEn ? 'Search...' : 'Cari...';
        $total = method_exists($posts, 'total') ? (int) $posts->total() : (int) count($posts);
        $resultCountText = $isEn
            ? "Showing {$posts->count()} of {$total} announcements"
            : "Menampilkan {$posts->count()} dari {$total} pengumuman";
        $searchCountTemplate = $isEn
            ? 'Showing :shown of :total announcements'
            : 'Menampilkan :shown dari :total pengumuman';
        $heroImage = filled($heroBanner?->image_path ?? null)
            ? asset('storage/' . ltrim((string) $heroBanner->image_path, '/'))
            : asset('assets/images/blog/blog-details.png');

        $stripContent = static function (?string $html): string {
            $plain = strip_tags((string) ($html ?? ''));
            $plain = preg_replace('/\s+/u', ' ', $plain);
            return trim((string) $plain);
        };
        $excerpt = static function (?string $html, int $limit = 140) use ($stripContent): string {
            return \Illuminate\Support\Str::limit($stripContent($html), $limit, '...');
        };
        $extractImage = static function (?string $html): ?string {
            if (!filled($html)) {
                return null;
            }

            return preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches) === 1 ? $matches[1] : null;
        };
        $normalizeImage = static function (?string $src): ?string {
            if (!filled($src)) {
                return null;
            }

            if (\Illuminate\Support\Str::startsWith($src, ['http://', 'https://', 'data:'])) {
                return $src;
            }

            return asset(ltrim((string) $src, '/'));
        };
    @endphp
    <div class="page-announcements-index page-services-index services-v2" id="servicesIndexPage">
        <header class="services-v2-hero" style="--services-hero-image:url('{{ $heroImage }}');">
            <div class="services-v2-hero__bg" aria-hidden="true"></div>
            <div class="services-v2-hero__veil" aria-hidden="true"></div>
            <div class="services-v2-hero__mesh" aria-hidden="true"></div>

            <div class="services-v2-hero__inner">
                <div class="services-v2-hero__copy" data-services-reveal>
                    <div class="services-v2-hero__kicker">{{ $isEn ? 'Announcements' : 'Pengumuman' }}</div>
                    <h1 class="services-v2-hero__title">{{ $heroTitle }}</h1>
                    <p class="services-v2-hero__subtitle">{{ $heroSubtitle }}</p>
                </div>
            </div>

            <a href="#announcements-catalog-section" class="services-v2-hero__scroll"
                data-scroll-to="#announcements-catalog-section">
                {{ $isEn ? 'View announcements' : 'Lihat pengumuman' }}
            </a>
        </header>

        <section id="announcements-search-panel" class="services-v2-search"
            aria-label="{{ $isEn ? 'Search announcements' : 'Cari pengumuman' }}" data-services-reveal>
            <div class="services-v2-search__head">
                <h2 class="services-v2-search__title">{{ $searchTitle }}</h2>
            </div>

            <form class="services-v2-search__form" role="search"
                aria-label="{{ $isEn ? 'Search announcements' : 'Cari pengumuman' }}">
                <div class="services-v2-search__toolbar services-v2-search__toolbar--solo">
                    <div class="services-v2-search__field services-v2-search__field--keyword">
                        <label class="services-v2-search__label sr-only"
                            for="announcements-q">{{ $isEn ? 'Keyword' : 'Kata kunci' }}</label>
                        <div class="services-v2-search__input-wrap">
                            <input id="announcements-q" class="services-v2-input" name="q" value=""
                                placeholder="{{ $searchPlaceholder }}" data-realtime-search-input
                                data-realtime-search-mode="filter" data-realtime-search-scope=".announcements-card-grid"
                                data-realtime-search-item-selector="[data-realtime-search-item]"
                                data-realtime-search-empty-selector="[data-realtime-search-empty]"
                                data-realtime-search-count-selector="[data-realtime-search-count]"
                                data-realtime-search-count-template="{{ $searchCountTemplate }}">
                            <button
                                type="button"
                                class="services-v2-search__clear services-v2-search__clear--disabled"
                                aria-label="{{ $isEn ? 'Reset search' : 'Reset pencarian' }}"
                                data-announcements-clear-search>
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </section>

        <section class="services-v2-catalog" aria-label="{{ $isEn ? 'Announcement result info' : 'Info hasil pengumuman' }}">
            <div class="services-v2-resultbar" data-services-reveal style="justify-content:flex-end;">
                <div class="services-v2-resultbar__count" data-realtime-search-count>{{ $resultCountText }}</div>
            </div>
        </section>

        <section id="announcements-catalog-section" class="page-section"
            aria-label="{{ $isEn ? 'Announcement list' : 'Daftar pengumuman' }}" data-services-reveal>
            <div class="card-grid announcements-card-grid {{ $posts->count() === 1 ? 'card-grid--single' : '' }}" data-infinite-container>
                @if($posts->count() > 0)
                    @include('public.announcements._items', compact('posts', 'isEn', 'excerpt', 'extractImage', 'normalizeImage'))
                @else
                    <x-card class="card-item" data-services-reveal>
                        <div class="card-title">{{ $isEn ? 'No announcements yet' : 'Belum ada pengumuman' }}</div>
                        <p class="card-desc">
                            {{ $isEn ? 'There are no published announcements at the moment.' : 'Saat ini belum ada pengumuman yang dipublikasikan.' }}
                        </p>
                    </x-card>
                @endif
                @if ($posts->count() > 0)
                    <x-card class="card-item hidden" data-realtime-search-empty>
                        <div class="card-title">{{ $isEn ? 'Announcement not found' : 'Pengumuman tidak ditemukan' }}</div>
                        <p class="card-desc">
                            {{ $isEn ? 'Try another keyword for realtime search.' : 'Coba kata kunci lain untuk pencarian realtime.' }}
                        </p>
                    </x-card>
                @endif
            </div>
        </section>

        <div class="page-pagination" data-services-reveal data-infinite-pagination>
            {{ $posts->onEachSide(1)->links('components.public.pagination') }}</div>
        @if ($posts->count() > 0)
            <div class="public-infinite-load" data-infinite-list data-next-page-url="{{ $posts->nextPageUrl() ?? '' }}"
                data-end-text="{{ $isEn ? 'All announcements have been loaded' : 'Semua pengumuman sudah dimuat' }}"
                data-load-more-text="{{ $isEn ? 'Load more' : 'Muat lebih banyak' }}"
                data-loading-text="{{ $isEn ? 'Loading...' : 'Memuat...' }}"
                data-error-text="{{ $isEn ? 'Failed to load. Try again.' : 'Gagal memuat. Coba lagi.' }}">
                <button type="button" class="public-infinite-load__button" @disabled(!$posts->hasMorePages()) data-infinite-load-more>
                    {{ $isEn ? 'Load more' : 'Muat lebih banyak' }}
                </button>
                <div class="public-infinite-load__status" data-infinite-status aria-live="polite"></div>
                <div class="public-infinite-load__sentinel" data-infinite-sentinel aria-hidden="true"></div>
            </div>
        @endif
    </div>

@endsection
