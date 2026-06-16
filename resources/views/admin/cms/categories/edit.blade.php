@extends('layouts.app')
@section('section','Manajemen Konten')
@section('content')
<div class="page-admin-cms page-admin-cms-categories-edit" data-cms-page="categories-edit">
  <header class="cms-page-header">
    <div class="cms-page-heading">
      <h1 class="cms-page-title">Edit Kategori</h1>
      <p class="cms-page-subtitle">{{ $item->name_id }}</p>
    </div>
    <div class="cms-page-actions">
      <x-button href="{{ route('admin.cms.categories.index') }}" variant="ghost">Kembali</x-button>
    </div>
  </header>

  <div class="cms-form-layout">
    <x-card class="cms-card cms-form-card">
      <form method="post" action="{{ route('admin.cms.categories.update',$item) }}" class="cms-form">
        @csrf @method('PUT')
        <div class="cms-form__grid">
          <x-input name="name_id" label="Nama (ID)" value="{{ old('name_id',$item->name_id) }}" required />
          <x-input name="name_en" label="Name (EN)" value="{{ old('name_en',$item->name_en) }}" />

          <x-input name="slug" label="Slug" value="{{ old('slug',$item->slug) }}" required />
          <div class="cms-hint">Perubahan slug akan mempengaruhi URL konten yang memakai kategori ini.</div>
        </div>

        <div class="cms-form__actions">
          <x-button type="submit">Simpan</x-button>
          <x-button href="{{ route('admin.cms.categories.index') }}" variant="ghost">Batal</x-button>
        </div>
      </form>
    </x-card>

    <x-card class="cms-card cms-side-card">
      <div class="cms-card__title">Info</div>
      <div class="cms-preview-meta">
        <div class="cms-preview-meta__row">
          <span class="cms-preview-meta__k">Type</span>
          <x-badge>service</x-badge>
        </div>
        <div class="cms-preview-meta__row">
          <span class="cms-preview-meta__k">Slug</span>
          <span class="cms-preview-meta__v">{{ $item->slug }}</span>
        </div>
      </div>
    </x-card>
  </div>
</div>
@endsection
