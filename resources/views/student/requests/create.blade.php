@extends('layouts.app')
@section('section', 'Ajukan')
@section('content')
    @php
        $fieldCount = (int) count($fields ?? []);
    @endphp
    <div class="page-student-requests-create" data-student-requests-create-page>
        <header class="student-page-header student-create-hero">
            <div class="student-page-heading student-create-hero__heading">
                <div class="student-create-hero__kicker-row">
                    <div class="student-page-kicker">Ajukan layanan</div>
                </div>
                <h2 class="student-page-title">{{ $service->title_id }}</h2>
                <p class="student-page-subtitle">Lengkapi data dengan benar sebelum mengirim permohonan.
                </p>
            </div>
            <div class="student-page-actions student-create-hero__actions">
                <div class="student-meta student-create-hero__meta">
                    <div class="student-meta-pill" aria-label="Jumlah field">
                        <div class="student-meta-pill__label">Field</div>
                        <div class="student-meta-pill__value">{{ $fieldCount }}</div>
                    </div>
                </div>
                <x-button variant="secondary" class="student-page-detail-btn"
                    href="{{ route('services.show', $service) }}">Lihat detail</x-button>
            </div>
        </header>

        <div class="student-create-layout">
            <div class="student-create-main">
                <x-card class="student-create-form">
                    <div class="student-card-header student-create-form__header">
                        <div>
                            <div class="student-card-title">Form permohonan</div>
                            <div class="student-card-subtitle">Field ditampilkan sesuai kebutuhan layanan.</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('student.requests.store') }}" class="student-form"
                        enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="service_id" value="{{ $service->id }}">

                        @if ($fieldCount > 0)
                            <div class="student-form-fields">
                                @foreach ($fields as $f)
                                    @php
                                        $name = "fields[{$f->id}]";
                                        $oldKey = "fields.{$f->id}";
                                        $oldValue = old($oldKey);
                                        $label = app()->getLocale() === 'en' ? $f->label_en ?? $f->label_id : $f->label_id;
                                        $isFull = in_array($f->type, ['textarea', 'richtext'], true);
                                    @endphp

                                    <div class="student-form-field {{ $isFull ? 'is-full' : '' }}">
                                        @if ($f->type === 'richtext')
                                            <x-tiptap-editor :label="$label" :name="$name" :value="$oldValue"
                                                height="min-h-[180px]" />
                                        @elseif($isFull)
                                            <x-textarea :label="$label" :name="$name"
                                                rows="4">{{ $oldValue }}</x-textarea>
                                        @elseif($f->type === 'file')
                                            <x-input type="file" :label="$label" :name="$name" />
                                        @elseif($f->type === 'date')
                                            <x-input type="date" :label="$label" :name="$name" :value="$oldValue" />
                                        @elseif($f->type === 'number')
                                            <x-input type="number" :label="$label" :name="$name" :value="$oldValue" />
                                        @elseif($f->type === 'select')
                                            <x-select :label="$label" :name="$name">
                                                <option value="">-- pilih --</option>
                                                @foreach ($f->options_json ?? [] as $opt)
                                                    <option value="{{ $opt }}" @selected((string) $oldValue === (string) $opt)>
                                                        {{ $opt }}</option>
                                                @endforeach
                                            </x-select>
                                        @else
                                            <x-input type="text" :label="$label" :name="$name" :value="$oldValue" />
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if (!empty($isCertificateService))
                            @include('student.requests._certificate_fields', [
                                'certificateEditorState' => $certificateEditorState ?? [],
                                'certificateInternalSignerOptions' =>
                                    $certificateInternalSignerOptions ?? collect(),
                                'isRevision' => false,
                                'hasBaseFields' => $fieldCount > 0,
                            ])
                        @endif

                        @if (empty($isCertificateService) && !empty($pemohonSignatureSigners) && count($pemohonSignatureSigners) > 0)
                            <div class="student-create-subsection mt-6">
                                <div class="student-create-subsection__title">Tanda tangan pemohon</div>
                                <div class="student-create-subsection__subtitle">
                                    Upload tanda tangan pemohon agar proses penandatanganan lebih cepat.
                                </div>

                                <div class="student-create-subsection__grid">
                                    @foreach ($pemohonSignatureSigners as $s)
                                        @php
                                            $idx = (int) $s->order_index;
                                            $mimeTypes = is_array($s->signature_file_types)
                                                ? array_values(array_filter($s->signature_file_types))
                                                : [];
                                            $mimeTypes = array_values(
                                                array_intersect($mimeTypes, ['image/png', 'image/jpeg', 'image/webp']),
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
                                            $helpParts = [];
                                            if (!empty($mimeLabels)) {
                                                $helpParts[] = 'Format: ' . implode(', ', $mimeLabels) . '.';
                                            }
                                            if ($maxKb > 0) {
                                                $helpParts[] = 'Maksimal ' . $maxKb . ' KB.';
                                            }
                                            $help = implode(' ', $helpParts);
                                        @endphp
                                        <x-input type="file" :name="'pemohon_signatures[' . $idx . ']'" :label="'Tanda tangan ' . $labelFromAdmin" :accept="$accept"
                                            :help="$help" />
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (empty($isCertificateService) && !empty($customSigners) && count($customSigners) > 0)
                            <div class="student-create-subsection mt-6">
                                <div class="student-create-subsection__title">Penandatangan lain</div>
                                <div class="student-create-subsection__subtitle">
                                    Layanan ini membutuhkan unggah tanda tangan tambahan untuk penandatangan lain. Label
                                    ditentukan oleh admin.
                                </div>

                                <div class="student-create-subsection__grid">
                                    @foreach ($customSigners as $s)
                                        @php
                                            $idx = (int) $s->order_index;
                                            $mimeTypes = is_array($s->signature_file_types)
                                                ? array_values(array_filter($s->signature_file_types))
                                                : [];
                                            $mimeTypes = array_values(
                                                array_intersect($mimeTypes, ['image/png', 'image/jpeg', 'image/webp']),
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
                                            $helpParts = [];
                                            if (!empty($mimeLabels)) {
                                                $helpParts[] = 'Format: ' . implode(', ', $mimeLabels) . '.';
                                            }
                                            if ($maxKb > 0) {
                                                $helpParts[] = 'Maksimal ' . $maxKb . ' KB.';
                                            }
                                            $help = implode(' ', $helpParts);
                                        @endphp
                                        <div class="student-create-subsection__field space-y-3">
                                            <x-input type="file" :name="'custom_signatures[' . $idx . ']'" :label="'Tanda tangan ' . $labelFromAdmin" :accept="$accept"
                                                :help="$help" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if (empty($isCertificateService) && !empty($dosenSelectSigners) && count($dosenSelectSigners) > 0)
                            <div class="student-create-subsection mt-6">
                                <div class="student-create-subsection__title">Pemilihan dosen penandatangan</div>
                                <div class="student-create-subsection__subtitle">
                                    Pilih dosen/pimpinan yang akan menjadi penandatangan untuk tahapan role DOSEN.
                                </div>

                                <div class="student-create-subsection__grid">
                                    @foreach ($dosenSelectSigners as $s)
                                        @php
                                            $idx = (int) $s->order_index;
                                            $labelFromAdmin = trim((string) ($s->custom_label ?? ''));
                                            if ($labelFromAdmin === '') {
                                                $labelFromAdmin = 'Dosen #' . $idx;
                                            }
                                            $selectedUserId = (int) old('dosen_signers.' . $idx, 0);
                                        @endphp

                                        <x-select :name="'dosen_signers[' . $idx . ']'" :label="'Pilih ' . $labelFromAdmin" data-scrollable-user-select="1">
                                            <option value="">-- pilih user --</option>
                                            @foreach ($dosenSignerOptions ?? [] as $u)
                                                <option value="{{ $u->id }}" @selected($selectedUserId === (int) $u->id)>
                                                    {{ $u->name }}</option>
                                            @endforeach
                                        </x-select>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($service->allow_general_attachments)
                            <div class="student-create-subsection mt-6">
                                <div class="student-create-subsection__title">Lampiran umum</div>
                                <div class="student-create-subsection__subtitle">
                                    Unggah file pendukung tambahan jika diperlukan. Bagian ini opsional dan Anda dapat memilih lebih dari satu file.
                                </div>

                                <div class="student-create-subsection__grid">
                                    <div class="student-create-subsection__field space-y-3">
                                        <x-input type="file" name="attachments[]" label="File lampiran pendukung" multiple />
                                        <div class="text-xs text-muted">
                                            Kosongkan jika layanan ini tidak membutuhkan lampiran tambahan.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="student-form-actions">
                            <x-button type="submit" class="student-form-actions__primary">Submit</x-button>
                            <x-button variant="ghost" class="student-form-actions__secondary"
                                href="{{ route('services.index') }}">Batal</x-button>
                        </div>
                    </form>
                </x-card>
            </div>

            <aside class="student-create-aside">
                <x-card class="student-create-help-card">
                    <div class="student-card-title">Panduan singkat</div>
                    <ul class="student-help-list">
                        <li>Pastikan data sesuai dokumen resmi.</li>
                        <li>Cek kembali ejaan dan format tanggal/angka.</li>
                        @if ($service->allow_general_attachments)
                            <li>Lampiran umum bisa diunggah langsung dari form ini dan bisa lebih dari satu file.</li>
                        @endif
                        @if (!empty($isCertificateService))
                            <li>Untuk sertifikat/piagam, wajib upload dokumen sumber <span class="font-mono">.pptx</span>
                                dan isi signer sesuai token.</li>
                            <li>Gunakan font umum (Times New Roman/Arial/Calibri), hindari mirror/flip, dan cek file yang dipilih sebelum submit.</li>
                        @endif
                    </ul>
                </x-card>

                <x-card class="student-create-note student-create-note-card">
                    <div class="student-card-title">Catatan</div>
                    <div class="student-create-note__body">
                        Permohonan akan diproses sesuai antrian. Anda dapat memantau status dari halaman permohonan.
                    </div>
                </x-card>
            </aside>
        </div>
    </div>
@endsection
