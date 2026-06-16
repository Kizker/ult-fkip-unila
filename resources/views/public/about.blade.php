@extends('layouts.public')

@section('title', __('app.about'))

@section('content')
@php
    $isEn = app()->getLocale() === 'en';
    $heroTitle = $isEn ? 'About ULT FKIP Unila' : 'Tentang ULT FKIP Unila';
    $heroSubtitle = $isEn
        ? 'Learn more about ULT FKIP Unila - our services, commitments, and how we help you.'
        : 'Mengenal lebih dekat ULT FKIP Unila - layanan, komitmen, dan bagaimana kami membantu Anda.';
    $contentKicker = $isEn ? 'About Profile' : 'Profil Layanan';
    $contentTitle = $isEn ? 'Information and Service Commitment' : 'Informasi dan Komitmen Layanan';
    $contentSubtitle = $isEn
        ? 'Find key information about ULT FKIP Unila and explore supporting links in one tidy section.'
        : 'Temukan informasi utama tentang ULT FKIP Unila dan akses tautan pendukung dalam satu section yang rapi.';
    $heroImage = filled($heroBanner?->image_path ?? null)
        ? asset('storage/' . ltrim((string) $heroBanner->image_path, '/'))
        : asset('assets/images/blog/blog-details.png');
@endphp

<div class="page-about page-services-index services-v2" id="aboutPage">
    <header class="services-v2-hero" data-services-hero-image="{{ $heroImage }}">
        <div class="services-v2-hero__bg" aria-hidden="true"></div>
        <div class="services-v2-hero__veil" aria-hidden="true"></div>
        <div class="services-v2-hero__mesh" aria-hidden="true"></div>

        <div class="services-v2-hero__inner">
            <div class="services-v2-hero__copy" data-services-reveal>
                <div class="services-v2-hero__kicker">{{ $isEn ? 'About' : 'Tentang' }}</div>
                <h1 class="services-v2-hero__title">{{ $heroTitle }}</h1>
                <p class="services-v2-hero__subtitle">{{ $heroSubtitle }}</p>
            </div>
        </div>

        <a href="#about-content-section"
            class="services-v2-hero__scroll"
            data-scroll-to="#about-content-section">
            {{ $isEn ? 'View content' : 'Lihat konten' }}
        </a>
    </header>

    <section id="about-content-section" class="about-content-section page-section" aria-label="{{ __('app.about') }}">
        <div class="about-content-shell">
            <div class="about-overview" data-services-reveal>
                <div class="about-overview__kicker">{{ $contentKicker }}</div>
                <h2 class="about-overview__title">{{ $contentTitle }}</h2>
                <p class="about-overview__subtitle">{{ $contentSubtitle }}</p>
                <div class="about-overview__chips" aria-label="{{ $isEn ? 'About highlights' : 'Sorotan tentang ULT' }}">
                    <span class="about-overview__chip">{{ $isEn ? 'Academic services' : 'Layanan akademik' }}</span>
                    <span class="about-overview__chip">{{ $isEn ? 'Student support' : 'Dukungan mahasiswa' }}</span>
                    <span class="about-overview__chip">{{ $isEn ? 'Integrated information' : 'Informasi terintegrasi' }}</span>
                </div>
            </div>

            <div class="about-grid">
                <x-card class="about-card" data-services-reveal>
                    <div class="prose prose-sm dark:prose-invert about-prose">
                        {!! $isEn ? ($aboutEn ?? $aboutId ?? '<p><strong>DATA TIDAK TERSEDIA: Konten Tentang ULT</strong></p>') : ($aboutId ?? '<p><strong>DATA TIDAK TERSEDIA: Konten Tentang ULT</strong></p>') !!}
                    </div>
                </x-card>

                <aside class="about-side" aria-label="{{ $isEn ? 'Quick links' : 'Tautan cepat' }}" data-services-reveal>
                    <div class="about-side__panel">
                        <div class="about-side__title">{{ $isEn ? 'Quick links' : 'Tautan cepat' }}</div>
                        <div class="about-side__links">
                            <a class="about-link" href="{{ route('services.index') }}">
                                <span class="about-link__label">{{ __('app.services') }}</span>
                                <span class="about-link__chev" aria-hidden="true">&rarr;</span>
                            </a>
                            <a class="about-link" href="{{ route('announcements.index') }}">
                                <span class="about-link__label">{{ __('app.announcements') }}</span>
                                <span class="about-link__chev" aria-hidden="true">&rarr;</span>
                            </a>
                            <a class="about-link" href="{{ route('blog.index') }}">
                                <span class="about-link__label">{{ __('app.blog') }}</span>
                                <span class="about-link__chev" aria-hidden="true">&rarr;</span>
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>
@endsection
