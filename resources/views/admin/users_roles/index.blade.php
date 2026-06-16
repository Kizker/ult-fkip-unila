@extends('layouts.app')
@section('section','Pengguna dan Peran')
@section('content')
@php
  $permissionLabel = static fn (?string $name): string => \App\Support\PermissionLabel::make($name);
  $totalUsers = method_exists($users, 'total') ? (int) $users->total() : (int) $users->count();
  $shownUsers = (int) $users->count();
@endphp
<div class="page-admin-users-roles page-admin-users-roles-index" data-admin-users-roles-page>
  <header class="admin-page-header ur-header">
    <div class="ur-header__copy">
      <div class="ur-kicker">Manajemen akses</div>
      <h1 class="ur-title">Pengguna dan Peran</h1>
      <p class="ur-subtitle">
        Atur role dan direct permission per pengguna. Pastikan prinsip least privilege.
      </p>
    </div>
    <div class="ur-header__meta">
      <div class="ur-meta-pill">
        <div class="ur-meta-pill__label">Roles</div>
        <div class="ur-meta-pill__value">{{ $roles->count() }}</div>
      </div>
      <div class="ur-meta-pill">
        <div class="ur-meta-pill__label">Permissions</div>
        <div class="ur-meta-pill__value">{{ $permissions->count() }}</div>
      </div>
    </div>
  </header>

  <x-card class="admin-search-card ur-toolbar" data-admin-search-card>
    <div class="admin-search" role="search" aria-label="Pencarian user dan peran">
      <div class="admin-search__toolbar">
        <div class="admin-search__field">
          <label for="admin-users-roles-live-search" class="sr-only">Cari user</label>
          <div class="admin-search__input-wrap">
            <input
              id="admin-users-roles-live-search"
              type="text"
              class="admin-search__input"
              placeholder="Cari..."
              data-realtime-search-input
              data-realtime-search-mode="filter"
              data-realtime-search-scope=".ur-list"
              data-realtime-search-item-selector=".ur-card[data-realtime-search-item]"
              data-realtime-search-empty-selector="[data-realtime-search-empty]"
              data-realtime-search-count-selector="[data-realtime-search-count]"
            >
            <button type="button" class="admin-search__clear admin-search__clear--disabled" aria-label="Hapus pencarian" data-admin-search-clear data-reset-url="{{ route('admin.users_roles.index') }}">
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
    <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $shownUsers }} dari {{ $totalUsers }} user">Menampilkan {{ $shownUsers }} dari {{ $totalUsers }} user</div>
  </div>

  <div class="ur-list">
    @foreach($users as $u)
      @php
        $directPermissionNames = $u->permissions->pluck('name');
        $directPermissionLabels = $directPermissionNames->map(static fn (string $name): string => $permissionLabel($name))->implode(' ');
        $userRoleSearchText = trim(implode(' ', array_filter([
          $u->name,
          $u->email,
          $u->unit?->name,
          $u->roles->pluck('name')->implode(' '),
          $directPermissionNames->implode(' '),
          $directPermissionLabels,
        ])));
      @endphp
      <x-card class="ur-card ur-surface" data-realtime-search-item data-realtime-search-text="{{ $userRoleSearchText }}">
        <div class="ur-card__top">
          <div class="ur-user">
            <x-user.avatar :user="$u" size="44" class="ur-avatar" />
            <div class="ur-user__meta">
              <div class="ur-user__name">{{ $u->name }}</div>
              <div class="ur-user__sub">
                <span class="ur-user__email">{{ $u->email }}</span>
                <span class="ur-sep" aria-hidden="true">&bull;</span>
                <span class="ur-user__unit">Unit: {{ $u->unit?->name ?? '-' }}</span>
              </div>
            </div>
          </div>

          <div class="ur-badges" aria-label="Roles saat ini">
            @forelse($u->roles as $r)
              <x-badge variant="primary">{{ $r->name }}</x-badge>
            @empty
              <span class="ur-muted">Belum ada role.</span>
            @endforelse
          </div>
        </div>

        <div class="ur-panels">
          <form class="ur-panel" method="POST" action="{{ route('admin.users_roles.roles',$u) }}" aria-label="Edit roles {{ $u->name }}">
            @csrf
            <div class="ur-panel__head">
              <div>
                <div class="ur-panel__title">Roles</div>
                <div class="ur-panel__hint">Hak akses berbasis peran.</div>
              </div>
              <x-button variant="secondary" type="submit">Simpan</x-button>
            </div>

            <div class="ur-checkgrid">
              @foreach($roles as $r)
                <label class="ur-check">
                  <input
                    type="checkbox"
                    name="roles[]"
                    value="{{ $r->name }}"
                    @checked($u->hasRole($r->name))
                    class="ur-check__box"
                  >
                  <span class="ur-check__label">{{ $r->name }}</span>
                </label>
              @endforeach
            </div>
          </form>

          <form class="ur-panel" method="POST" action="{{ route('admin.users_roles.permissions',$u) }}" aria-label="Edit direct permissions {{ $u->name }}">
            @csrf
            <div class="ur-panel__head">
              <div>
                <div class="ur-panel__title">Direct permissions</div>
                <div class="ur-panel__hint">Override khusus (gunakan seperlunya).</div>
              </div>
              <x-button variant="secondary" type="submit">Simpan</x-button>
            </div>

            <div class="ur-checkgrid ur-checkgrid--scroll">
              @foreach($permissions as $p)
                <label class="ur-check">
                  <input
                    type="checkbox"
                    name="permissions[]"
                    value="{{ $p->name }}"
                    @checked($u->hasDirectPermission($p->name))
                    class="ur-check__box"
                  >
                  <span class="ur-check__label" title="{{ $p->name }}">{{ $permissionLabel($p->name) }}</span>
                </label>
              @endforeach
            </div>
          </form>
        </div>
      </x-card>
    @endforeach
    @if($users->count() > 0)
      <x-card class="hidden ur-surface" data-realtime-search-empty>
        <div class="ur-muted">Tidak ada user yang cocok dengan pencarian.</div>
      </x-card>
    @endif
  </div>

  <div class="ur-pagination">{{ $users->onEachSide(1)->links('components.public.pagination') }}</div>
</div>
@endsection
