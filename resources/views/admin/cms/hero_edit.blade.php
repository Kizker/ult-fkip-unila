@extends('layouts.app')
@section('section','Manajemen Konten')
@section('content')
<div class="page-admin-cms page-admin-cms-hero-edit" data-cms-page="hero-edit">
  <header class="cms-page-header">
    <div class="cms-page-heading">
      <h1 class="cms-page-title">Hero Banner</h1>
      <p class="cms-page-subtitle">Ubah hero beranda publik (judul, subjudul, CTA, dan gambar).</p>
    </div>
    <nav class="cms-page-actions" aria-label="Aksi halaman">
      <x-button href="{{ route('admin.cms.index') }}" variant="ghost">Kembali</x-button>
    </nav>
  </header>

  <div class="cms-form-layout">
    <x-card class="cms-card cms-form-card">
      <form method="post" action="{{ route('admin.cms.hero.update') }}" enctype="multipart/form-data" class="cms-form">
        @csrf

        <div class="cms-form__section">
          <div class="cms-form__title">Konten</div>
          <div class="cms-form__grid">
            <x-input name="title_id" label="Judul (ID)" value="{{ old('title_id',$hero?->title_id) }}" required />
            <x-input name="title_en" label="Title (EN)" value="{{ old('title_en',$hero?->title_en) }}" />

            <x-input name="subtitle_id" label="Subjudul (ID)" value="{{ old('subtitle_id',$hero?->subtitle_id) }}" />
            <x-input name="subtitle_en" label="Subtitle (EN)" value="{{ old('subtitle_en',$hero?->subtitle_en) }}" />
          </div>
        </div>

        <div class="cms-form__section">
          <div class="cms-form__title">CTA</div>
          <div class="cms-form__grid">
            <x-input name="cta_label_id" label="CTA Label (ID)" value="{{ old('cta_label_id',$hero?->cta_label_id) }}" />
            <x-input name="cta_label_en" label="CTA Label (EN)" value="{{ old('cta_label_en',$hero?->cta_label_en) }}" />
            <x-input name="cta_url" label="CTA URL" value="{{ old('cta_url',$hero?->cta_url) }}" placeholder="/login" />
            <div class="cms-toggle-row">
              <label class="cms-toggle">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active',$hero?->is_active) ? 'checked' : '' }}>
                <span>Aktifkan hero ini</span>
              </label>
            </div>
          </div>
        </div>

        <div class="cms-form__section">
          <div class="cms-form__title">Gambar</div>
          <div class="cms-file">
            <x-file-input
              name="image"
              label="Gambar Hero (jpg/png/webp, max 2MB)"
              accept=".jpg,.jpeg,.png,.webp"
            />
          </div>
          <p class="cms-hint">Rekomendasi: rasio 16:9 (mis. 1600×900), WebP/JPG terkompresi untuk hasil lebih cepat.</p>
        </div>

        <div class="cms-form__actions">
          <x-button type="submit">Simpan</x-button>
          <x-button href="{{ route('admin.cms.index') }}" variant="ghost">Batal</x-button>
        </div>
      </form>
    </x-card>

    <x-card class="cms-card cms-side-card">
      <div class="cms-card__title">Preview</div>
      <div class="cms-card__subtitle">Pratinjau tampilan gambar hero.</div>

      @if($hero?->image_path)
        <img class="cms-preview-img" src="{{ asset('storage/'.$hero->image_path) }}" alt="Hero">
      @else
        <div class="cms-empty">Belum ada gambar hero.</div>
      @endif

      <div class="cms-preview-meta">
        <div class="cms-preview-meta__row">
          <span class="cms-preview-meta__k">Status</span>
          <x-badge :variant="$hero?->is_active ? 'success' : 'warning'">{{ $hero?->is_active ? 'Active' : 'Inactive' }}</x-badge>
        </div>
        <div class="cms-preview-meta__row">
          <span class="cms-preview-meta__k">Judul</span>
          <span class="cms-preview-meta__v">{{ $hero?->title_id ?? '-' }}</span>
        </div>
        <div class="cms-preview-meta__row">
          <span class="cms-preview-meta__k">CTA</span>
          <span class="cms-preview-meta__v">{{ $hero?->cta_url ?? '-' }}</span>
        </div>
      </div>
    </x-card>
  </div>
</div>
@endsection
