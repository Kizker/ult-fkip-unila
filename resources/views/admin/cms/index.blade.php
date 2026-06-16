@extends('layouts.app')
@section('section','Manajemen Konten')
@section('content')
<div class="page-admin-cms page-admin-cms-index" data-cms-page="index">
  <header class="cms-page-header">
    <div class="cms-page-heading">
      <h1 class="cms-page-title">Manajemen Konten</h1>
      <p class="cms-page-subtitle">Kelola hero, konten Tentang ULT, kategori layanan, blog, dan pengumuman.</p>
    </div>
    <nav class="cms-page-actions" aria-label="Navigasi CMS">
      <x-button href="{{ route('admin.cms.hero.edit') }}">Hero</x-button>
      <x-button href="{{ route('admin.cms.blogs.index') }}" variant="ghost">Blog</x-button>
      <x-button href="{{ route('admin.cms.announcements.index') }}" variant="ghost">Pengumuman</x-button>
      <x-button href="{{ route('admin.cms.categories.index') }}" variant="ghost">Kategori Layanan</x-button>
      <x-button href="{{ route('admin.cms.settings.edit') }}" variant="ghost">Site Settings</x-button>
    </nav>
  </header>

  <div class="cms-dashboard">
    <div class="cms-dashboard__main">
      <x-card class="cms-card">
        <div class="cms-card__header">
          <div>
            <div class="cms-card__title">Hero aktif</div>
            <div class="cms-card__subtitle">Konten hero untuk halaman publik.</div>
          </div>
          <x-button href="{{ route('admin.cms.hero.edit') }}" variant="ghost">Edit</x-button>
        </div>

        <div class="cms-hero-preview">
          <div class="cms-hero-preview__text">
            <div class="cms-hero-preview__headline">{{ $hero?->title_id ?? 'Judul hero belum diisi' }}</div>
            <div class="cms-hero-preview__meta">
              <x-badge :variant="$hero?->is_active ? 'success' : 'warning'">{{ $hero?->is_active ? 'Active' : 'Inactive' }}</x-badge>
              @if($hero?->cta_url)
                <span class="cms-hero-preview__muted">CTA: {{ $hero->cta_url }}</span>
              @endif
            </div>
          </div>
          @if($hero?->image_path)
            <img class="cms-hero-preview__img" src="{{ asset('storage/'.$hero->image_path) }}" alt="Hero">
          @endif
        </div>
      </x-card>

      <x-card class="cms-card">
        <div class="cms-card__header">
          <div>
            <div class="cms-card__title">Konten terbaru</div>
            <div class="cms-card__subtitle">Gabungan blog dan pengumuman yang tampil di publik.</div>
          </div>
          <div class="inline-flex gap-2">
            <x-button href="{{ route('admin.cms.blogs.index') }}" variant="ghost">Blog</x-button>
            <x-button href="{{ route('admin.cms.announcements.index') }}" variant="ghost">Pengumuman</x-button>
          </div>
        </div>

        <div class="cms-list">
          @forelse($recentContents as $p)
            @php
              $isBlog = $p->content_type === 'blog';
              $editRoute = $isBlog ? route('admin.cms.blogs.edit', $p->id) : route('admin.cms.announcements.edit', $p->id);
            @endphp
            <a class="cms-list__item" href="{{ $editRoute }}">
              <div class="cms-list__main">
                <div class="cms-list__title">{{ $p->title_id }}</div>
                <div class="cms-list__meta">{{ $isBlog ? 'blog' : 'announcement' }} - {{ $p->published_at ? \Illuminate\Support\Carbon::parse($p->published_at)->format('d M Y') : '-' }}</div>
              </div>
              <x-badge :variant="$p->is_published ? 'success' : 'warning'">{{ $p->is_published ? 'Published' : 'Draft' }}</x-badge>
            </a>
          @empty
            <div class="cms-empty">Belum ada konten.</div>
          @endforelse
        </div>
        @if(method_exists($recentContents, 'hasPages') && $recentContents->hasPages())
          <div class="cms-pagination mt-4">
            {{ $recentContents->onEachSide(1)->links('components.public.pagination') }}
          </div>
        @endif
      </x-card>
    </div>

    <div class="cms-dashboard__side">
      <x-card class="cms-card">
        <div class="cms-card__title">Akses cepat</div>
        <div class="cms-quicklinks">
          <a class="cms-quicklinks__item" href="{{ route('admin.cms.blogs.create') }}">Tambah Blog</a>
          <a class="cms-quicklinks__item" href="{{ route('admin.cms.announcements.create') }}">Tambah Pengumuman</a>
          <a class="cms-quicklinks__item" href="{{ route('admin.cms.categories.create') }}">Tambah Kategori Layanan</a>
          @if(auth()->user()?->hasRole('Superadmin'))
            <a class="cms-quicklinks__item" href="{{ route('admin.user_guides.index') }}">Kelola Panduan Pengguna</a>
          @endif
          <a class="cms-quicklinks__item" href="{{ route('admin.cms.settings.edit') }}">Edit Site Settings</a>
        </div>
      </x-card>

      <x-card class="cms-card">
        <div class="cms-card__title">Tips keamanan</div>
        <ul class="cms-tips">
          <li>Konten editor disanitasi (allowlist tag) sebelum disimpan.</li>
          <li>Hindari menempel script/iframe dari sumber tidak tepercaya.</li>
          <li>Gunakan gambar webp/jpg terkompresi untuk performa.</li>
        </ul>
      </x-card>
    </div>
  </div>
</div>
@endsection
