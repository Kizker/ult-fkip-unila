@extends('layouts.app')
@section('section', 'Detail Permohonan')
@section('content')
    @php
        $requestSnapshot = is_array($req->data?->document_snapshot_json) ? $req->data->document_snapshot_json : [];
        $snapshotTemplatePath = trim((string) data_get($requestSnapshot, 'template.file_path', ''));
        $isDocService = (bool) (
            $req->service?->usesRequestPptxSource()
            || $req->service?->templates?->firstWhere('type', \App\Enums\ServiceTemplateType::MAIN_DOCX)
            || $snapshotTemplatePath !== ''
        );
        $currentUnitLabel = match ($req->currentUnit?->type) {
            \App\Enums\UnitType::jurusan => 'Jurusan saat ini',
            \App\Enums\UnitType::prodi => 'Program Studi saat ini',
            \App\Enums\UnitType::fakultas => 'Fakultas saat ini',
            default => 'Unit saat ini',
        };
    @endphp

    <div class="page-admin-requests-show">
        <header class="admin-page-header">
            <div class="admin-page-heading">
                <p class="admin-page-kicker">Permohonan {{ $req->request_code }}</p>
                <h1 class="admin-page-title">{{ $req->display_title }}</h1>
                <p class="admin-page-subtitle">Detail permohonan, data mahasiswa, lampiran, dan aksi workflow.</p>
            </div>
            <div class="admin-page-actions">
                <x-button class="admin-page-back-btn !border-[rgb(var(--c-primary))] !bg-[rgb(var(--c-primary))] !text-white hover:!border-[rgb(var(--c-primary))] hover:!bg-transparent hover:!text-[rgb(var(--c-primary))] dark:hover:!border-white dark:hover:!bg-transparent dark:hover:!text-white" variant="ghost" href="{{ route('admin.requests.index') }}">&larr; Kembali</x-button>
            </div>
        </header>

        <div class="ars-layout">
            <div class="ars-main">
                <x-card>
                    <div class="ars-hero">
                        <div class="ars-hero__left">
                            <div class="ars-hero__kicker">Status saat ini</div>
                            <div class="ars-hero__badge">
                                <x-status-badge :status="$req->current_status->value ?? $req->current_status" />
                            </div>
                        </div>
                        <div class="ars-hero__right">
                            <div class="ars-hero__label">Diajukan</div>
                            <div class="ars-hero__time">
                                {{ optional($req->submitted_at ?? $req->created_at)->format('d M Y H:i') }}</div>
                        </div>
                    </div>

                    <div class="ars-kvgrid">
                        <div class="ars-kv">
                            <div class="ars-kv__label">Mahasiswa</div>
                            <div class="ars-kv__value">{{ $req->student->name }} ({{ $req->student->email }})</div>
                        </div>
                        <div class="ars-kv">
                            <div class="ars-kv__label">{{ $currentUnitLabel }}</div>
                            <div class="ars-kv__value">{{ $req->currentUnit?->name ?? '-' }}</div>
                        </div>
                        @if ($isDocService)
                            <div class="ars-kv">
                                <div class="ars-kv__label">NOMOR_SURAT</div>
                                <div class="ars-kv__value" data-nomor-surat-display>{{ $req->nomor_surat ?? '-' }}</div>
                            </div>
                            <div class="ars-kv">
                                <div class="ars-kv__label">TANGGAL_SURAT</div>
                                <div class="ars-kv__value">{{ $req->tanggal_surat?->format('Y-m-d') ?? '-' }}</div>
                            </div>
                        @endif
                        @if ($req->documentNumber)
                            <div class="ars-kv ars-kv--full">
                                <div class="ars-kv__label">Nomor dokumen</div>
                                <div class="ars-kv__value">
                                    {{ app(\App\Services\DocumentNumberService::class)->renderNumber($req->documentNumber) }}
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($isDocService)
                        <div class="mt-3">
                            <x-button variant="secondary" href="{{ route('requests.preview', $req) }}" target="_blank">Preview Dokumen</x-button>
                        </div>
                    @endif

                </x-card>

                @if ($isDocService)
                    <x-card>
                        <div class="ars-card-header">
                            <div class="ars-card-title">Layanan Dokumen — Gate & Signing</div>
                            @php
                                $gateActorLabel = $initialGateRoleLabel !== '' ? $initialGateRoleLabel : 'petugas gate awal';
                            @endphp
                            <div class="ars-card-subtitle">Template nomor surat dipilih oleh {{ $gateActorLabel }}, lalu nomor urut diterbitkan otomatis oleh sistem sebelum masuk review ULT.</div>
                        </div>

                        @php
                            $st = $req->current_status->value ?? $req->current_status;
                            $passButtonLabel = $initialGateRoleLabel === 'Staf ULT'
                                ? 'Setujui & lanjut ke Review ULT'
                                : 'Setujui & kirim ke ULT';
                        @endphp

                        @if (in_array($st, ['DIAJUKAN', 'PERLU_PERBAIKAN'], true))
                            @if(!empty($canInitialGateActions))
                                @php
                                    $letterFormats = $letterFormats ?? collect();
                                    $letterApplicableUnits = $letterApplicableUnits ?? collect();
                                @endphp

                                <div class="ars-form mt-4">
                                    @if ($letterFormats->isNotEmpty())
                                        <div class="mb-4">
                                            <label class="text-sm font-medium">Template nomor surat</label>
                                            <select form="gate-pass-form" name="letter_format_id" class="as-input mt-1 w-full" required data-scrollable-select="1" data-scrollable-search-placeholder="Cari..." data-scrollable-empty-text="Template nomor surat tidak ditemukan.">
                                                <option value="">-- pilih template --</option>
                                                @foreach ($letterFormats as $f)
                                                    <option value="{{ $f->id }}" @selected((int) old('letter_format_id') === (int) $f->id)>
                                                        {{ $f->format_key }} - {{ $f->name }} ({{ $f->unit?->type?->value ?? '-' }}: {{ $f->unit?->name ?? '-' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="text-xs text-muted mt-1">Nomor urut surat akan diterbitkan otomatis oleh sistem saat Anda menyetujui permohonan ini.</div>
                                            @if ($letterApplicableUnits->isNotEmpty())
                                                <div class="text-xs text-muted mt-1">
                                                    Cakupan pemohon:
                                                    {{ $letterApplicableUnits->map(fn($u) => $u->type->value.': '.$u->name)->implode(' -> ') }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="ars-note mb-4">
                                            Belum ada template nomor surat aktif untuk cakupan unit pemohon. Tambahkan template nomor surat terlebih dahulu sebelum menyetujui permohonan.
                                        </div>
                                    @endif

                                    <div class="flex gap-2 flex-wrap">
                                        <form id="gate-pass-form" method="POST" action="{{ route('admin.doc_requests.gate.verify',$req) }}">
                                            @csrf
                                            <input type="hidden" name="decision" value="PASS">
                                            <x-button type="submit" :disabled="$letterFormats->isEmpty()">{{ $passButtonLabel }}</x-button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.doc_requests.gate.verify',$req) }}">
                                            @csrf
                                            <input type="hidden" name="decision" value="REJECT">
                                            <x-button type="submit" variant="secondary">Tolak</x-button>
                                        </form>
                                    </div>
                                    <form class="mt-3" method="POST" action="{{ route('admin.doc_requests.gate.verify',$req) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="REVISION">
                                        <x-textarea name="note" rows="3" label="Catatan perbaikan untuk mahasiswa (wajib)" required>{{ old('note') }}</x-textarea>
                                        <div class="mt-2">
                                            <x-button type="submit" variant="secondary">Minta perbaikan</x-button>
                                        </div>
                                    </form>
                                    <div class="text-xs text-muted mt-2">Catatan: saat disetujui, sistem akan menerbitkan nomor surat final berdasarkan template yang dipilih.</div>
                                </div>
                            @else
                                <div class="ars-note">
                                    Aksi gate awal (pilih template nomor surat + verifikasi awal) hanya untuk
                                    <strong>{{ $initialGateRoleLabel !== '' ? $initialGateRoleLabel : 'role gate yang ditentukan layanan' }}</strong>.
                                </div>
                            @endif
                        @elseif ($st === 'REVIEW_ULT')
                            <div class="ars-form">
                                <x-input name="nomor_surat_ro" label="Nomor surat" value="{{ $req->nomor_surat ?? '-' }}" disabled />
                            </div>

                            <div class="ars-form mt-4">
                                @can('reviewUlt', $req)
                                    <div class="flex gap-2 flex-wrap">
                                        <form method="POST" action="{{ route('admin.doc_requests.start_signing',$req) }}">
                                            @csrf
                                            <x-button type="submit">Setujui & mulai penandatanganan</x-button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.doc_requests.gate.verify',$req) }}">
                                            @csrf
                                            <input type="hidden" name="decision" value="REJECT">
                                            <x-button type="submit" variant="secondary">Tolak</x-button>
                                        </form>
                                    </div>
                                    <form class="mt-3" method="POST" action="{{ route('admin.doc_requests.gate.verify',$req) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="REVISION">
                                        <x-textarea name="note" rows="3" label="Catatan perbaikan untuk mahasiswa (wajib)" required>{{ old('note') }}</x-textarea>
                                        <div class="mt-2">
                                            <x-button type="submit" variant="secondary">Kembalikan untuk perbaikan</x-button>
                                        </div>
                                    </form>
                                @else
                                    <div class="text-sm text-muted">Aksi review ULT dan mulai penandatanganan hanya untuk Staf ULT (atau role yang diberi permission setara).</div>
                                @endcan
                            </div>
                        @else
                            <div class="admin-empty">Tahap gate/review sudah selesai. Lanjutkan proses via portal signer bila diperlukan.</div>
                        @endif

                        <div class="mt-4">
                            <div class="admin-card-subtitle">Signer chain</div>
                            <div class="ars-approval-list">
                                @foreach($req->signoffs as $so)
                                    <div class="ars-approval">
                                        <div class="ars-approval__meta">{{ $so->signer_role }} &bull; order {{ $so->order_index }}</div>
                                        <div class="ars-approval__status">{{ $so->status->value ?? $so->status }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if (($req->current_status->value ?? $req->current_status) === 'READY_FOR_FINAL')
                            <div class="mt-4 flex gap-2">
                                <x-button
                                    href="{{ route('staff.assemble.show',$req) }}"
                                    variant="secondary"
                                    class="ars-open-finalize-btn"
                                >Buka Finalisasi Dokumen</x-button>
                            </div>
                        @endif
                    </x-card>
                @endif

                <x-card>
                    <div class="ars-card-header">
                        <div class="ars-card-title">Data permohonan</div>
                    </div>
                    @php
                        $attachmentMap = $req->attachments->keyBy('id');
                    @endphp
                    <div class="ars-kvlist">
                        @if (filled($req->activity_title))
                            <div class="ars-kvrow">
                                <div class="ars-kvrow__label">Judul permohonan</div>
                                <div class="ars-kvrow__value">{{ $req->display_title }}</div>
                            </div>
                            <div class="ars-kvrow">
                                <div class="ars-kvrow__label">Judul kegiatan</div>
                                <div class="ars-kvrow__value">{{ $req->activity_title }}</div>
                            </div>
                        @endif
                        @foreach ($req->fieldValues as $fv)
                            @php
                                $fieldType = (string) ($fv->field->type ?? 'text');
                                $isRichText = $fieldType === 'richtext';
                                $displayValue = '-';
                                $downloadAttachment = null;

                                if ($fv->value_text !== null && $fv->value_text !== '') {
                                    $displayValue = (string) $fv->value_text;
                                } elseif ($fv->value_number !== null) {
                                    $displayValue = (string) $fv->value_number;
                                } elseif ($fv->value_date) {
                                    $displayValue = $fv->value_date->format('Y-m-d');
                                } elseif (is_array($fv->value_json)) {
                                    $json = $fv->value_json;
                                    $attachmentId = isset($json['attachment_id']) && is_numeric($json['attachment_id'])
                                        ? (int) $json['attachment_id']
                                        : null;
                                    $originalName = trim((string) ($json['original'] ?? ''));

                                    if ($attachmentId) {
                                        $downloadAttachment = $attachmentMap->get($attachmentId);
                                    }

                                    if ($downloadAttachment) {
                                        $displayValue = (string) ($downloadAttachment->original_name ?: ($originalName !== '' ? $originalName : 'File terunggah'));
                                    } elseif ($originalName !== '') {
                                        $displayValue = $originalName;
                                    } elseif (array_key_exists('value', $json) && !is_array($json['value']) && !is_object($json['value'])) {
                                        $displayValue = (string) $json['value'];
                                    } else {
                                        $pairs = [];
                                        foreach ($json as $k => $v) {
                                            if ($v === null) {
                                                continue;
                                            }
                                            if (is_array($v)) {
                                                $flat = implode(', ', array_map(static function ($item) {
                                                    return is_scalar($item) ? (string) $item : '[data]';
                                                }, $v));
                                                $pairs[] = is_string($k) ? "{$k}: {$flat}" : $flat;
                                                continue;
                                            }
                                            $val = is_scalar($v) ? (string) $v : '[data]';
                                            $pairs[] = is_string($k) ? "{$k}: {$val}" : $val;
                                        }
                                        $displayValue = !empty($pairs) ? implode(' | ', $pairs) : '-';
                                    }
                                }
                            @endphp
                            <div class="ars-kvrow">
                                <div class="ars-kvrow__label">{{ $fv->field->label_id ?? '-' }}</div>
                                <div class="ars-kvrow__value">
                                    @if($isRichText && $displayValue !== '-')
                                        <div class="content-prose">{!! app(\App\Services\HtmlSanitizer::class)->clean((string) $displayValue) !!}</div>
                                    @else
                                        {{ $displayValue }}
                                    @endif
                                </div>
                                @if ($downloadAttachment)
                                    <div class="ars-attachment__sub">
                                        <a href="{{ route('attachments.download', $downloadAttachment) }}">Download file</a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-card>

                <x-card>
                    <div class="ars-card-header">
                        <div class="ars-card-title">Lampiran</div>
                        <div class="ars-card-subtitle">Unduh lampiran yang diunggah oleh mahasiswa.</div>
                    </div>

                    <div class="ars-attachment-list">
                        @forelse($req->attachments as $a)
                            <div class="ars-attachment">
                                <div class="ars-attachment__meta">
                                    <div class="ars-attachment__name">{{ $a->original_name }}</div>
                                    <div class="ars-attachment__sub">{{ $a->kind->value }} &bull; {{ $a->mime }}
                                    </div>
                                </div>
                                <div class="ars-attachment__actions">
                                    <x-button variant="ghost"
                                        href="{{ route('attachments.download', $a) }}">Download</x-button>
                                </div>
                            </div>
                        @empty
                            <div class="admin-empty">Belum ada lampiran.</div>
                        @endforelse
                    </div>
                </x-card>

            </div>

            <div class="ars-side">
                @if (!$isDocService)
                    @php
                        $wf = $req->service->workflow;
                        $steps = $wf ? app(\App\Services\RequestWorkflowService::class)->getSteps($wf) : [];
                        $currentKey = $req->current_step_key;
                        $currentStep = collect($steps)->firstWhere('key', $currentKey);

                        $labels = [
                            'verify' => 'Verifikasi',
                            'review' => 'Review / Proses',
                            'sign' => 'Tanda Tangan',
                            'forward_faculty' => 'Teruskan ke Fakultas',
                            'issue_number' => 'Terbitkan Nomor',
                            'upload_output' => 'Tandai Output Diunggah',
                            'complete' => 'Selesaikan',
                            'request_revision' => 'Minta Perbaikan',
                            'reject' => 'Tolak',
                        ];
                    @endphp

                    <x-card>
                        <div class="ars-card-header">
                            <div class="ars-card-title">Aksi Workflow</div>
                            <div class="ars-card-subtitle">Step saat ini: <span
                                    class="font-semibold text-fg">{{ $currentStep['label_id'] ?? ($currentKey ?? '-') }}</span>
                            </div>
                        </div>

                        @if (!$currentStep)
                            <div class="admin-empty">Konfigurasi step untuk <code class="px-1">{{ $currentKey }}</code>
                                tidak ditemukan.</div>
                        @else
                            <form class="ars-wf-form" method="POST" action="{{ route('admin.requests.action', $req) }}"
                                data-wf-form>
                                @csrf
                                <input type="hidden" name="action" value="" data-wf-action-input>
                                <x-textarea name="note" rows="3"
                                    label="Catatan (opsional)">{{ old('note') }}</x-textarea>

                                <div class="ars-wf-actions">
                                    @foreach ($currentStep['actions_allowed'] ?? [] as $act)
                                        <x-button type="button" variant="secondary" data-wf-action="{{ $act }}"
                                            :data-confirm="in_array($act, ['reject', 'request_revision'], true)
                                                ? 'Yakin menjalankan aksi ini?'
                                                : null">{{ $labels[$act] ?? $act }}</x-button>
                                    @endforeach
                                </div>
                            </form>
                        @endif
                    </x-card>
                @endif

                @if (!$isDocService)
                <x-card>
                    <div class="ars-card-header">
                        <div class="ars-card-title">Ubah Status</div>
                        <div class="ars-card-subtitle">(Legacy / debugging) Disarankan gunakan Aksi Workflow agar tidak
                            melompati status.</div>
                    </div>
                    <form class="ars-form" method="POST" action="{{ route('admin.requests.status', $req) }}">
                        @csrf
                        <x-select name="to_status" label="Ke status">
                            @foreach (\App\Enums\RequestStatus::cases() as $st)
                                <option value="{{ $st->value }}">{{ $st->value }}</option>
                            @endforeach
                        </x-select>
                        <x-textarea name="note" rows="3"
                            label="Catatan (opsional)">{{ old('note') }}</x-textarea>
                        <x-button type="submit" variant="secondary">Update</x-button>
                    </form>
                </x-card>
                @endif

                <x-card>
                    <div class="ars-card-header">
                        <div class="ars-card-title">Riwayat</div>
                    </div>
                    <div class="mb-3">
                        <x-input
                                placeholder="Cari..."
                            data-realtime-search-input
                            data-realtime-search-mode="filter"
                            data-realtime-search-scope=".ars-history-list"
                            data-realtime-search-item-selector=".ars-history[data-realtime-search-item]"
                            data-realtime-search-empty-selector="[data-realtime-search-empty-history]"
                            data-realtime-search-count-selector="[data-realtime-search-count-history]"
                        />
                        <div class="text-xs text-muted mt-2" data-realtime-search-count-history></div>
                    </div>
                    <div class="ars-history-list">
                        @foreach ($req->histories as $h)
                            @php
                                $historySearchText = trim(implode(' ', array_filter([
                                    \Carbon\Carbon::parse($h->created_at)->format('d M Y H:i'),
                                    $h->actor?->name,
                                    $h->from_status,
                                    $h->to_status,
                                    $h->note,
                                ])));
                            @endphp
                            <div class="ars-history" data-realtime-search-item data-realtime-search-text="{{ $historySearchText }}">
                                <div class="ars-history__meta">
                                    {{ \Carbon\Carbon::parse($h->created_at)->format('d M Y H:i') }} &bull;
                                    {{ $h->actor?->name }}</div>
                                <div class="ars-history__line">{{ $h->from_status ?? '-' }} &rarr; <span
                                        class="ars-history__to">{{ $h->to_status }}</span></div>
                                @if ($h->note)
                                    <div class="ars-history__note">{{ $h->note }}</div>
                                @endif
                            </div>
                        @endforeach
                        @if($req->histories->count() > 0)
                            <div class="admin-empty hidden" data-realtime-search-empty-history>
                                Tidak ada riwayat yang cocok dengan pencarian.
                            </div>
                        @endif
                    </div>
                </x-card>
            </div>
        </div>
    </div>
@endsection
