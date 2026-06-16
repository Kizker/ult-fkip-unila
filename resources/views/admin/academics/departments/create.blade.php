@extends('layouts.app')
@section('section','Jurusan')
@section('content')
<div class="page-admin-academics-departments-create">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master akademik</div>
      <h1 class="admin-page-title">Tambah Jurusan</h1>
      <p class="admin-page-subtitle">Buat jurusan baru untuk kebutuhan pengelompokan program studi.</p>
    </div>
    <div class="admin-page-actions">
      <x-button variant="ghost" href="{{ route('admin.jurusan.index') }}">Kembali</x-button>
    </div>
  </header>

  <div class="as-form-layout">
    <x-card class="as-form-card">
      <form class="as-form" method="POST" action="{{ route('admin.jurusan.store') }}">
        @csrf

        <div class="as-form-grid">
          <x-input
            name="code"
            label="Kode"
            required
            value="{{ old('code') }}"
            help="Contoh: JUR-IP"
          />
          <x-input
            name="name"
            label="Nama Jurusan"
            required
            value="{{ old('name') }}"
          />
        </div>

        <x-input
          name="faculty_display"
          label="Fakultas (otomatis)"
          value="{{ $faculty->name }}"
          readonly
          disabled
        />

        <div class="as-form-grid as-form-grid--tight">
          <div class="text-sm text-muted">Status</div>
          <label class="as-switch">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="as-switch__box">
            <span class="as-switch__label">Active</span>
          </label>
        </div>

        <div class="as-form-actions">
          <x-button type="submit">Simpan</x-button>
          <x-button variant="ghost" href="{{ route('admin.jurusan.index') }}">Batal</x-button>
        </div>
      </form>
    </x-card>

    <div class="as-aside">
      <x-card>
        <div class="admin-card-title">Panduan</div>
        <ul class="as-help">
          <li>Kode harus unik (dipakai untuk referensi internal).</li>
          <li>Fakultas otomatis di-set ke FKIP.</li>
          <li>Nonaktifkan jika belum siap dipakai pada proses layanan.</li>
        </ul>
      </x-card>
    </div>
  </div>
</div>
@endsection
