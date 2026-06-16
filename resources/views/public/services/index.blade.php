@extends('layouts.public')

@section('title', __('app.services'))

@section('content')
    @php
        $total = method_exists($services, 'total') ? (int) $services->total() : (int) count($services);
        $currentCategory = collect($categories ?? [])->firstWhere('id', (int) $category);
        $hasFilter = filled($q) || filled($category);
        $isEn = app()->getLocale() === 'en';

        $heroTitle = $isEn ? 'Find and Apply Services' : 'Temukan dan Ajukan Layanan';

        $heroSubtitle = $isEn
            ? 'Browse categories, review requirements, and submit requests online in minutes.'
            : 'Telusuri kategori, cek persyaratan, lalu ajukan layanan secara online dalam hitungan menit.';

        $heroImage = filled($heroBanner?->image_path ?? null)
            ? asset('storage/' . ltrim((string) $heroBanner->image_path, '/'))
            : asset('assets/images/blog/blog-details.png');

        $categoryLabel = $currentCategory
            ? ($isEn
                ? $currentCategory->name_en ?? $currentCategory->name_id
                : $currentCategory->name_id)
            : ($isEn
                ? 'All categories'
                : 'Semua kategori');
    @endphp

    <div class="page-services-index services-v2" id="servicesIndexPage">
        <header class="services-v2-hero" style="--services-hero-image:url('{{ $heroImage }}');">
            <div class="services-v2-hero__bg" aria-hidden="true"></div>
            <div class="services-v2-hero__veil" aria-hidden="true"></div>
            <div class="services-v2-hero__mesh" aria-hidden="true"></div>

            <div class="services-v2-hero__inner">
                <div class="services-v2-hero__copy" data-services-reveal>
                    <div class="services-v2-hero__kicker">{{ $isEn ? 'Services' : 'Layanan' }}</div>
                    <h1 class="services-v2-hero__title">{{ $heroTitle }}</h1>
                    <p class="services-v2-hero__subtitle">{{ $heroSubtitle }}</p>

                </div>
            </div>

            <a href="#services-catalog-section"
                class="services-v2-hero__scroll"
                data-scroll-to="#services-catalog-section">
                {{ $isEn ? 'Find services' : 'Cari layanan' }}
            </a>
        </header>

        <section id="services-search-panel" class="services-v2-search" aria-label="Filter layanan" data-services-reveal>
            <div class="services-v2-search__head">
                <h2 class="services-v2-search__title">{{ $isEn ? 'Search & Filter Services' : 'Cari dan Filter Layanan' }}
                </h2>
            </div>

            <form class="services-v2-search__form" method="GET" role="search" aria-label="Cari layanan">
                <div class="services-v2-search__toolbar">
                    <div class="services-v2-search__field services-v2-search__field--keyword">
                        <label class="services-v2-search__label sr-only" for="services-q">{{ $isEn ? 'Keyword' : 'Kata kunci' }}</label>
                        <div class="services-v2-search__input-wrap">
                            <input id="services-q" class="services-v2-input" name="q" value="{{ $q }}"
                                placeholder="{{ $isEn ? 'Search...' : 'Cari...' }}"
                                data-realtime-search-input data-realtime-search-mode="filter"
                                data-realtime-search-scope=".services-catalog-grid"
                                data-realtime-search-item-selector="[data-realtime-search-item]"
                                data-realtime-search-empty-selector="[data-realtime-search-empty]"
                                data-realtime-search-count-selector="[data-realtime-search-count]">
                            <button
                                type="button"
                                class="services-v2-search__clear {{ $hasFilter ? '' : 'services-v2-search__clear--disabled' }}"
                                aria-label="{{ $isEn ? 'Reset search and filters' : 'Reset pencarian dan filter' }}"
                                data-services-clear-search
                                data-reset-url="{{ route('services.index') }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <details class="services-v2-filter-menu">
                        <summary class="services-v2-filter-menu__toggle" aria-label="{{ $isEn ? 'Open filters' : 'Buka filter' }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 7h16M7 12h10M10 17h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                            </svg>
                        </summary>

                        <div class="services-v2-filter-menu__panel">
                            <div class="services-v2-search__field services-v2-search__field--category">
                                <label class="services-v2-search__label"
                                    for="services-category">{{ $isEn ? 'Category' : 'Kategori' }}</label>
                                <select id="services-category" name="category" class="services-v2-input" onchange="this.form.requestSubmit()">
                                    <option value="">{{ $isEn ? 'All services' : 'Semua layanan' }}</option>
                                    @foreach ($categories ?? [] as $cat)
                                        <option value="{{ $cat->id }}" @selected((string) $category === (string) $cat->id)>
                                            {{ $isEn ? $cat->name_en ?? $cat->name_id : $cat->name_id }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if ($hasFilter)
                                <a class="services-v2-filter-menu__reset" href="{{ route('services.index') }}">
                                    {{ $isEn ? 'Reset filters' : 'Reset filter' }}
                                </a>
                            @endif
                        </div>
                    </details>
                </div>
            </form>
        </section>

        <section id="services-catalog-section" class="services-v2-catalog" aria-label="Daftar layanan">
            <div class="services-v2-resultbar" data-services-reveal style="justify-content:flex-end;">
                <div class="services-v2-resultbar__count" data-realtime-search-count>
                    {{ $isEn ? "Showing {$services->count()} of {$total} services" : "Menampilkan {$services->count()} dari {$total} layanan" }}
                </div>
                @if ($hasFilter)
                    <div class="services-v2-resultbar__chips">
                        @if (filled($q))
                            <span class="services-v2-chip">{{ $isEn ? 'Query' : 'Pencarian' }}:
                                "{{ $q }}"</span>
                        @endif
                        @if ($currentCategory)
                            <span class="services-v2-chip">{{ $categoryLabel }}</span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="services-catalog-grid" data-infinite-container>
                @if($services->count() > 0)
                    @include('public.services._items', ['services' => $services, 'isEn' => $isEn, 'loopOffset' => 0])
                @else
                    <x-card class="services-v2-empty" data-services-reveal>
                        <div class="services-v2-empty__title">{{ $isEn ? 'Service not found' : 'Layanan tidak ditemukan' }}
                        </div>
                        <div class="services-v2-empty__text">
                            {{ $isEn ? 'Try another keyword or choose a different category.' : 'Coba ubah kata kunci atau pilih kategori lain.' }}
                        </div>
                        @if ($hasFilter)
                            <div class="services-v2-empty__action">
                                <x-button href="{{ route('services.index') }}" variant="secondary">
                                    {{ $isEn ? 'Show all services' : 'Lihat semua layanan' }}
                                </x-button>
                            </div>
                        @endif
                    </x-card>
                @endif
                @if ($services->count() > 0)
                    <x-card class="services-v2-empty hidden" data-realtime-search-empty>
                        <div class="services-v2-empty__title">{{ $isEn ? 'Service not found' : 'Layanan tidak ditemukan' }}
                        </div>
                        <div class="services-v2-empty__text">
                            {{ $isEn ? 'Try another keyword for realtime search.' : 'Coba kata kunci lain untuk pencarian realtime.' }}
                        </div>
                    </x-card>
                @endif
            </div>
        </section>

        <div class="page-pagination services-v2-pagination" data-services-reveal data-infinite-pagination>
            {{ $services->onEachSide(1)->links('components.public.pagination') }}
        </div>
        @if ($services->count() > 0)
            <div class="public-infinite-load" data-infinite-list data-next-page-url="{{ $services->nextPageUrl() ?? '' }}"
                data-end-text="{{ $isEn ? 'All services have been loaded' : 'Semua layanan sudah dimuat' }}"
                data-load-more-text="{{ $isEn ? 'Load more' : 'Muat lebih banyak' }}"
                data-loading-text="{{ $isEn ? 'Loading...' : 'Memuat...' }}"
                data-error-text="{{ $isEn ? 'Failed to load. Try again.' : 'Gagal memuat. Coba lagi.' }}">
                <button type="button" class="public-infinite-load__button" @disabled(!$services->hasMorePages()) data-infinite-load-more>
                    {{ $isEn ? 'Load more' : 'Muat lebih banyak' }}
                </button>
                <div class="public-infinite-load__status" data-infinite-status aria-live="polite"></div>
                <div class="public-infinite-load__sentinel" data-infinite-sentinel aria-hidden="true"></div>
            </div>
        @endif
    </div>

@endsection

