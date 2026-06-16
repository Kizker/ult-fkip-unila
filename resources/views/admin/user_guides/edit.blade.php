@extends('layouts.app')
@section('section', 'Panduan Pengguna')

@section('content')
    <div
        class="page-admin-user-guides page-admin-user-guides-edit"
        data-user-guides-form
        data-translate-url="{{ route('admin.utils.translate') }}"
    >
        <header class="admin-page-header">
            <div class="admin-page-heading">
                <div class="admin-page-kicker">Dokumen publik</div>
                <h1 class="admin-page-title">Edit Panduan Pengguna</h1>
                <p class="admin-page-subtitle">
                    Perbarui metadata, jenis konten, akses role, serta file atau tautan video panduan pengguna.
                </p>
            </div>
            <div class="admin-page-actions">
                <x-button href="{{ route('admin.user_guides.index') }}" variant="ghost">Kembali</x-button>
            </div>
        </header>

        @include('admin.user_guides._form', [
            'item' => $item,
            'action' => route('admin.user_guides.update', $item),
            'method' => 'PUT',
            'submitLabel' => 'Simpan Perubahan',
            'backHref' => route('admin.user_guides.index'),
        ])
    </div>
@endsection
