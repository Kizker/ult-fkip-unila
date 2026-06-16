@extends('layouts.app')
@section('section','Manajemen Konten')
@section('content')
<div class="page-admin-cms page-admin-cms-categories-create" data-cms-page="categories-create">
  <header class="cms-page-header">
    <div class="cms-page-heading">
      <h1 class="cms-page-title">Tambah Kategori</h1>
      <p class="cms-page-subtitle">Kategori khusus untuk layanan.</p>
    </div>
    <div class="cms-page-actions">
      <x-button href="{{ route('admin.cms.categories.index') }}" variant="ghost">Kembali</x-button>
    </div>
  </header>

  <div class="cms-form-layout">
    <x-card class="cms-card cms-form-card">
      <form method="post" action="{{ route('admin.cms.categories.store') }}" class="cms-form">
        @csrf
        <div class="cms-form__grid">
          <x-input name="name_id" label="Nama (ID)" value="{{ old('name_id') }}" required />
          <x-input name="name_en" label="Name (EN)" value="{{ old('name_en') }}" />

          <x-input name="slug" label="Slug (opsional)" value="{{ old('slug') }}" />
          <div class="cms-hint">Jika slug kosong, sistem akan membuat slug otomatis dari Nama (ID).</div>
        </div>

        <div class="cms-form__actions">
          <x-button type="submit">Simpan</x-button>
          <x-button href="{{ route('admin.cms.categories.index') }}" variant="ghost">Batal</x-button>
        </div>
      </form>
    </x-card>

    <x-card class="cms-card cms-side-card">
      <div class="cms-card__title">Catatan</div>
      <div class="cms-card__subtitle">Saran penamaan agar konsisten.</div>
      <ul class="cms-tips">
        <li>Pakai kata yang ringkas dan mudah dibaca.</li>
        <li>Hindari singkatan yang tidak umum.</li>
        <li>Gunakan slug lowercase dengan tanda minus.</li>
      </ul>
    </x-card>
  </div>
</div>
@endsection
