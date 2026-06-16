@extends('layouts.app')
@section('section','Manajemen Konten')
@section('content')
<div class="page-admin-cms page-admin-cms-posts-create page-admin-cms-announcements-create" data-cms-page="announcements-create">
  <header class="cms-page-header">
    <div class="cms-page-heading">
      <h1 class="cms-page-title">Tambah Pengumuman</h1>
      <p class="cms-page-subtitle">Buat pengumuman publik.</p>
    </div>
    <div class="cms-page-actions">
      <x-button href="{{ route('admin.cms.announcements.index') }}" variant="ghost">Kembali</x-button>
    </div>
  </header>

  <x-card class="cms-card cms-form-card">
    <form method="post" action="{{ route('admin.cms.announcements.store') }}" enctype="multipart/form-data" class="cms-form">
      @csrf

      <div class="cms-form__section">
        <div class="cms-form__title">Metadata</div>
        <div class="cms-form__grid">
          <x-input name="title_id" label="Judul (ID)" value="{{ old('title_id') }}" required />
          <x-input name="title_en" label="Title (EN)" value="{{ old('title_en') }}" />

          <x-input name="slug" label="Slug (opsional)" value="{{ old('slug') }}" placeholder="auto jika kosong" />
          <x-input name="published_at" label="Published at (YYYY-MM-DD)" value="{{ old('published_at') }}" />
        </div>

        <div class="cms-toggle-row">
          <label class="cms-toggle">
            <input type="checkbox" name="is_published" value="1" {{ old('is_published', false) ? 'checked' : '' }}>
            <span>Publish</span>
          </label>
        </div>
      </div>

      <div class="cms-form__section">
        <div class="cms-form__title">Konten</div>
        <div class="cms-form__editors">
          <x-tiptap-editor
            name="content_html_id"
            :value="old('content_html_id','')"
            label="Konten (ID)"
            localeHint="Default Bahasa Indonesia"
            help="Konten disanitasi server-side."
          />
          <x-tiptap-editor
            name="content_html_en"
            :value="old('content_html_en','')"
            label="Content (EN)"
            localeHint="English optional"
            help="Kosongkan jika tidak tersedia."
          />
        </div>
      </div>

      <div class="cms-form__section cms-form__section--media">
        <div class="cms-form__title">Gambar</div>
        <p class="cms-media-lead">Upload cover untuk kartu pengumuman di halaman publik.</p>

        <div class="cms-media-layout">
          <div class="cms-media-preview">
            <div class="cms-empty cms-cover-empty">
              <span class="cms-cover-empty__title">Belum ada cover pengumuman.</span>
              <span class="cms-cover-empty__desc">Rekomendasi rasio 16:9, maksimal ukuran 2MB.</span>
            </div>
          </div>

          <div class="cms-media-controls">
            <div class="cms-file">
              <x-file-input
                id="announcement_image"
                name="image"
                label="Cover Pengumuman (jpg/png/webp, max 2MB)"
                accept=".jpg,.jpeg,.png,.webp"
              />
            </div>
            <p class="cms-hint">Gambar akan tampil di halaman publik (home, daftar pengumuman, dan detail).</p>
          </div>
        </div>
      </div>

      <div class="cms-form__actions">
        <x-button type="submit">Simpan</x-button>
        <x-button href="{{ route('admin.cms.announcements.index') }}" variant="ghost">Batal</x-button>
      </div>
    </form>
  </x-card>
</div>
@endsection
