@extends('layouts.app')
@section('section','Peran')
@section('content')
@php
  $selected = old('permissions', $role->permissions->pluck('name')->all());
  $permissionLabel = static fn (?string $name): string => \App\Support\PermissionLabel::make($name);
@endphp

<div class="page-admin-roles-edit page-admin-role-form">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master akses</div>
      <h1 class="admin-page-title">Edit Role</h1>
      <p class="admin-page-subtitle">Ubah nama role dan permission yang dimiliki.</p>
    </div>
    <div class="admin-page-actions">
      <x-button variant="ghost" href="{{ route('admin.roles.index') }}">Kembali</x-button>
    </div>
  </header>

  <div class="rr-layout">
    <x-card class="rr-form-card">
      <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="rr-form">
        @csrf
        @method('PUT')

        <div class="rr-field">
          <x-input name="name" label="Nama role" value="{{ old('name', $role->name) }}" required @disabled($role->name === 'Superadmin') />
          @if($role->name === 'Superadmin')
            <p class="text-xs text-muted mt-1">Nama role Superadmin dikunci untuk menjaga konsistensi sistem.</p>
          @endif
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
                  @checked(in_array($p->name, $selected, true))
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
        <div class="admin-card-title">Catatan</div>
        <ul class="rr-help">
          <li>Perubahan permission berlaku untuk semua user dengan role ini.</li>
          <li>Gunakan prinsip least privilege agar akses tetap aman.</li>
          <li>Cek kembali menu yang terdampak setelah menyimpan perubahan.</li>
        </ul>
      </x-card>
    </aside>
  </div>
</div>
@endsection
