@extends('layouts.public')

@section('title', app()->getLocale() === 'en' ? ($guide->title_en ?: $guide->title_id) : $guide->title_id)

@section('content')
    @php
        $isEn = app()->getLocale() === 'en';
        $title = $isEn ? ($guide->title_en ?: $guide->title_id) : $guide->title_id;
        $summary = $isEn ? ($guide->summary_en ?: $guide->summary_id) : $guide->summary_id;
        $summary = filled($summary)
            ? $summary
            : ($guide->isVideo()
                ? ($isEn ? 'Video tutorial guide.' : 'Panduan dalam bentuk video tutorial.')
                : ($isEn ? 'PDF user guide document.' : 'Dokumen panduan pengguna dalam format PDF.'));
        $roles = $guide->roles->pluck('name')->values()->all();
        $publishedDate = optional($guide->published_at)->format('d M Y');
        $heroImage = filled($heroBanner?->image_path ?? null)
            ? asset('storage/' . ltrim((string) $heroBanner->image_path, '/'))
            : asset('assets/images/blog/blog-details.png');
    @endphp

    <div class="page-user-guide-show page-services-show page-services-index services-v2" id="userGuideShowPage">
        <header class="services-v2-hero services-v2-hero--show" style="--services-hero-image:url('{{ $heroImage }}');">
            <div class="services-v2-hero__bg" aria-hidden="true"></div>
            <div class="services-v2-hero__veil" aria-hidden="true"></div>
            <div class="services-v2-hero__mesh" aria-hidden="true"></div>

            <div class="services-v2-hero__inner">
                <div class="services-v2-hero__copy">
                    <div class="services-v2-hero__kicker">{{ $isEn ? 'User Guide Detail' : 'Detail Panduan Pengguna' }}</div>
                    <h1 class="services-v2-hero__title">{{ $title }}</h1>
                    <p class="services-v2-hero__subtitle">{{ $summary }}</p>
                </div>
            </div>

            <a href="#user-guide-content"
                class="services-v2-hero__scroll"
                data-scroll-to="#user-guide-content">
                {{ $isEn ? 'View guide' : 'Lihat panduan' }}
            </a>
        </header>

        <section id="user-guide-content" class="service-show-main" aria-label="{{ $isEn ? 'Guide details' : 'Detail panduan' }}">
            <div class="service-show-main__back page-back" data-service-show-reveal>
                <a class="back-link" href="{{ route('user_guides.index') }}">
                    <span class="service-show-back-link__icon" aria-hidden="true">&larr;</span>
                    <span class="service-show-back-link__text">{{ $isEn ? 'Back to guides' : 'Kembali ke panduan' }}</span>
                </a>
            </div>

            <section class="content-card service-show-shell content-panel content-panel--doc-preview" data-service-show-reveal aria-label="{{ $guide->isVideo() ? ($isEn ? 'Video preview' : 'Preview video') : ($isEn ? 'PDF preview' : 'Preview PDF') }}">
                <div class="service-doc-preview__top">
                    <div>
                        <div class="content-panel__title">{{ $guide->isVideo() ? ($isEn ? 'Guide Preview (Video)' : 'Preview Panduan (Video)') : ($isEn ? 'Guide Preview (PDF)' : 'Preview Panduan (PDF)') }}</div>
                        <div class="service-doc-preview__hint">
                            {{ $isEn ? 'Publication date' : 'Tanggal publikasi' }}: <strong>{{ $publishedDate ?: '-' }}</strong>
                        </div>
                    </div>
                    <div class="service-show-head__chips">
                        <span class="service-show-chip">{{ $guide->is_public ? ($isEn ? 'Public' : 'Umum') : ($isEn ? 'Role-based' : 'Berdasarkan role') }}</span>
                        @if (!empty($roles))
                            <span class="service-show-chip">{{ implode(', ', $roles) }}</span>
                        @endif
                    </div>
                </div>

                @if ($guide->isVideo())
                    <div class="service-doc-preview__wrap service-doc-preview__wrap--video" role="region" aria-label="Video preview area">
                        @if ($videoEmbedUrl)
                            <iframe
                                class="service-doc-preview__frame service-doc-preview__frame--video"
                                src="{{ $videoEmbedUrl }}"
                                title="{{ $title }}"
                                loading="lazy"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin"
                                allowfullscreen
                            ></iframe>
                        @else
                            <div class="service-doc-preview__fallback service-doc-preview__fallback--boxed">
                                {{ $isEn ? 'Video preview is unavailable for this link.' : 'Preview video tidak tersedia untuk tautan ini.' }}
                            </div>
                        @endif
                    </div>

                    <div class="user-guide-actions">
                        <x-button href="{{ $videoUrl }}" variant="secondary" target="_blank">
                            {{ $isEn ? 'Open on YouTube' : 'Buka di YouTube' }}
                        </x-button>
                    </div>

                    <div class="service-doc-preview__fallback">
                        {{ $isEn ? 'If the embedded player does not appear, use the Open on YouTube button.' : 'Jika player video tidak tampil, gunakan tombol Buka di YouTube.' }}
                    </div>
                @else
                    <div class="service-doc-preview__wrap" role="region" aria-label="PDF preview area">
                        <iframe class="service-doc-preview__frame" src="{{ route('user_guides.file', $guide->slug) }}" title="{{ $title }}" loading="lazy"></iframe>
                    </div>

                    <div class="user-guide-actions">
                        <x-button href="{{ route('user_guides.file', $guide->slug) }}" variant="secondary" target="_blank">
                            {{ $isEn ? 'Open in new tab' : 'Buka tab baru' }}
                        </x-button>
                        <x-button href="{{ route('user_guides.download', $guide->slug) }}">
                            {{ $isEn ? 'Download PDF' : 'Unduh PDF' }}
                        </x-button>
                    </div>

                    <div class="service-doc-preview__fallback">
                        {{ $isEn ? 'If PDF preview does not appear, use the Open in new tab button.' : 'Jika preview PDF tidak tampil, gunakan tombol Buka tab baru.' }}
                    </div>
                @endif
            </section>
        </section>
    </div>
@endsection
