@extends('layouts.app')
@section('section','Peran')
@section('content')
@php
  $permissionLabel = static fn (?string $name): string => \App\Support\PermissionLabel::make($name);
@endphp
<div class="page-admin-roles-create page-admin-role-form">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master akses</div>
      <h1 class="admin-page-title">Tambah Role</h1>
      <p class="admin-page-subtitle">Buat role baru dan pilih permission yang boleh diakses.</p>
    </div>
    <div class="admin-page-actions">
      <x-button variant="ghost" href="{{ route('admin.roles.index') }}">Kembali</x-button>
    </div>
  </header>

  <div class="rr-layout">
    <x-card class="rr-form-card">
      <form method="POST" action="{{ route('admin.roles.store') }}" class="rr-form">
        @csrf

        <div class="rr-field">
          <x-input name="name" label="Nama role" value="{{ old('name') }}" required />
        </div>

        <section class="rr-permissions">
          <div class="rr-permissions__head">
            <div class="admin-card-title">Permissions</div>
            <div class="admin-card-subtitle">Pilih permission yang dimiliki role ini.</div>
          </div>

          <div class="rr-permissions__grid">
            @foreach($permissions as $p)
              <label class="rr-perm-item">
                <input
                  type="checkbox"
                  name="permissions[]"
                  value="{{ $p->name }}"
                  @checked(in_array($p->name, old('permissions', []), true))
                  class="rr-perm-item__box"
                />
                <span class="rr-perm-item__label" title="{{ $p->name }}">{{ $permissionLabel($p->name) }}</span>
              </label>
            @endforeach
          </div>

          @error('permissions')
            <p class="text-sm text-[rgb(var(--c-danger))] mt-2">{{ $message }}</p>
          @enderror
          @error('permissions.*')
            <p class="text-sm text-[rgb(var(--c-danger))] mt-2">{{ $message }}</p>
          @enderror
        </section>

        <div class="rr-actions">
          <x-button type="submit">Simpan</x-button>
          <x-button variant="ghost" href="{{ route('admin.roles.index') }}">Batal</x-button>
        </div>
      </form>
    </x-card>

    <aside class="rr-side">
      <x-card class="rr-side-card">
        <div class="admin-card-title">Panduan</div>
        <ul class="rr-help">
          <li>Gunakan nama role yang konsisten dan mudah dibaca tim.</li>
          <li>Aktifkan hanya permission yang benar-benar dibutuhkan.</li>
          <li>Role baru bisa langsung dipilih saat menambah user.</li>
        </ul>
      </x-card>
    </aside>
  </div>
</div>
@endsection
