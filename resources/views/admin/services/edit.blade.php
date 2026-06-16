@extends('layouts.app')
@section('section','Layanan')
@section('content')
<div class="page-admin-services-edit" data-services-form data-translate-url="{{ route('admin.utils.translate') }}">
  <header class="admin-page-header">
    <div class="admin-page-heading">
      <div class="admin-page-kicker">Master layanan</div>
      <h1 class="admin-page-title">Edit: {{ $service->title_id }}</h1>
      <p class="admin-page-subtitle">Perbarui konten layanan. Setup dokumen (template/placeholder/signers) bersifat wajib untuk layanan dokumen.</p>
    </div>
    <div class="admin-page-actions">
      <x-button variant="secondary" href="{{ route('services.show',$service) }}">Preview publik</x-button>
      <x-button variant="secondary" href="#setup-dokumen">Setup Dokumen</x-button>
      <x-button variant="ghost" href="{{ route('admin.layanan.index') }}">Kembali</x-button>
    </div>
  </header>

  <div class="as-form-layout">
    <x-card class="as-form-card xl:col-span-2">
      <form class="as-form" method="POST" action="{{ route('admin.layanan.update',$service) }}">
        @csrf
        @method('PUT')

        @php
          $docErrorsFlat = \Illuminate\Support\Arr::flatten($readinessErrors ?? []);
          $docReady = empty($docErrorsFlat);
          $selectedDocumentSourceType = (string) old('document_source_type', $service->document_source_type?->value ?? \App\Enums\DocumentSourceType::MAIN_DOCX_TEMPLATE->value);
          $isCertificateMode = $selectedDocumentSourceType === \App\Enums\DocumentSourceType::REQUEST_PPTX->value;
          $docFlowDisabled = $isCertificateMode;
        @endphp
        <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-4">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="min-w-0">
              <div class="text-sm font-semibold">Status dokumen</div>
              @if($docReady)
                <div class="text-sm text-muted mt-1">Readiness: siap publish.</div>
              @else
                <div class="text-sm text-muted mt-1">
                  Readiness: belum siap ({{ count($docErrorsFlat) }} item).
                  {{ $isCertificateMode ? 'Cek konfigurasi gate/workflow mode sertifikat.' : 'Upload template dan lengkapi mapping.' }}
                </div>
              @endif
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
              <option value="{{ $cat->id }}" @selected((string) old('category_id', $service->category_id) === (string) $cat->id)>
                {{ $cat->name_id }}
              </option>
            @endforeach
          </x-select>

          <x-select name="document_source_type" label="Sumber Dokumen Awal" required>
            <option value="{{ \App\Enums\DocumentSourceType::MAIN_DOCX_TEMPLATE->value }}" @selected(old('document_source_type', $service->document_source_type?->value ?? \App\Enums\DocumentSourceType::MAIN_DOCX_TEMPLATE->value) === \App\Enums\DocumentSourceType::MAIN_DOCX_TEMPLATE->value)>
              DOCX admin
            </option>
            <option value="{{ \App\Enums\DocumentSourceType::REQUEST_PPTX->value }}" @selected(old('document_source_type', $service->document_source_type?->value) === \App\Enums\DocumentSourceType::REQUEST_PPTX->value)>
              PPTX pemohon
            </option>
          </x-select>
        </div>

        <div class="as-form-grid">
          <x-input name="title_id" label="Judul (ID)" value="{{ old('title_id',$service->title_id) }}" required />
          <x-input name="title_en" label="Title (EN)" value="{{ old('title_en',$service->title_en) }}" />
        </div>

        <div class="as-form-grid">
          <x-textarea name="summary_id" label="Ringkasan (ID)" rows="2">{{ old('summary_id',$service->summary_id) }}</x-textarea>
          <x-textarea name="summary_en" label="Summary (EN)" rows="2">{{ old('summary_en',$service->summary_en) }}</x-textarea>
        </div>

        <div class="as-activation">
          <div class="as-activation__meta">
            <div class="as-activation__title">Status layanan</div>
            <div class="as-activation__desc">Aktifkan jika layanan sudah siap ditampilkan untuk pemohon.</div>
          </div>

          <label class="as-activation__toggle">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active',$service->is_active)) class="as-activation__input">
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
          <input type="checkbox" name="allow_general_attachments" value="1" @checked(old('allow_general_attachments', $service->allow_general_attachments)) class="as-check__box">
          <span class="as-check__label">Tampilkan lampiran umum pada form pengajuan <span class="text-xs text-muted ml-2">(opsional, multi-file)</span></span>
        </label>

        <x-tiptap-editor
          name="requirements_html_id"
          label="Persyaratan"
          localeHint="ID"
          :value="old('requirements_html_id',$service->requirements_html_id)"
          placeholder="Tulis persyaratan (opsional). Gunakan list untuk poin."
          help="Opsional. Gunakan list (bullet/numbered) untuk poin."
        />
        <x-tiptap-editor
          name="sop_html_id"
          label="SOP"
          localeHint="ID"
          :value="old('sop_html_id',$service->sop_html_id)"
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
              <div class="text-xs text-muted mt-1">
                Default: Admin Jurusan &rarr; Review ULT &rarr; Penandatangan Fakultas (Dekan/WD). Petugas gate awal bisa diubah ke Staf ULT.
                @if($isCertificateMode)
                  Untuk mode Sertifikat/Piagam, sumber dokumen berasal dari upload .pptx pemohon.
                @endif
              </div>
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
                <input type="checkbox" name="workflow_flags[require_pemohon_signature]" value="1" @checked(!$docFlowDisabled && old('workflow_flags.require_pemohon_signature',$service->workflow?->require_pemohon_signature)) @disabled($docFlowDisabled) class="as-check__box" data-doc-flow-flag="pemohon">
                <span class="as-check__label">TTD Pemohon <span class="text-xs text-muted ml-2">(diisi pemohon saat permohonan)</span></span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_org_secretary_signature]" value="1" @checked(!$docFlowDisabled && old('workflow_flags.require_org_secretary_signature',$service->workflow?->require_org_secretary_signature)) @disabled($docFlowDisabled) class="as-check__box" data-doc-flow-flag="org_secretary">
                <span class="as-check__label">TTD Sekretaris Organisasi <span class="text-xs text-muted ml-2">(sebelum verifikasi Admin Jurusan)</span></span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_org_chair_signature]" value="1" @checked(!$docFlowDisabled && old('workflow_flags.require_org_chair_signature',$service->workflow?->require_org_chair_signature)) @disabled($docFlowDisabled) class="as-check__box" data-doc-flow-flag="org_chair">
                <span class="as-check__label">TTD Ketua Organisasi <span class="text-xs text-muted ml-2">(sebelum verifikasi Admin Jurusan)</span></span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_other_lecturer_signature]" value="1" @checked(!$docFlowDisabled && old('workflow_flags.require_other_lecturer_signature',$service->workflow?->require_other_lecturer_signature)) @disabled($docFlowDisabled) class="as-check__box" data-doc-flow-flag="other_lecturer">
                <span class="as-check__label">TTD Dosen lainnya</span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_kaprodi_signature]" value="1" @checked(!$docFlowDisabled && old('workflow_flags.require_kaprodi_signature',$service->workflow?->require_kaprodi_signature)) @disabled($docFlowDisabled) class="as-check__box" data-doc-flow-flag="kaprodi">
                <span class="as-check__label">TTD Ketua Prodi (Kaprodi)</span>
              </label>
              <label class="as-check">
                <input type="checkbox" name="workflow_flags[require_kajur_signature]" value="1" @checked(!$docFlowDisabled && old('workflow_flags.require_kajur_signature',$service->workflow?->require_kajur_signature)) @disabled($docFlowDisabled) class="as-check__box" data-doc-flow-flag="kajur">
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
      </form>
    </x-card>
  </div>

  <x-card class="mt-4 as-note-card">
    <div class="admin-card-title">Catatan</div>
    <ul class="as-help">
      <li>Preview publik membantu cek tampilan sebelum layanan diaktifkan.</li>
      <li>Opsi alur tambahan hanya mengubah urutan tahapan dokumen (preview & konfigurasi layanan).</li>
      <li>Setup dokumen ada di bawah halaman ini dan wajib untuk layanan dokumen.</li>
    </ul>
  </x-card>

  <div id="setup-dokumen" class="mt-6">
    <div class="page-admin-services-doc" data-services-doc-page>
      <div class="doc-embed-bar">
        <div class="doc-embed-bar__left">
          <div class="doc-embed-bar__title">Setup Layanan Dokumen</div>
          <div class="doc-embed-bar__sub">
            {{ $isCertificateMode ? 'Mode Sertifikat/Piagam: pemohon upload .pptx, output akhir tetap PDF.' : 'Template DOCX wajib diupload sebelum layanan dipublish.' }}
          </div>
        </div>
      </div>

      @can('doc_services.manage')
        @include('admin.services.documents._setup_grid', [
          'service' => $service,
          'readinessErrors' => $readinessErrors,
        ])
      @else
        <x-card class="doc-card">
          <div class="admin-card-title">Akses dibatasi</div>
          <div class="admin-card-subtitle">Anda tidak memiliki permission untuk mengelola setup dokumen layanan.</div>
        </x-card>
      @endcan
    </div>
  </div>
</div>
@endsection
