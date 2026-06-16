@extends('layouts.app')
@section('section','Layanan')
@section('content')
<div class="page-admin-services-create page-admin-services-edit" data-services-form data-translate-url="{{ route('admin.utils.translate') }}">
  @php
    $selectedDocumentSourceType = (string) old('document_source_type', \App\Enums\DocumentSourceType::MAIN_DOCX_TEMPLATE->value);
    $isCertificateMode = $selectedDocumentSourceType === \App\Enums\DocumentSourceType::REQUEST_PPTX->value;
    $docFlowDisabled = $isCertificateMode;
  @endphp
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master layanan</div>
      <h1 class="admin-page-title">Tambah Layanan</h1>
      <p class="admin-page-subtitle">Buat layanan baru beserta persyaratan, SOP, workflow, dan setup dokumen.</p>
    </div>
    <div class="admin-page-actions">
      <x-button variant="secondary" href="#setup-dokumen">Setup Dokumen</x-button>
      <x-button variant="ghost" href="{{ route('admin.layanan.index') }}">Kembali</x-button>
    </div>
  </header>

  <form class="as-form space-y-6" method="POST" action="{{ route('admin.layanan.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="as-form-layout">
      <x-card class="as-form-card xl:col-span-2">
        <div class="space-y-6">
          <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
              <div class="min-w-0">
                <div class="text-sm font-semibold">Status dokumen</div>
                <div class="text-sm text-muted mt-1">Readiness: akan dicek setelah layanan disimpan.</div>
              </div>
              <div class="flex items-center gap-2 flex-wrap">
                <x-button variant="ghost" href="#setup-dokumen">Buka setup dokumen</x-button>
              </div>
            </div>
          </div>

          <div class="as-form-grid">
            <x-select name="category_id" label="Kategori Layanan" required>
              <option value="">Pilih kategori</option>
              @foreach(($serviceCategories ?? []) as $cat)
                <option value="{{ $cat->id }}" @selected((string) old('category_id') === (string) $cat->id)>
                  {{ $cat->name_id }}
                </option>
              @endforeach
            </x-select>

            <x-select name="document_source_type" label="Sumber Dokumen Awal" required>
              <option value="{{ \App\Enums\DocumentSourceType::MAIN_DOCX_TEMPLATE->value }}" @selected(old('document_source_type', \App\Enums\DocumentSourceType::MAIN_DOCX_TEMPLATE->value) === \App\Enums\DocumentSourceType::MAIN_DOCX_TEMPLATE->value)>
                DOCX admin
              </option>
              <option value="{{ \App\Enums\DocumentSourceType::REQUEST_PPTX->value }}" @selected(old('document_source_type') === \App\Enums\DocumentSourceType::REQUEST_PPTX->value)>
                PPTX pemohon
              </option>
            </x-select>
          </div>

          <div class="as-form-grid">
            <x-input name="title_id" label="Judul (ID)" value="{{ old('title_id') }}" required />
            <x-input name="title_en" label="Title (EN)" value="{{ old('title_en') }}" />
          </div>

          <div class="as-form-grid">
            <x-textarea name="summary_id" label="Ringkasan (ID)" rows="2">{{ old('summary_id') }}</x-textarea>
            <x-textarea name="summary_en" label="Summary (EN)" rows="2">{{ old('summary_en') }}</x-textarea>
          </div>

          <div class="as-activation">
            <div class="as-activation__meta">
              <div class="as-activation__title">Status layanan</div>
              <div class="as-activation__desc">Aktifkan jika layanan sudah siap ditampilkan untuk pemohon.</div>
            </div>

            <label class="as-activation__toggle">
              <input type="checkbox" name="is_active" value="1" @checked(old('is_active')) class="as-activation__input">
              <span class="as-activation__track" aria-hidden="true">
                <span class="as-activation__thumb" aria-hidden="true"></span>
              </span>
              <span class="as-activation__state">
                <span class="as-activation__on">Active</span>
                <span class="as-activation__off">Inactive</span>
              </span>
            </label>
          </div>

          <label class="as-check">
            <input type="checkbox" name="allow_general_attachments" value="1" @checked(old('allow_general_attachments')) class="as-check__box">
            <span class="as-check__label">Tampilkan lampiran umum pada form pengajuan <span class="text-xs text-muted ml-2">(opsional, multi-file)</span></span>
          </label>

          <x-tiptap-editor
            name="requirements_html_id"
            label="Persyaratan"
            localeHint="ID"
            :value="old('requirements_html_id')"
            placeholder="Tulis persyaratan (opsional). Gunakan list untuk poin."
            help="Opsional. Gunakan list (bullet/numbered) untuk poin."
          />
          <x-tiptap-editor
            name="sop_html_id"
            label="SOP"
            localeHint="ID"
            :value="old('sop_html_id')"
            placeholder="Tulis SOP (opsional)."
            help="Opsional."
          />

        <div class="as-workflow space-y-4 doc-flow" data-doc-flow>
          <div class="as-workflow__head">
            <div>
              <div class="flex flex-wrap items-center gap-2">
                <div class="admin-card-title font-extrabold">Alur dokumen</div>
              </div>
              <div class="admin-card-subtitle">Atur tahapan tambahan untuk layanan dokumen dan lihat preview alur secara realtime.</div>
            </div>
          </div>

          <div class="doc-flow__card">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
              <div class="min-w-0">
                <div class="text-sm font-semibold">Preview alur dokumen</div>
                <div class="text-xs text-muted mt-1">Default: Admin Jurusan &rarr; Review ULT &rarr; Penandatangan Fakultas (Dekan/WD). Petugas gate awal bisa diubah ke Staf ULT.</div>
              </div>
              <div class="doc-flow__meta">
                <span class="doc-flow__meta-pill">Nomor Surat: <span class="doc-mono">NOMOR_SURAT</span></span>
              </div>
            </div>

            <div class="doc-flow__disabled-note {{ $docFlowDisabled ? '' : 'hidden' }}" data-doc-flow-disabled-note>
              Bagian ini nonaktif karena sumber awal dokumen memakai PPTX pemohon.
            </div>

            <div class="doc-flow__preview">
              <div class="doc-flow__preview-label">Preview realtime</div>
              <div class="doc-flow__preview-value" data-doc-flow-preview aria-live="polite">Memuat preview…</div>
            </div>

            <div class="mt-5">
              <div class="text-sm font-semibold">Opsi alur tambahan (opsional)</div>
              <div class="text-xs text-muted mt-1">Centang sesuai kebutuhan. Opsi yang dipilih akan masuk ke preview alur dokumen.</div>
            </div>

            <div class="as-workflow__grid mt-4 doc-flow__options">
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_pemohon_signature]" value="1" @checked(!$docFlowDisabled && old('workflow_flags.require_pemohon_signature')) @disabled($docFlowDisabled) class="as-check__box" data-doc-flow-flag="pemohon">
                <span class="as-check__label">TTD Pemohon <span class="text-xs text-muted ml-2">(diisi pemohon saat permohonan)</span></span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_org_secretary_signature]" value="1" @checked(!$docFlowDisabled && old('workflow_flags.require_org_secretary_signature')) @disabled($docFlowDisabled) class="as-check__box" data-doc-flow-flag="org_secretary">
                <span class="as-check__label">TTD Sekretaris Organisasi <span class="text-xs text-muted ml-2">(sebelum verifikasi Admin Jurusan)</span></span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_org_chair_signature]" value="1" @checked(!$docFlowDisabled && old('workflow_flags.require_org_chair_signature')) @disabled($docFlowDisabled) class="as-check__box" data-doc-flow-flag="org_chair">
                <span class="as-check__label">TTD Ketua Organisasi <span class="text-xs text-muted ml-2">(sebelum verifikasi Admin Jurusan)</span></span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_other_lecturer_signature]" value="1" @checked(!$docFlowDisabled && old('workflow_flags.require_other_lecturer_signature')) @disabled($docFlowDisabled) class="as-check__box" data-doc-flow-flag="other_lecturer">
                <span class="as-check__label">TTD Dosen lainnya</span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_kaprodi_signature]" value="1" @checked(!$docFlowDisabled && old('workflow_flags.require_kaprodi_signature')) @disabled($docFlowDisabled) class="as-check__box" data-doc-flow-flag="kaprodi">
                <span class="as-check__label">TTD Ketua Prodi (Kaprodi)</span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_kajur_signature]" value="1" @checked(!$docFlowDisabled && old('workflow_flags.require_kajur_signature')) @disabled($docFlowDisabled) class="as-check__box" data-doc-flow-flag="kajur">
                <span class="as-check__label">TTD Ketua Jurusan (Kajur)</span>
              </label>
            </div>

            <div class="doc-flow__hint">
              Nomor surat diisi pada tahap verifikasi petugas gate awal sebelum proses signing.
            </div>
          </div>
        </div>

          <div class="as-form-actions">
            <x-button type="submit">Simpan</x-button>
            <x-button variant="ghost" href="{{ route('admin.layanan.index') }}">Batal</x-button>
          </div>
        </div>
      </x-card>

    </div>

    <x-card class="mt-6 as-note-card">
      <div class="admin-card-title">Catatan</div>
      <ul class="as-help">
        <li>Judul/Ringkasan EN terisi otomatis dari versi ID (bisa Anda ubah manual).</li>
        <li>Setup dokumen bisa langsung diisi di bawah sebelum simpan.</li>
        <li>Jika ragu, simpan dulu lalu lanjutkan setup di halaman edit.</li>
      </ul>
    </x-card>

    <div id="setup-dokumen" class="mt-8">
      <div class="page-admin-services-doc" data-services-doc-page>
        <div class="doc-embed-bar">
          <div class="doc-embed-bar__left">
            <div class="doc-embed-bar__title">Setup Layanan Dokumen</div>
            <div class="doc-embed-bar__sub">Template DOCX wajib diupload sebelum layanan dipublish.</div>
          </div>
        </div>

        <x-card class="doc-card">
          <div class="space-y-6">
            <div class="space-y-1">
              <div class="admin-card-title">Template Utama</div>
              <div class="admin-card-subtitle">
                Upload MAIN_DOCX. Placeholder format: <span class="doc-mono">@{{PLACEHOLDER_KEY}}</span>.
              </div>
            </div>

            <div class="as-form-grid">
              <x-input type="file" name="main_template" label="Template Utama (MAIN_DOCX) — .docx"
                accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document" />
              <label class="as-check">
                <input type="checkbox" name="extract_placeholders" value="1" class="as-check__box" @checked(old('extract_placeholders', true))>
                <span class="as-check__label">Ekstrak placeholder otomatis setelah simpan</span>
              </label>
            </div>

          @can('doc_services.manage')
            <details class="doc-details">
              <summary class="doc-details__summary">Gate (pemeriksaan awal)</summary>
              <div class="doc-details__body space-y-3">
                @php
                  $gateStepsInit = old('gate_steps_json') ? json_decode(old('gate_steps_json'), true) : ['VERIFY_INITIAL','INPUT_NOMOR_SURAT'];
                  if (!is_array($gateStepsInit)) $gateStepsInit = ['VERIFY_INITIAL','INPUT_NOMOR_SURAT'];
                  $gateRoleSelected = trim((string) old('gate_role', 'Admin Jurusan'));
                  $gateRoleSelected = match (strtoupper(str_replace(' ', '_', $gateRoleSelected))) {
                    'ADMIN_JURUSAN', 'ADMIN_JURUSAN_PER_PRODI', 'ADMIN_PRODI' => 'Admin Jurusan',
                    'STAF_ULT', 'STAFF_ULT' => 'Staf ULT',
                    default => in_array($gateRoleSelected, ['Admin Jurusan', 'Staf ULT'], true) ? $gateRoleSelected : 'Admin Jurusan',
                  };
                @endphp

                <div class="space-y-1">
                  <label class="text-sm font-medium" for="gate-role-create">Petugas gate awal</label>
                  <select id="gate-role-create" name="gate_role" class="as-input">
                    <option value="Admin Jurusan" @selected($gateRoleSelected === 'Admin Jurusan')>Admin Jurusan / Admin Prodi</option>
                    <option value="Staf ULT" @selected($gateRoleSelected === 'Staf ULT')>Staf ULT</option>
                  </select>
                  <div class="text-xs text-muted">Default: Admin Jurusan.</div>
                </div>

                <div x-data="gateStepsEditor({ initial: @js($gateStepsInit) })" class="space-y-2">
                  <input type="hidden" name="gate_steps_json" :value="json">

                  <div class="text-sm font-medium">Langkah gate</div>
                  <div class="text-xs text-muted">Wajib: verifikasi awal & input nomor surat (terkunci).</div>

                  <div class="flex flex-wrap gap-2">
                    <span class="doc-chip">VERIFY_INITIAL</span>
                    <span class="doc-chip">INPUT_NOMOR_SURAT</span>
                    <template x-for="s in steps.filter(x => !required.includes(x))" :key="s">
                      <button type="button" class="doc-chip doc-chip--muted" @click="toggle(s)" x-text="s"></button>
                    </template>
                  </div>

                  <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <input type="text" class="as-input" placeholder="Tambah step (opsional)..." x-model="custom">
                    <x-button type="button" variant="secondary" class="w-full sm:w-auto justify-center" @click="addCustom">Tambah</x-button>
                  </div>

                  <details class="doc-details">
                    <summary class="doc-details__summary">Advanced: lihat JSON</summary>
                    <div class="doc-details__body">
                      <pre class="doc-code" x-text="json"></pre>
                    </div>
                  </details>
                </div>
              </div>
            </details>
          @endcan

          @can('doc_signers.manage')
            <details class="doc-details">
              <summary class="doc-details__summary">Rantai penandatangan</summary>
              <div class="doc-details__body space-y-3">
                @php
                  $signersInit = old('signers_json') ? json_decode(old('signers_json'), true) : [];
                  if (!is_array($signersInit)) $signersInit = [];
                @endphp

                <div x-data="signersEditor({ initial: @js($signersInit), roleOptions: @js($signerRoleOptions ?? []) })" class="space-y-3">
                  <input type="hidden" name="signers_json" :value="json">

                  <div class="doc-hint">Urutan otomatis (1,2,3...). Kosongkan role untuk menghapus.</div>

                  <div class="space-y-3">
                    <template x-for="(s, idx) in signers" :key="idx">
                      <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-3 space-y-3">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                          <div class="text-sm font-semibold">Signer <span class="doc-mono" x-text="idx + 1"></span></div>
                          <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                            <button type="button" class="tiptap-btn flex-1 sm:flex-none justify-center" @click="moveUp(idx)" :disabled="idx===0">↑</button>
                            <button type="button" class="tiptap-btn flex-1 sm:flex-none justify-center" @click="moveDown(idx)" :disabled="idx===signers.length-1">↓</button>
                            <button type="button" class="tiptap-btn flex-1 sm:flex-none justify-center" @click="remove(idx)">Hapus</button>
                          </div>
                        </div>

                        <div class="as-form-grid">
                          <div class="space-y-1">
                            <label class="text-sm font-medium">Role</label>
                            <select class="as-input" x-model="s.role">
                              <option value="">-</option>
                              @forelse(($signerRoleOptions ?? []) as $opt)
                                <option value="{{ $opt['value'] }}">{{ $opt['label'] }}</option>
                              @empty
                                <option value="" disabled>Belum ada akun pimpinan (DEKAN/WD). Role scope KAJUR/SEKJUR/KAPRODI mengikuti data pemohon.</option>
                              @endforelse
                              <template x-if="s.role && Array.isArray(roleValues) && !roleValues.includes(s.role)">
                                <option :value="s.role" x-text="'(Tidak ada akun) ' + s.role"></option>
                              </template>
                            </select>
                          </div>
                          <div class="space-y-1">
                            <label class="text-sm font-medium">Wajib</label>
                            <label class="as-check">
                              <input type="checkbox" class="as-check__box" x-model="s.is_required">
                              <span class="as-check__label">Ya</span>
                            </label>
                          </div>
                        </div>

                        <div class="space-y-1" x-show="s.role === 'CUSTOM' || s.role === 'DOSEN' || s.role === 'PEMOHON'">
                          <label class="text-sm font-medium">Label penandatangan</label>
                          <input
                            type="text"
                            class="as-input"
                            name="signer_labels[]"
                            x-model="s.custom_label"
                            placeholder="Contoh: Pembimbing Akademik"
                          >
                          <div class="text-xs text-muted">Untuk CUSTOM label wajib. Untuk DOSEN dan PEMOHON label opsional untuk tampilan.</div>
                        </div>

                        <div class="space-y-2">
                          <label class="as-check">
                            <input type="checkbox" class="as-check__box" x-model="s.requires_signature_upload">
                            <span class="as-check__label">Wajib upload tanda tangan (image)</span>
                          </label>

                          <div x-show="s.requires_signature_upload" class="as-form-grid">
                            <div class="space-y-1">
                              <div class="text-sm font-medium">Tipe file</div>
                              <div class="flex flex-wrap gap-3">
                                <label class="as-check">
                                  <input type="checkbox" class="as-check__box" value="image/png" x-model="s.signature_file_types">
                                  <span class="as-check__label">PNG</span>
                                </label>
                                <label class="as-check">
                                  <input type="checkbox" class="as-check__box" value="image/jpeg" x-model="s.signature_file_types">
                                  <span class="as-check__label">JPG</span>
                                </label>
                                <label class="as-check">
                                  <input type="checkbox" class="as-check__box" value="image/webp" x-model="s.signature_file_types">
                                  <span class="as-check__label">WEBP</span>
                                </label>
                              </div>
                            </div>
                            <div class="space-y-1">
                              <label class="text-sm font-medium">Maks ukuran (KB)</label>
                              <input type="number" class="as-input" min="1" step="1" x-model="s.signature_max_size_kb">
                            </div>
                          </div>
                        </div>
                      </div>
                    </template>
                  </div>

                  <x-button type="button" variant="secondary" @click="add">Tambah signer</x-button>

                  <details class="doc-details">
                    <summary class="doc-details__summary">Advanced: lihat JSON</summary>
                    <div class="doc-details__body">
                      <pre class="doc-code" x-text="json"></pre>
                    </div>
                  </details>
                </div>
              </div>
          </details>
          @endcan

          {{--
          @can('doc_placeholders.manage')
            <details class="doc-details mt-3">
              <summary class="doc-details__summary">Placeholder Mapping</summary>
              <div class="doc-details__body space-y-3">
                @php
                  $mappingInit = old('placeholders_items_json') ? json_decode(old('placeholders_items_json'), true) : [];
                  if (!is_array($mappingInit)) $mappingInit = [];
                @endphp

                <div x-data="placeholderMappingEditor({ initial: @js($mappingInit) })" class="space-y-3">
                  <input type="hidden" name="placeholders_items_json" :value="json">

                  <div class="doc-hint">Isi jika Anda sudah tahu key placeholder dari template. Kalau belum, lewati dulu lalu mapping lewat tabel di halaman edit.</div>

                  <datalist id="service-create-source-ref-suggest">
                    <option value="nama"></option>
                    <option value="email"></option>
                    <option value="npm"></option>
                    <option value="nip"></option>
                    <option value="jurusan"></option>
                    <option value="prodi"></option>
                    <option value="fakultas"></option>
                    <option value="user.name"></option>
                    <option value="user.email"></option>
                    <option value="user.user_number"></option>
                    <option value="user.jabatan"></option>
                    <option value="unit.name"></option>
                    <option value="unit.parent.name"></option>
                    <option value="unit.parent.parent.name"></option>
                  </datalist>

                  <div class="space-y-3">
                    <template x-for="(it, idx) in items" :key="idx">
                      <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-3 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                          <div class="text-sm font-semibold">Item <span class="doc-mono" x-text="idx + 1"></span></div>
                          <button type="button" class="tiptap-btn" @click="remove(idx)">Hapus</button>
                        </div>

                        <div class="as-form-grid">
                          <div class="space-y-1">
                            <label class="text-sm font-medium">Placeholder key</label>
                            <input type="text" class="as-input" placeholder="contoh: NPM, NAMA_MHS" x-model="it.placeholder_key">
                          </div>
                          <div class="space-y-1">
                            <label class="text-sm font-medium">Sumber Data</label>
                            <select class="as-input" x-model="it.source_type">
                              <option value="FORM">Form Pemohon (input)</option>
                              <option value="PROFILE">Profil (akun/unit/signer)</option>
                              <option value="INTERNAL">Internal (diisi sistem)</option>
                              <option value="SYSTEM_AUTOFILL">Otomatis Sistem</option>
                            </select>
                          </div>
                        </div>

                        <div class="as-form-grid">
                          <div class="space-y-1">
                            <label class="text-sm font-medium">Acuan (opsional)</label>
                            <input type="text" class="as-input" placeholder="contoh: npm / nama / user.name" list="service-create-source-ref-suggest" x-model="it.source_ref">
                            <div class="text-xs text-muted">Form Pemohon: isi key field (mis. npm). Profil: bisa pakai shortcut (nama/email/jurusan/prodi/fakultas).</div>
                          </div>
                          <div class="space-y-1">
                            <label class="text-sm font-medium">Wajib</label>
                            <label class="as-check">
                              <input type="checkbox" class="as-check__box" x-model="it.is_required">
                              <span class="as-check__label">Ya</span>
                            </label>
                          </div>
                        </div>

                        <div class="space-y-1">
                          <label class="text-sm font-medium">Catatan (opsional)</label>
                          <input type="text" class="as-input" x-model="it.notes">
                        </div>
                      </div>
                    </template>
                  </div>

                  <x-button type="button" variant="secondary" @click="add">Tambah item</x-button>

                  <details class="doc-details">
                    <summary class="doc-details__summary">Advanced: lihat JSON</summary>
                    <div class="doc-details__body">
                      <pre class="doc-code" x-text="json"></pre>
                    </div>
                  </details>
                </div>
              </div>
            </details>
          @endcan

          @can('doc_services.manage')
            <details class="doc-details mt-3">
              <summary class="doc-details__summary">Form Builder</summary>
              <div class="doc-details__body space-y-3">
                @php
                  $fieldsInit = old('fields_json') ? json_decode(old('fields_json'), true) : [];
                  if (!is_array($fieldsInit)) $fieldsInit = [];
                @endphp

                <div x-data="serviceFieldsEditor({ initial: @js($fieldsInit) })" class="space-y-3">
                  <input type="hidden" name="fields_json" :value="json">

                  <div class="doc-hint">Tambah field lewat form. Field ini akan tampil di form pengajuan mahasiswa.</div>

                  <div class="space-y-3">
                    <template x-for="(f, idx) in fields" :key="idx">
                      <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-3 space-y-3">
                        <div class="flex items-center justify-between gap-2">
                          <div class="text-sm font-semibold">Field <span class="doc-mono" x-text="idx + 1"></span></div>
                          <div class="flex items-center gap-2">
                            <button type="button" class="tiptap-btn" @click="moveUp(idx)" :disabled="idx===0">↑</button>
                            <button type="button" class="tiptap-btn" @click="moveDown(idx)" :disabled="idx===fields.length-1">↓</button>
                            <button type="button" class="tiptap-btn" @click="remove(idx)">Hapus</button>
                          </div>
                        </div>

                        <div class="as-form-grid">
                          <div class="space-y-1">
                            <label class="text-sm font-medium">Kunci field</label>
                            <input type="text" class="as-input" placeholder="contoh: npm, nama_mhs" x-model="f.key">
                          </div>
                          <div class="space-y-1">
                            <label class="text-sm font-medium">Label (ID)</label>
                            <input type="text" class="as-input" placeholder="contoh: NPM" x-model="f.label_id">
                          </div>
                        </div>

                        <div class="as-form-grid">
                          <div class="space-y-1">
                            <label class="text-sm font-medium">Tipe input</label>
                            <select class="as-input" x-model="f.type">
                              <option value="text">text</option>
                              <option value="textarea">textarea</option>
                              <option value="number">number</option>
                              <option value="date">date</option>
                              <option value="select">select</option>
                              <option value="checkbox">checkbox</option>
                              <option value="json">json</option>
                              <option value="file">file</option>
                            </select>
                          </div>
                          <div class="space-y-1">
                            <label class="text-sm font-medium">Urutan</label>
                            <input type="number" class="as-input" step="1" x-model="f.sort_order">
                          </div>
                        </div>

                        <div class="as-form-grid">
                          <div class="space-y-1">
                            <label class="text-sm font-medium">Hubungkan ke placeholder (opsional)</label>
                            <input type="text" class="as-input" placeholder="contoh: NPM" x-model="f.maps_to_placeholder_key">
                          </div>
                          <div class="space-y-1">
                            <label class="text-sm font-medium">Wajib</label>
                            <label class="as-check">
                              <input type="checkbox" class="as-check__box" x-model="f.required">
                              <span class="as-check__label">Ya</span>
                            </label>
                          </div>
                        </div>

                        <div class="space-y-1">
                          <label class="text-sm font-medium">Aturan validasi (1 baris = 1 aturan)</label>
                          <textarea rows="3" class="as-input" x-model="f.rules_lines" placeholder="contoh: max:255&#10;regex:/^[0-9]+$/"></textarea>
                        </div>

                        <div x-show="f.type==='select'" class="space-y-1">
                          <label class="text-sm font-medium">Pilihan dropdown (1 baris = 1 opsi)</label>
                          <textarea rows="3" class="as-input" x-model="f.options_lines" placeholder="contoh: A&#10;B&#10;C"></textarea>
                        </div>
                      </div>
                    </template>
                  </div>

                  <x-button type="button" variant="secondary" @click="add">Tambah field</x-button>

                  <details class="doc-details">
                    <summary class="doc-details__summary">Advanced: lihat JSON</summary>
                    <div class="doc-details__body">
                      <pre class="doc-code" x-text="json"></pre>
                    </div>
                  </details>
                </div>
              </div>
            </details>
          @endcan
	          --}}

	          <div class="doc-hint">Setelah simpan, halaman edit akan terbuka di bagian setup dokumen untuk melengkapi mapping jika diperlukan.</div>
          </div>
	        </x-card>
      </div>
    </div>
  </form>
</div>
@endsection
