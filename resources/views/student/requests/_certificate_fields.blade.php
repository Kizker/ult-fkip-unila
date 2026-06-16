@php
    $state = is_array($certificateEditorState ?? null) ? $certificateEditorState : [];
    $stateSigners = is_array($state['signers'] ?? null) ? array_values($state['signers']) : [];
    $stateSignaturePreviewByIndex = [];
    foreach ($stateSigners as $i => $stateRow) {
        if (!is_array($stateRow)) {
            continue;
        }
        $stateSignaturePreviewByIndex[(int) $i] = (string) ($stateRow['signature_preview_url'] ?? '');
    }
    $rawInitialSigners = old('certificate_signers');
    if (!is_array($rawInitialSigners)) {
        $rawInitialSigners = $stateSigners;
    }

    $initialSigners = [];
    foreach ($rawInitialSigners as $rawIndex => $row) {
        if (!is_array($row)) {
            continue;
        }
        $idx = (int) $rawIndex;
        $initialSigners[] = [
            'type' => strtolower((string) ($row['type'] ?? ($row['signer_type'] ?? 'internal'))),
            'internal_user_id' => (string) ($row['internal_user_id'] ?? ($row['signer_user_id'] ?? '')),
            'name' => (string) ($row['name'] ?? ''),
            'id_number' => (string) ($row['id_number'] ?? ''),
            'jabatan' => (string) ($row['jabatan'] ?? ''),
            'signature_preview_url' =>
                (string) ($row['signature_preview_url'] ?? ($stateSignaturePreviewByIndex[$idx] ?? '')),
        ];
    }
    if (empty($initialSigners)) {
        $initialSigners[] = [
            'type' => 'internal',
            'internal_user_id' => '',
            'name' => '',
            'id_number' => '',
            'jabatan' => '',
            'signature_preview_url' => '',
        ];
    }

    $sourceAttachment = $state['source_attachment'] ?? null;
    $sourceOriginalName = (string) ($state['source_original_name'] ?? '');
    $sourceDownloadUrl = $sourceAttachment ? route('attachments.download', $sourceAttachment) : null;
    $sourceServerPreviewUrl = route('student.requests.certificate.source_preview');
    $isRevision = (bool) ($isRevision ?? false);
    $activityTitle = old('certificate_activity_title');
    if (!is_string($activityTitle)) {
        $activityTitle = (string) ($state['activity_title'] ?? '');
    }
    $pemohonUser = auth()->user();
    $pemohonIdentityCandidates = [
        data_get($pemohonUser, 'student_number'),
        data_get($pemohonUser, 'user_number'),
        data_get($pemohonUser, 'nip'),
        data_get($pemohonUser, 'employee_number'),
        data_get($pemohonUser, 'nik'),
    ];
    $pemohonIdentityNumber = '';
    foreach ($pemohonIdentityCandidates as $candidate) {
        $value = trim((string) ($candidate ?? ''));
        if ($value !== '') {
            $pemohonIdentityNumber = $value;
            break;
        }
    }
    $pemohonSignerProfile = [
        'name' => trim((string) data_get($pemohonUser, 'name', '')),
        'id_number' => $pemohonIdentityNumber,
        'jabatan' => trim((string) data_get($pemohonUser, 'jabatan', '')),
    ];
    $internalSignerProfiles = collect($certificateInternalSignerOptions ?? collect())
        ->map(function ($u) {
            if (!is_object($u)) {
                return null;
            }

            $id = (int) ($u->id ?? 0);
            if ($id < 1) {
                return null;
            }

            $identityCandidates = [
                data_get($u, 'identity_number'),
                data_get($u, 'student_number'),
                data_get($u, 'user_number'),
                data_get($u, 'nip'),
                data_get($u, 'employee_number'),
                data_get($u, 'nik'),
            ];
            $identityNumber = '';
            foreach ($identityCandidates as $candidate) {
                $value = trim((string) ($candidate ?? ''));
                if ($value !== '') {
                    $identityNumber = $value;
                    break;
                }
            }

            return [
                'id' => (string) $id,
                'name' => trim((string) ($u->name ?? '')),
                'id_number' => $identityNumber,
                'jabatan' => trim((string) ($u->jabatan ?? '')),
            ];
        })
        ->filter(fn($row) => is_array($row))
        ->values()
        ->all();
@endphp

<div class="page-student-requests-certificate" data-student-requests-certificate-page>
    <div class="cert-editor {{ !empty($hasBaseFields) ? 'mt-3' : 'mt-0' }} rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-4"
        x-data="window.certificateSignerEditor(@js($initialSigners), @js($internalSignerProfiles), @js($pemohonSignerProfile), @js(['source_preview_url' => $sourceServerPreviewUrl]))">
    <div class="cert-editor__head">
        <div class="cert-editor__kicker">Sertifikat/Piagam</div>
        <div class="cert-editor__title">Dokumen Sumber & Penandatangan</div>
        <div class="cert-editor__subtitle">Upload file sumber <strong>.pptx</strong> dan tentukan signer sejak awal.
        </div>
    </div>

    <div class="cert-editor__source mt-4 grid gap-4">
        <x-input type="text" name="certificate_activity_title" label="Judul kegiatan"
            :value="$activityTitle" required
            help="Akan dipakai untuk judul permohonan dan pencarian, misalnya: Seminar Nasional FKIP 2026." />
        <div class="space-y-1">
            <label class="text-sm font-medium" for="certificate_source_pptx">
                {{ $isRevision ? 'Upload .pptx sumber (opsional jika sudah ada)' : 'Upload .pptx sumber (wajib)' }}
            </label>
            <div class="cert-file-field" data-file-field data-file-empty-label="Belum ada file dipilih">
                <input
                    id="certificate_source_pptx"
                    name="certificate_source_pptx"
                    type="file"
                    accept=".pptx,application/vnd.openxmlformats-officedocument.presentationml.presentation"
                    class="sr-only"
                    @required(!$isRevision)
                    data-file-input
                    x-on:change="onSourcePptxChange($event)"
                />
                <button type="button" class="cert-file-field__button" data-file-trigger>Pilih file</button>
                <div class="cert-file-field__name" data-file-name aria-live="polite">Belum ada file dipilih</div>
            </div>
            <p class="text-xs text-muted">Format wajib .pptx. File ini menjadi sumber finalisasi PDF.</p>
            @error('certificate_source_pptx')
                <p class="text-sm text-[rgb(var(--c-danger))]">{{ $message }}</p>
            @enderror
        </div>
        <div class="cert-editor__source-preview"
            x-show="hasSelectedSourcePptx()"
            x-transition.opacity.duration.150ms>
            <div class="text-xs text-muted mb-1">Dokumen yang dipilih (belum disubmit):</div>
            <div class="text-sm font-medium break-all" x-text="selectedSourcePptxName"></div>
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <x-button type="button" variant="secondary"
                    @click="previewSourcePptxOnServer()"
                    x-bind:disabled="!canPreviewSourceOnServer()">
                    <span x-show="!sourceServerPreviewBusy">Preview Dokumen</span>
                    <span x-show="sourceServerPreviewBusy">Menyiapkan preview...</span>
                </x-button>
            </div>
            <div class="text-xs text-muted mt-2">
                Klik <strong>Preview Dokumen</strong> untuk membuka hasil render PDF di tab baru sebelum submit.
            </div>
            <div class="cert-editor__source-preview-state cert-editor__source-preview-state--error mt-3"
                x-show="!sourceServerPreviewBusy && sourceServerPreviewError !== ''"
                x-text="sourceServerPreviewError">
            </div>
        </div>
        @if ($sourceOriginalName !== '')
            <div class="cert-editor__source-current text-xs text-muted">
                <span class="cert-editor__source-label">File saat ini:</span>
                <strong>{{ $sourceOriginalName }}</strong>
                @if ($sourceDownloadUrl)
                    (<a href="{{ $sourceDownloadUrl }}" target="_blank" rel="noopener noreferrer">download</a>)
                @endif
            </div>
        @endif
    </div>

    <div class="cert-editor__signers mt-5">
        <div class="cert-editor__section-head flex items-center justify-between gap-2">
            <div>
                <div class="cert-editor__section-title text-sm font-semibold">Daftar penandatangan</div>
                <div class="cert-editor__section-subtitle text-xs text-muted mt-1">Signer bisa dosen, pemohon, atau
                    custom. Urutan signer mengikuti urutan di bawah.</div>
            </div>
            <x-button type="button" variant="secondary" class="cert-editor__add-btn" @click="addSigner">Tambah
                signer</x-button>
        </div>

        <template x-for="(signer, idx) in signers" :key="idx">
            <div class="cert-editor__signer mt-3 rounded-xl border border-[rgb(var(--c-border))] bg-white/60 p-3 space-y-3"
                x-transition.opacity.duration.180ms :style="`--signer-index:${idx}`">
                <div class="cert-editor__signer-head flex items-center justify-between gap-2">
                    <div class="cert-editor__signer-title text-sm font-semibold">
                        <span class="cert-editor__signer-badge" x-text="idx + 1"></span>
                        <span>Signer</span>
                    </div>
                    <button type="button" class="cert-editor__remove-btn text-xs text-red-600 hover:underline"
                        @click="removeSigner(idx)" x-show="signers.length > 1">Hapus</button>
                </div>

                <div class="cert-editor__grid cert-editor__grid--2 grid gap-3 md:grid-cols-2">
                    <div>
                        <label class="cert-editor__label text-sm font-medium">Tipe signer</label>
                        <select class="as-input mt-1" :name="`certificate_signers[${idx}][type]`" x-model="signer.type"
                            @change="onTypeChange(signer)">
                            <option value="internal">Dosen</option>
                            <option value="pemohon">Pemohon</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>

                    <div x-show="signer.type === 'internal'">
                        <label class="cert-editor__label text-sm font-medium">User dosen</label>
                        <select class="as-input mt-1" :name="`certificate_signers[${idx}][internal_user_id]`"
                            x-model="signer.internal_user_id" @change="onInternalUserChange(signer)"
                            data-scrollable-user-select="1">
                            <option value="">-- pilih user --</option>
                            @foreach ($certificateInternalSignerOptions ?? collect() as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="cert-editor__grid cert-editor__grid--3 grid gap-3 md:grid-cols-3">
                    <div>
                        <label class="cert-editor__label text-sm font-medium">Nama penandatangan</label>
                        <input type="text" class="as-input mt-1" :class="inputReadonlyClass(signer, 'name')"
                            :name="`certificate_signers[${idx}][name]`" x-model="signer.name"
                            :readonly="isFieldLocked(signer, 'name')"
                            placeholder="Opsional untuk dosen/pemohon, wajib untuk custom">
                    </div>
                    <div>
                        <label class="cert-editor__label text-sm font-medium">ID penandatangan (NIP/NPM/dll)</label>
                        <input type="text" class="as-input mt-1" :class="inputReadonlyClass(signer, 'id_number')"
                            :name="`certificate_signers[${idx}][id_number]`" x-model="signer.id_number"
                            :readonly="isFieldLocked(signer, 'id_number')" placeholder="Wajib">
                    </div>
                    <div>
                        <label class="cert-editor__label text-sm font-medium">Jabatan (opsional)</label>
                        <input type="text" class="as-input mt-1" :class="inputReadonlyClass(signer, 'jabatan')"
                            :name="`certificate_signers[${idx}][jabatan]`" x-model="signer.jabatan"
                            :readonly="isFieldLocked(signer, 'jabatan')" placeholder="Contoh: Dekan">
                    </div>
                </div>

                <div class="cert-editor__signature" x-show="signer.type === 'pemohon' || signer.type === 'custom'">
                    <div class="grid gap-4 md:gap-6 md:grid-cols-2 items-start">
                        <div>
                            <label class="cert-editor__label text-sm font-medium">Tanda tangan gambar
                                (PNG/JPG/WEBP)</label>
                            <div class="ui-file-field mt-1" data-file-field data-file-empty-label="Belum ada file dipilih">
                                <input
                                    type="file"
                                    class="sr-only"
                                    :name="`certificate_signatures[${idx}]`"
                                    accept="image/png,image/jpeg,image/webp"
                                    data-file-input
                                    @change="onSignatureFileChange($event, signer)">
                                <button type="button" class="ui-file-field__button" data-file-trigger>Pilih file</button>
                                <div class="ui-file-field__name" data-file-name aria-live="polite">Belum ada file dipilih</div>
                            </div>
                            <div class="cert-editor__help text-xs text-muted mt-1">Wajib untuk pemohon/custom. Maksimal
                                1 MB.</div>
                        </div>
                        <div class="rounded-xl border border-[rgb(var(--c-border))] bg-white/70 p-3"
                            x-show="hasSignaturePreview(signer)" x-transition.opacity.duration.150ms>
                            <div class="text-xs text-muted mb-1" x-text="signaturePreviewLabel(signer)"></div>
                            <a class="inline-block" :href="signaturePreviewUrl(signer) || '#'"
                                :class="hasSignaturePreview(signer) ? '' : 'pointer-events-none'" target="_blank"
                                rel="noopener noreferrer">
                                <img :src="signaturePreviewUrl(signer)"
                                    :alt="`Preview tanda tangan ${signer.name || ('Signer ' + (idx + 1))}`"
                                    class="h-24 w-full object-contain">
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <div class="cert-editor__guide content-panel--certificate mt-5 rounded-xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-3">
        <div class="cert-editor__guide-title">Pedoman Sertifikat/Piagam</div>
        <div class="service-doc-preview__hint service-cert-guide__intro">
            Bagian ini khusus layanan dengan dokumen sumber <span class="font-mono">.pptx</span> dari pemohon.
        </div>
        <ol class="as-help service-cert-guide__list mt-3">
            <li class="service-cert-guide__item">
                <div class="service-cert-guide__copy">
                    <p class="service-cert-guide__text">
                        Siapkan dan upload dokumen sumber <span class="font-mono">.pptx</span>.
                    </p>
                </div>
            </li>
            <li class="service-cert-guide__item">
                <div class="service-cert-guide__copy">
                    <p class="service-cert-guide__text">
                        Tentukan daftar signer sejak awal (internal/pemohon/custom).
                    </p>
                </div>
            </li>
            <li class="service-cert-guide__item">
                <div class="service-cert-guide__copy">
                    <p class="service-cert-guide__text">Pastikan token wajib ada:</p>
                    <div class="service-cert-guide__chips">
                        <span class="font-mono">{{ '{' }}{{ '{' }}nomor_surat{{ '}' }}{{ '}' }}</span>
                        <span class="font-mono">{{ '{' }}{{ '{' }}tanggal_ttd{{ '}' }}{{ '}' }}</span>
                        <span class="font-mono">i</span>
                    </div>
                    <p class="service-cert-guide__text">serta token signer per indeks.</p>
                </div>
            </li>
            <li class="service-cert-guide__item">
                <div class="service-cert-guide__copy">
                    <p class="service-cert-guide__text">
                        Tempatkan token <span class="font-mono">{{ '{' }}{{ '{' }}ttd_i{{ '}' }}{{ '}' }}</span> di shape TTD yang <strong>transparan</strong> (tanpa fill dan tanpa outline). Samakan tinggi shape antar signer, lalu sistem akan menghitung lebar TTD dari tinggi shape (bukan tinggi menyesuaikan lebar).
                    </p>
                </div>
            </li>
            <li class="service-cert-guide__item">
                <div class="service-cert-guide__copy">
                    <p class="service-cert-guide__text">Token opsional diproses jika ada di template:</p>
                    <div class="service-cert-guide__chips">
                        <span class="font-mono">{{ '{' }}{{ '{' }}jabatan_penandatangan_i{{ '}' }}{{ '}' }}</span>
                        <span class="font-mono">{{ '{' }}{{ '{' }}nama_penerima{{ '}' }}{{ '}' }}</span>
                    </div>
                </div>
            </li>
            <li class="service-cert-guide__item">
                <div class="service-cert-guide__copy">
                    <p class="service-cert-guide__text">Jangan pakai token:</p>
                    <div class="service-cert-guide__chips">
                        <span class="font-mono">{{ '{' }}{{ '{' }}kota_ttd{{ '}' }}{{ '}' }}</span>
                        <span class="service-cert-guide__chip-sep">dan</span>
                        <span class="font-mono">{{ '{' }}{{ '{' }}tanggal_surat{{ '}' }}{{ '}' }}</span>
                    </div>
                </div>
            </li>
            <li class="service-cert-guide__item">
                <div class="service-cert-guide__copy">
                    <p class="service-cert-guide__text">Rekomendasi font yang paling aman dipakai di server:</p>
                    <div class="service-cert-guide__chips">
                        <span><strong>Serif:</strong> <span class="font-mono">Times New Roman</span>, <span class="font-mono">Cambria</span>, <span class="font-mono">Georgia</span></span>
                    </div>
                    <div class="service-cert-guide__chips">
                        <span><strong>Serif tambahan:</strong> <span class="font-mono">Garamond</span>, <span class="font-mono">Palatino Linotype</span>, <span class="font-mono">Book Antiqua</span>, <span class="font-mono">Constantia</span></span>
                    </div>
                    <div class="service-cert-guide__chips">
                        <span><strong>Sans Serif:</strong> <span class="font-mono">Arial</span>, <span class="font-mono">Calibri</span>, <span class="font-mono">Segoe UI</span>, <span class="font-mono">Verdana</span></span>
                    </div>
                    <div class="service-cert-guide__chips">
                        <span><strong>Sans Serif tambahan:</strong> <span class="font-mono">Tahoma</span>, <span class="font-mono">Trebuchet MS</span>, <span class="font-mono">Corbel</span>, <span class="font-mono">Candara</span></span>
                    </div>
                    <div class="service-cert-guide__chips">
                        <span><strong>Monospace (opsional):</strong> <span class="font-mono">Consolas</span>, <span class="font-mono">Courier New</span></span>
                    </div>
                    <p class="service-cert-guide__text">Hindari font theme seperti <span class="font-mono">+mn-lt</span>/<span class="font-mono">+mj-lt</span>. Jika perlu, jalankan <strong>Replace Fonts</strong> di PowerPoint.</p>
                    <p class="service-cert-guide__text">Untuk admin, cek daftar font server dengan perintah <span class="font-mono">fc-list</span> agar rekomendasi benar-benar sesuai server produksi.</p>
                </div>
            </li>
            <li class="service-cert-guide__item">
                <div class="service-cert-guide__copy">
                    <p class="service-cert-guide__text">Gunakan font regular. Jika ingin tebal/miring/garis bawah, gunakan tools <strong>Bold</strong>, <strong>Italic</strong>, dan <strong>Underline</strong> di PowerPoint.</p>
                </div>
            </li>
            <li class="service-cert-guide__item">
                <div class="service-cert-guide__copy">
                    <p class="service-cert-guide__text">Hindari elemen <strong>mirror/flip</strong> atau dekoratif kompleks. Jika tetap diperlukan, ubah dulu menjadi gambar final (PNG).</p>
                </div>
            </li>
            <li class="service-cert-guide__item">
                <div class="service-cert-guide__copy">
                    <p class="service-cert-guide__text">Sebelum submit, cek dulu file yang dipilih. Setelah submit, lakukan <strong>Preview Dokumen</strong> di halaman detail. Jika masih ada pergeseran, perbaiki template lalu ajukan ulang/perbaikan.</p>
                </div>
            </li>
        </ol>
        <div class="service-cert-example">
            <div class="service-doc-preview__hint service-cert-example__label">Contoh layout sertifikat/piagam:</div>
            <a class="service-cert-example__link" href="{{ asset('example/CONTOH SERTIFIKAT_page-0001.jpg') }}"
                target="_blank" rel="noopener noreferrer">
                <img class="service-cert-example__image"
                    src="{{ asset('example/CONTOH SERTIFIKAT_page-0001.jpg') }}"
                    alt="Contoh sertifikat piagam untuk acuan placeholder" loading="lazy">
            </a>
            <div class="service-cert-example__caption">Klik gambar untuk membuka ukuran penuh.</div>
        </div>
        <div class="service-doc-preview__fallback service-cert-note mt-2">
            Catatan: <span class="font-mono">{{ '{' }}{{ '{' }}tanggal_ttd{{ '}' }}{{ '}' }}</span> mengikuti waktu signer terakhir. Jumlah signer harus cocok dengan token indeks (<span class="font-mono">ttd_1..ttd_n</span>).
        </div>
    </div>
    </div>
</div>
