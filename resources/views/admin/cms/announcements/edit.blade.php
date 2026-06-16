@extends('layouts.app')
@section('section','Manajemen Konten')
@section('content')
<div
  class="page-admin-cms page-admin-cms-posts-edit page-admin-cms-announcements-edit"
  data-cms-page="announcements-edit"
  data-cms-autosave-url="{{ route('admin.cms.announcements.autosave', $item) }}"
  data-cms-csrf-token="{{ csrf_token() }}"
>
  <header class="cms-page-header">
    <div class="cms-page-heading">
      <h1 class="cms-page-title">Edit Pengumuman</h1>
      <p class="cms-page-subtitle">{{ $item->title_id }}</p>
    </div>
    <div class="cms-page-actions">
      <x-button href="{{ route('admin.cms.announcements.index') }}" variant="ghost">Kembali</x-button>
    </div>
  </header>

  <x-card class="cms-card cms-form-card">
    <form id="cms_post_form" method="post" action="{{ route('admin.cms.announcements.update',$item) }}" enctype="multipart/form-data" class="cms-form">
      @csrf @method('PUT')

      <div class="cms-form__section">
        <div class="cms-form__title">Metadata</div>
        <div class="cms-form__grid">
          <x-input name="title_id" label="Judul (ID)" value="{{ old('title_id',$item->title_id) }}" required />
          <x-input name="title_en" label="Title (EN)" value="{{ old('title_en',$item->title_en) }}" />

          <x-input name="slug" label="Slug" value="{{ old('slug',$item->slug) }}" required />
          <x-input name="published_at" label="Published at (YYYY-MM-DD)" value="{{ old('published_at', optional($item->published_at)->format('Y-m-d')) }}" />
        </div>

        <div class="cms-toggle-row">
          <label class="cms-toggle">
            <input type="checkbox" name="is_published" value="1" {{ old('is_published', $item->is_published) ? 'checked' : '' }}>
            <span>Publish</span>
          </label>
          <div class="cms-inline-badge">
            <x-badge :variant="$item->is_published ? 'success' : 'warning'">{{ $item->is_published ? 'Published' : 'Draft' }}</x-badge>
          </div>
        </div>
      </div>

      <div class="cms-form__section">
        <div class="cms-form__title">Konten</div>
        <div class="cms-form__editors">
          <x-tiptap-editor
            name="content_html_id"
            :value="old('content_html_id',$item->content_html_id ?? '')"
            label="Konten (ID)"
            localeHint="Default Bahasa Indonesia"
            help="Konten disanitasi server-side."
          />
          <x-tiptap-editor
            name="content_html_en"
            :value="old('content_html_en',$item->content_html_en ?? '')"
            label="Content (EN)"
            localeHint="English optional"
            help="Kosongkan jika tidak tersedia."
          />
        </div>
      </div>

      <div class="cms-form__section cms-form__section--media">
        <div class="cms-form__title">Gambar</div>
        <p class="cms-media-lead">Kelola cover pengumuman untuk kartu konten di halaman publik.</p>

        <div class="cms-media-layout">
          <div class="cms-media-preview">
            @if ($item->image_path)
              <img class="cms-preview-img" src="{{ asset('storage/' . ltrim($item->image_path, '/')) }}" alt="Cover {{ $item->title_id }}">
            @else
              <div class="cms-empty cms-cover-empty">
                <span class="cms-cover-empty__title">Belum ada cover pengumuman.</span>
                <span class="cms-cover-empty__desc">Upload cover horizontal agar kartu publik lebih menarik.</span>
              </div>
            @endif
          </div>

          <div class="cms-media-controls">
            <div class="cms-file">
              <x-file-input
                id="announcement_image"
                name="image"
                label="Ganti Cover Pengumuman (jpg/png/webp, max 2MB)"
                accept=".jpg,.jpeg,.png,.webp"
              />
            </div>
            <p class="cms-hint">Gunakan gambar horizontal agar tampil lebih proporsional pada kartu publik.</p>

            @if ($item->image_path)
              <label class="cms-toggle mt-3">
                <input type="checkbox" name="remove_image" value="1" {{ old('remove_image', false) ? 'checked' : '' }}>
                <span>Hapus cover saat ini</span>
              </label>
            @endif
          </div>
        </div>
      </div>

      <div class="cms-form__actions">
        <x-button type="submit">Simpan</x-button>
        <x-button href="{{ route('admin.cms.announcements.index') }}" variant="ghost">Batal</x-button>
        <x-button href="{{ route('admin.cms.announcements.preview', $item) }}" variant="secondary" target="_blank">Preview</x-button>
      </div>

      <div class="cms-autosave text-sm text-muted" id="autosave_status" data-cms-autosave-status>Autosave: menunggu perubahan...</div>
    </form>
  </x-card>
</div>
@endsection
