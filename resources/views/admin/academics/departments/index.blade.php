@extends('layouts.app')
@section('section','Jurusan')
@section('content')
@php
  $total = method_exists($items, 'total') ? (int) $items->total() : (int) count($items);
  $shown = (int) $items->count();
@endphp

<div class="page-admin-academics-departments-index">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master akademik</div>
      <h1 class="admin-page-title">Jurusan</h1>
      <p class="admin-page-subtitle">Kelola daftar jurusan dan relasinya ke program studi.</p>
    </div>

    <div class="admin-page-actions">
      <div class="admin-meta">
        <div class="admin-meta-pill" aria-label="Total jurusan">
          <div class="admin-meta-pill__label">Total</div>
          <div class="admin-meta-pill__value">{{ $total }}</div>
        </div>
      </div>
      <x-button href="{{ route('admin.jurusan.create') }}">Tambah jurusan</x-button>
    </div>
  </header>

  <x-card class="admin-search-card" data-admin-search-card>
    <div class="admin-search" role="search" aria-label="Pencarian jurusan">
      <div class="admin-search__toolbar">
        <div class="admin-search__field">
          <label for="admin-departments-live-search" class="sr-only">Cari jurusan</label>
          <div class="admin-search__input-wrap">
            <input
              id="admin-departments-live-search"
              type="text"
              class="admin-search__input"
              placeholder="Cari..."
              data-realtime-search-input
              data-realtime-search-mode="filter"
              data-realtime-search-scope=".departments-search-scope"
              data-realtime-search-item-selector="[data-realtime-search-item]"
              data-realtime-search-empty-selector="[data-realtime-search-empty]"
              data-realtime-search-count-selector="[data-realtime-search-count]"
            >
            <button type="button" class="admin-search__clear admin-search__clear--disabled" aria-label="Hapus pencarian" data-admin-search-clear data-reset-url="{{ route('admin.jurusan.index') }}">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </x-card>

  <div class="admin-search-resultbar">
    <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $shown }} dari {{ $total }} jurusan">Menampilkan {{ $shown }} dari {{ $total }} jurusan</div>
  </div>

  <x-card class="departments-search-scope">
    <div class="table-responsive overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-muted">
          <tr>
            <th class="text-left py-2">Kode</th>
            <th class="text-left py-2">Nama Jurusan</th>
            <th class="text-left py-2">Fakultas</th>
            <th class="text-right py-2">Prodi</th>
            <th class="text-right py-2">User</th>
            <th class="text-left py-2">Status</th>
            <th class="text-right py-2">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $it)
            @php
              $departmentSearchText = trim(implode(' ', array_filter([
                $it->code,
                $it->name,
                $it->parent?->name,
                (string) ($it->prodi_count ?? 0),
                (string) ($it->users_count ?? 0),
                $it->is_active ? 'active aktif' : 'inactive nonaktif',
              ])));
            @endphp
            <tr class="border-t border-[rgb(var(--c-border))]" data-realtime-search-item data-realtime-search-text="{{ $departmentSearchText }}">
              <td class="py-2 font-mono text-xs text-muted">{{ $it->code }}</td>
              <td class="py-2 font-medium">{{ $it->name }}</td>
              <td class="py-2 text-muted">{{ $it->parent?->name ?? '-' }}</td>
              <td class="py-2 text-right">{{ (int) ($it->prodi_count ?? 0) }}</td>
              <td class="py-2 text-right">{{ (int) ($it->users_count ?? 0) }}</td>
              <td class="py-2">
                <x-badge :variant="$it->is_active ? 'success' : 'warning'">
                  {{ $it->is_active ? 'Active' : 'Inactive' }}
                </x-badge>
              </td>
              <td class="py-2 text-right">
                <div class="inline-flex gap-2">
                  <x-button href="{{ route('admin.jurusan.edit',$it) }}" variant="ghost">Edit</x-button>
                  <form method="post" action="{{ route('admin.jurusan.destroy',$it) }}" onsubmit="return confirm('Hapus jurusan ini?')">
                    @csrf @method('DELETE')
                    <x-button type="submit" variant="danger">Hapus</x-button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr class="border-t border-[rgb(var(--c-border))]">
              <td class="py-6 text-center text-muted" colspan="7">Belum ada jurusan.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="table-cards">
      <div class="grid gap-3">
        @forelse($items as $it)
          @php
            $departmentSearchText = trim(implode(' ', array_filter([
              $it->code,
              $it->name,
              $it->parent?->name,
              (string) ($it->prodi_count ?? 0),
              (string) ($it->users_count ?? 0),
              $it->is_active ? 'active aktif' : 'inactive nonaktif',
            ])));
          @endphp
          <article class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-3" data-realtime-search-item data-realtime-search-text="{{ $departmentSearchText }}">
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <div class="text-xs text-muted font-mono">{{ $it->code }}</div>
                <div class="mt-1 text-sm font-semibold break-words">{{ $it->name }}</div>
              </div>
              <x-badge :variant="$it->is_active ? 'success' : 'warning'">
                {{ $it->is_active ? 'Active' : 'Inactive' }}
              </x-badge>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
              <div class="rounded-xl border border-[rgb(var(--c-border))] px-2 py-1.5">
                <div class="text-muted">Prodi</div>
                <div class="font-semibold text-sm">{{ (int) ($it->prodi_count ?? 0) }}</div>
              </div>
              <div class="rounded-xl border border-[rgb(var(--c-border))] px-2 py-1.5">
                <div class="text-muted">User</div>
                <div class="font-semibold text-sm">{{ (int) ($it->users_count ?? 0) }}</div>
              </div>
            </div>

            <div class="mt-3 text-xs text-muted">
              <span class="font-semibold text-[rgb(var(--c-fg))]">Fakultas:</span>
              <span class="break-words">{{ $it->parent?->name ?? '-' }}</span>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
              <x-button href="{{ route('admin.jurusan.edit',$it) }}" variant="ghost">Edit</x-button>
              <form method="post" action="{{ route('admin.jurusan.destroy',$it) }}" onsubmit="return confirm('Hapus jurusan ini?')">
                @csrf @method('DELETE')
                <x-button type="submit" variant="danger">Hapus</x-button>
              </form>
            </div>
          </article>
        @empty
          <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] px-4 py-6 text-center text-sm text-muted">
            Belum ada jurusan.
          </div>
        @endforelse
      </div>
    </div>
    @if($items->count() > 0)
      <div class="hidden rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] px-4 py-6 text-center text-sm text-muted mt-3" data-realtime-search-empty>
        Tidak ada jurusan yang cocok dengan pencarian.
      </div>
    @endif

    <div class="mt-4">{{ $items->onEachSide(1)->links('components.public.pagination') }}</div>
  </x-card>
</div>
@endsection
