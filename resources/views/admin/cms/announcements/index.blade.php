@extends('layouts.app')
@section('section','Manajemen Konten')
@section('content')
@php
  $total = method_exists($items, 'total') ? (int) $items->total() : (int) count($items);
  $shown = (int) $items->count();
@endphp
<div class="page-admin-cms page-admin-cms-posts-index page-admin-cms-announcements-index" data-cms-page="announcements-index">
  <header class="cms-page-header">
    <div class="cms-page-heading">
      <h1 class="cms-page-title">Pengumuman</h1>
      <p class="cms-page-subtitle">Kelola pengumuman publik.</p>
    </div>
    <div class="cms-page-actions">
      <x-button href="{{ route('admin.cms.announcements.create') }}">Tambah</x-button>
      <x-button href="{{ route('admin.cms.index') }}" variant="ghost">Kembali</x-button>
    </div>
  </header>

  <x-card class="cms-card admin-search-card" data-admin-search-card>
    <div class="admin-search" role="search" aria-label="Pencarian pengumuman">
      <div class="admin-search__toolbar">
        <div class="admin-search__field">
          <label for="admin-cms-announcements-live-search" class="sr-only">Cari pengumuman</label>
          <div class="admin-search__input-wrap">
            <input
              id="admin-cms-announcements-live-search"
              type="text"
              class="admin-search__input"
              placeholder="Cari..."
              data-realtime-search-input
              data-realtime-search-mode="filter"
              data-realtime-search-scope=".cms-table-wrap"
              data-realtime-search-item-selector="tbody tr[data-realtime-search-item]"
              data-realtime-search-empty-selector="tbody tr[data-realtime-search-empty]"
              data-realtime-search-count-selector="[data-realtime-search-count]"
            >
            <button type="button" class="admin-search__clear admin-search__clear--disabled" aria-label="Hapus pencarian" data-admin-search-clear data-reset-url="{{ route('admin.cms.announcements.index') }}">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="admin-search-resultbar">
      <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $shown }} dari {{ $total }} pengumuman">Menampilkan {{ $shown }} dari {{ $total }} pengumuman</div>
    </div>

    <div class="cms-table-wrap table-responsive overflow-x-auto">
      <table class="cms-table w-full text-sm">
        <thead class="text-muted">
          <tr>
            <th class="text-left py-2">Judul</th>
            <th class="text-left py-2">Tanggal</th>
            <th class="text-left py-2">Status</th>
            <th class="text-right py-2">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $it)
            <tr class="border-t border-[rgb(var(--c-border))]" data-realtime-search-item data-realtime-search-text="{{ trim($it->title_id . ' ' . $it->slug . ' ' . ($it->is_published ? 'published' : 'draft')) }}">
              <td class="py-2" data-label="Judul">
                <div class="font-medium">{{ $it->title_id }}</div>
                <div class="text-muted text-xs">{{ $it->slug }}</div>
              </td>
              <td class="py-2 text-muted" data-label="Tanggal">{{ optional($it->published_at)->format('Y-m-d') ?? '-' }}</td>
              <td class="py-2" data-label="Status">
                <x-badge :variant="$it->is_published ? 'success' : 'warning'">{{ $it->is_published ? 'Published' : 'Draft' }}</x-badge>
              </td>
              <td class="py-2 text-right" data-label="Aksi">
                <div class="cms-row-actions inline-flex gap-2">
                  <x-button href="{{ route('admin.cms.announcements.edit',$it) }}" variant="ghost">Edit</x-button>
                  <form method="post" action="{{ route('admin.cms.announcements.destroy',$it) }}">
                    @csrf @method('DELETE')
                    <x-button type="submit" variant="danger" data-confirm="Hapus pengumuman ini?">Hapus</x-button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr class="border-t border-[rgb(var(--c-border))]">
              <td class="py-6 text-center text-muted" colspan="4">Belum ada pengumuman.</td>
            </tr>
          @endforelse
          @if($items->count() > 0)
            <tr class="border-t border-[rgb(var(--c-border))] hidden" data-realtime-search-empty>
              <td class="py-6 text-center text-muted" colspan="4">Tidak ada pengumuman yang cocok dengan pencarian.</td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>

    <div class="cms-pagination">{{ $items->onEachSide(1)->links('components.public.pagination') }}</div>
  </x-card>
</div>
@endsection
