@extends('layouts.app')
@section('section', 'Finalisasi Dokumen')
@section('content')
    @php
        $status = $req->current_status->value ?? $req->current_status;
        $latestPlacement = $req->signaturePlacements->first();
        $placementMap = collect($latestPlacement?->placements_json ?? [])->keyBy('signer_role');
        $activeSigners = $req->signoffs->filter(fn($s) => (bool) $s->signature_file_path)->values();
        $useManualPlacement = old('use_manual_placement', '0') === '1';
    @endphp

    <div class="page-staff-assemble-show page-admin-requests-show" data-staff-assemble-show-page>
        <header class="admin-page-header">
            <div class="admin-page-heading">
                <div class="admin-page-kicker">Permohonan {{ $req->request_code }}</div>
                <h1 class="admin-page-title">Finalisasi Dokumen - {{ $req->display_title }}</h1>
                <p class="admin-page-subtitle">Placement TTD berbasis page break + koordinat (unit: pt). Output tersimpan
                    pada private storage.</p>
            </div>
            <div class="admin-page-actions">
                <div class="admin-meta">
                    <div class="admin-meta-pill admin-meta-pill--status">
                        <div class="admin-meta-pill__label">Status</div>
                        <div class="admin-meta-pill__value">
                            <x-status-badge :status="$status" />
                        </div>
                    </div>
                </div>
                <x-button class="staff-assemble-back-btn !border-[rgb(var(--c-primary))] !bg-[rgb(var(--c-primary))] !text-white hover:!border-[rgb(var(--c-primary))] hover:!bg-transparent hover:!text-[rgb(var(--c-primary))] dark:hover:!border-white dark:hover:!bg-transparent dark:hover:!text-white" variant="ghost" href="{{ route('admin.requests.show', $req) }}">&larr; Kembali</x-button>
            </div>
        </header>

        <div class="ars-layout">
            <div class="ars-main">
                <x-card class="sa-panel sa-panel--placement">
                    <div class="ars-card-header">
                        <div class="ars-card-title">Placement Tanda Tangan</div>
                        <div class="ars-card-subtitle">Default: auto di bawah jabatan dan di atas nama penandatangan.
                            Aktifkan manual hanya jika perlu koreksi posisi.</div>
                    </div>
                    <form method="POST" action="{{ route('staff.assemble.preview', $req) }}" class="ars-form mt-3">
                        @csrf
                        <label class="sa-placement-toggle">
                            <input type="checkbox" name="use_manual_placement" value="1" data-manual-placement-toggle
                                @checked($useManualPlacement)>
                            <span>Gunakan placement manual (opsional jika auto-placement belum pas)</span>
                        </label>

                        <div class="sa-placement-grid @if (!$useManualPlacement) is-disabled @endif"
                            aria-label="Form placement tanda tangan" data-manual-placement-grid>
                            @forelse($activeSigners as $s)
                                @php
                                    $pref = $placementMap->get($s->signer_role);
                                @endphp
                                <section class="sa-placement-card">
                                    <header class="sa-placement-card__head">
                                        <div>
                                            <div class="sa-placement-card__role">{{ $s->signer_role }}</div>
                                            <div class="sa-placement-card__meta">Urutan {{ $s->order_index }}</div>
                                        </div>
                                        <span class="sa-placement-chip" data-placement-chip>Auto</span>
                                    </header>

                                    <input type="hidden" name="placements[{{ $loop->index }}][signer_role]"
                                        value="{{ $s->signer_role }}">

                                    <div class="sa-placement-fields">
                                        <label class="sa-field">
                                            <span class="sa-field__label">Halaman</span>
                                            <input class="sa-field__input" type="number" min="1" step="1"
                                                name="placements[{{ $loop->index }}][page_number]"
                                                value="{{ old('placements.' . $loop->index . '.page_number', $pref['page_number'] ?? 1) }}"
                                                @disabled(!$useManualPlacement) />
                                        </label>
                                        <label class="sa-field">
                                            <span class="sa-field__label">X (pt)</span>
                                            <input class="sa-field__input" type="number" min="0" step="0.1"
                                                name="placements[{{ $loop->index }}][x_pt]"
                                                value="{{ old('placements.' . $loop->index . '.x_pt', $pref['x_pt'] ?? 72) }}"
                                                @disabled(!$useManualPlacement) />
                                        </label>
                                        <label class="sa-field">
                                            <span class="sa-field__label">Y (pt)</span>
                                            <input class="sa-field__input" type="number" min="0" step="0.1"
                                                name="placements[{{ $loop->index }}][y_pt]"
                                                value="{{ old('placements.' . $loop->index . '.y_pt', $pref['y_pt'] ?? 500) }}"
                                                @disabled(!$useManualPlacement) />
                                        </label>
                                        <label class="sa-field">
                                            <span class="sa-field__label">Lebar (pt)</span>
                                            <input class="sa-field__input" type="number" min="1" step="0.1"
                                                name="placements[{{ $loop->index }}][width_pt]"
                                                value="{{ old('placements.' . $loop->index . '.width_pt', $pref['width_pt'] ?? 150) }}"
                                                @disabled(!$useManualPlacement) />
                                        </label>
                                        <label class="sa-field">
                                            <span class="sa-field__label">Tinggi (pt)</span>
                                            <input class="sa-field__input" type="number" min="1" step="0.1"
                                                name="placements[{{ $loop->index }}][height_pt]"
                                                value="{{ old('placements.' . $loop->index . '.height_pt', $pref['height_pt'] ?? 54) }}"
                                                @disabled(!$useManualPlacement) />
                                        </label>
                                    </div>
                                </section>
                            @empty
                                <div class="sa-empty">Belum ada signer dengan file tanda tangan. Lengkapi proses signing
                                    terlebih dulu.</div>
                            @endforelse
                        </div>

                        <div class="sa-hint">
                            Tips: saat mode manual aktif, 1 inci = 72 pt. Gunakan hanya untuk fine-tuning jika
                            auto-placement belum sejajar.
                        </div>
                        <div class="sa-actions">
                            <x-button type="submit" variant="secondary" :disabled="$activeSigners->isEmpty()">Preview</x-button>
                            <x-button type="submit" formaction="{{ route('staff.assemble.finalize', $req) }}"
                                class="sa-finalize-btn" :disabled="$activeSigners->isEmpty()">Finalize</x-button>
                        </div>
                    </form>
                </x-card>

                <x-card class="sa-panel sa-panel--preview">
                    <div class="ars-card-header">
                        <div class="ars-card-title">Preview Dokumen</div>
                        <div class="ars-card-subtitle">Hasil preview terbaru tampil langsung di halaman ini.</div>
                    </div>

                    @if ($selectedOutput)
                        @php
                            $isPdfPreview =
                                strtolower(pathinfo((string) $selectedOutput->file_path, PATHINFO_EXTENSION)) === 'pdf';
                        @endphp
                        <div class="sa-preview-wrap mt-3">
                            @if ($isPdfPreview)
                                <iframe class="sa-preview-frame"
                                    src="{{ route('staff.assemble.output_inline', ['request' => $req, 'output' => $selectedOutput]) }}"
                                    title="Preview Dokumen" loading="lazy"></iframe>
                            @else
                                <div class="sa-preview-fallback">
                                    Format output saat ini DOCX, browser tidak selalu bisa menampilkan langsung.
                                    <x-button variant="secondary"
                                        href="{{ route('staff.assemble.output_inline', ['request' => $req, 'output' => $selectedOutput]) }}"
                                        target="_blank">Buka Preview di Tab Baru</x-button>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="ars-note mt-3">Belum ada preview. Isi posisi tanda tangan lalu klik tombol
                            <strong>Preview</strong>.</div>
                    @endif
                </x-card>

                <x-card class="sa-panel sa-panel--outputs">
                    <div class="ars-card-header">
                        <div class="ars-card-title">Latest Outputs</div>
                        <div class="ars-card-subtitle">Riwayat output dokumen terakhir dari proses assembly.</div>
                    </div>
                    <div class="ars-attachment-list mt-3">
                        @forelse($req->outputs as $o)
                            <div class="ars-attachment">
                                <div class="ars-attachment__meta">
                                    <div class="ars-attachment__name">{{ $o->output_type }} &bull;
                                        {{ $o->original_filename }}</div>
                                    <div class="ars-attachment__sub">{{ optional($o->created_at)->format('d M Y H:i') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="ars-note">Belum ada output.</div>
                        @endforelse
                    </div>
                </x-card>
            </div>

            <div class="ars-side">
                <x-card class="sa-panel sa-panel--signers sa-sticky">
                    <div class="admin-card-title">Signer & Signature Files</div>
                    <div class="ars-kvlist">
                        @foreach ($req->signoffs as $s)
                            <div class="ars-kvrow">
                                <div class="ars-kvrow__label">{{ $s->signer_role }} ({{ $s->order_index }})</div>
                                <div class="ars-kvrow__value">
                                    {{ $s->status->value ?? $s->status }} @if ($s->signature_file_path)
                                        &bull; signature OK
                                    @else
                                        &bull; signature belum ada
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </div>
        </div>
    </div>

@endsection
