@extends('layouts.app')
@section('section', 'Template Nomor Surat')

@section('content')
    @php
        $total = method_exists($formats, 'total') ? (int) $formats->total() : (int) count($formats);
        $items = method_exists($formats, 'getCollection') ? $formats->getCollection() : collect($formats);
        $activeCount = (int) $items->where('is_active', true)->count();
        $inactiveCount = max(0, (int) $items->count() - $activeCount);
        $unitCount = (int) $items->pluck('unit_id')->filter()->unique()->count();
        $firstItem = method_exists($formats, 'firstItem') ? (int) ($formats->firstItem() ?? 0) : ($total > 0 ? 1 : 0);
        $lastItem = method_exists($formats, 'lastItem') ? (int) ($formats->lastItem() ?? 0) : $total;
        $shown = (int) $formats->count();
        $filterCount = filled($unitId) ? 1 : 0;
        $selectedUnit = collect($units)->firstWhere('id', (int) $unitId);
    @endphp

    <div class="page-admin-doc-formats-index page-admin-doc-formats-index--letter">
        <header class="admin-page-header">
            <div class="admin-page-heading">
                <div class="admin-page-kicker">Nomor surat</div>
                <h1 class="admin-page-title">Template Nomor Surat</h1>
                <p class="admin-page-subtitle">
                    Atur template nomor surat per unit (Prodi/Jurusan/Fakultas) dan per <span
                        class="dn-mono">format_key</span>.
                </p>
            </div>

            <div class="admin-page-actions">
                <div class="admin-meta">
                    <div class="admin-meta-pill" aria-label="Total template">
                        <div class="admin-meta-pill__label">Total</div>
                        <div class="admin-meta-pill__value">{{ $total }}</div>
                    </div>
                </div>

                <x-button href="{{ route('admin.letter_formats.create') }}">Tambah template</x-button>
            </div>
        </header>

        <section class="lnf-stat-grid" aria-label="Ringkasan template nomor surat">
            <article class="lnf-stat-card">
                <div class="lnf-stat-card__label">Total template</div>
                <div class="lnf-stat-card__value">{{ $total }}</div>
                <div class="lnf-stat-card__caption">Semua data dalam scope akses Anda</div>
            </article>
            <article class="lnf-stat-card">
                <div class="lnf-stat-card__label">Aktif (halaman ini)</div>
                <div class="lnf-stat-card__value">{{ $activeCount }}</div>
                <div class="lnf-stat-card__caption">Template yang siap dipakai</div>
            </article>
            <article class="lnf-stat-card">
                <div class="lnf-stat-card__label">Nonaktif (halaman ini)</div>
                <div class="lnf-stat-card__value">{{ $inactiveCount }}</div>
                <div class="lnf-stat-card__caption">Perlu review sebelum dipakai</div>
            </article>
            <article class="lnf-stat-card">
                <div class="lnf-stat-card__label">Unit terwakili</div>
                <div class="lnf-stat-card__value">{{ $unitCount }}</div>
                <div class="lnf-stat-card__caption">Unit unik di halaman saat ini</div>
            </article>
        </section>

        <x-card class="admin-search-card dn-toolbar lnf-toolbar" data-admin-search-card>
            <div class="admin-search" role="search" aria-label="Pencarian template nomor surat">
                <div class="admin-search__toolbar">
                    <div class="admin-search__field">
                        <label for="admin-letter-formats-live-search" class="sr-only">Cari template</label>
                        <div class="admin-search__input-wrap">
                            <input
                                id="admin-letter-formats-live-search"
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
                            <button type="button" class="admin-search__clear {{ $filterCount > 0 ? '' : 'admin-search__clear--disabled' }}" aria-label="Reset pencarian dan filter" data-admin-search-clear data-reset-url="{{ route('admin.letter_formats.index') }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <details class="admin-search-filter-menu">
                        <summary class="admin-search-filter-menu__toggle" aria-label="Buka filter template nomor surat">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 7h16M7 12h10M10 17h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                            </svg>
                        </summary>

                        <div class="admin-search-filter-menu__panel">
                            <form method="GET" class="admin-search-filter-menu__form">
                                <div class="admin-search-filter-menu__field">
                                    <label class="admin-search-filter-menu__label" for="admin-letter-formats-unit">Filter Unit</label>
                                    <select id="admin-letter-formats-unit" name="unit_id" data-admin-search-track onchange="this.form.requestSubmit()">
                                        <option value="">Semua unit dalam scope</option>
                                        @foreach ($units as $u)
                                            <option value="{{ $u->id }}" @selected((int) $unitId === (int) $u->id)>{{ $u->type->value }} &mdash; {{ $u->name }} ({{ $u->code }})</option>
                                        @endforeach
                                    </select>
                                    <p class="lnf-field-hint">Pilih unit spesifik untuk mempermudah audit format key dan template aktif.</p>
                                </div>
                            </form>
                        </div>
                    </details>
                </div>
            </div>
        </x-card>

        <div class="admin-search-resultbar">
            <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $shown }} dari {{ $total }} template">Menampilkan {{ $shown }} dari {{ $total }} template</div>
            @if($selectedUnit)
                <div class="admin-search-resultbar__chips">
                    <span class="admin-search-result-chip">Unit: {{ $selectedUnit->name }}</span>
                </div>
            @endif
        </div>

        <x-card class="dn-card lnf-table-card">
            <div class="lnf-table-header">
                <div>
                    <h2 class="lnf-table-title">Daftar template</h2>
                    <p class="lnf-table-subtitle">
                        @if ($total > 0)
                            Menampilkan <span class="dn-mono">{{ $firstItem }}-{{ $lastItem }}</span> dari
                            <span class="dn-mono">{{ $total }}</span> template.
                        @else
                            Menampilkan <span class="dn-mono">0</span> template.
                        @endif
                    </p>
                </div>
            </div>

            <div class="dn-table-wrap" role="region" aria-label="Tabel template nomor surat" tabindex="0">
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
                                $letterFormatSearchText = trim(implode(' ', array_filter([
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
                            <tr data-realtime-search-item data-realtime-search-text="{{ $letterFormatSearchText }}">
                                <td data-label="Unit">
                                    <div class="dn-unit__name">{{ $f->unit->name }}</div>
                                    <div class="dn-unit__meta">{{ $f->unit->type->value }} &bull; {{ $f->unit->code }}</div>
                                </td>
                                <td class="dn-mono" data-label="Key">{{ $f->format_key }}</td>
                                <td class="dn-cell" data-label="Nama">{{ $f->name }}</td>
                                <td class="dn-template dn-mono" data-label="Template">{{ $f->template }}</td>
                                <td class="dn-col-num" data-label="Padding">{{ $f->seq_padding }}</td>
                                <td class="dn-col-badge" data-label="Status">
                                    @if ($f->is_active)
                                        <x-badge variant="success">Aktif</x-badge>
                                    @else
                                        <x-badge variant="secondary">Nonaktif</x-badge>
                                    @endif
                                </td>
                                <td class="dn-col-actions" data-label="Aksi">
                                    <div class="dn-actions">
                                        @can('view', $f)
                                            <x-button href="{{ route('admin.letter_formats.show', $f) }}" variant="ghost"
                                                size="sm">History</x-button>
                                        @endcan
                                        @can('update', $f)
                                            <x-button href="{{ route('admin.letter_formats.edit', $f) }}" variant="secondary"
                                                size="sm">Edit</x-button>
                                        @endcan
                                        @can('delete', $f)
                                            <form method="POST" action="{{ route('admin.letter_formats.destroy', $f) }}"
                                                class="dn-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dn-danger-btn"
                                                    data-confirm="Hapus template ini?">Hapus</button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="dn-empty">
                                    <div class="lnf-empty-title">Belum ada template nomor surat.</div>
                                    <div class="lnf-empty-subtitle">Klik <span class="dn-mono">Tambah template</span> untuk
                                        membuat template baru.</div>
                                </td>
                            </tr>
                        @endforelse
                        @if($formats->count() > 0)
                            <tr class="hidden" data-realtime-search-empty>
                                <td colspan="7" class="dn-empty">
                                    <div class="lnf-empty-title">Tidak ada template yang cocok.</div>
                                    <div class="lnf-empty-subtitle">Coba ubah kata kunci pencarian Anda.</div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="dn-pagination">{{ $formats->onEachSide(1)->links('components.public.pagination') }}</div>
        </x-card>

        <x-card class="dn-help lnf-help">
            <div class="lnf-help-grid">
                <div>
                    <div class="dn-help__title">Placeholder tersedia</div>
                    <ul class="dn-help__list">
                        <li><span class="dn-mono">{SEQ}</span> atau <span class="dn-mono">{SEQ:4}</span> untuk padding
                            otomatis</li>
                        <li><span class="dn-mono">{UNIT_CODE}</span> atau <span class="dn-mono">{UNIT}</span> (alias)</li>
                        <li><span class="dn-mono">{YYYY}</span> tahun, <span class="dn-mono">{MM}</span> bulan</li>
                    </ul>
                </div>
                <div class="lnf-help-example" aria-label="Contoh template nomor surat">
                    <div class="lnf-help-example__label">Contoh template</div>
                    <div class="lnf-help-example__code dn-mono">{SEQ:5}/UN26.13/PN.01.00/{YYYY}</div>
                    <div class="lnf-help-example__label mt-3">Contoh hasil</div>
                    <div class="lnf-help-example__code dn-mono">00042/UN26.13/PN.01.00/{{ now()->year }}</div>
                </div>
            </div>
        </x-card>
    </div>
@endsection
