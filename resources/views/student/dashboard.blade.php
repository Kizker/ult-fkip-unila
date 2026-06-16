@extends('layouts.app')

@section('section', 'Portal Pengajuan')

@section('content')
    @php
        $total = (int) collect($counts)->sum();
        $recentCount = method_exists($recent, 'count') ? (int) $recent->count() : (int) count($recent);
        $activeStatusCount = (int) collect($counts)->filter(fn($c) => (int) $c > 0)->count();
    @endphp

    <div class="page-student-dashboard">
        <header class="student-page-header student-dashboard-hero">
            <div class="student-page-heading student-dashboard-hero__content">
                <div class="student-page-kicker student-dashboard-hero__kicker">Halo, {{ auth()->user()->name }}</div>
                <h1 class="student-page-title">Dashboard Pengajuan Layanan</h1>
                <p class="student-page-subtitle">Ringkasan pengajuan terbaru dan status proses layanan Anda.</p>
            </div>
            <div class="student-page-actions student-dashboard-hero__actions">
                <div class="student-dashboard-hero__cta">
                    <x-button class="student-dashboard-btn student-dashboard-btn--primary"
                        href="{{ route('services.index') }}">Ajukan layanan</x-button>
                    @if (auth()->user()?->can('doc_signoffs.decide'))
                        <x-button class="student-dashboard-btn student-dashboard-btn--secondary" variant="secondary"
                            href="{{ route('signer.requests.inbox') }}">Signer Inbox</x-button>
                    @endif
                </div>
            </div>
        </header>

        <div class="student-dashboard-layout">
            <x-card class="student-dashboard-main student-dashboard-card">
                <div class="student-card-header student-dashboard-card__header">
                    <div class="student-dashboard-card__intro">
                        <div class="student-dashboard-card__eyebrow">Aktivitas</div>
                        <div class="student-dashboard-card__titleline">
                            <div class="student-card-title">Permohonan terbaru</div>
                        </div>
                        <div class="student-card-subtitle">Pantau progres pengajuan terbaru tanpa membuka halaman detail
                            satu per satu.</div>
                    </div>
                    <x-button class="student-dashboard-btn student-dashboard-btn--ghost student-dashboard-card__header-btn !border-[rgb(var(--c-primary))] !bg-[rgb(var(--c-primary))] !text-white hover:!border-[rgb(var(--c-primary))] hover:!bg-transparent hover:!text-[rgb(var(--c-primary))] dark:hover:!border-white dark:hover:!bg-transparent dark:hover:!text-white"
                        variant="ghost" href="{{ route('student.requests.index') }}">Lihat semua</x-button>
                </div>

                <div class="student-request-list">
                    @forelse($recent as $r)
                        <article class="student-request-item">
                            <div class="student-request-item__meta">
                                <div class="student-request-item__status">
                                    <x-status-badge :status="$r->current_status->value ?? $r->current_status" />
                                </div>
                                <div class="student-request-item__title">
                                    <a href="{{ route('student.requests.show', $r) }}">{{ $r->display_title }}</a>
                                </div>
                                <div class="student-request-item__time">{{ $r->created_at->format('d M Y H:i') }}</div>
                            </div>
                            <div class="student-request-item__actions">
                                <x-button class="student-dashboard-btn student-dashboard-btn--secondary" variant="secondary"
                                    href="{{ route('student.requests.show', $r) }}">Detail</x-button>
                            </div>
                        </article>
                    @empty
                        <div class="student-empty">Belum ada permohonan.</div>
                    @endforelse
                </div>

                @if (method_exists($recent, 'hasPages') && $recent->hasPages())
                    <div class="student-pagination student-dashboard-pagination">
                        {{ $recent->onEachSide(1)->links('components.public.pagination') }}
                    </div>
                @endif
            </x-card>

            <x-card class="student-dashboard-side student-dashboard-card student-dashboard-card--aside">
                <div class="student-card-header student-dashboard-card__header">
                    <div class="student-dashboard-card__intro">
                        <div class="student-dashboard-card__titleline">
                            <div class="student-card-title">Status Pengajuan</div>
                        </div>
                        <div class="student-card-subtitle">Total permohonan berdasarkan status saat ini.</div>
                    </div>
                    <div class="student-dashboard-card__badge">{{ $activeStatusCount }} status aktif</div>
                </div>

                <div class="student-summary-metrics" role="list" aria-label="Ringkasan cepat pengajuan">
                    <div class="student-summary-metric student-summary-metric--primary" aria-label="Total permohonan">
                        <div class="student-summary-metric__label">Total</div>
                        <div class="student-summary-metric__value">{{ $total }}</div>
                        <div class="student-summary-metric__hint">Semua pengajuan</div>
                    </div>
                </div>

                <div class="student-summary-list">
                    @foreach ($counts as $st => $c)
                        <a href="{{ route('student.requests.index', ['status' => $st]) }}" class="student-summary-row"
                            aria-label="Lihat permohonan dengan status {{ str_replace('_', ' ', $st) }}">
                            <x-status-badge :status="$st" />
                            <div class="student-summary-row__count">{{ $c }}</div>
                        </a>
                    @endforeach
                </div>
            </x-card>
        </div>
    </div>
@endsection
