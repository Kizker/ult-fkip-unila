@extends('layouts.app')
@section('section','Layanan')
@section('content')
@php
  $total = method_exists($items, 'total') ? (int) $items->total() : (int) count($items);
  $shown = (int) $items->count();
@endphp

<div class="page-admin-services-index">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master data</div>
      <h1 class="admin-page-title">Layanan</h1>
      <p class="admin-page-subtitle">Kelola layanan, form dinamis, persyaratan, dan workflow.</p>
    </div>

    <div class="admin-page-actions">
      <div class="admin-meta">
        <div class="admin-meta-pill" aria-label="Total layanan">
          <div class="admin-meta-pill__label">Total</div>
          <div class="admin-meta-pill__value">{{ $total }}</div>
        </div>
      </div>
      <x-button href="{{ route('admin.layanan.create') }}">Tambah layanan</x-button>
    </div>
  </header>

  <x-card class="admin-search-card as-toolbar" data-admin-search-card>
    <div class="admin-search" role="search" aria-label="Pencarian layanan">
      <div class="admin-search__toolbar">
        <div class="admin-search__field">
          <label for="admin-services-live-search" class="sr-only">Cari layanan</label>
          <div class="admin-search__input-wrap">
            <input
              id="admin-services-live-search"
              type="text"
              class="admin-search__input"
              placeholder="Cari..."
              aria-label="Cari layanan"
              data-realtime-search-input
              data-realtime-search-mode="filter"
              data-realtime-search-scope=".as-grid"
              data-realtime-search-item-selector="[data-realtime-search-item]"
              data-realtime-search-empty-selector="[data-realtime-search-empty]"
              data-realtime-search-count-selector="[data-realtime-search-count]"
            >
            <button type="button" class="admin-search__clear admin-search__clear--disabled" aria-label="Hapus pencarian" data-admin-search-clear data-reset-url="{{ route('admin.layanan.index') }}">
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
    <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $shown }} dari {{ $total }} layanan">Menampilkan {{ $shown }} dari {{ $total }} layanan</div>
  </div>

  <div class="as-toolbar__hint mt-2">Ketik untuk mencari realtime berdasarkan judul, slug, atau kategori.</div>
  <div class="as-toolbar__legend mt-3" aria-label="Status">
    <span class="as-pill as-pill--active">Active</span>
    <span class="as-pill as-pill--inactive">Inactive</span>
    <span class="as-pill as-pill--inactive">Draft</span>
  </div>

	  <div class="as-grid">
	    @forelse($items as $s)
      @php
        $svcStatus = $s->status?->value ?? null;
        $docEnabled = (bool) ($s->usesRequestPptxSource()
          || $s->templates?->firstWhere('type', \App\Enums\ServiceTemplateType::MAIN_DOCX));
        $categoryName = $s->category?->name_id ?? '-';
        $docMode = $s->usesRequestPptxSource() ? 'PPTX_REQUEST' : ($docEnabled ? 'DOCX_TEMPLATE' : 'NO_DOC');
      @endphp
	      <x-card
	        class="as-card"
	        data-realtime-search-item
	        data-realtime-search-text="{{ $s->title_id }} {{ $s->slug }} {{ $categoryName }}"
	      >
	        <div class="as-card__row">
	          <div class="as-card__meta">
	            <div class="as-card__title">{{ $s->title_id }}</div>
	            <div class="as-card__sub">
	              <span class="as-card__slug">{{ $s->slug }}</span>
              <span class="as-sep" aria-hidden="true">&bull;</span>
              <span class="as-card__slug">{{ $categoryName }}</span>
              <span class="as-sep" aria-hidden="true">&bull;</span>
              <span class="as-card__status">
                <span class="as-pill {{ $s->is_active ? 'as-pill--active' : 'as-pill--inactive' }}">
                  {{ $s->is_active ? 'Active' : 'Inactive' }}
                </span>
              </span>
              <span class="as-sep" aria-hidden="true">&bull;</span>
              <span class="as-card__status">
                <span class="as-pill {{ $svcStatus === 'PUBLISHED' ? 'as-pill--active' : 'as-pill--inactive' }}">
                  {{ $svcStatus ?? 'LEGACY' }}
                </span>
              </span>
              <span class="as-sep" aria-hidden="true">&bull;</span>
              <span class="as-card__status">
                <span class="as-pill {{ $docEnabled ? 'as-pill--active' : 'as-pill--inactive' }}">
                  {{ $docMode }}
                </span>
              </span>
            </div>
          </div>

          <div class="as-card__actions">
            <x-button variant="secondary" href="{{ route('admin.layanan.edit',$s) }}">Edit</x-button>
            <form method="POST" action="{{ route('admin.layanan.destroy',$s) }}" class="as-card__delete">
              @csrf
              @method('DELETE')
              <button type="submit" class="as-danger-btn" data-confirm="Hapus layanan ini?">Hapus</button>
            </form>
          </div>
	        </div>
	      </x-card>
	    @empty
	      <x-card>
	        <div class="admin-empty">Belum ada layanan. Klik “Tambah layanan” untuk membuat layanan baru.</div>
	      </x-card>
	    @endforelse

	    <x-card class="as-card hidden" data-realtime-search-empty>
	      <div class="admin-empty">Tidak ada layanan yang cocok.</div>
	    </x-card>
	  </div>

  <div class="admin-pagination">{{ $items->onEachSide(1)->links('components.public.pagination') }}</div>
</div>
@endsection
