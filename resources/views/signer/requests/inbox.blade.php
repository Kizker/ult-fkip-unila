@extends('layouts.app')
@section('section', 'Signer Inbox')
@section('content')
    <div class="page-signer-inbox">
        <section class="sgi-hero-wrap">
            <header class="sgi-hero">
                <div>
                    <div class="sgi-hero__kicker">Signer Portal</div>
                    <h1 class="sgi-hero__title">Signer Inbox</h1>
                    <p class="sgi-hero__subtitle">Permohonan yang menunggu keputusan Anda pada tahap proses saat ini.</p>
                </div>
                <div class="sgi-hero__meta" aria-label="Ringkasan inbox">
                    <div class="sgi-meta-pill">
                        <div class="sgi-meta-pill__label">Total Antrian</div>
                        <div class="sgi-meta-pill__value">{{ $items->total() }}</div>
                    </div>
                    <div class="sgi-meta-pill">
                        <div class="sgi-meta-pill__label">Halaman</div>
                        <div class="sgi-meta-pill__value">{{ $items->currentPage() }}/{{ max(1, $items->lastPage()) }}</div>
                    </div>
                </div>
            </header>
        </section>

        <section class="sgi-list-wrap">
            <div class="sgi-list">
                <div class="sgi-list__head">
                    <div>
                        <h2 class="sgi-list__title">Daftar Permohonan</h2>
                        <div class="sgi-list__hint">Klik tombol detail untuk meninjau dokumen dan memberikan keputusan.
                        </div>
                    </div>
                </div>

                @if ($items->count() > 0)
                    <div class="sgi-grid">
                        @foreach ($items as $r)
                            @php
                                $activeSigner = $r->service?->signers?->firstWhere(
                                    'order_index',
                                    (int) $r->current_signer_order_index,
                                );
                                $serviceTitle = $r->display_title ?: ($r->service?->title_id ?? 'Layanan');
                                $rawRole = strtoupper(trim((string) ($activeSigner?->role ?? '')));
                                $roleMap = [
                                    'KAPRODI_SCOPE' => 'Ketua Program Studi',
                                    'KAPRODI' => 'Ketua Program Studi',
                                    'KAJUR_SCOPE' => 'Ketua Jurusan',
                                    'KAJUR' => 'Ketua Jurusan',
                                    'SEKJUR' => 'Sekretaris Jurusan',
                                    'SEKJUR_SCOPE' => 'Sekretaris Jurusan',
                                    'DOSEN' => 'Dosen',
                                    'DEKAN' => 'Dekan',
                                    'WD_AKADEMIK' => 'Wakil Dekan Bidang Akademik',
                                    'WD_UMUM' => 'Wakil Dekan Bidang Umum',
                                    'WD_KEMAHASISWAAN' => 'Wakil Dekan Bidang Kemahasiswaan',
                                    'PEMOHON' => 'Pemohon',
                                    'CUSTOM' => 'Penandatangan Khusus',
                                ];
                                $roleLabel = $roleMap[$rawRole] ?? null;
                                $customLabel = trim((string) ($activeSigner?->custom_label ?? ''));
                                if ($customLabel !== '' && in_array($rawRole, ['CUSTOM', 'DOSEN', 'PEMOHON'], true)) {
                                    $roleLabel = $customLabel;
                                }
                                if (!$roleLabel && $rawRole !== '') {
                                    $roleLabel = \Illuminate\Support\Str::of(
                                        strtolower(str_replace('_', ' ', $rawRole)),
                                    )
                                        ->title()
                                        ->value();
                                }
                                $stepLabel = $roleLabel
                                    ? 'Menunggu persetujuan ' . $roleLabel
                                    : 'Menunggu proses tahap ' . $r->current_signer_order_index;

                                $studentUnit = $r->student?->unit;
                                $prodiUnit = $studentUnit?->ancestorOfType(\App\Enums\UnitType::prodi) ?? $studentUnit;
                                $jurusanUnit =
                                    $studentUnit?->ancestorOfType(\App\Enums\UnitType::jurusan) ??
                                    $studentUnit?->parent;
                                $unitDisplay = trim(
                                    collect([$jurusanUnit?->name, $prodiUnit?->name])
                                        ->filter()
                                        ->implode(' - '),
                                );
                                if ($unitDisplay === '') {
                                    $unitDisplay = $r->currentUnit?->name ?? '-';
                                }
                            @endphp
                            <article class="sgi-card">
                                <div class="sgi-card__head">
                                    <div class="sgi-card__service">
                                        <a href="{{ route('signer.requests.show', $r) }}">{{ $serviceTitle }}</a>
                                    </div>
                                    <div class="sgi-card__id">No. Permohonan {{ $r->request_code }}</div>
                                </div>

                                <div class="sgi-card__meta">
                                    <div class="sgi-kv">
                                        <span class="sgi-kv__label">Pemohon</span>
                                        <span class="sgi-kv__value">{{ $r->student?->name ?? '-' }}</span>
                                    </div>
                                    <div class="sgi-kv">
                                        <span class="sgi-kv__label">Jurusan - Program Studi</span>
                                        <span class="sgi-kv__value">{{ $unitDisplay }}</span>
                                    </div>
                                    <div class="sgi-kv">
                                        <span class="sgi-kv__label">Tahap Proses Saat Ini</span>
                                        <span class="sgi-kv__value">{{ $stepLabel }}</span>
                                    </div>
                                    <div class="sgi-kv">
                                        <span class="sgi-kv__label">Update Terakhir</span>
                                        <span
                                            class="sgi-kv__value">{{ optional($r->updated_at)->format('d M Y H:i') }}</span>
                                    </div>
                                </div>

                                <div class="sgi-card__actions">
                                    <x-button class="sgi-btn-open" href="{{ route('signer.requests.show', $r) }}">Buka
                                        Detail</x-button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="sgi-empty">
                        <div class="sgi-empty__title">Tidak ada antrian saat ini</div>
                        <div class="sgi-empty__text">Semua permohonan pada step Anda sudah selesai diproses.</div>
                    </div>
                @endif

                <div class="sgi-pagination">
                    {{ $items->onEachSide(1)->links('components.public.pagination') }}
                </div>
            </div>
        </section>
    </div>
@endsection
