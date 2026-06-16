@extends('layouts.app')
@section('section', 'Kritik dan Saran')

@section('content')
    @php
        $statusLabels = [
            \App\Models\FeedbackMessage::STATUS_BARU => 'Baru',
            \App\Models\FeedbackMessage::STATUS_DIPROSES => 'Diproses',
            \App\Models\FeedbackMessage::STATUS_SELESAI => 'Selesai',
        ];

        $categoryLabels = [
            \App\Models\FeedbackMessage::CATEGORY_MASUKAN => 'Masukan',
            \App\Models\FeedbackMessage::CATEGORY_SARAN => 'Saran',
            \App\Models\FeedbackMessage::CATEGORY_KOMPLAIN => 'Komplain',
        ];

        $total = method_exists($items, 'total') ? (int) $items->total() : (int) count($items);
        $filterCount = (filled($q) ? 1 : 0) + (filled($status) ? 1 : 0) + (filled($category) ? 1 : 0);
        $shown = (int) $items->count();
    @endphp

    <div class="page-admin-feedback-index">
        <header class="admin-page-header">
            <div class="admin-page-heading">
                <div class="admin-page-kicker">Layanan Publik</div>
                <h1 class="admin-page-title">Kritik dan Saran</h1>
                <p class="admin-page-subtitle">Kelola masukan umum dan komplain resmi dari pengguna.</p>
            </div>
            <div class="admin-page-actions">
                <div class="admin-meta">
                    <div class="admin-meta-pill" aria-label="Total pesan">
                        <div class="admin-meta-pill__label">Total</div>
                        <div class="admin-meta-pill__value">{{ $total }}</div>
                    </div>
                </div>
            </div>
        </header>

        <x-card class="admin-search-card feedback-toolbar" data-admin-search-card>
            <form method="GET" class="admin-search" role="search" aria-label="Pencarian kritik dan saran">
                <div class="admin-search__toolbar">
                    <div class="admin-search__field">
                        <label for="admin-feedback-search" class="sr-only">Cari feedback</label>
                        <div class="admin-search__input-wrap">
                            <input id="admin-feedback-search" type="text" class="admin-search__input" placeholder="Cari..."
                                data-realtime-search-input
                                data-realtime-search-mode="filter"
                                data-realtime-search-scope=".feedback-list"
                                data-realtime-search-item-selector=".feedback-item[data-realtime-search-item]"
                                data-realtime-search-empty-selector="[data-realtime-search-empty]"
                                data-realtime-search-count-selector="[data-realtime-search-count]">
                            <button type="button" class="admin-search__clear {{ $filterCount > 0 ? '' : 'admin-search__clear--disabled' }}" aria-label="Reset pencarian dan filter" data-admin-search-clear data-reset-url="{{ route('admin.feedback.index') }}">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M6 6l12 12M18 6l-12 12" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <details class="admin-search-filter-menu">
                        <summary class="admin-search-filter-menu__toggle" aria-label="Buka filter feedback">
                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M4 7h16M7 12h10M10 17h4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="1.8"/>
                            </svg>
                        </summary>

                        <div class="admin-search-filter-menu__panel">
                            <div class="admin-search-filter-menu__form">
                                <div class="admin-search-filter-menu__field">
                                    <label class="admin-search-filter-menu__label" for="admin-feedback-status">Status</label>
                                    <select id="admin-feedback-status" name="status" data-admin-search-track onchange="this.form.requestSubmit()">
                                        <option value="">Semua</option>
                                        @foreach(\App\Models\FeedbackMessage::STATUSES as $statusValue)
                                            <option value="{{ $statusValue }}" @selected($status === $statusValue)>{{ $statusLabels[$statusValue] ?? $statusValue }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="admin-search-filter-menu__field">
                                    <label class="admin-search-filter-menu__label" for="admin-feedback-category">Kategori</label>
                                    <select id="admin-feedback-category" name="category" data-admin-search-track onchange="this.form.requestSubmit()">
                                        <option value="">Semua</option>
                                        @foreach(\App\Models\FeedbackMessage::CATEGORIES as $categoryValue)
                                            <option value="{{ $categoryValue }}" @selected($category === $categoryValue)>{{ $categoryLabels[$categoryValue] ?? $categoryValue }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </details>
                </div>
            </form>
        </x-card>

        <div class="admin-search-resultbar">
            <div class="admin-search-resultbar__count admin-search-resultbar__count--live" data-realtime-search-count data-default-count-text="Menampilkan {{ $shown }} dari {{ $total }} pesan">Menampilkan {{ $shown }} dari {{ $total }} pesan</div>
            @if($filterCount > 0)
                <div class="admin-search-resultbar__chips">
                    @if(filled($status))
                        <span class="admin-search-result-chip">Status: {{ $statusLabels[$status] ?? $status }}</span>
                    @endif
                    @if(filled($category))
                        <span class="admin-search-result-chip">Kategori: {{ $categoryLabels[$category] ?? $category }}</span>
                    @endif
                </div>
            @endif
        </div>

        <div class="feedback-list">
            @forelse($items as $item)
                @php
                    $statusVariant = match ($item->status) {
                        \App\Models\FeedbackMessage::STATUS_SELESAI => 'success',
                        \App\Models\FeedbackMessage::STATUS_DIPROSES => 'warning',
                        default => 'primary',
                    };
                @endphp
                @php
                    $feedbackSearchText = trim(implode(' ', array_filter([
                        $item->name,
                        $item->email,
                        $item->phone,
                        $item->message,
                        $statusLabels[$item->status] ?? $item->status,
                        $categoryLabels[$item->category] ?? $item->category,
                    ])));
                @endphp
                <x-card class="feedback-item" data-realtime-search-item data-realtime-search-text="{{ $feedbackSearchText }}">
                    <div class="feedback-item__row">
                        <div class="feedback-item__meta">
                            <div class="feedback-item__head">
                                <div class="feedback-item__identity">
                                    <div class="feedback-item__name">
                                        <a href="{{ route('admin.feedback.show', $item) }}">{{ $item->name }}</a>
                                    </div>
                                    <div class="feedback-item__sub">
                                        <span>{{ $item->email }}</span>
                                        @if(filled($item->phone))
                                            <span class="feedback-item__sep" aria-hidden="true">&bull;</span>
                                            <span>{{ $item->phone }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="feedback-item__date">
                                    {{ optional($item->created_at)->format('d M Y H:i') }}
                                </div>
                            </div>

                            <div class="feedback-item__chips">
                                <x-badge variant="default">
                                    {{ $categoryLabels[$item->category] ?? $item->category }}
                                </x-badge>
                                <x-badge :variant="$statusVariant">
                                    {{ $statusLabels[$item->status] ?? $item->status }}
                                </x-badge>
                            </div>

                            <p class="feedback-item__message">
                                {{ \Illuminate\Support\Str::limit($item->message, 180, '...') }}
                            </p>
                        </div>

                        <div class="feedback-item__actions">
                            <x-button href="{{ route('admin.feedback.show', $item) }}" variant="secondary">Detail</x-button>
                        </div>
                    </div>
                </x-card>
            @empty
                <x-card>
                    <div class="admin-empty">Belum ada kritik/saran.</div>
                </x-card>
            @endforelse
        </div>
        @if($items->count() > 0)
            <x-card class="hidden" data-realtime-search-empty>
                <div class="admin-empty">Tidak ada kritik atau saran yang cocok dengan pencarian.</div>
            </x-card>
        @endif

        <div class="admin-pagination feedback-pagination">
            {{ $items->onEachSide(1)->links('components.public.pagination') }}
        </div>
    </div>
@endsection
