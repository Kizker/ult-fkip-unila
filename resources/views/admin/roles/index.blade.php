@extends('layouts.app')
@section('section','Peran')
@section('content')
@php
  $totalRoles = method_exists($roles, 'total') ? (int) $roles->total() : $roles->count();
  $totalPerms = $permissions->count();
  $permissionLabel = static fn (?string $name): string => \App\Support\PermissionLabel::make($name);
  $shownRoles = (int) $roles->count();
@endphp

<div class="page-admin-roles-index">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master akses</div>
      <h1 class="admin-page-title">Peran</h1>
      <p class="admin-page-subtitle">Siapkan role beserta permission-nya. Saat menambah user, cukup pilih role yang sudah ada.</p>
    </div>

    <div class="admin-page-actions">
      <div class="admin-meta">
        <div class="admin-meta-pill" aria-label="Total roles">
          <div class="admin-meta-pill__label">Roles</div>
          <div class="admin-meta-pill__value">{{ $totalRoles }}</div>
        </div>
        <div class="admin-meta-pill" aria-label="Total permissions">
          <div class="admin-meta-pill__label">Permissions</div>
          <div class="admin-meta-pill__value">{{ $totalPerms }}</div>
        </div>
      </div>

      <x-button variant="secondary" href="{{ route('admin.roles.create') }}">Tambah Role</x-button>
    </div>
  </header>

  <x-card class="admin-search-card roles-toolbar" data-admin-search-card>
    <div class="admin-search" role="search" aria-label="Pencarian role">
      <div class="admin-search__toolbar">
        <div class="admin-search__field">
          <label for="admin-roles-live-search" class="sr-only">Cari role</label>
          <div class="admin-search__input-wrap">
            <input
              id="admin-roles-live-search"
              type="text"
              class="admin-search__input"
              placeholder="Cari..."
              data-realtime-search-input
              data-realtime-search-mode="filter"
              data-realtime-search-scope=".roles-grid"
              data-realtime-search-item-selector="[data-realtime-search-item]"
              data-realtime-search-empty-selector="[data-realtime-search-empty]"
              data-realtime-search-count-selector="[data-realtime-search-count]"
            >
            <button type="button" class="admin-search__clear admin-search__clear--disabled" aria-label="Hapus pencarian" data-admin-search-clear data-reset-url="{{ route('admin.roles.index') }}">
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
    <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $shownRoles }} dari {{ $totalRoles }} role">Menampilkan {{ $shownRoles }} dari {{ $totalRoles }} role</div>
  </div>

  <div class="grid gap-4 mt-6 roles-grid">
    @foreach($roles as $r)
      @php
        $permCount = $r->permissions->count();
        $assigned = (int) (($assignedCounts[$r->id] ?? 0));
        $cannotDelete = $r->name === 'Superadmin' || $assigned > 0;
        $previewPerms = $r->permissions->sortBy('name')->take(10);
        $permissionNames = $r->permissions->pluck('name');
        $permissionLabels = $permissionNames->map(static fn (string $name): string => $permissionLabel($name))->implode(' ');
        $searchText = trim($r->name . ' ' . $permissionNames->implode(' ') . ' ' . $permissionLabels . ' ' . $assigned);
      @endphp
      <x-card class="roles-item" data-realtime-search-item data-realtime-search-text="{{ $searchText }}">
        <div class="roles-item__row flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
          <div class="roles-item__main min-w-0">
            <div class="flex items-start gap-3">
              <div class="h-10 w-10 rounded-2xl bg-[rgb(var(--c-primary)/.10)] border border-[rgb(var(--c-primary)/.18)] flex items-center justify-center flex-shrink-0">
                <span class="text-[rgb(var(--c-primary))] font-semibold text-sm">
                  {{ mb_substr($r->name, 0, 1) }}
                </span>
              </div>

              <div class="min-w-0">
                <div class="text-sm font-semibold">{{ $r->name }}</div>
                <div class="text-xs text-muted mt-1 flex flex-wrap gap-x-2 gap-y-1">
                  <span>{{ $permCount }} permission</span>
                  <span class="text-zinc-300 dark:text-zinc-700" aria-hidden="true">&bull;</span>
                  <span>{{ $assigned }} user</span>
                </div>
              </div>
            </div>

            <details class="mt-3 role-perms">
              <summary class="cursor-pointer select-none list-none flex items-center justify-between gap-3">
                <div class="text-xs font-semibold text-muted">Permissions</div>
                <div class="text-xs text-[rgb(var(--c-primary))] font-semibold">
                  <span class="role-perms__toggle role-perms__toggle--see">
                    {{ $permCount > 10 ? 'Lihat semua' : 'Lihat' }}
                  </span>
                  <span class="role-perms__toggle role-perms__toggle--close">Tutup</span>
                </div>
              </summary>

              <div class="mt-3">
                <div class="flex flex-wrap gap-2">
                  @forelse($previewPerms as $p)
                    <x-badge variant="primary" title="{{ $p->name }}">{{ $permissionLabel($p->name) }}</x-badge>
                  @empty
                    <span class="text-xs text-muted">Belum ada permission.</span>
                  @endforelse
                  @if($permCount > 10)
                    <span class="text-xs text-muted">+{{ $permCount - 10 }} lagi</span>
                  @endif
                </div>

                @if($permCount > 10)
                  <div class="roles-perms__all mt-3 max-h-56 overflow-auto rounded-xl border border-[rgb(var(--c-border))] bg-white/60 dark:bg-zinc-900/40 p-3">
                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                      @foreach($r->permissions->sortBy('name') as $p)
                        <div class="text-xs text-zinc-700 dark:text-zinc-200 truncate" title="{{ $p->name }}">
                          {{ $permissionLabel($p->name) }}
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endif
              </div>
            </details>
          </div>

          <div class="roles-item__actions flex items-center gap-2 justify-end flex-wrap">
            <x-button variant="secondary" href="{{ route('admin.roles.edit', $r) }}">Edit</x-button>
            <form method="POST" action="{{ route('admin.roles.destroy', $r) }}" onsubmit="return confirm('Hapus role ini?')">
              @csrf
              @method('DELETE')
              <x-button variant="danger" type="submit" :disabled="$cannotDelete">Hapus</x-button>
            </form>

            @if($cannotDelete)
              <div class="roles-item__note w-full text-xs text-muted text-right">
                @if($r->name === 'Superadmin')
                  Tidak bisa menghapus role Superadmin.
                @else
                  Tidak bisa dihapus karena dipakai {{ $assigned }} user.
                @endif
              </div>
            @endif
          </div>
        </div>
      </x-card>
    @endforeach
    @if($roles->count() > 0)
      <x-card class="hidden" data-realtime-search-empty>
        <div class="admin-empty">Tidak ada role yang cocok dengan pencarian.</div>
      </x-card>
    @endif
  </div>

  @if(method_exists($roles, 'hasPages') && $roles->hasPages())
    <div class="admin-pagination mt-6">
      {{ $roles->onEachSide(1)->links('components.public.pagination') }}
    </div>
  @endif
</div>
@endsection
