@php
  $state = is_array($certificateEditorState ?? null) ? $certificateEditorState : [];
  $stateSigners = is_array($state['signers'] ?? null) ? array_values($state['signers']) : [];
  $stateSignaturePreviewByIndex = [];
  foreach ($stateSigners as $i => $stateRow) {
    if (!is_array($stateRow)) continue;
    $stateSignaturePreviewByIndex[(int) $i] = (string) ($stateRow['signature_preview_url'] ?? '');
  }
  $rawInitialSigners = old('certificate_signers');
  if (!is_array($rawInitialSigners)) {
    $rawInitialSigners = $stateSigners;
  }

  $initialSigners = [];
  foreach ($rawInitialSigners as $rawIndex => $row) {
    if (!is_array($row)) continue;
    $idx = (int) $rawIndex;
    $initialSigners[] = [
      'type' => strtolower((string) ($row['type'] ?? $row['signer_type'] ?? 'internal')),
      'internal_user_id' => (string) ($row['internal_user_id'] ?? $row['signer_user_id'] ?? ''),
      'name' => (string) ($row['name'] ?? ''),
      'id_number' => (string) ($row['id_number'] ?? ''),
      'jabatan' => (string) ($row['jabatan'] ?? ''),
      'signature_preview_url' => (string) ($row['signature_preview_url'] ?? ($stateSignaturePreviewByIndex[$idx] ?? '')),
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
  $isRevision = (bool) ($isRevision ?? false);
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
    ->filter(fn ($row) => is_array($row))
    ->values()
    ->all();
@endphp

<div class="cert-editor mt-6 rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-4" x-data="certificateSignerEditor(@js($initialSigners), @js($internalSignerProfiles), @js($pemohonSignerProfile))">
  <div class="cert-editor__head">
    <div class="cert-editor__kicker">Sertifikat/Piagam</div>
    <div class="cert-editor__title">Dokumen Sumber & Penandatangan</div>
    <div class="cert-editor__subtitle">Upload file sumber <strong>.pptx</strong> dan tentukan signer sejak awal.</div>
  </div>

  <div class="cert-editor__source mt-4 grid gap-4">
    <x-input
      type="file"
      name="certificate_source_pptx"
      label="{{ $isRevision ? 'Upload .pptx sumber (opsional jika sudah ada)' : 'Upload .pptx sumber (wajib)' }}"
      accept=".pptx,application/vnd.openxmlformats-officedocument.presentationml.presentation"
      :required="!$isRevision"
      help="Format wajib .pptx. File ini menjadi sumber finalisasi PDF."
    />
    @if($sourceOriginalName !== '')
      <div class="cert-editor__source-current text-xs text-muted">
        <span class="cert-editor__source-label">File saat ini:</span>
        <strong>{{ $sourceOriginalName }}</strong>
        @if($sourceDownloadUrl)
          (<a href="{{ $sourceDownloadUrl }}" target="_blank" rel="noopener noreferrer">download</a>)
        @endif
      </div>
    @endif
  </div>

  <div class="cert-editor__signers mt-5">
    <div class="cert-editor__section-head flex items-center justify-between gap-2">
      <div>
        <div class="cert-editor__section-title text-sm font-semibold">Daftar penandatangan</div>
        <div class="cert-editor__section-subtitle text-xs text-muted mt-1">Signer bisa dosen, pemohon, atau custom. Urutan signer mengikuti urutan di bawah.</div>
      </div>
      <x-button type="button" variant="secondary" class="cert-editor__add-btn" @click="addSigner">Tambah signer</x-button>
    </div>

    <template x-for="(signer, idx) in signers" :key="idx">
      <div class="cert-editor__signer mt-3 rounded-xl border border-[rgb(var(--c-border))] bg-white/60 p-3 space-y-3" x-transition.opacity.duration.180ms :style="`--signer-index:${idx}`">
        <div class="cert-editor__signer-head flex items-center justify-between gap-2">
          <div class="cert-editor__signer-title text-sm font-semibold">
            <span class="cert-editor__signer-badge" x-text="idx + 1"></span>
            <span>Signer</span>
          </div>
          <button type="button" class="cert-editor__remove-btn text-xs text-red-600 hover:underline" @click="removeSigner(idx)" x-show="signers.length > 1">Hapus</button>
        </div>

        <div class="cert-editor__grid cert-editor__grid--2 grid gap-3 md:grid-cols-2">
          <div>
            <label class="cert-editor__label text-sm font-medium">Tipe signer</label>
            <select class="as-input mt-1" :name="`certificate_signers[${idx}][type]`" x-model="signer.type" @change="onTypeChange(signer)">
              <option value="internal">Dosen</option>
              <option value="pemohon">Pemohon</option>
              <option value="custom">Custom</option>
            </select>
          </div>

          <div x-show="signer.type === 'internal'">
            <label class="cert-editor__label text-sm font-medium">User dosen</label>
            <select class="as-input mt-1" :name="`certificate_signers[${idx}][internal_user_id]`" x-model="signer.internal_user_id" @change="onInternalUserChange(signer)" data-scrollable-user-select="1">
              <option value="">-- pilih user --</option>
              @foreach(($certificateInternalSignerOptions ?? collect()) as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="cert-editor__grid cert-editor__grid--3 grid gap-3 md:grid-cols-3">
          <div>
            <label class="cert-editor__label text-sm font-medium">Nama penandatangan</label>
            <input
              type="text"
              class="as-input mt-1"
              :class="inputReadonlyClass(signer, 'name')"
              :name="`certificate_signers[${idx}][name]`"
              x-model="signer.name"
              :readonly="isFieldLocked(signer, 'name')"
              placeholder="Opsional untuk dosen/pemohon, wajib untuk custom"
            >
          </div>
          <div>
            <label class="cert-editor__label text-sm font-medium">ID penandatangan (NIP/NPM/dll)</label>
            <input
              type="text"
              class="as-input mt-1"
              :class="inputReadonlyClass(signer, 'id_number')"
              :name="`certificate_signers[${idx}][id_number]`"
              x-model="signer.id_number"
              :readonly="isFieldLocked(signer, 'id_number')"
              placeholder="Wajib"
            >
          </div>
          <div>
            <label class="cert-editor__label text-sm font-medium">Jabatan (opsional)</label>
            <input
              type="text"
              class="as-input mt-1"
              :class="inputReadonlyClass(signer, 'jabatan')"
              :name="`certificate_signers[${idx}][jabatan]`"
              x-model="signer.jabatan"
              :readonly="isFieldLocked(signer, 'jabatan')"
              placeholder="Contoh: Dekan"
            >
          </div>
        </div>

        <div class="cert-editor__signature" x-show="signer.type === 'pemohon' || signer.type === 'custom'">
          <div class="grid gap-4 md:gap-6 md:grid-cols-2 items-start">
            <div>
              <label class="cert-editor__label text-sm font-medium">Tanda tangan gambar (PNG/JPG/WEBP)</label>
              <input
                type="file"
                class="as-input mt-1"
                :name="`certificate_signatures[${idx}]`"
                accept="image/png,image/jpeg,image/webp"
                @change="onSignatureFileChange($event, signer)"
              >
              <div class="cert-editor__help text-xs text-muted mt-1">Wajib untuk pemohon/custom. Maksimal 1 MB.</div>
            </div>
            <div class="rounded-xl border border-[rgb(var(--c-border))] bg-white/70 p-3" x-show="hasSignaturePreview(signer)" x-transition.opacity.duration.150ms>
              <div class="text-xs text-muted mb-1" x-text="signaturePreviewLabel(signer)"></div>
              <a
                class="inline-block"
                :href="signaturePreviewUrl(signer) || '#'"
                :class="hasSignaturePreview(signer) ? '' : 'pointer-events-none'"
                target="_blank"
                rel="noopener noreferrer"
              >
                <img
                  :src="signaturePreviewUrl(signer)"
                  :alt="`Preview tanda tangan ${signer.name || ('Signer ' + (idx + 1))}`"
                  class="h-24 w-full object-contain"
                >
              </a>
            </div>
          </div>
        </div>
      </div>
    </template>
  </div>

  <div class="cert-editor__guide mt-5 rounded-xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-3">
    <div class="cert-editor__guide-title text-sm font-semibold">Pedoman token Sertifikat/Piagam</div>
    <ul class="cert-editor__guide-list as-help mt-2 text-xs">
      <li>Token wajib global: <span class="font-mono">{{ '{' }}{{ '{' }}nomor_surat{{ '}' }}{{ '}' }}</span>, <span class="font-mono">{{ '{' }}{{ '{' }}tanggal_ttd{{ '}' }}{{ '}' }}</span>.</li>
      <li>Token wajib per signer (i=1..n): <span class="font-mono">{{ '{' }}{{ '{' }}ttd_i{{ '}' }}{{ '}' }}</span>, <span class="font-mono">{{ '{' }}{{ '{' }}nama_penandatangan_i{{ '}' }}{{ '}' }}</span>, <span class="font-mono">{{ '{' }}{{ '{' }}id_penandatangan_i{{ '}' }}{{ '}' }}</span>.</li>
      <li>Tempatkan <span class="font-mono">{{ '{' }}{{ '{' }}ttd_i{{ '}' }}{{ '}' }}</span> di dalam shape TTD yang <strong>transparan</strong> (tanpa fill dan tanpa outline). Samakan tinggi shape antar signer agar konsisten. Sistem memakai tinggi shape sebagai acuan lalu lebar TTD mengikuti proporsi tinggi (bukan tinggi menyesuaikan lebar).</li>
      <li>Token opsional: <span class="font-mono">{{ '{' }}{{ '{' }}jabatan_penandatangan_i{{ '}' }}{{ '}' }}</span>, <span class="font-mono">{{ '{' }}{{ '{' }}nama_penerima{{ '}' }}{{ '}' }}</span>.</li>
      <li>Token tidak dipakai: <span class="font-mono">{{ '{' }}{{ '{' }}kota_ttd{{ '}' }}{{ '}' }}</span>, <span class="font-mono">{{ '{' }}{{ '{' }}tanggal_surat{{ '}' }}{{ '}' }}</span>.</li>
      <li><span class="font-mono">{{ '{' }}{{ '{' }}tanggal_ttd{{ '}' }}{{ '}' }}</span> mengikuti waktu signer terakhir.</li>
      <li>Jumlah signer harus cocok dengan indeks token (<span class="font-mono">1..n</span>) agar tidak gagal validasi.</li>
    </ul>
    <div class="mt-3">
      <div class="text-xs text-muted">Contoh layout sertifikat/piagam:</div>
      <a
        class="mt-2 block rounded-xl border border-[rgb(var(--c-border))] bg-white overflow-hidden"
        href="{{ asset('example/CONTOH SERTIFIKAT_page-0001.jpg') }}"
        target="_blank"
        rel="noopener noreferrer"
      >
        <img
          src="{{ asset('example/CONTOH SERTIFIKAT_page-0001.jpg') }}"
          alt="Contoh sertifikat piagam untuk acuan placeholder"
          class="block w-full h-auto"
          loading="lazy"
        >
      </a>
      <div class="text-xs text-muted mt-2">Klik gambar untuk membuka ukuran penuh.</div>
    </div>
  </div>
</div>

<script>
  if (!window.certificateSignerEditor) {
    window.certificateSignerEditor = function(initialSigners, internalSignerProfiles, pemohonSignerProfile) {
      const normalizeType = (value) => {
        const t = String(value || '').toLowerCase();
        if (['internal', 'pemohon', 'custom'].includes(t)) return t;
        return 'internal';
      };
      const clean = (value) => String(value ?? '').trim();
      const emptyLocks = () => ({
        name: false,
        id_number: false,
        jabatan: false,
      });
      const signerProfileMap = {};
      const profileRows = Array.isArray(internalSignerProfiles) ? internalSignerProfiles : [];
      profileRows.forEach((row) => {
        const id = clean(row?.id);
        if (id === '') return;
        signerProfileMap[id] = {
          name: clean(row?.name),
          id_number: clean(row?.id_number),
          jabatan: clean(row?.jabatan),
        };
      });
      const pemohonProfile = {
        name: clean(pemohonSignerProfile?.name),
        id_number: clean(pemohonSignerProfile?.id_number),
        jabatan: clean(pemohonSignerProfile?.jabatan),
      };
      const normalize = (row) => ({
        type: normalizeType(row?.type),
        last_type: normalizeType(row?.type),
        internal_user_id: row?.internal_user_id ? String(row.internal_user_id) : '',
        name: row?.name ? String(row.name) : '',
        id_number: row?.id_number ? String(row.id_number) : '',
        jabatan: row?.jabatan ? String(row.jabatan) : '',
        signature_preview_url: row?.signature_preview_url ? String(row.signature_preview_url) : '',
        signature_live_preview_url: '',
        locked: emptyLocks(),
      });
      const resetAutofilledFields = (signer) => {
        signer.name = '';
        signer.id_number = '';
        signer.jabatan = '';
        signer.locked = emptyLocks();
      };
      const applyAutofillProfile = (signer) => {
        if (!signer || typeof signer !== 'object') return;

        const previousLocks = signer.locked && typeof signer.locked === 'object'
          ? {
              name: !!signer.locked.name,
              id_number: !!signer.locked.id_number,
              jabatan: !!signer.locked.jabatan,
            }
          : emptyLocks();
        signer.locked = emptyLocks();

        const type = normalizeType(signer.type);
        if (!['internal', 'pemohon'].includes(type)) {
          return;
        }

        let profile = null;
        if (type === 'internal') {
          const userId = clean(signer.internal_user_id);
          if (userId === '') {
            return;
          }
          profile = signerProfileMap[userId] ?? null;
        } else if (type === 'pemohon') {
          profile = pemohonProfile;
        }
        if (!profile) {
          return;
        }

        const fields = ['name', 'id_number', 'jabatan'];
        fields.forEach((field) => {
          const profileValue = clean(profile[field]);
          if (profileValue !== '') {
            signer[field] = profileValue;
            signer.locked[field] = true;
            return;
          }

          if (previousLocks[field]) {
            signer[field] = '';
          }
          signer.locked[field] = false;
        });
      };

      const base = Array.isArray(initialSigners) ? initialSigners.map(normalize) : [];
      const signers = base.length ? base : [normalize({ type: 'internal' })];
      signers.forEach((row) => applyAutofillProfile(row));

      return {
        signers,
        init() {
          window.addEventListener('beforeunload', () => {
            this.revokeAllSignatureBlobUrls();
          }, { once: true });
        },
        revokeSignatureBlobUrl(url) {
          const raw = clean(url);
          if (raw === '' || !raw.startsWith('blob:')) return;
          URL.revokeObjectURL(raw);
        },
        clearLiveSignaturePreview(signer) {
          if (!signer || typeof signer !== 'object') return;
          this.revokeSignatureBlobUrl(signer.signature_live_preview_url);
          signer.signature_live_preview_url = '';
        },
        revokeAllSignatureBlobUrls() {
          this.signers.forEach((row) => this.clearLiveSignaturePreview(row));
        },
        onSignatureFileChange(event, signer) {
          if (!signer || typeof signer !== 'object') return;
          this.clearLiveSignaturePreview(signer);

          const input = event?.target;
          const file = input?.files?.[0] ?? null;
          if (!file) return;
          if (!String(file.type || '').startsWith('image/')) return;

          signer.signature_live_preview_url = URL.createObjectURL(file);
        },
        signaturePreviewUrl(signer) {
          const live = clean(signer?.signature_live_preview_url);
          if (live !== '') return live;
          return clean(signer?.signature_preview_url);
        },
        hasSignaturePreview(signer) {
          return this.signaturePreviewUrl(signer) !== '';
        },
        signaturePreviewLabel(signer) {
          if (clean(signer?.signature_live_preview_url) !== '') {
            return 'Preview baru (belum disimpan)';
          }
          if (clean(signer?.signature_preview_url) !== '') {
            return 'Tanda tangan tersimpan';
          }
          return 'Preview tanda tangan';
        },
        onTypeChange(signer) {
          if (!signer) return;
          const nextType = normalizeType(signer.type);
          const prevType = normalizeType(signer.last_type);
          signer.type = nextType;

          if (nextType !== prevType) {
            resetAutofilledFields(signer);
            if (nextType !== 'internal') {
              signer.internal_user_id = '';
            }
          }

          signer.last_type = nextType;
          applyAutofillProfile(signer);
        },
        onInternalUserChange(signer) {
          if (!signer) return;
          signer.internal_user_id = signer.internal_user_id ? String(signer.internal_user_id) : '';
          if (normalizeType(signer.type) !== 'internal') {
            return;
          }
          resetAutofilledFields(signer);
          applyAutofillProfile(signer);
        },
        isFieldLocked(signer, field) {
          return !!(signer?.locked && signer.locked[field]);
        },
        inputReadonlyClass(signer, field) {
          return this.isFieldLocked(signer, field) ? 'bg-slate-100 text-slate-500 cursor-not-allowed' : '';
        },
        addSigner() {
          const signer = normalize({ type: 'internal' });
          this.signers.push(signer);
        },
        removeSigner(index) {
          if (this.signers.length <= 1) return;
          this.clearLiveSignaturePreview(this.signers[index]);
          this.signers.splice(index, 1);
        },
      };
    };
  }
</script>
