@extends('layouts.app')
@section('section', 'Detail Permohonan')
@section('content')
    @php
        $status = $req->current_status->value ?? $req->current_status;
        $submittedAt = optional($req->submitted_at ?? $req->created_at)->format('d M Y H:i');
        $historyCount = (int) ($req->histories?->count() ?? 0);
        $generalAttachments = ($req->attachments ?? collect())
            ->filter(fn($a) => (int) ($a->service_field_id ?? 0) === 0 && ($a->kind->value ?? null) === 'input')
            ->values();
        $attachmentCount = (int) $generalAttachments->count();
        $requestSnapshot = is_array($req->data?->document_snapshot_json) ? $req->data->document_snapshot_json : [];
        $snapshotTemplatePath = trim((string) data_get($requestSnapshot, 'template.file_path', ''));
        $hasDocPreview =
            (bool) ($req->service?->usesRequestPptxSource() ||
                $req->service?->templates?->firstWhere('type', \App\Enums\ServiceTemplateType::MAIN_DOCX) ||
                $snapshotTemplatePath !== '');
        $canEditRequestData = $status === 'PERLU_PERBAIKAN';
        $serviceSigners = $requestSigners ?? ($req->service?->signers ?? collect());
        $isCertificateService = (bool) ($certificateEditorState['is_certificate'] ?? false);
        $customSignatureSigners = $serviceSigners
            ->filter(fn($s) => strtoupper((string) $s->role) === 'CUSTOM')
            ->sortBy('order_index')
            ->values();
        $pemohonSignatureSigners = $serviceSigners
            ->filter(fn($s) => strtoupper((string) $s->role) === 'PEMOHON')
            ->sortBy('order_index')
            ->values();
        $dosenSelectSigners = $serviceSigners
            ->filter(fn($s) => strtoupper((string) $s->role) === 'DOSEN')
            ->sortBy('order_index')
            ->values();
    @endphp
    <div class="page-student-requests-show" data-student-requests-show-page>
        <header class="student-page-header">
            <div class="student-page-heading">
                <p class="student-page-kicker">Permohonan {{ $req->request_code }}</p>
                <h2 class="student-page-title">{{ $req->display_title }}</h2>
                <p class="student-page-subtitle">Detail permohonan, lampiran, catatan, dan riwayat status.</p>
            </div>
            <div class="student-page-actions">
                <x-button class="student-page-back-btn !border-[rgb(var(--c-primary))] !bg-[rgb(var(--c-primary))] !text-white hover:!border-[rgb(var(--c-primary))] hover:!bg-transparent hover:!text-[rgb(var(--c-primary))] dark:hover:!border-white dark:hover:!bg-transparent dark:hover:!text-white" variant="ghost"
                    href="{{ route('student.requests.index') }}">Kembali</x-button>
            </div>
        </header>

        <div class="student-show-layout">
            <div class="student-show-main">
                <x-card class="student-show-card student-show-card--overview">
                    <div class="student-request-hero">
                        <div class="student-request-hero__left">
                            <div class="student-request-hero__kicker">Status saat ini</div>
                            <div class="student-request-hero__badge">
                                <x-status-badge :status="$status" />
                            </div>
                        </div>
                        <div class="student-request-hero__right">
                            <div class="student-request-hero__label">Diajukan</div>
                            <div class="student-request-hero__time">{{ $submittedAt }}</div>
                        </div>
                    </div>

                    <div class="student-overview-grid">
                        @if ($req->documentNumber)
                            <div class="student-docnum student-docnum--document">
                                <div class="student-docnum__label">Nomor dokumen</div>
                                <div class="student-docnum__value">
                                    {{ app(\App\Services\DocumentNumberService::class)->renderNumber($req->documentNumber) }}
                                </div>
                            </div>
                        @endif

                        <div class="student-docnum student-docnum--action">
                            <div class="student-docnum__header">
                                <div class="student-docnum__label">Aksi</div>
                            </div>
                            <div class="student-docnum__body">
                            @if (($req->current_status->value ?? $req->current_status) === 'PERLU_PERBAIKAN')
                                <form class="student-revision-form space-y-3" method="POST"
                                    action="{{ route('student.requests.revision', $req) }}">
                                    @csrf
                                    <x-textarea name="note" rows="3"
                                        label="Catatan perbaikan">{{ old('note') }}</x-textarea>
                                    <x-button type="submit">Kirim perbaikan</x-button>
                                </form>
                            @else
                                <div class="student-action-empty">
                                    <div class="student-action-empty__text">Tidak ada aksi saat ini.</div>
                                </div>
                            @endif
                            </div>
                        </div>
                    </div>

                </x-card>

                <x-card class="student-show-card">
                    <div class="student-card-header">
                        <div class="student-card-title">Data permohonan</div>
                        <div class="student-card-subtitle">
                            {{ $canEditRequestData ? 'Perbaiki data permohonan Anda lalu kirim perbaikan.' : 'Data yang Anda kirim saat pengajuan.' }}
                        </div>
                    </div>

                    @if ($canEditRequestData)
                        <form method="POST" action="{{ route('student.requests.data.update', $req) }}" class="space-y-3"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="grid gap-3 md:grid-cols-2">
                                @foreach (($requestFields ?? collect())->sortBy('sort_order') as $f)
                                    @php
                                        $name = "fields[{$f->id}]";
                                        $oldKey = "fields.{$f->id}";
                                        $rawOld = old($oldKey);
                                        $fv = $req->fieldValues->firstWhere('service_field_id', $f->id);
                                        $label =
                                            app()->getLocale() === 'en' ? $f->label_en ?? $f->label_id : $f->label_id;
                                        $currentValue = '';
                                        $currentAttachment = null;
                                        $isFull = in_array($f->type, ['textarea', 'richtext', 'json', 'file'], true);

                                        if ($f->type === 'file') {
                                            $currentAttachmentId = null;
                                            if (
                                                is_array($fv?->value_json) &&
                                                isset($fv->value_json['attachment_id']) &&
                                                is_numeric($fv->value_json['attachment_id'])
                                            ) {
                                                $currentAttachmentId = (int) $fv->value_json['attachment_id'];
                                            }
                                            if ($currentAttachmentId) {
                                                $currentAttachment = $req->attachments->firstWhere(
                                                    'id',
                                                    $currentAttachmentId,
                                                );
                                            }
                                        } elseif ($rawOld !== null) {
                                            $currentValue = $rawOld;
                                        } elseif (!$fv) {
                                            $currentValue = '';
                                        } elseif ($f->type === 'number') {
                                            $currentValue = $fv->value_number ?? '';
                                        } elseif ($f->type === 'date') {
                                            $currentValue = optional($fv->value_date)->format('Y-m-d');
                                        } elseif ($f->type === 'json') {
                                            if (is_array($fv->value_json)) {
                                                $currentValue = array_key_exists('value', $fv->value_json)
                                                    ? (string) $fv->value_json['value']
                                                    : json_encode($fv->value_json, JSON_UNESCAPED_UNICODE);
                                            } else {
                                                $currentValue = '';
                                            }
                                        } else {
                                            $currentValue = $fv->value_text ?? '';
                                        }
                                    @endphp

                                    <div class="{{ $isFull ? 'md:col-span-2' : '' }}">
                                        @if ($f->type === 'file')
                                            <x-input type="file" :label="$label" :name="$name" />
                                            @if ($currentAttachment)
                                                <div class="text-xs text-muted mt-1">
                                                    File saat ini: <strong>{{ $currentAttachment->original_name }}</strong>
                                                    (<a
                                                        href="{{ route('attachments.download', $currentAttachment) }}">download</a>)
                                                </div>
                                            @else
                                                <div class="text-xs text-muted mt-1">Belum ada file tersimpan untuk field
                                                    ini.</div>
                                            @endif
                                        @elseif ($f->type === 'richtext')
                                            <x-tiptap-editor :label="$label" :name="$name" :value="$currentValue"
                                                height="min-h-[180px]" />
                                        @elseif ($f->type === 'textarea' || $f->type === 'json')
                                            <x-textarea :label="$label" :name="$name"
                                                rows="4">{{ $currentValue }}</x-textarea>
                                        @elseif ($f->type === 'date')
                                            <x-input type="date" :label="$label" :name="$name"
                                                :value="$currentValue" />
                                        @elseif ($f->type === 'number')
                                            <x-input type="number" :label="$label" :name="$name"
                                                :value="$currentValue" />
                                        @elseif ($f->type === 'select')
                                            <x-select :label="$label" :name="$name">
                                                <option value="">-- pilih --</option>
                                                @foreach ($f->options_json ?? [] as $opt)
                                                    <option value="{{ $opt }}" @selected((string) $currentValue === (string) $opt)>
                                                        {{ $opt }}</option>
                                                @endforeach
                                            </x-select>
                                        @else
                                            <x-input type="text" :label="$label" :name="$name"
                                                :value="$currentValue" />
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            @if ($isCertificateService)
                                @include('student.requests._certificate_fields', [
                                    'certificateEditorState' => $certificateEditorState ?? [],
                                    'certificateInternalSignerOptions' =>
                                        $certificateInternalSignerOptions ?? collect(),
                                    'isRevision' => true,
                                ])
                            @endif

                            @if (!$isCertificateService && $pemohonSignatureSigners->isNotEmpty())
                                <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-4">
                                    <div class="text-sm font-semibold">Tanda tangan pemohon</div>
                                    <div class="text-xs text-muted mt-1">
                                        Unggah file baru untuk mengganti tanda tangan pemohon yang sudah tersimpan.
                                    </div>

                                    <div class="grid gap-5 mt-4">
                                        @foreach ($pemohonSignatureSigners as $s)
                                            @php
                                                $idx = (int) $s->order_index;
                                                $mimeTypes = is_array($s->signature_file_types)
                                                    ? array_values(array_filter($s->signature_file_types))
                                                    : [];
                                                $mimeTypes = array_values(
                                                    array_intersect($mimeTypes, [
                                                        'image/png',
                                                        'image/jpeg',
                                                        'image/webp',
                                                    ]),
                                                );
                                                if (empty($mimeTypes)) {
                                                    $mimeTypes = ['image/png', 'image/jpeg', 'image/webp'];
                                                }
                                                $mimeLabels = [];
                                                foreach ($mimeTypes as $mime) {
                                                    $mimeLabels[] = match ($mime) {
                                                        'image/png' => 'PNG',
                                                        'image/jpeg' => 'JPG/JPEG',
                                                        'image/webp' => 'WEBP',
                                                        default => (string) $mime,
                                                    };
                                                }
                                                $mimeLabels = array_values(array_unique(array_filter($mimeLabels)));
                                                $accept = implode(',', $mimeTypes);
                                                $maxKb = (int) ($s->signature_max_size_kb ?? 0);
                                                if ($maxKb <= 0) {
                                                    $maxKb = 256;
                                                }
                                                $labelFromAdmin = trim((string) ($s->custom_label ?? ''));
                                                if ($labelFromAdmin === '') {
                                                    $labelFromAdmin = 'Pemohon #' . $idx;
                                                }
                                                $existingSignoff = $req->signoffs->first(function ($so) use ($idx) {
                                                    return (int) ($so->order_index ?? 0) === $idx &&
                                                        strtoupper((string) ($so->signer_role ?? '')) === 'PEMOHON';
                                                });
                                                $hasExisting =
                                                    trim((string) ($existingSignoff->signature_file_path ?? '')) !== '';
                                                $helpParts = [];
                                                if (!empty($mimeLabels)) {
                                                    $helpParts[] = 'Format: ' . implode(', ', $mimeLabels) . '.';
                                                }
                                                if ($maxKb > 0) {
                                                    $helpParts[] = 'Maksimal ' . $maxKb . ' KB.';
                                                }
                                                $helpParts[] = $hasExisting
                                                    ? 'Saat ini: sudah ada tanda tangan tersimpan.'
                                                    : 'Saat ini: belum ada tanda tangan tersimpan (wajib upload).';
                                                $help = implode(' ', $helpParts);
                                                $previewUrl = $hasExisting
                                                    ? route('student.requests.signature.preview', [
                                                        'request' => $req,
                                                        'signoff' => $existingSignoff,
                                                    ])
                                                    : null;
                                            @endphp
                                            <div class="grid gap-4 md:gap-6 md:grid-cols-2 items-start"
                                                data-signature-live-preview-item>
                                                <x-input type="file" :name="'pemohon_signatures[' . $idx . ']'" :label="'Tanda tangan ' . $labelFromAdmin"
                                                    :accept="$accept" :help="$help"
                                                    data-signature-live-preview-input="1" />
                                                <div class="rounded-xl border border-[rgb(var(--c-border))] bg-white/70 p-3 @if (!$previewUrl) hidden @endif"
                                                    data-signature-live-preview-box>
                                                    <div class="text-xs text-muted mb-1" data-signature-live-preview-label>
                                                        @if ($previewUrl)
                                                            Tanda tangan tersimpan
                                                        @else
                                                            Preview tanda tangan
                                                        @endif
                                                    </div>
                                                    <a href="{{ $previewUrl ?: '#' }}" target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-block @if (!$previewUrl) pointer-events-none @endif"
                                                        data-signature-live-preview-link>
                                                        <img src="{{ $previewUrl ?: '' }}"
                                                            data-signature-stored-src="{{ $previewUrl ?: '' }}"
                                                            data-signature-live-preview-img
                                                            alt="Preview tanda tangan {{ $labelFromAdmin }}"
                                                            class="h-24 w-full object-contain">
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (!$isCertificateService && $customSignatureSigners->isNotEmpty())
                                <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-4">
                                    <div class="text-sm font-semibold">Penandatangan lain</div>
                                    <div class="text-xs text-muted mt-1">
                                        Anda bisa mengganti tanda tangan penandatangan custom saat perbaikan.
                                    </div>

                                    <div class="grid gap-5 mt-4">
                                        @foreach ($customSignatureSigners as $s)
                                            @php
                                                $idx = (int) $s->order_index;
                                                $mimeTypes = is_array($s->signature_file_types)
                                                    ? array_values(array_filter($s->signature_file_types))
                                                    : [];
                                                $mimeTypes = array_values(
                                                    array_intersect($mimeTypes, [
                                                        'image/png',
                                                        'image/jpeg',
                                                        'image/webp',
                                                    ]),
                                                );
                                                if (empty($mimeTypes)) {
                                                    $mimeTypes = ['image/png', 'image/jpeg', 'image/webp'];
                                                }
                                                $mimeLabels = [];
                                                foreach ($mimeTypes as $mime) {
                                                    $mimeLabels[] = match ($mime) {
                                                        'image/png' => 'PNG',
                                                        'image/jpeg' => 'JPG/JPEG',
                                                        'image/webp' => 'WEBP',
                                                        default => (string) $mime,
                                                    };
                                                }
                                                $mimeLabels = array_values(array_unique(array_filter($mimeLabels)));
                                                $accept = implode(',', $mimeTypes);
                                                $maxKb = (int) ($s->signature_max_size_kb ?? 0);
                                                if ($maxKb <= 0) {
                                                    $maxKb = 256;
                                                }
                                                $labelFromAdmin = trim((string) ($s->custom_label ?? ''));
                                                if ($labelFromAdmin === '') {
                                                    $labelFromAdmin = 'Penandatangan #' . $idx;
                                                }
                                                $existingSignoff = $req->signoffs->first(function ($so) use ($idx) {
                                                    return (int) ($so->order_index ?? 0) === $idx &&
                                                        strtoupper((string) ($so->signer_role ?? '')) === 'CUSTOM';
                                                });
                                                $hasExisting =
                                                    trim((string) ($existingSignoff->signature_file_path ?? '')) !== '';
                                                $helpParts = [];
                                                if (!empty($mimeLabels)) {
                                                    $helpParts[] = 'Format: ' . implode(', ', $mimeLabels) . '.';
                                                }
                                                if ($maxKb > 0) {
                                                    $helpParts[] = 'Maksimal ' . $maxKb . ' KB.';
                                                }
                                                $helpParts[] = $hasExisting
                                                    ? 'Saat ini: sudah ada tanda tangan tersimpan.'
                                                    : 'Saat ini: belum ada tanda tangan tersimpan (wajib upload).';
                                                $help = implode(' ', $helpParts);
                                                $previewUrl = $hasExisting
                                                    ? route('student.requests.signature.preview', [
                                                        'request' => $req,
                                                        'signoff' => $existingSignoff,
                                                    ])
                                                    : null;
                                            @endphp
                                            <div class="grid gap-4 md:gap-6 md:grid-cols-2 items-start"
                                                data-signature-live-preview-item>
                                                <x-input type="file" :name="'custom_signatures[' . $idx . ']'" :label="'Tanda tangan ' . $labelFromAdmin"
                                                    :accept="$accept" :help="$help"
                                                    data-signature-live-preview-input="1" />
                                                <div class="rounded-xl border border-[rgb(var(--c-border))] bg-white/70 p-3 @if (!$previewUrl) hidden @endif"
                                                    data-signature-live-preview-box>
                                                    <div class="text-xs text-muted mb-1" data-signature-live-preview-label>
                                                        @if ($previewUrl)
                                                            Tanda tangan tersimpan
                                                        @else
                                                            Preview tanda tangan
                                                        @endif
                                                    </div>
                                                    <a href="{{ $previewUrl ?: '#' }}" target="_blank"
                                                        rel="noopener noreferrer"
                                                        class="inline-block @if (!$previewUrl) pointer-events-none @endif"
                                                        data-signature-live-preview-link>
                                                        <img src="{{ $previewUrl ?: '' }}"
                                                            data-signature-stored-src="{{ $previewUrl ?: '' }}"
                                                            data-signature-live-preview-img
                                                            alt="Preview tanda tangan {{ $labelFromAdmin }}"
                                                            class="h-24 w-full object-contain">
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (!$isCertificateService && $dosenSelectSigners->isNotEmpty())
                                <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-4">
                                    <div class="text-sm font-semibold">Pemilihan dosen penandatangan</div>
                                    <div class="text-xs text-muted mt-1">
                                        Anda dapat memilih atau mengganti dosen/pimpinan untuk step role DOSEN.
                                    </div>

                                    <div class="grid gap-4 mt-4 lg:grid-cols-2">
                                        @foreach ($dosenSelectSigners as $s)
                                            @php
                                                $idx = (int) $s->order_index;
                                                $labelFromAdmin = trim((string) ($s->custom_label ?? ''));
                                                if ($labelFromAdmin === '') {
                                                    $labelFromAdmin = 'Dosen #' . $idx;
                                                }
                                                $existingSignoff = $req->signoffs->first(function ($so) use ($idx) {
                                                    return (int) ($so->order_index ?? 0) === $idx &&
                                                        strtoupper((string) ($so->signer_role ?? '')) === 'DOSEN';
                                                });
                                                $existingUserId = (int) ($existingSignoff->signer_user_id ?? 0);
                                                $selectedUserId = (int) old(
                                                    'dosen_signers.' . $idx,
                                                    $existingUserId > 0 ? $existingUserId : 0,
                                                );
                                            @endphp
                                            <div class="space-y-1">
                                                <x-select :name="'dosen_signers[' . $idx . ']'" :label="'Pilih ' . $labelFromAdmin"
                                                    data-scrollable-user-select="1">
                                                    <option value="">-- pilih user --</option>
                                                    @foreach ($dosenSignerOptions ?? [] as $u)
                                                        <option value="{{ $u->id }}" @selected($selectedUserId === (int) $u->id)>
                                                            {{ $u->name }}</option>
                                                    @endforeach
                                                </x-select>
                                                <div class="text-xs text-muted">
                                                    {{ $existingUserId > 0 ? 'Saat ini: user signer sudah dipilih.' : 'Saat ini: belum ada user signer terpilih.' }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="flex flex-wrap items-center gap-2">
                                <x-button type="submit">Simpan perubahan data</x-button>
                                <span class="text-xs text-muted">
                                    @if ($isCertificateService)
                                        Untuk sertifikat/piagam, Anda bisa memperbarui dokumen sumber .pptx dan daftar
                                        signer di form ini.
                                    @else
                                        Semua data form, termasuk pilihan dosen signer, tanda tangan pemohon/custom, dan
                                        lampiran umum, bisa diperbarui dari form ini.
                                    @endif
                                </span>
                            </div>

                            @if ($req->service?->allow_general_attachments)
                                <div class="student-create-subsection mt-6">
                                    <div class="student-create-subsection__title">Lampiran umum</div>
                                    <div class="student-create-subsection__subtitle">
                                        Tambahkan lampiran pendukung baru jika diperlukan. File lama tetap tersimpan dan
                                        tidak akan diganti.
                                    </div>

                                    <div class="student-create-subsection__grid">
                                        <div class="student-create-subsection__field space-y-3">
                                            <x-input type="file" name="attachments[]"
                                                label="Tambah lampiran pendukung" multiple />
                                            <div class="text-xs text-muted">
                                                Bagian ini opsional dan mendukung lebih dari satu file.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </form>
                    @else
                        <div class="student-kv-list">
                            @php $fieldValues = $req->fieldValues->keyBy('service_field_id'); @endphp
                            @if (filled($req->activity_title))
                                <div class="student-kv">
                                    <div class="student-kv__label">Judul permohonan</div>
                                    <div class="student-kv__value">{{ $req->display_title }}</div>
                                </div>
                                <div class="student-kv">
                                    <div class="student-kv__label">Judul kegiatan</div>
                                    <div class="student-kv__value">{{ $req->activity_title }}</div>
                                </div>
                            @endif
                            @foreach (($requestFields ?? collect())->sortBy('sort_order') as $f)
                                @continue($f->type === 'file')
                                @php
                                    $fv = $fieldValues->get($f->id);
                                    $isRichText = $f->type === 'richtext';
                                    $display = '-';
                                    if ($fv) {
                                        if ($fv->value_text !== null && $fv->value_text !== '') {
                                            $display = $fv->value_text;
                                        } elseif ($fv->value_number !== null) {
                                            $display = $fv->value_number;
                                        } elseif ($fv->value_date !== null) {
                                            $display = optional($fv->value_date)->format('Y-m-d') ?: '-';
                                        } elseif (is_array($fv->value_json)) {
                                            $display = json_encode($fv->value_json, JSON_UNESCAPED_UNICODE);
                                        }
                                    }
                                @endphp
                                <div class="student-kv">
                                    <div class="student-kv__label">{{ $f->label_id }}</div>
                                    <div class="student-kv__value">
                                        @if ($isRichText && $display !== '-')
                                            <div class="content-prose">{!! app(\App\Services\HtmlSanitizer::class)->clean((string) $display) !!}</div>
                                        @else
                                            {{ $display }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if ($req->outputs->first() || $hasDocPreview)
                        <div class="student-data-actions">
                            <div class="student-data-actions__left">
                                @if ($req->outputs->first())
                                    <x-button
                                        class="!border-[rgb(var(--c-primary))] !bg-[rgb(var(--c-primary))] !text-white hover:!border-[rgb(var(--c-primary))] hover:!bg-transparent hover:!text-[rgb(var(--c-primary))] dark:hover:!border-white dark:hover:!bg-transparent dark:hover:!text-white"
                                        variant="secondary"
                                        href="{{ route('student.requests.output', $req) }}">Unduh berkas</x-button>
                                @endif
                            </div>
                            <div class="student-data-actions__right">
                                @if ($hasDocPreview)
                                    <x-button
                                        class="!border-[rgb(var(--c-primary))] !bg-transparent !text-[rgb(var(--c-primary))] hover:!border-[rgb(var(--c-primary))] hover:!bg-[rgb(var(--c-primary))] hover:!text-white dark:!border-[rgb(var(--c-primary))] dark:!bg-transparent dark:!text-[rgb(var(--c-primary))] dark:hover:!border-[rgb(var(--c-primary))] dark:hover:!bg-[rgb(var(--c-primary))] dark:hover:!text-white"
                                        variant="ghost" href="{{ route('requests.preview', $req) }}"
                                        target="_blank">Buka preview</x-button>
                                @endif
                            </div>
                        </div>
                    @endif
                </x-card>

                @if ($req->service?->allow_general_attachments)
                    <x-card class="student-show-card">
                        <div class="student-card-header">
                            <div class="student-card-title">Lampiran</div>
                            <div class="student-card-subtitle">Daftar lampiran umum yang sudah diunggah bersama form
                                permohonan.
                            </div>
                        </div>

                        <div class="student-attachment-list">
                            @forelse($generalAttachments as $a)
                                <div class="student-attachment-item">
                                    <div class="student-attachment-item__meta">
                                        <div class="student-attachment-item__name">{{ $a->original_name }}</div>
                                        <div class="student-attachment-item__sub">{{ $a->kind->value }} &bull;
                                            {{ number_format($a->size / 1024, 1) }} KB</div>
                                    </div>
                                    <div class="student-attachment-item__actions">
                                        <x-button variant="ghost"
                                            href="{{ route('attachments.download', $a) }}">Download</x-button>
                                    </div>
                                </div>
                            @empty
                                <div class="student-empty">Belum ada lampiran umum.</div>
                            @endforelse
                        </div>
                    </x-card>
                @endif

                <x-card class="student-show-card">
                    <div class="student-card-header">
                        <div class="student-card-title">Catatan</div>
                        <div class="student-card-subtitle">Catatan terlihat oleh Anda dan petugas (kecuali catatan
                            internal).</div>
                    </div>
                    <form class="student-note-form" method="POST" action="{{ route('student.requests.note', $req) }}">
                        @csrf
                        <x-textarea name="body" rows="3"
                            label="Tulis catatan (opsional)">{{ old('body') }}</x-textarea>
                        <x-button type="submit" class="student-note-submit">
                            Kirim Catatan
                        </x-button>
                    </form>
                    <div class="student-note-list">
                        @foreach ($req->notes->where('is_internal', false) as $n)
                            <div class="student-note">
                                <div class="student-note__meta">{{ $n->actor?->name }} &bull;
                                    {{ \Carbon\Carbon::parse($n->created_at)->format('d M Y H:i') }}</div>
                                <div class="student-note__body">{{ $n->body }}</div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </div>

            <div class="student-show-side">
                <x-card class="student-show-card">
                    <div class="student-card-header">
                        <div class="student-card-title">Riwayat Status</div>
                    </div>
                    <div class="student-history-list">
                        @foreach ($req->histories as $h)
                            <div class="student-history">
                                <div class="student-history__meta">
                                    {{ \Carbon\Carbon::parse($h->created_at)->format('d M Y H:i') }} &bull;
                                    {{ $h->actor?->name }}</div>
                                <div class="student-history__body">
                                    <div class="student-history__line">{{ $h->from_status ?? '-' }} &rarr; <span
                                            class="student-history__to">{{ $h->to_status }}</span></div>
                                    @if ($h->note)
                                        <div class="student-history__note">{{ $h->note }}</div>
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
