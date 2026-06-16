@extends('layouts.app')
@section('section','Program Studi')
@section('content')
<div class="page-admin-academics-study-programs-edit">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master akademik</div>
      <h1 class="admin-page-title">Edit Program Studi</h1>
      <p class="admin-page-subtitle">Perbarui data program studi.</p>
    </div>
    <div class="admin-page-actions">
      <x-button variant="ghost" href="{{ route('admin.prodi.index') }}">Kembali</x-button>
    </div>
  </header>

  <div class="as-form-layout">
    <x-card class="as-form-card">
      <form class="as-form" method="POST" action="{{ route('admin.prodi.update',$prodi) }}">
        @csrf
        @method('PUT')

        @php
          $singleDepartment = $departments->count() === 1 ? $departments->first() : null;
        @endphp
        @if($singleDepartment)
          <input type="hidden" name="parent_id" value="{{ $singleDepartment->id }}">
          <x-input
            name="department_display"
            label="Jurusan (otomatis)"
            value="{{ $singleDepartment->name }}"
            readonly
            disabled
          />
        @else
          <x-select name="parent_id" label="Jurusan" required>
            <option value="">Pilih jurusan</option>
            @foreach($departments as $d)
              <option value="{{ $d->id }}" @selected((string) old('parent_id', $prodi->parent_id) === (string) $d->id)>{{ $d->name }}</option>
            @endforeach
          </x-select>
        @endif

        <div class="as-form-grid">
          <x-input
            name="code"
            label="Kode"
            required
            value="{{ old('code', $prodi->code) }}"
          />
          <x-input
            name="name"
            label="Nama Prodi"
            required
            value="{{ old('name', $prodi->name) }}"
          />
        </div>

        <div class="as-form-grid as-form-grid--tight">
          <div class="text-sm text-muted">Status</div>
          <label class="as-switch">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $prodi->is_active)) class="as-switch__box">
            <span class="as-switch__label">Active</span>
          </label>
        </div>

        <div class="as-form-actions">
          <x-button type="submit">Simpan</x-button>
          <x-button variant="ghost" href="{{ route('admin.prodi.index') }}">Batal</x-button>
        </div>
      </form>
    </x-card>

    <div class="as-aside">
      <x-card>
        <div class="admin-card-title">Catatan</div>
        <ul class="as-help">
          <li>Prodi yang masih dipakai user tidak bisa dihapus.</li>
          <li>Pindah jurusan akan mempengaruhi scope pemrosesan permohonan.</li>
        </ul>
      </x-card>
    </div>
  </div>
</div>
@endsection
