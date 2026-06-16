@extends('layouts.app')
@section('section','Template Nomor Dokumen')

@section('content')
@php
  $total = method_exists($formats, 'total') ? (int) $formats->total() : (int) count($formats);
  $shown = (int) $formats->count();
  $filterCount = filled($unitId) ? 1 : 0;
  $selectedUnit = collect($units)->firstWhere('id', (int) $unitId);
@endphp

<div class="page-admin-doc-formats-index">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Nomor dokumen</div>
      <h1 class="admin-page-title">Format Nomor Dokumen</h1>
      <p class="admin-page-subtitle">
        Atur template nomor per unit (Prodi/Jurusan/Fakultas) dan per <span class="dn-mono">format_key</span>.
      </p>
    </div>

    <div class="admin-page-actions">
      <div class="admin-meta">
        <div class="admin-meta-pill" aria-label="Total format">
          <div class="admin-meta-pill__label">Total</div>
          <div class="admin-meta-pill__value">{{ $total }}</div>
        </div>
      </div>

      <x-button href="{{ route('admin.doc_formats.create') }}">Tambah format</x-button>
    </div>
  </header>

  <x-card class="admin-search-card dn-toolbar" data-admin-search-card>
    <div class="admin-search" role="search" aria-label="Pencarian format nomor dokumen">
      <div class="admin-search__toolbar">
        <div class="admin-search__field">
          <label for="admin-doc-formats-live-search" class="sr-only">Cari format</label>
          <div class="admin-search__input-wrap">
            <input
              id="admin-doc-formats-live-search"
              type="text"
              class="admin-search__input"
              placeholder="Cari..."
              data-realtime-search-input
              data-realtime-search-mode="filter"
              data-realtime-search-scope=".dn-table-wrap"
              data-realtime-search-item-selector="tbody tr[data-realtime-search-item]"
              data-realtime-search-empty-selector="tbody tr[data-realtime-search-empty]"
              data-realtime-search-count-selector="[data-realtime-search-count]"
            >
            <button type="button" class="admin-search__clear {{ $filterCount > 0 ? '' : 'admin-search__clear--disabled' }}" aria-label="Reset pencarian dan filter" data-admin-search-clear data-reset-url="{{ route('admin.doc_formats.index') }}">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
              </svg>
            </button>
          </div>
        </div>

        <details class="admin-search-filter-menu">
          <summary class="admin-search-filter-menu__toggle" aria-label="Buka filter format nomor dokumen">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 7h16M7 12h10M10 17h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
            </svg>
          </summary>

          <div class="admin-search-filter-menu__panel">
            <form method="GET" class="admin-search-filter-menu__form">
              <div class="admin-search-filter-menu__field">
                <label class="admin-search-filter-menu__label" for="admin-doc-formats-unit">Filter Unit</label>
                <select id="admin-doc-formats-unit" name="unit_id" data-admin-search-track onchange="this.form.requestSubmit()">
                  <option value="">Semua unit dalam scope</option>
                  @foreach($units as $u)
                    <option value="{{ $u->id }}" @selected((int)$unitId === (int)$u->id)>{{ $u->type->value }} &mdash; {{ $u->name }} ({{ $u->code }})</option>
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
    <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $shown }} dari {{ $total }} format">Menampilkan {{ $shown }} dari {{ $total }} format</div>
    @if($selectedUnit)
      <div class="admin-search-resultbar__chips">
        <span class="admin-search-result-chip">Unit: {{ $selectedUnit->name }}</span>
      </div>
    @endif
  </div>

  <x-card class="dn-card">
    <div class="dn-table-wrap" role="region" aria-label="Tabel format nomor dokumen" tabindex="0">
      <table class="dn-table">
        <thead>
          <tr>
            <th>Unit</th>
            <th>Key</th>
            <th>Nama</th>
            <th>Template</th>
            <th class="dn-col-num">Padding</th>
            <th class="dn-col-badge">Status</th>
            <th class="dn-col-actions">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($formats as $f)
            @php
              $docFormatSearchText = trim(implode(' ', array_filter([
                $f->unit->name,
                $f->unit->type->value,
                $f->unit->code,
                $f->format_key,
                $f->name,
                $f->template,
                (string) $f->seq_padding,
                $f->is_active ? 'aktif active' : 'nonaktif inactive',
              ])));
            @endphp
            <tr data-realtime-search-item data-realtime-search-text="{{ $docFormatSearchText }}">
              <td>
                <div class="dn-unit__name">{{ $f->unit->name }}</div>
                <div class="dn-unit__meta">{{ $f->unit->type->value }} &bull; {{ $f->unit->code }}</div>
              </td>
              <td class="dn-mono">{{ $f->format_key }}</td>
              <td class="dn-cell">{{ $f->name }}</td>
              <td class="dn-template dn-mono">{{ $f->template }}</td>
              <td class="dn-col-num">{{ $f->seq_padding }}</td>
              <td class="dn-col-badge">
                @if($f->is_active)
                  <x-badge variant="success">Aktif</x-badge>
                @else
                  <x-badge variant="secondary">Nonaktif</x-badge>
                @endif
              </td>
              <td class="dn-col-actions">
                <div class="dn-actions">
                  <x-button href="{{ route('admin.doc_formats.edit', $f) }}" variant="secondary" size="sm">Edit</x-button>
                  <form method="POST" action="{{ route('admin.doc_formats.destroy', $f) }}" class="dn-delete">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dn-danger-btn" data-confirm="Hapus format ini?">Hapus</button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="dn-empty">Belum ada format. Klik <span class="dn-mono">Tambah format</span> untuk membuat format baru.</td>
            </tr>
          @endforelse
          @if($formats->count() > 0)
            <tr class="hidden" data-realtime-search-empty>
              <td colspan="7" class="dn-empty">Tidak ada format yang cocok dengan pencarian.</td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>

    <div class="dn-pagination">{{ $formats->onEachSide(1)->links('components.public.pagination') }}</div>
  </x-card>

  <x-card class="dn-help">
    <div class="dn-help__title">Placeholder tersedia</div>
    <ul class="dn-help__list">
      <li><span class="dn-mono">{SEQ}</span> atau <span class="dn-mono">{SEQ:4}</span> untuk padding otomatis</li>
      <li><span class="dn-mono">{UNIT_CODE}</span> atau <span class="dn-mono">{UNIT}</span> (alias)</li>
      <li><span class="dn-mono">{YYYY}</span> tahun, <span class="dn-mono">{MM}</span> bulan</li>
    </ul>
  </x-card>
</div>
@endsection
