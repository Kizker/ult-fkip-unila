@extends('layouts.public')

@section('title', __('app.offline_title'))

@section('content')
@php
    $isEn = app()->getLocale() === 'en';
@endphp

<div class="page-offline">
    <section class="offline-shell" aria-labelledby="offline-title">
        <div class="offline-grid">
            <x-card class="offline-card">
                <div class="offline-kicker">{{ $isEn ? 'Offline Mode' : 'Mode Offline' }}</div>
                <h1 id="offline-title" class="offline-title">{{ __('app.offline_title') }}</h1>
                <p class="offline-desc">{{ __('app.offline_desc') }}</p>

                <div class="offline-actions">
                    <x-button href="{{ url('/') }}">{{ __('app.back_home') }}</x-button>
                    <x-button variant="secondary" href="{{ request()->fullUrl() }}">
                        {{ $isEn ? 'Try Again' : 'Coba Lagi' }}
                    </x-button>
                </div>
            </x-card>

            <aside class="offline-side" aria-label="{{ $isEn ? 'Offline tips' : 'Tips saat offline' }}">
                <div class="offline-side__title">{{ $isEn ? 'What you can do now' : 'Yang bisa Anda lakukan sekarang' }}</div>
                <ul class="offline-side__list">
                    <li>{{ $isEn ? 'Check your internet connection and retry in a few moments.' : 'Periksa koneksi internet Anda lalu coba kembali beberapa saat lagi.' }}</li>
                    <li>{{ $isEn ? 'Open pages you have visited before while waiting for connection recovery.' : 'Buka halaman yang sebelumnya pernah Anda kunjungi sambil menunggu koneksi pulih.' }}</li>
                    <li>{{ $isEn ? 'Return to the home page to continue browsing available cached content.' : 'Kembali ke beranda untuk melanjutkan akses konten yang sudah tersimpan.' }}</li>
                </ul>
            </aside>
        </div>
    </section>
</div>
@endsection
