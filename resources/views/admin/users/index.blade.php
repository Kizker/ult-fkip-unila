@extends('layouts.app')
@section('section','Pengguna')
@section('content')
@php
  $total = method_exists($items, 'total') ? (int) $items->total() : (int) count($items);
  $filterCount = (filled($q) ? 1 : 0) + (filled($unitId) ? 1 : 0);
  $selectedUnit = collect($units)->firstWhere('id', (int) $unitId);
@endphp

<div class="page-admin-users-index" data-admin-users-index-page>
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master data</div>
      <h1 class="admin-page-title">Pengguna</h1>
      <p class="admin-page-subtitle">Tambah, ubah, hapus user, dan atur role.</p>
    </div>

    <div class="admin-page-actions">
      <div class="admin-meta">
        <div class="admin-meta-pill" aria-label="Total user">
          <div class="admin-meta-pill__label">Total</div>
          <div class="admin-meta-pill__value">{{ $total }}</div>
        </div>
        <div class="admin-meta-pill" aria-label="Filter aktif">
          <div class="admin-meta-pill__label">Filter</div>
          <div class="admin-meta-pill__value">{{ $filterCount }}</div>
        </div>
      </div>

      <x-button variant="secondary" href="{{ route('admin.users.create') }}">Tambah User</x-button>
    </div>
  </header>

  <x-card class="admin-search-card admin-users-toolbar" data-admin-search-card>
    <div class="admin-search" role="search" aria-label="Pencarian user admin">
      <div class="admin-search__toolbar">
        <div class="admin-search__field">
          <label for="admin-users-live-search" class="sr-only">Cari user</label>
          <div class="admin-search__input-wrap">
            <input
              id="admin-users-live-search"
              type="text"
              name="q"
              value="{{ $q }}"
              class="admin-search__input"
              placeholder="Cari..."
              data-realtime-search-input
              data-realtime-search-mode="filter"
              data-realtime-search-scope=".users-grid"
              data-realtime-search-item-selector="[data-realtime-search-item]"
              data-realtime-search-empty-selector="[data-realtime-search-empty]"
              data-realtime-search-count-selector="[data-realtime-search-count]"
            >
            <button
              type="button"
              class="admin-search__clear {{ $filterCount > 0 ? '' : 'admin-search__clear--disabled' }}"
              aria-label="Reset pencarian dan filter"
              data-admin-search-clear
              data-reset-url="{{ route('admin.users.index') }}"
            >
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
              </svg>
            </button>
          </div>
        </div>

        <details class="admin-search-filter-menu">
          <summary class="admin-search-filter-menu__toggle" aria-label="Buka filter user">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 7h16M7 12h10M10 17h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
            </svg>
          </summary>

          <div class="admin-search-filter-menu__panel">
            <form method="GET" class="admin-search-filter-menu__form">
              <input type="hidden" name="q" value="{{ $q }}">
              <div class="admin-search-filter-menu__field">
                <label class="admin-search-filter-menu__label" for="admin-users-unit">Unit</label>
                <select id="admin-users-unit" name="unit_id" data-admin-search-track onchange="this.form.requestSubmit()">
                  <option value="">Semua</option>
                  @foreach($units as $u)
                    <option value="{{ $u->id }}" @selected((string)$unitId===(string)$u->id)>{{ $u->name }}</option>
                  @endforeach
                </select>
              </div>
            </form>
          </div>
        </details>
      </div>
    </div>
  </x-card>

  <div class="admin-search-resultbar">
    <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $items->count() }} dari {{ $total }} user">Menampilkan {{ $items->count() }} dari {{ $total }} user</div>
    @if($filterCount > 0)
      <div class="admin-search-resultbar__chips">
        @if($selectedUnit)
          <span class="admin-search-result-chip">Unit: {{ $selectedUnit->name }}</span>
        @endif
      </div>
    @endif
  </div>

  <div class="grid gap-4 mt-6 users-grid admin-users-list">
    @forelse($items as $u)
      @php
        $userSearchText = trim(implode(' ', array_filter([
          $u->name,
          $u->email,
          $u->student_number,
          $u->unit?->name,
          $u->roles->pluck('name')->implode(' '),
        ])));
      @endphp
      <x-card class="admin-users-item" data-realtime-search-item data-realtime-search-text="{{ $userSearchText }}">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between admin-users-item__row">
          <div class="flex items-start gap-3 min-w-0 admin-users-item__identity">
            <x-user.avatar :user="$u" size="44" class="rounded-2xl" />
            <div class="min-w-0">
              <div class="text-sm font-semibold">{{ $u->name }}</div>
              <div class="text-xs text-muted mt-1 flex flex-wrap gap-x-2 gap-y-1 admin-users-item__meta">
                <span class="admin-users-item__text">{{ $u->email }}</span>
                @if($u->student_number)
                  <span class="text-zinc-300 dark:text-zinc-700 admin-users-item__sep" aria-hidden="true">&bull;</span>
                  <span class="admin-users-item__text">Nomor Induk (NIP/NPM): {{ $u->student_number }}</span>
                @endif
                <span class="text-zinc-300 dark:text-zinc-700 admin-users-item__sep" aria-hidden="true">&bull;</span>
                <span class="admin-users-item__text">Unit: {{ $u->unit?->name ?? '-' }}</span>
              </div>

              <div class="mt-2 flex flex-wrap gap-2" aria-label="Roles user">
                @forelse($u->roles as $r)
                  <x-badge variant="primary">{{ $r->name }}</x-badge>
                @empty
                  <span class="text-xs text-muted">Belum ada role</span>
                @endforelse
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2 justify-end flex-wrap admin-users-item__actions">
            <x-button variant="secondary" href="{{ route('admin.users.edit',$u) }}">Edit</x-button>
            <form class="admin-users-item__delete" method="POST" action="{{ route('admin.users.destroy',$u) }}" onsubmit="return confirm('Hapus user ini?')">
              @csrf
              @method('DELETE')
              <x-button variant="danger" type="submit">Hapus</x-button>
            </form>
          </div>
        </div>
      </x-card>
    @empty
      <x-card class="admin-users-empty">
        <div class="admin-empty">Belum ada user.</div>
      </x-card>
    @endforelse
    @if($items->count() > 0)
      <x-card class="hidden admin-users-empty" data-realtime-search-empty>
        <div class="admin-empty">Tidak ada user yang cocok dengan pencarian.</div>
      </x-card>
    @endif
  </div>

  <div class="admin-pagination admin-users-pagination">{{ $items->onEachSide(1)->links('components.public.pagination') }}</div>
</div>
@endsection
