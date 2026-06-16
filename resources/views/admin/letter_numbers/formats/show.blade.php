@extends('layouts.app')
@section('section','Template Nomor Surat')

@section('content')
@php
  $nextSeq = (int) $lastSeq + 1;
  $total = method_exists($items, 'total') ? (int) $items->total() : (int) count($items);
  $shown = (int) $items->count();
  $filterCount = filled($year) ? 1 : 0;
@endphp

<div class="page-admin-doc-formats-index page-admin-doc-formats-index--letter page-admin-doc-formats-show--letter">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Nomor surat</div>
      <h1 class="admin-page-title">History Template</h1>
      <p class="admin-page-subtitle">
        {{ $format->unit->type->value }} &mdash; {{ $format->unit->name }} ({{ $format->unit->code }}) &bull; <span class="dn-mono">{{ $format->format_key }}</span>
      </p>
    </div>

    <div class="admin-page-actions">
      <x-button variant="secondary" href="{{ route('admin.letter_formats.export', ['letter_format' => $format, 'year' => $year]) }}">Export Excel</x-button>
      <x-button variant="ghost" href="{{ route('admin.letter_formats.index') }}">Kembali</x-button>
      @can('update', $format)
        <x-button variant="secondary" href="{{ route('admin.letter_formats.edit', $format) }}">Edit</x-button>
      @else
        <span class="text-xs text-muted">Read-only (turunan)</span>
      @endcan
    </div>
  </header>

  <x-card class="admin-search-card dn-toolbar" data-admin-search-card>
    <div class="admin-search" role="search" aria-label="Pencarian history nomor surat">
      <div class="admin-search__toolbar">
        <div class="admin-search__field">
          <label for="admin-letter-format-history-live-search" class="sr-only">Cari history</label>
          <div class="admin-search__input-wrap">
            <input
              id="admin-letter-format-history-live-search"
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
            <button type="button" class="admin-search__clear {{ $filterCount > 0 ? '' : 'admin-search__clear--disabled' }}" aria-label="Reset pencarian dan filter" data-admin-search-clear data-reset-url="{{ route('admin.letter_formats.show', $format) }}">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
              </svg>
            </button>
          </div>
        </div>

        <details class="admin-search-filter-menu">
          <summary class="admin-search-filter-menu__toggle" aria-label="Buka filter history nomor surat">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 7h16M7 12h10M10 17h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
            </svg>
          </summary>

          <div class="admin-search-filter-menu__panel">
            <form method="GET" class="admin-search-filter-menu__form">
              <div class="admin-search-filter-menu__field">
                <label class="admin-search-filter-menu__label" for="admin-letter-format-history-year">Tahun</label>
                <input id="admin-letter-format-history-year" name="year" type="number" value="{{ $year }}" min="2000" max="2100" data-admin-search-track onchange="this.form.requestSubmit()" />
              </div>
            </form>
          </div>
        </details>
      </div>
    </div>
  </x-card>

  <div class="admin-search-resultbar">
    <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $shown }} dari {{ $total }} history">Menampilkan {{ $shown }} dari {{ $total }} history</div>
    @if($filterCount > 0)
      <div class="admin-search-resultbar__chips">
        <span class="admin-search-result-chip">Tahun: {{ $year }}</span>
      </div>
    @endif
  </div>

  <x-card class="dn-card lnf-table-card">
    <div class="dn-history-summary">
      <div class="dn-history-metrics">
        <div class="admin-meta-pill" aria-label="Jumlah dipakai">
          <div class="admin-meta-pill__label">Dipakai ({{ $year }})</div>
          <div class="admin-meta-pill__value">{{ (int) $usedCount }}</div>
        </div>
        <div class="admin-meta-pill" aria-label="Nomor terbesar terpakai">
          <div class="admin-meta-pill__label">Max terpakai</div>
          <div class="admin-meta-pill__value">{{ (int) $maxUsed }}</div>
        </div>
        <div class="admin-meta-pill" aria-label="Nomor terakhir sequence">
          <div class="admin-meta-pill__label">Last seq</div>
          <div class="admin-meta-pill__value">{{ (int) $lastSeq }}</div>
        </div>
        <div class="admin-meta-pill" aria-label="Nomor berikutnya">
          <div class="admin-meta-pill__label">Next</div>
          <div class="admin-meta-pill__value">{{ (int) $nextSeq }}</div>
        </div>
      </div>

      @can('update', $format)
        <form method="POST" action="{{ route('admin.letter_formats.sequence', $format) }}" class="dn-seq-form">
          @csrf
          <input type="hidden" name="year" value="{{ $year }}">
          <div class="dn-seq-form__field">
            <label class="dn-label">Set last_seq</label>
            <input type="number" name="last_seq" value="{{ old('last_seq', $lastSeq) }}" min="{{ (int) $maxUsed }}" class="dn-select" />
            <div class="text-xs text-muted mt-1">Hanya bisa dinaikkan (min = max terpakai).</div>
          </div>
          <x-button type="submit" variant="secondary">Simpan</x-button>
        </form>
      @endcan
    </div>

    <div class="dn-table-wrap" role="region" aria-label="Tabel history nomor surat" tabindex="0">
      <table class="dn-table">
        <thead>
          <tr>
            <th>Tanggal</th>
            <th>Nomor</th>
            <th class="dn-col-num">Seq</th>
            <th>Pemohon</th>
            <th>Diinput oleh</th>
            <th class="dn-col-actions">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($items as $it)
            @php
              $historySearchText = trim(implode(' ', array_filter([
                $it->number_text,
                (string) $it->number_seq,
                $it->request?->student?->name,
                $it->issuer?->name,
                $it->issued_at?->format('Y-m-d H:i'),
              ])));
            @endphp
            <tr data-realtime-search-item data-realtime-search-text="{{ $historySearchText }}">
              <td class="dn-cell" data-label="Tanggal">{{ $it->issued_at?->format('Y-m-d H:i') ?? '-' }}</td>
              <td class="dn-template dn-mono" data-label="Nomor">{{ $it->number_text }}</td>
              <td class="dn-col-num" data-label="Seq">{{ (int) $it->number_seq }}</td>
              <td class="dn-cell" data-label="Pemohon">{{ $it->request?->student?->name ?? '-' }}</td>
              <td class="dn-cell" data-label="Diinput oleh">{{ $it->issuer?->name ?? '-' }}</td>
              <td class="dn-col-actions" data-label="Aksi">
                <div class="dn-actions">
                  <x-button href="{{ $it->request ? route('admin.requests.show', $it->request) : '#' }}" variant="ghost" size="sm">Buka permohonan</x-button>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="dn-empty">Belum ada nomor yang diterbitkan untuk tahun {{ $year }}.</td>
            </tr>
          @endforelse
          @if($items->count() > 0)
            <tr class="hidden" data-realtime-search-empty>
              <td colspan="6" class="dn-empty">Tidak ada history nomor surat yang cocok dengan pencarian.</td>
            </tr>
          @endif
        </tbody>
      </table>
    </div>

    <div class="dn-pagination">{{ $items->onEachSide(1)->links('components.public.pagination') }}</div>
  </x-card>

  <x-card class="dn-help">
    <div class="dn-help__title">Template saat ini</div>
    <div class="dn-mono">{{ $format->template }}</div>
  </x-card>
</div>
@endsection
