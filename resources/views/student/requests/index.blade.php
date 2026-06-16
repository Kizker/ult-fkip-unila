@extends('layouts.app')
@section('section','Permohonan')
@section('content')
@php
  $filteredTotal = method_exists($items, 'total') ? (int) $items->total() : (int) count($items);
  $total = (int) ($overallTotal ?? $filteredTotal);
  $shown = (int) $items->count();
  $filterCount = ($status ? 1 : 0) + ($serviceId ? 1 : 0);
  $selectedService = collect($services)->firstWhere('id', (int) $serviceId);
  $selectedStatusLabel = $status ? (collect($statusOptions)->firstWhere('value', $status)['label'] ?? str_replace('_', ' ', $status)) : null;
  $selectedStatusTotal = $status ? (int) ($statusTotals[$status] ?? 0) : null;
  $hasFilters = filled($status) || filled($serviceId);
@endphp
<div class="page-student-requests-index" data-student-requests-index-page>
  <header class="student-page-header">
    <div class="student-page-heading">
      <div class="student-page-kicker">Permohonan</div>
      <h1 class="student-page-title">Permohonan Saya</h1>
      <p class="student-page-subtitle">Lacak status, revisi, dan unduh output dari permohonan Anda.</p>
    </div>
    <div class="student-page-actions">
      <div class="student-meta">
        <div class="student-meta-pill" aria-label="Total permohonan">
          <div class="student-meta-pill__label">Total</div>
          <div class="student-meta-pill__value">{{ $total }}</div>
        </div>
        <div class="student-meta-pill" aria-label="Filter aktif">
          <div class="student-meta-pill__label">{{ $status ? 'Status' : 'Filter' }}</div>
          <div class="student-meta-pill__value">{{ $status ? $selectedStatusTotal : $filterCount }}</div>
        </div>
      </div>
      <div class="student-page-cta">
        <x-button href="{{ route('services.index') }}">Ajukan layanan</x-button>
        @if(auth()->user()?->can('doc_signoffs.decide'))
          <x-button variant="secondary" href="{{ route('signer.requests.inbox') }}">Signer Inbox</x-button>
        @endif
      </div>
    </div>
  </header>

  <section class="student-filter-card" aria-label="Pencarian dan filter permohonan">
    <div class="student-requests-search" role="search" aria-label="Pencarian permohonan">
      <div class="student-requests-search__toolbar">
        <div class="student-requests-search__field">
          <label for="student-requests-live-search" class="sr-only">Cari permohonan</label>
          <div class="student-requests-search__input-wrap">
            <input
              id="student-requests-live-search"
              type="text"
              class="student-live-search__input"
              placeholder="Cari..."
              data-realtime-search-input
              data-realtime-search-mode="filter"
              data-realtime-search-scope=".student-requests-grid"
              data-realtime-search-item-selector="[data-realtime-search-item]"
              data-realtime-search-empty-selector="[data-realtime-search-empty]"
              data-realtime-search-count-selector="[data-realtime-search-count]"
            >
            <button
              type="button"
              class="student-requests-search__clear {{ $hasFilters ? '' : 'student-requests-search__clear--disabled' }}"
              aria-label="Reset pencarian dan filter"
              data-student-requests-clear-search
              data-reset-url="{{ route('student.requests.index') }}">
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
              </svg>
            </button>
          </div>
        </div>

        <details class="student-requests-filter-menu">
          <summary class="student-requests-filter-menu__toggle" aria-label="Buka filter permohonan">
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M4 7h16M7 12h10M10 17h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
            </svg>
          </summary>

          <div class="student-requests-filter-menu__panel">
            <form class="student-filter-form" method="GET">
              <div class="student-requests-filter-menu__field">
                <label class="student-requests-filter-menu__label" for="student-requests-status">Status</label>
                <select id="student-requests-status" name="status" class="student-requests-filter-menu__select" onchange="this.form.requestSubmit()">
                  <option value="">Semua</option>
                  @foreach($statusOptions as $statusOption)
                    <option value="{{ $statusOption['value'] }}" @selected($status === $statusOption['value'])>{{ $statusOption['label'] }}</option>
                  @endforeach
                </select>
              </div>

              <div class="student-requests-filter-menu__field">
                <label class="student-requests-filter-menu__label" for="student-requests-service">Layanan</label>
                <select id="student-requests-service" name="service_id" class="student-requests-filter-menu__select" onchange="this.form.requestSubmit()">
                  <option value="">Semua</option>
                  @foreach($services as $s)
                    <option value="{{ $s->id }}" @selected((string)$serviceId===(string)$s->id)>{{ $s->title_id }}</option>
                  @endforeach
                </select>
              </div>

              @if($hasFilters)
                <a class="student-requests-filter-menu__reset" href="{{ route('student.requests.index') }}">Reset filter</a>
              @endif
            </form>
          </div>
        </details>
      </div>
    </div>
  </section>

  <div class="student-resultbar" aria-live="polite">
    <div class="student-resultbar__count student-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $shown }} dari {{ $filteredTotal }} hasil, total keseluruhan {{ $total }} permohonan">Menampilkan {{ $shown }} dari {{ $filteredTotal }} hasil, total keseluruhan {{ $total }} permohonan</div>
    @if($filterCount > 0)
      <div class="student-resultbar__chips">
        @if($status)
          <span class="student-result-chip">Status: {{ $selectedStatusLabel }} ({{ $selectedStatusTotal }})</span>
        @endif
        @if($selectedService)
          <span class="student-result-chip">Layanan: {{ $selectedService->title_id }}</span>
        @endif
      </div>
    @endif
  </div>

  <div class="student-requests-grid" data-infinite-container>
    @if($items->count() > 0)
      @include('student.requests._items', ['items' => $items])
    @else
      <x-card class="student-index-card">
        <div class="student-empty">Belum ada permohonan. Silakan ajukan layanan terlebih dahulu.</div>
      </x-card>
    @endif
    @if($items->count() > 0)
      <x-card class="student-index-card hidden" data-realtime-search-empty>
        <div class="student-empty">Permohonan tidak ditemukan. Coba kata kunci lain.</div>
      </x-card>
    @endif
  </div>

  <div class="student-pagination" data-infinite-pagination>{{ $items->onEachSide(1)->links('components.public.pagination') }}</div>
  @if($items->count() > 0)
    <div class="public-infinite-load"
      data-infinite-list
      data-infinite-auto="1"
      data-next-page-url="{{ $items->nextPageUrl() ?? '' }}"
      data-end-text="Semua permohonan sudah dimuat"
      data-load-more-text="Muat lebih banyak"
      data-loading-text="Memuat..."
      data-error-text="Gagal memuat. Coba lagi.">
      <button
        type="button"
        class="public-infinite-load__button"
        @disabled(!$items->hasMorePages())
        data-infinite-load-more
        aria-hidden="true"
        tabindex="-1">
        Muat lebih banyak
      </button>
      <div class="public-infinite-load__status" data-infinite-status aria-live="polite">{{ $items->hasMorePages() ? '' : 'Semua permohonan sudah dimuat' }}</div>
      <div class="public-infinite-load__sentinel" data-infinite-sentinel aria-hidden="true"></div>
    </div>
  @endif
</div>
<script>
  (() => {
    const root = document.querySelector('.page-student-requests-index[data-student-requests-index-page]');
    if (!root) return;

    const searchInput = root.querySelector('#student-requests-live-search');
    const statusSelect = root.querySelector('#student-requests-status');
    const serviceSelect = root.querySelector('#student-requests-service');
    const clearButton = root.querySelector('[data-student-requests-clear-search]');
    const countEl = root.querySelector('[data-realtime-search-count]');
    const grid = root.querySelector('.student-requests-grid');
    const emptyEl = grid ? grid.querySelector('[data-realtime-search-empty]') : null;
    if (!searchInput || !clearButton || !grid) return;

    const defaultCountText = String(countEl?.getAttribute('data-default-count-text') || countEl?.textContent || '').trim();
    const normalize = (value) => String(value || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .trim();

    const setShown = (el, shown) => {
      el.classList.toggle('hidden', !shown);
      el.hidden = !shown;
      el.style.display = shown ? '' : 'none';
    };

    const hasKeyword = () => !!String(searchInput.value || '').trim();
    const hasFilters = () => !!String(statusSelect?.value || '').trim() || !!String(serviceSelect?.value || '').trim();
    const hasActiveQueryFilters = () => {
      const params = new URLSearchParams(window.location.search || '');
      return ['status', 'service_id'].some((key) => !!String(params.get(key) || '').trim());
    };

    const syncClearState = () => {
      clearButton.classList.toggle('student-requests-search__clear--disabled', !hasKeyword() && !hasFilters() && !hasActiveQueryFilters());
    };

    const applySearch = () => {
      const q = normalize(searchInput.value);
      const items = Array.from(grid.querySelectorAll('[data-realtime-search-item]'));
      let shown = 0;

      items.forEach((item) => {
        const haystack = normalize(item.getAttribute('data-realtime-search-text') || item.textContent || '');
        const match = q === '' || haystack.includes(q);
        setShown(item, match);
        if (match) shown += 1;
      });

      if (emptyEl) setShown(emptyEl, q !== '' && shown === 0);
      if (countEl) countEl.textContent = q ? `Menampilkan ${shown} dari ${items.length}` : defaultCountText;
    };

    searchInput.addEventListener('input', () => {
      applySearch();
      syncClearState();
    });

    clearButton.addEventListener('click', (event) => {
      event.preventDefault();

      if (hasKeyword()) {
        searchInput.value = '';
        applySearch();
        syncClearState();
        searchInput.focus();
        return;
      }

      if (hasFilters() || hasActiveQueryFilters()) {
        const resetUrl = clearButton.getAttribute('data-reset-url') || '';
        if (resetUrl) window.location.href = resetUrl;
      }
    });

    statusSelect?.addEventListener('change', syncClearState);
    serviceSelect?.addEventListener('change', syncClearState);

    applySearch();
    syncClearState();
  })();
</script>
@endsection
