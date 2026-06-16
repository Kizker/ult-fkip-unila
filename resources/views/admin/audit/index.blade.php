@extends('layouts.app')
@section('section','Log Audit')
@section('content')
@php
  $total = method_exists($logs, 'total') ? (int) $logs->total() : (int) count($logs);
  $filterCount =
    (filled($q) ? 1 : 0) +
    (filled($action ?? null) ? 1 : 0) +
    (filled($entityType ?? null) ? 1 : 0) +
    (filled($from ?? null) ? 1 : 0) +
    (filled($to ?? null) ? 1 : 0);
@endphp
<div class="page-admin-audit-index" data-admin-audit-index-page>
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Sistem</div>
      <h1 class="admin-page-title">Log Audit</h1>
      <p class="admin-page-subtitle">Jejak aksi penting (login, perubahan status, download, RBAC).</p>
    </div>

    <div class="admin-page-actions">
      <div class="admin-meta">
        <div class="admin-meta-pill" aria-label="Total audit logs">
          <div class="admin-meta-pill__label">Total</div>
          <div class="admin-meta-pill__value">{{ $total }}</div>
        </div>
        <div class="admin-meta-pill" aria-label="Filter aktif">
          <div class="admin-meta-pill__label">Filter</div>
          <div class="admin-meta-pill__value">{{ $filterCount }}</div>
        </div>
      </div>
    </div>
  </header>

  <x-card class="admin-search-card audit-filters" data-admin-search-card>
    <form method="GET" class="admin-search" role="search" aria-label="Pencarian audit log">
      <div class="admin-search__toolbar">
        <div class="admin-search__field">
          <label for="admin-audit-search" class="sr-only">Cari audit log</label>
          <div class="admin-search__input-wrap">
            <input
              id="admin-audit-search"
              type="text"
              name="q"
              value="{{ $q }}"
              class="admin-search__input"
              placeholder="Cari..."
              data-realtime-search-input
              data-realtime-search-mode="filter"
              data-realtime-search-scope=".audit-list"
              data-realtime-search-item-selector="[data-realtime-search-item]"
              data-realtime-search-empty-selector="[data-realtime-search-empty]"
              data-realtime-search-count-selector="[data-realtime-search-count]"
            />
            <button type="button" class="admin-search__clear {{ $filterCount > 0 ? '' : 'admin-search__clear--disabled' }}" aria-label="Reset pencarian dan filter" data-admin-search-clear data-reset-url="{{ route('admin.audit.index') }}">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
              </svg>
            </button>
          </div>
        </div>

        <details class="admin-search-filter-menu">
          <summary class="admin-search-filter-menu__toggle" aria-label="Buka filter audit">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 7h16M7 12h10M10 17h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
            </svg>
          </summary>

          <div class="admin-search-filter-menu__panel">
            <div class="admin-search-filter-menu__form">
              <div class="admin-search-filter-menu__field">
                <label class="admin-search-filter-menu__label" for="admin-audit-action">Action</label>
                <select id="admin-audit-action" name="action" data-admin-search-track onchange="this.form.requestSubmit()">
                  <option value="">Semua action</option>
                  @foreach($actionOptions as $opt)
                    <option value="{{ $opt }}" @selected((string) $action === (string) $opt)>{{ $opt }}</option>
                  @endforeach
                </select>
              </div>

              <div class="admin-search-filter-menu__field">
                <label class="admin-search-filter-menu__label" for="admin-audit-entity">Entity</label>
                <select id="admin-audit-entity" name="entity_type" data-admin-search-track onchange="this.form.requestSubmit()">
                  <option value="">Semua entity</option>
                  @foreach($entityTypeOptions as $opt)
                    <option value="{{ $opt }}" @selected((string) $entityType === (string) $opt)>{{ $opt }}</option>
                  @endforeach
                </select>
              </div>

              <div class="admin-search-filter-menu__field">
                <label class="admin-search-filter-menu__label" for="admin-audit-from">Dari</label>
                <input id="admin-audit-from" type="date" name="from" value="{{ $from }}" data-admin-search-track onchange="this.form.requestSubmit()">
              </div>

              <div class="admin-search-filter-menu__field">
                <label class="admin-search-filter-menu__label" for="admin-audit-to">Sampai</label>
                <input id="admin-audit-to" type="date" name="to" value="{{ $to }}" data-admin-search-track onchange="this.form.requestSubmit()">
              </div>
            </div>
          </div>
        </details>
      </div>
    </form>
  </x-card>

  <div class="admin-search-resultbar">
    <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $logs->count() }} dari {{ $total }} log">Menampilkan {{ $logs->count() }} dari {{ $total }} log</div>
    @if($filterCount > 0)
      <div class="admin-search-resultbar__chips">
        @if(filled($action ?? null))
          <span class="admin-search-result-chip">Action: {{ $action }}</span>
        @endif
        @if(filled($entityType ?? null))
          <span class="admin-search-result-chip">Entity: {{ $entityType }}</span>
        @endif
        @if(filled($from ?? null))
          <span class="admin-search-result-chip">Dari: {{ $from }}</span>
        @endif
        @if(filled($to ?? null))
          <span class="admin-search-result-chip">Sampai: {{ $to }}</span>
        @endif
      </div>
    @endif
  </div>

  <div class="audit-list">
    @forelse($logs as $l)
      @php
        $auditSearchText = trim(implode(' ', array_filter([
          $l->action,
          $l->entity_type,
          $l->entity_id,
          $l->actor?->name,
          $l->actor?->email,
          json_encode($l->metadata, JSON_UNESCAPED_UNICODE),
        ])));
      @endphp
      <x-card class="audit-item" data-realtime-search-item data-realtime-search-text="{{ $auditSearchText }}">
        <div class="audit-row">
          <div class="audit-main">
            <div class="audit-meta">{{ $l->created_at->format('d M Y H:i') }} &bull; {{ $l->actor?->name ?? 'system' }}</div>
            <div class="audit-action">{{ $l->action }}</div>
            <div class="audit-entity">{{ $l->entity_type }} #{{ $l->entity_id }}</div>
          </div>

          <div class="audit-side" aria-label="Metadata JSON">
            <pre class="audit-json">{{ json_encode($l->metadata, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
          </div>
        </div>
      </x-card>
    @empty
      <x-card class="audit-empty">
        <div class="admin-empty">Tidak ada audit log yang cocok dengan filter saat ini.</div>
      </x-card>
    @endforelse
    @if($logs->count() > 0)
      <x-card class="hidden audit-empty" data-realtime-search-empty>
        <div class="admin-empty">Tidak ada audit log yang cocok dengan pencarian.</div>
      </x-card>
    @endif
  </div>

  <div class="audit-pagination">{{ $logs->onEachSide(1)->links('components.public.pagination') }}</div>
</div>
@endsection
