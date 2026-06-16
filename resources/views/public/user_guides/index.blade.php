@extends('layouts.public')
@section('title', __('app.user_guides'))

@section('content')
    @php
        $isEn = app()->getLocale() === 'en';
        $heroTitle = $isEn ? 'User Guide Library' : 'Perpustakaan Panduan Pengguna';
        $heroSubtitle = $isEn
            ? 'Browse PDF guides and video tutorials in one library.'
            : 'Temukan berbagai panduan pengguna dalam format PDF dan video tutorial dalam satu perpustakaan.';
        $searchTitle = $isEn ? 'Search Guides' : 'Cari Panduan';
        $searchPlaceholder = $isEn ? 'Search...' : 'Cari...';
        $searchCountTemplate = $isEn ? 'Showing :shown of :total guides' : 'Menampilkan :shown dari :total panduan';
        $total = method_exists($guides, 'total') ? (int) $guides->total() : (int) count($guides);
        $resultCountText = $isEn
            ? "Showing {$guides->count()} of {$total} guides"
            : "Menampilkan {$guides->count()} dari {$total} panduan";
        $heroImage = filled($heroBanner?->image_path ?? null)
            ? asset('storage/' . ltrim((string) $heroBanner->image_path, '/'))
            : asset('assets/images/blog/blog-details.png');
    @endphp

    <div class="page-user-guides-index page-services-index services-v2" id="userGuidesIndexPage">
        <header class="services-v2-hero" style="--services-hero-image:url('{{ $heroImage }}');">
            <div class="services-v2-hero__bg" aria-hidden="true"></div>
            <div class="services-v2-hero__veil" aria-hidden="true"></div>
            <div class="services-v2-hero__mesh" aria-hidden="true"></div>

            <div class="services-v2-hero__inner">
                <div class="services-v2-hero__copy" data-services-reveal>
                    <div class="services-v2-hero__kicker">{{ $isEn ? 'Guides' : 'Panduan' }}</div>
                    <h1 class="services-v2-hero__title">{{ $heroTitle }}</h1>
                    <p class="services-v2-hero__subtitle">{{ $heroSubtitle }}</p>
                </div>
            </div>

            <a href="#guides-catalog-section" class="services-v2-hero__scroll" data-scroll-to="#guides-catalog-section">
                {{ $isEn ? 'View guides' : 'Lihat panduan' }}
            </a>
        </header>

        <section class="services-v2-search" aria-label="{{ $isEn ? 'Search guides' : 'Cari panduan' }}" data-services-reveal>
            <div class="services-v2-search__head">
                <h2 class="services-v2-search__title">{{ $searchTitle }}</h2>
            </div>

            <form class="services-v2-search__form" role="search" aria-label="{{ $isEn ? 'Search guides' : 'Cari panduan' }}">
                <div class="services-v2-search__toolbar services-v2-search__toolbar--solo">
                    <div class="services-v2-search__field services-v2-search__field--keyword">
                        <label class="services-v2-search__label sr-only" for="guides-q">{{ $isEn ? 'Keyword' : 'Kata kunci' }}</label>
                        <div class="services-v2-search__input-wrap">
                            <input id="guides-q" class="services-v2-input" name="q" value="{{ $q }}"
                                placeholder="{{ $searchPlaceholder }}"
                                data-realtime-search-input
                                data-realtime-search-mode="filter"
                                data-realtime-search-scope=".guides-card-grid"
                                data-realtime-search-item-selector="[data-realtime-search-item]"
                                data-realtime-search-empty-selector="[data-realtime-search-empty]"
                                data-realtime-search-count-selector="[data-realtime-search-count]"
                                data-realtime-search-count-template="{{ $searchCountTemplate }}">
                            <button
                                type="button"
                                class="services-v2-search__clear {{ filled($q) ? '' : 'services-v2-search__clear--disabled' }}"
                                aria-label="{{ $isEn ? 'Reset search' : 'Reset pencarian' }}"
                                data-services-clear-search
                                data-reset-url="{{ route('user_guides.index') }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </section>

        <section class="services-v2-catalog" aria-label="{{ $isEn ? 'Guide result info' : 'Info hasil panduan' }}">
            <div class="services-v2-resultbar" data-services-reveal style="justify-content:flex-end;">
                <div class="services-v2-resultbar__count" data-realtime-search-count>{{ $resultCountText }}</div>
            </div>
        </section>

        <section id="guides-catalog-section" class="page-section" aria-label="{{ $isEn ? 'Guide list' : 'Daftar panduan' }}" data-services-reveal>
            <div class="card-grid guides-card-grid {{ $guides->count() === 1 ? 'card-grid--single' : '' }}" data-infinite-container>
                @if($guides->count() > 0)
                    @include('public.user_guides._items', compact('guides', 'isEn'))
                @else
                    <x-card class="card-item" data-services-reveal>
                        <div class="card-title">{{ $isEn ? 'No guides available' : 'Belum ada panduan tersedia' }}</div>
                        <p class="card-desc">
                            {{ $isEn ? 'No published guide can be shown for your account right now.' : 'Belum ada panduan terbit yang bisa ditampilkan untuk akun Anda saat ini.' }}
                        </p>
                    </x-card>
                @endif
                @if ($guides->count() > 0)
                    <x-card class="card-item hidden" data-realtime-search-empty>
                        <div class="card-title">{{ $isEn ? 'Guide not found' : 'Panduan tidak ditemukan' }}</div>
                        <p class="card-desc">
                            {{ $isEn ? 'Try another keyword for realtime search.' : 'Coba kata kunci lain untuk pencarian realtime.' }}
                        </p>
                    </x-card>
                @endif
            </div>
        </section>

        <div class="page-pagination" data-services-reveal data-infinite-pagination>
            {{ $guides->onEachSide(1)->links('components.public.pagination') }}
        </div>
        @if ($guides->count() > 0)
            <div class="public-infinite-load" data-infinite-list data-next-page-url="{{ $guides->nextPageUrl() ?? '' }}"
                data-end-text="{{ $isEn ? 'All guides have been loaded' : 'Semua panduan sudah dimuat' }}"
                data-load-more-text="{{ $isEn ? 'Load more' : 'Muat lebih banyak' }}"
                data-loading-text="{{ $isEn ? 'Loading...' : 'Memuat...' }}"
                data-error-text="{{ $isEn ? 'Failed to load. Try again.' : 'Gagal memuat. Coba lagi.' }}">
                <button type="button" class="public-infinite-load__button" @disabled(!$guides->hasMorePages()) data-infinite-load-more>
                    {{ $isEn ? 'Load more' : 'Muat lebih banyak' }}
                </button>
                <div class="public-infinite-load__status" data-infinite-status aria-live="polite"></div>
                <div class="public-infinite-load__sentinel" data-infinite-sentinel aria-hidden="true"></div>
            </div>
        @endif
    </div>
@endsection
