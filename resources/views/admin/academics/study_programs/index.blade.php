@extends('layouts.app')
@section('section','Program Studi')
@section('content')
@php
  $total = method_exists($items, 'total') ? (int) $items->total() : (int) count($items);
  $shown = (int) $items->count();
  $filterCount = filled($departmentId) ? 1 : 0;
  $selectedDepartment = collect($departments)->firstWhere('id', (int) $departmentId);
@endphp

<div class="page-admin-academics-study-programs-index">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master akademik</div>
      <h1 class="admin-page-title">Program Studi</h1>
      <p class="admin-page-subtitle">Kelola program studi dan pengelompokan berdasarkan jurusan.</p>
    </div>

    <div class="admin-page-actions">
      <div class="admin-meta">
        <div class="admin-meta-pill" aria-label="Total prodi">
          <div class="admin-meta-pill__label">Total</div>
          <div class="admin-meta-pill__value">{{ $total }}</div>
        </div>
      </div>
      <x-button href="{{ route('admin.prodi.create') }}">Tambah prodi</x-button>
    </div>
  </header>

  <x-card class="admin-search-card" data-admin-search-card>
    <div class="admin-search" role="search" aria-label="Pencarian program studi">
      <div class="admin-search__toolbar">
        <div class="admin-search__field">
          <label for="admin-study-programs-live-search" class="sr-only">Cari prodi</label>
          <div class="admin-search__input-wrap">
            <input
              id="admin-study-programs-live-search"
              type="text"
              class="admin-search__input"
              placeholder="Cari..."
              data-realtime-search-input
              data-realtime-search-mode="filter"
              data-realtime-search-scope=".study-programs-search-scope"
              data-realtime-search-item-selector="[data-realtime-search-item]"
              data-realtime-search-empty-selector="[data-realtime-search-empty]"
              data-realtime-search-count-selector="[data-realtime-search-count]"
            >
            <button type="button" class="admin-search__clear {{ $filterCount > 0 ? '' : 'admin-search__clear--disabled' }}" aria-label="Reset pencarian dan filter" data-admin-search-clear data-reset-url="{{ route('admin.prodi.index') }}">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
              </svg>
            </button>
          </div>
        </div>

        <details class="admin-search-filter-menu">
          <summary class="admin-search-filter-menu__toggle" aria-label="Buka filter program studi">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 7h16M7 12h10M10 17h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
            </svg>
          </summary>

          <div class="admin-search-filter-menu__panel">
            <form method="GET" class="admin-search-filter-menu__form">
              <div class="admin-search-filter-menu__field">
                <label class="admin-search-filter-menu__label" for="admin-study-programs-department">Jurusan</label>
                <select id="admin-study-programs-department" name="jurusan_id" data-admin-search-track onchange="this.form.requestSubmit()">
                  <option value="">Semua</option>
                  @foreach($departments as $d)
                    <option value="{{ $d->id }}" @selected((string) $departmentId === (string) $d->id)>{{ $d->name }}</option>
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
    <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $shown }} dari {{ $total }} prodi">Menampilkan {{ $shown }} dari {{ $total }} prodi</div>
    @if($selectedDepartment)
      <div class="admin-search-resultbar__chips">
        <span class="admin-search-result-chip">Jurusan: {{ $selectedDepartment->name }}</span>
      </div>
    @endif
  </div>

  <x-card class="study-programs-search-scope">
    <div class="table-responsive overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="text-muted">
          <tr>
            <th class="text-left py-2">Kode</th>
            <th class="text-left py-2">Nama Prodi</th>
            <th class="text-left py-2">Jurusan</th>
            <th class="text-right py-2">User</th>
            <th class="text-left py-2">Status</th>
            <th class="text-right py-2">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $it)
            @php
              $studyProgramSearchText = trim(implode(' ', array_filter([
                $it->code,
                $it->name,
                $it->parent?->name,
                (string) ($it->users_count ?? 0),
                $it->is_active ? 'active aktif' : 'inactive nonaktif',
              ])));
            @endphp
            <tr class="border-t border-[rgb(var(--c-border))]" data-realtime-search-item data-realtime-search-text="{{ $studyProgramSearchText }}">
              <td class="py-2 font-mono text-xs text-muted">{{ $it->code }}</td>
              <td class="py-2 font-medium">{{ $it->name }}</td>
              <td class="py-2 text-muted">{{ $it->parent?->name ?? '-' }}</td>
              <td class="py-2 text-right">{{ (int) ($it->users_count ?? 0) }}</td>
              <td class="py-2">
                <x-badge :variant="$it->is_active ? 'success' : 'warning'">
                  {{ $it->is_active ? 'Active' : 'Inactive' }}
                </x-badge>
              </td>
              <td class="py-2 text-right">
                <div class="inline-flex gap-2">
                  <x-button href="{{ route('admin.prodi.edit',$it) }}" variant="ghost">Edit</x-button>
                  <form method="post" action="{{ route('admin.prodi.destroy',$it) }}" onsubmit="return confirm('Hapus prodi ini?')">
                    @csrf @method('DELETE')
                    <x-button type="submit" variant="danger">Hapus</x-button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr class="border-t border-[rgb(var(--c-border))]">
              <td class="py-6 text-center text-muted" colspan="6">Belum ada program studi.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="table-cards">
      <div class="grid gap-3">
        @forelse($items as $it)
          @php
            $studyProgramSearchText = trim(implode(' ', array_filter([
              $it->code,
              $it->name,
              $it->parent?->name,
              (string) ($it->users_count ?? 0),
              $it->is_active ? 'active aktif' : 'inactive nonaktif',
            ])));
          @endphp
          <article class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-3" data-realtime-search-item data-realtime-search-text="{{ $studyProgramSearchText }}">
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
                <div class="text-muted">User</div>
                <div class="font-semibold text-sm">{{ (int) ($it->users_count ?? 0) }}</div>
              </div>
              <div class="rounded-xl border border-[rgb(var(--c-border))] px-2 py-1.5">
                <div class="text-muted">Status</div>
                <div class="font-semibold text-sm">{{ $it->is_active ? 'Aktif' : 'Nonaktif' }}</div>
              </div>
            </div>

            <div class="mt-3 text-xs text-muted">
              <span class="font-semibold text-[rgb(var(--c-fg))]">Jurusan:</span>
              <span class="break-words">{{ $it->parent?->name ?? '-' }}</span>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
              <x-button href="{{ route('admin.prodi.edit',$it) }}" variant="ghost">Edit</x-button>
              <form method="post" action="{{ route('admin.prodi.destroy',$it) }}" onsubmit="return confirm('Hapus prodi ini?')">
                @csrf @method('DELETE')
                <x-button type="submit" variant="danger">Hapus</x-button>
              </form>
            </div>
          </article>
        @empty
          <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] px-4 py-6 text-center text-sm text-muted">
            Belum ada program studi.
          </div>
        @endforelse
      </div>
    </div>
    @if($items->count() > 0)
      <div class="hidden rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] px-4 py-6 text-center text-sm text-muted mt-3" data-realtime-search-empty>
        Tidak ada program studi yang cocok dengan pencarian.
      </div>
    @endif

    <div class="mt-4">{{ $items->onEachSide(1)->links('components.public.pagination') }}</div>
  </x-card>
</div>
@endsection
