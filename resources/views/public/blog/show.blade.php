@extends('layouts.public')

@section('title', app()->getLocale() === 'en' ? ($post->title_en ?? $post->title_id) : $post->title_id)

@section('content')
    @php
        $isEn = app()->getLocale() === 'en';
        $postTitle = $isEn ? ($post->title_en ?? $post->title_id) : $post->title_id;
        $postHtml = $isEn ? ($post->content_html_en ?? $post->content_html_id) : $post->content_html_id;
        $heroImage = filled($heroBanner?->image_path ?? null)
            ? asset('storage/' . ltrim((string) $heroBanner->image_path, '/'))
            : asset('assets/images/blog/blog-details.png');
        $postImage = filled($post->image_path ?? null)
            ? asset('storage/' . ltrim((string) $post->image_path, '/'))
            : null;
        $publishedDate = optional($post->published_at)->format('d M Y');
    @endphp

    <div class="page-blog-show page-services-show page-services-index services-v2" id="blogShowPage">
        <header class="services-v2-hero services-v2-hero--show" style="--services-hero-image:url('{{ $heroImage }}');">
            <div class="services-v2-hero__bg" aria-hidden="true"></div>
            <div class="services-v2-hero__veil" aria-hidden="true"></div>
            <div class="services-v2-hero__mesh" aria-hidden="true"></div>

            <div class="services-v2-hero__inner">
                <div class="services-v2-hero__copy">
                    <div class="services-v2-hero__kicker">{{ $isEn ? 'Blog Detail' : 'Detail Blog' }}</div>
                    <h1 class="services-v2-hero__title">{{ $postTitle }}</h1>
                </div>
            </div>

            <a href="#blog-show-content" class="services-v2-hero__scroll" data-scroll-to="#blog-show-content">
                {{ $isEn ? 'View blog' : 'Lihat blog' }}
            </a>
        </header>

        <section id="blog-show-content" class="service-show-main"
            aria-label="{{ $isEn ? 'Blog details' : 'Detail blog' }}">
            <div class="service-show-main__back page-back" data-service-show-reveal>
                <a class="back-link" href="{{ route('blog.index') }}">
                    <span class="service-show-back-link__icon" aria-hidden="true">&larr;</span>
                    <span class="service-show-back-link__text">{{ $isEn ? 'Back to blog' : 'Kembali ke blog' }}</span>
                </a>
            </div>

            <x-card class="content-card service-show-shell" data-service-show-reveal>
                <section class="content-panel content-panel--doc-preview" data-service-show-reveal
                    aria-label="{{ $isEn ? 'Blog content' : 'Isi blog' }}">
                    <div class="service-doc-preview__top">
                        <div>
                            <div class="content-panel__title">{{ $isEn ? 'Blog Content' : 'Isi Blog' }}</div>
                            <div class="service-doc-preview__hint">
                                {{ $isEn ? 'Publication date' : 'Tanggal publikasi' }}:
                                <strong>{{ $publishedDate ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="service-show-head__chips"
                            aria-label="{{ $isEn ? 'Blog highlights' : 'Ringkasan blog' }}">
                            <span class="service-show-chip">Blog</span>
                            @if (filled($publishedDate))
                                <span class="service-show-chip">{{ $publishedDate }}</span>
                            @endif
                        </div>
                    </div>

                    @if ($postImage)
                        <a class="service-cert-example__link" href="{{ $postImage }}" target="_blank"
                            rel="noopener noreferrer">
                            <img class="service-cert-example__image" src="{{ $postImage }}"
                                alt="{{ $postTitle }}" loading="lazy">
                        </a>
                    @endif

                    <div class="prose prose-sm dark:prose-invert content-prose">
                        {!! $postHtml !!}
                    </div>
                </section>

            </x-card>
        </section>
    </div>
@endsection
