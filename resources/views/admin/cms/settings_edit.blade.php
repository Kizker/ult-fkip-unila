@extends('layouts.app')
@section('section','Manajemen Konten')
@section('content')
<div class="page-admin-cms page-admin-cms-settings-edit" data-cms-page="settings-edit" data-cms-settings-form data-translate-url="{{ route('admin.utils.translate') }}">
  <header class="cms-page-header">
    <div class="cms-page-heading">
      <h1 class="cms-page-title">Site Settings</h1>
      <p class="cms-page-subtitle">Konfigurasi global yang mempengaruhi workflow dan halaman publik.</p>
    </div>
    <nav class="cms-page-actions" aria-label="Aksi halaman">
      <x-button href="{{ route('admin.cms.index') }}" variant="ghost">Kembali</x-button>
    </nav>
  </header>

  <x-card class="cms-card cms-form-card">
    <form method="post" action="{{ route('admin.cms.settings.update') }}" class="cms-form">
      @csrf

      <div class="cms-form__section">
        <div class="cms-form__title">Konten Tentang ULT</div>
        <div class="cms-form__editors">
          <x-tiptap-editor
            name="about_ult_html_id"
            :value="old('about_ult_html_id', $settings['about_ult_html_id'] ?? '')"
            label="Tentang ULT (HTML, ID)"
            help="Akan tampil di halaman publik Tentang ULT."
          />
          <x-tiptap-editor
            name="about_ult_html_en"
            :value="old('about_ult_html_en', $settings['about_ult_html_en'] ?? '')"
            label="About ULT (HTML, EN)"
            help="Opsional. Terisi otomatis dari versi ID, dan tetap bisa Anda edit manual."
          />
        </div>
      </div>

      <div class="cms-form__actions">
        <x-button type="submit">Simpan</x-button>
        <x-button href="{{ route('admin.cms.index') }}" variant="ghost">Batal</x-button>
      </div>
    </form>
  </x-card>
</div>
@endsection
