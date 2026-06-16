@php
  $mainTemplate = $service->templates->firstWhere('type', \App\Enums\ServiceTemplateType::MAIN_DOCX);
  $hasTemplate = (bool) $mainTemplate;
  $isCertificateMode = $service->usesRequestPptxSource();
  $placeholderCount = (int) $service->placeholders->count();
  $fieldCount = (int) $service->fields->count();
  $signerCount = (int) $service->signers->count();
  $hasGate = filled($service->workflow?->gate_role) && !empty($service->workflow?->gate_steps_json);
  $isReady = empty(\Illuminate\Support\Arr::flatten($readinessErrors));
  $gateSignerHref = $hasGate ? '#doc-signers' : '#doc-gate';

  $nextAction = [
    'title' => 'Cek Publish Readiness',
    'href' => '#doc-readiness',
    'variant' => 'secondary',
  ];
  if (!$hasTemplate) {
    $nextAction = ['title' => 'Upload Template Utama', 'href' => '#doc-template', 'variant' => 'primary'];
  } elseif ($placeholderCount === 0) {
    $nextAction = ['title' => 'Ekstrak Placeholder', 'href' => '#doc-template', 'variant' => 'secondary'];
  } elseif ($signerCount === 0) {
    $nextAction = ['title' => 'Isi Signer Chain', 'href' => '#doc-signers', 'variant' => 'secondary'];
  } elseif (!$isReady) {
    $nextAction = ['title' => 'Perbaiki Readiness', 'href' => '#doc-readiness', 'variant' => 'secondary'];
  }
@endphp

@if($isCertificateMode)
<div class="doc-setup-grid">
  <x-card class="doc-card">
    <div class="admin-card-title">Mode Sertifikat/Piagam (REQUEST_PPTX)</div>
    <div class="admin-card-subtitle mt-1">
      Layanan ini memakai dokumen sumber <span class="doc-mono">.pptx</span> dari pemohon. Setup template DOCX, mapping placeholder, dan signer chain layanan tidak dipakai.
    </div>
    <ul class="as-help mt-3">
      <li>Flow tetap sama: gate nomor surat, review ULT, signing multi-penandatangan, finalisasi PDF.</li>
      <li>Daftar signer diisi pemohon saat pengajuan/perbaikan.</li>
      <li>Validasi token wajib sertifikat dilakukan saat mulai signing oleh ULT.</li>
    </ul>
  </x-card>

  <aside class="doc-setup-side" aria-label="Pengaturan Sertifikat">
    <x-card class="doc-card doc-anchor" id="doc-gate">
      <div class="doc-card__head">
        <div class="doc-card__heading">
          <div class="admin-card-title">Gate Nomor Surat</div>
          <div class="admin-card-subtitle">Tetap wajib aktif untuk mode sertifikat/piagam.</div>
        </div>
      </div>
      <form method="POST" action="{{ route('admin.layanan.dokumen.gate',$service) }}" class="ars-form">
        @csrf
        @method('PUT')
        @php
          $gateStepsInit = old('gate_steps_json') ? json_decode(old('gate_steps_json'), true) : ($service->workflow?->gate_steps_json ?? ['VERIFY_INITIAL','INPUT_NOMOR_SURAT']);
          if (!is_array($gateStepsInit)) $gateStepsInit = ['VERIFY_INITIAL','INPUT_NOMOR_SURAT'];
          $gateRoleSelected = trim((string) old('gate_role', $service->workflow?->gate_role ?? 'Admin Jurusan'));
          $gateRoleSelected = match (strtoupper(str_replace(' ', '_', $gateRoleSelected))) {
            'ADMIN_JURUSAN', 'ADMIN_JURUSAN_PER_PRODI', 'ADMIN_PRODI' => 'Admin Jurusan',
            'STAF_ULT', 'STAFF_ULT' => 'Staf ULT',
            default => in_array($gateRoleSelected, ['Admin Jurusan', 'Staf ULT'], true) ? $gateRoleSelected : 'Admin Jurusan',
          };
        @endphp
        <div class="space-y-1">
          <label class="text-sm font-medium" for="gate-role-certificate">Petugas gate awal</label>
          <select id="gate-role-certificate" name="gate_role" class="as-input">
            <option value="Admin Jurusan" @selected($gateRoleSelected === 'Admin Jurusan')>Admin Jurusan/Prodi</option>
            <option value="Staf ULT" @selected($gateRoleSelected === 'Staf ULT')>Staf ULT</option>
          </select>
          <div class="text-xs text-muted">Default: Admin Jurusan.</div>
        </div>
        <input type="hidden" name="gate_steps_json" value="{{ json_encode($gateStepsInit) }}">
        <x-button type="submit" variant="secondary">Simpan Gate</x-button>
      </form>
    </x-card>

    <x-card class="doc-card doc-anchor" id="doc-readiness">
      <div class="doc-card__head">
        <div class="doc-card__heading">
          <div class="admin-card-title">Publish Readiness</div>
          <div class="admin-card-subtitle">Readiness sertifikat berfokus ke workflow gate.</div>
        </div>
      </div>
      @if(empty(\Illuminate\Support\Arr::flatten($readinessErrors)))
        <div class="doc-callout doc-callout--ok">
          <div class="doc-callout__label">Siap publish</div>
          <div class="doc-callout__value">Tidak ada error readiness.</div>
        </div>
        @can('doc_services.publish')
          <form method="POST" action="{{ route('admin.layanan.dokumen.publish',$service) }}" class="ars-form">
            @csrf
            <x-button type="submit">Publish</x-button>
          </form>
        @endcan
      @else
        <div class="doc-callout doc-callout--warn">
          <div class="doc-callout__label">Belum siap publish</div>
          <div class="doc-callout__value">Perbaiki item berikut.</div>
        </div>
        <ul class="doc-issues">
          @foreach($readinessErrors as $cat => $msgs)
            @foreach($msgs as $m)
              <li><span class="doc-issues__cat">[{{ $cat }}]</span> {{ $m }}</li>
            @endforeach
          @endforeach
        </ul>
      @endif
    </x-card>
  </aside>
</div>
@else
<div class="doc-setup-grid">
  <x-card class="doc-card doc-overview doc-anchor" id="doc-overview">
    <div class="doc-overview__row">
      <div class="doc-overview__main">
        <div class="admin-card-title">Ringkasan Setup</div>
        <div class="admin-card-subtitle">Checklist cepat agar layanan dokumen siap dipublish.</div>

        <div class="doc-steps" aria-label="Checklist setup dokumen">
          <a class="doc-step doc-step--link" href="#doc-template" aria-label="Buka setup Template Utama">
            <div class="doc-step__title">Template Utama</div>
            <div class="doc-step__meta">
              @if($hasTemplate)
                <x-badge variant="success">Terpasang</x-badge>
                <span class="doc-mono doc-step__mono" title="{{ $mainTemplate->original_filename }}">{{ $mainTemplate->original_filename }}</span>
              @else
                <x-badge variant="warning">Belum</x-badge>
                <span class="text-muted">Upload .docx</span>
              @endif
            </div>
          </a>

          <a class="doc-step doc-step--link" href="#doc-placeholders" aria-label="Buka setup Placeholder">
            <div class="doc-step__title">Placeholder</div>
            <div class="doc-step__meta">
              <x-badge variant="{{ $placeholderCount > 0 ? 'success' : 'warning' }}">{{ $placeholderCount }} key</x-badge>
              <span class="text-muted">mapping disimpan</span>
            </div>
          </a>

	          <a class="doc-step doc-step--link" href="#doc-form" aria-label="Buka setup Form Builder">
	            <div class="doc-step__title">Form Pemohon</div>
	            <div class="doc-step__meta">
	              <x-badge variant="{{ $fieldCount > 0 ? 'success' : 'default' }}">{{ $fieldCount }} field</x-badge>
	              <span class="text-muted">otomatis (placeholder FORM)</span>
	            </div>
	          </a>

          <a class="doc-step doc-step--link" href="{{ $gateSignerHref }}" aria-label="Buka setup Gate & Signer">
            <div class="doc-step__title">Gate & Signer</div>
            <div class="doc-step__meta">
              <x-badge variant="{{ $hasGate ? 'success' : 'warning' }}">{{ $hasGate ? 'Gate OK' : 'Gate cek' }}</x-badge>
              <x-badge variant="{{ $signerCount > 0 ? 'success' : 'warning' }}">{{ $signerCount }} signer</x-badge>
            </div>
          </a>

          <a class="doc-step doc-step--link" href="#doc-readiness" aria-label="Buka detail Publish Readiness">
            <div class="doc-step__title">Readiness</div>
            <div class="doc-step__meta">
              <x-badge variant="{{ $isReady ? 'success' : 'warning' }}">{{ $isReady ? 'Siap publish' : 'Ada isu' }}</x-badge>
              <span class="text-muted">lihat detail di bawah</span>
            </div>
          </a>
        </div>
      </div>

      <div class="doc-overview__side">
        <div class="doc-nav" aria-label="Navigasi cepat">
          <a href="#doc-template" class="doc-nav__link">Template</a>
          <a href="#doc-placeholders" class="doc-nav__link">Placeholder</a>
          <a href="#doc-form" class="doc-nav__link">Form</a>
          <a href="#doc-gate" class="doc-nav__link">Gate</a>
          <a href="#doc-signers" class="doc-nav__link">Signer</a>
          <a href="#doc-readiness" class="doc-nav__link">Readiness</a>
        </div>

        <div class="doc-overview__cta">
          <div class="doc-hint">Rekomendasi langkah berikutnya</div>
          <x-button href="{{ $nextAction['href'] }}" variant="{{ $nextAction['variant'] }}">{{ $nextAction['title'] }}</x-button>
        </div>
      </div>
    </div>
  </x-card>

  <div class="doc-setup-main">
    <x-card class="doc-card doc-anchor" id="doc-template">
      <div class="doc-card__head">
        <div class="doc-card__heading">
          <div class="admin-card-title">Template Utama</div>
          <div class="admin-card-subtitle">
            File disimpan di private storage. Format placeholder wajib <span class="doc-mono">@{{PLACEHOLDER_KEY}}</span>.
          </div>
        </div>
        <div class="doc-card__chips">
          <span class="doc-chip">MAIN_DOCX</span>
        </div>
      </div>

      @if($hasTemplate)
        <div class="doc-callout">
          <div class="doc-callout__label">Template saat ini</div>
          <div class="doc-callout__value">
            <div class="doc-template-current">
              <span class="doc-mono">{{ $mainTemplate->original_filename }}</span>
              @if(filled($mainTemplate?->created_at))
                <span class="doc-template-current__meta">diunggah {{ optional($mainTemplate->created_at)->format('d M Y H:i') }}</span>
              @endif
            </div>
          </div>
        </div>
      @else
        <div class="doc-callout doc-callout--warn">
          <div class="doc-callout__label">Template belum diupload</div>
          <div class="doc-callout__value">Upload file <span class="doc-mono">.docx</span> terlebih dahulu.</div>
        </div>
      @endif

      @can('doc_templates.upload')
        <form method="POST" action="{{ route('admin.layanan.dokumen.template',$service) }}" enctype="multipart/form-data" class="ars-form">
          @csrf
          <div class="space-y-1">
            <label class="text-sm font-medium" for="doc-template-upload">Upload .docx</label>
            <div class="doc-form-row doc-form-row--upload">
              <div class="doc-form-grow">
                <div class="ui-file-field @error('file') is-error @enderror" data-file-field data-file-empty-label="Belum ada file dipilih">
                  <input
                    id="doc-template-upload"
                    name="file"
                    type="file"
                    accept=".docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                    required
                    class="sr-only"
                    data-file-input
                  />
                  <button type="button" class="ui-file-field__button" data-file-trigger>Pilih file</button>
                  <div class="ui-file-field__name" data-file-name aria-live="polite">Belum ada file dipilih</div>
                </div>
              </div>
              <div class="doc-form-actions doc-form-actions--upload">
                <x-button type="submit" class="doc-upload-btn">Upload Template</x-button>
              </div>
            </div>
            <p class="text-xs text-muted">Maksimal 10MB. Setelah upload, klik Ekstrak Placeholder agar tabel mapping terisi.</p>
            @error('file')
              <p class="text-sm text-[rgb(var(--c-danger))]">{{ $message }}</p>
            @enderror
          </div>
          @can('doc_placeholders.manage')
            <label class="as-check">
              <input type="checkbox" name="extract_placeholders" value="1" class="as-check__box" checked>
              <span class="as-check__label">Ekstrak placeholder otomatis setelah upload</span>
            </label>
          @endcan
        </form>
      @else
        <div class="doc-callout doc-callout--warn">
          <div class="doc-callout__label">Akses dibatasi</div>
          <div class="doc-callout__value">Anda tidak memiliki permission untuk upload template.</div>
        </div>
      @endcan

      <div class="doc-divider"></div>

      @can('doc_placeholders.manage')
        <form method="POST" action="{{ route('admin.layanan.dokumen.extract',$service) }}" class="ars-form">
          @csrf
          <div class="doc-actions-row">
            <div class="doc-actions-row__hint">
              Ekstrak ulang placeholder setelah mengganti template.
              <span class="doc-hint">Aksi ini akan menambah placeholder baru dan mempertahankan mapping yang sudah ada.</span>
            </div>
            <x-button type="submit" variant="secondary" :disabled="!$hasTemplate">Ekstrak Placeholder</x-button>
          </div>
        </form>
      @else
        <div class="doc-actions-row">
          <div class="doc-actions-row__hint">Ekstraksi placeholder membutuhkan permission <span class="doc-mono">doc_placeholders.manage</span>.</div>
        </div>
      @endcan
    </x-card>

    <x-card class="doc-card doc-anchor" id="doc-placeholders">
      <div class="doc-card__head">
        <div class="doc-card__heading">
          <div class="admin-card-title">Placeholder & Mapping</div>
          <div class="admin-card-subtitle">
            <span class="doc-chip doc-chip--locked">NOMOR_SURAT</span> dan <span class="doc-chip doc-chip--locked">TANGGAL_SURAT</span> terkunci.
          </div>
        </div>
      </div>

      @if($service->placeholders->isEmpty())
        <div class="doc-callout doc-callout--warn">
          <div class="doc-callout__label">Belum ada placeholder</div>
          <div class="doc-callout__value">Upload template lalu klik <span class="font-semibold">Ekstrak Placeholder</span>.</div>
        </div>
      @else
        @php
          $canEditPlaceholders = auth()->user()?->can('doc_placeholders.manage') ?? false;
        @endphp
        <form method="POST" action="{{ route('admin.layanan.dokumen.placeholders',$service) }}" class="ars-form">
          @csrf
          @method('PUT')

          @php
            $sourceRefSignerRoles = $service->signers->pluck('role')->filter()->unique()->values()->all();
            $formFields = $service->fields->sortBy('sort_order')->values();
            $formFieldKeys = $formFields->pluck('key')->filter()->unique()->values()->all();
          @endphp

          <div class="doc-table">
            <div class="doc-table__scroll">
              <table class="doc-table__table">
                <thead>
                <tr class="text-left">
                  <th>Placeholder</th>
                  <th>Sumber Data</th>
                  <th>Acuan</th>
                  <th>Wajib</th>
                  <th>Catatan</th>
                </tr>
                </thead>
                <tbody>
                  @foreach($service->placeholders->sortBy('placeholder_key') as $i => $ph)
                   @php
                     $locked = in_array($ph->placeholder_key, ['NOMOR_SURAT','TANGGAL_SURAT'], true);
                     $currentSourceType = (string) ($ph->source_type?->value ?? '');
                     $mappedFormField = $formFields->firstWhere('maps_to_placeholder_key', $ph->placeholder_key);
                     $autoFormKey = $mappedFormField?->key
                       ? (string) $mappedFormField->key
                       : (string) \Illuminate\Support\Str::of($ph->placeholder_key)->lower()->replace('-', '_');
                     $autoFormLabel = $mappedFormField && trim((string) ($mappedFormField->label_id ?? '')) !== ''
                       ? (string) $mappedFormField->label_id
                       : (string) \Illuminate\Support\Str::of($ph->placeholder_key)->replace('_', ' ')->lower()->title();

                     $savedRef = (string) old("items.$i.source_ref", $ph->source_ref);
                     $currentRef = ($currentSourceType === 'FORM' && trim($savedRef) === '') ? $autoFormKey : $savedRef;
                    $profileShortcuts = [
                      'nama' => 'Nama pemohon',
                      'email' => 'Email pemohon',
                      'npm' => 'Nomor Induk pemohon (NIP/NPM)',
                      'nip' => 'NIP pemohon',
                      'jurusan' => 'Jurusan pemohon',
                      'prodi' => 'Program studi pemohon',
                      'fakultas' => 'Fakultas pemohon',
                    ];
                    $profileRefs = [
                      'user.name' => 'Nama pemohon',
                      'user.email' => 'Email pemohon',
                      'user.user_number' => 'Nomor Induk pemohon (NIP/NPM)',
                      'user.jabatan' => 'Jabatan pemohon',
                      'unit.prodi.name' => 'Program studi pemohon',
                      'unit.jurusan.name' => 'Jurusan pemohon',
                      'unit.fakultas.name' => 'Fakultas pemohon',
                      'unit.name' => 'Program studi pemohon (legacy)',
                      'unit.parent.name' => 'Jurusan pemohon (legacy)',
                      'unit.parent.parent.name' => 'Fakultas pemohon (legacy)',
                    ];

                    $knownProfileValues = array_merge(array_keys($profileShortcuts), array_keys($profileRefs));
                    foreach ($sourceRefSignerRoles as $r) {
                      $knownProfileValues[] = "signer.$r.name";
                      $knownProfileValues[] = "signer.$r.user_number";
                      $knownProfileValues[] = "signer.$r.student_number";
                      $knownProfileValues[] = "signer.$r.jabatan";
                      $knownProfileValues[] = "signer.$r.unit.name";
                      $knownProfileValues[] = "signer.$r.unit.parent.name";
                      $knownProfileValues[] = "signer.$r.unit.parent.parent.name";
                      $knownProfileValues[] = "signer.$r.unit.prodi.name";
                      $knownProfileValues[] = "signer.$r.unit.jurusan.name";
                      $knownProfileValues[] = "signer.$r.unit.fakultas.name";
                    }
                    $knownFormValues = $formFieldKeys;
                    $isKnown = false;
                    if ($currentSourceType === 'FORM') $isKnown = in_array($currentRef, $knownFormValues, true) || $currentRef === '';
                    if ($currentSourceType === 'PROFILE') $isKnown = in_array($currentRef, $knownProfileValues, true) || $currentRef === '';
                    if (!in_array($currentSourceType, ['FORM','PROFILE'], true)) $isKnown = true;
                  @endphp
                   <tr data-ph-row data-ph-form-key="{{ $autoFormKey }}">
                    <td>
                      <input type="hidden" name="items[{{ $i }}][placeholder_key]" value="{{ $ph->placeholder_key }}">
                      <div class="doc-keycell">
                        <span class="doc-mono">{{ $ph->placeholder_key }}</span>
                        <button
                          type="button"
                          class="doc-iconbtn"
                          data-copy-text="{{ $ph->placeholder_key }}"
                          aria-label="Copy placeholder key {{ $ph->placeholder_key }}"
                          title="Copy"
                        >
                          <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M9 9h10v10H9V9Z" stroke="currentColor" stroke-width="2" />
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="2" />
                          </svg>
                        </button>
                        @if($locked)
                          <span class="doc-chip doc-chip--locked">locked</span>
                        @endif
                      </div>
                    </td>
                    <td>
                      @if($locked)
                        <input
                          type="hidden"
                          name="items[{{ $i }}][source_type]"
                          value="{{ $ph->placeholder_key === 'NOMOR_SURAT' ? 'INTERNAL' : 'SYSTEM_AUTOFILL' }}"
                        >
                      @endif
                      <select name="items[{{ $i }}][source_type]" class="as-input" data-ph-source-type @disabled($locked || !$canEditPlaceholders)>
                        <option value="FORM" @selected(($ph->source_type?->value ?? '') === 'FORM')>Form Pemohon (input)</option>
                        <option value="PROFILE" @selected(($ph->source_type?->value ?? '') === 'PROFILE')>Profil (akun/unit/signer)</option>
                        <option value="INTERNAL" @selected(($ph->source_type?->value ?? '') === 'INTERNAL')>Internal (diisi sistem)</option>
                        <option value="SYSTEM_AUTOFILL" @selected(($ph->source_type?->value ?? '') === 'SYSTEM_AUTOFILL')>Otomatis Sistem</option>
                      </select>
                    </td>
                    <td>
                      <input
                        type="hidden"
                        name="items[{{ $i }}][source_ref]"
                        value="{{ $currentRef }}"
                        data-ph-source-ref-hidden
                      >

                      <select class="as-input hidden" data-ph-source-ref-picker-old @disabled($locked || !$canEditPlaceholders)>
                        <option value="">(kosong)</option> 

                        <optgroup label="Form Pemohon (pilih field input)">
                          @foreach($formFields as $f)
                            @php
                              $optKey = (string) ($f->key ?? '');
                              $optLabel = trim((string) ($f->label_id ?? ''));
                              $optText = $optLabel !== '' ? "{$optLabel} — {$optKey}" : $optKey;
                            @endphp
                            @if($optKey !== '')
                              <option value="{{ $optKey }}" @selected($currentSourceType === 'FORM' && $currentRef === $optKey)>{{ $optText }}</option>
                            @endif
                          @endforeach
                        </optgroup>

                        <optgroup label="Profil Pemohon (pilih cepat)">
                          @foreach($profileShortcuts as $val => $label)
                            <option value="{{ $val }}" @selected($currentSourceType === 'PROFILE' && $currentRef === $val)>{{ $label }} — {{ $val }}</option>
                          @endforeach
                        </optgroup>

                        <optgroup label="Profil Pemohon (detail)">
                          @foreach($profileRefs as $val => $label)
                            <option value="{{ $val }}" @selected($currentSourceType === 'PROFILE' && $currentRef === $val)>{{ $label }} — {{ $val }}</option>
                          @endforeach
                          @foreach($sourceRefSignerRoles as $r)
                            <option value="signer.{{ $r }}.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.name")>Penandatangan {{ $r }}: Nama - signer.{{ $r }}.name</option>
                            <option value="signer.{{ $r }}.user_number" @selected($currentSourceType === 'PROFILE' && ($currentRef === "signer.$r.user_number" || $currentRef === "signer.$r.student_number"))>Penandatangan {{ $r }}: Nomor Induk (NIP/NPM) - signer.{{ $r }}.user_number</option>
                            <option value="signer.{{ $r }}.unit.prodi.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.unit.prodi.name")>Penandatangan {{ $r }}: Program Studi - signer.{{ $r }}.unit.prodi.name</option>
                            <option value="signer.{{ $r }}.unit.jurusan.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.unit.jurusan.name")>Penandatangan {{ $r }}: Jurusan - signer.{{ $r }}.unit.jurusan.name</option>
                            <option value="signer.{{ $r }}.unit.fakultas.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.unit.fakultas.name")>Penandatangan {{ $r }}: Fakultas - signer.{{ $r }}.unit.fakultas.name</option>
                            <option value="signer.{{ $r }}.unit.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.unit.name")>Penandatangan {{ $r }}: Unit (legacy) - signer.{{ $r }}.unit.name</option>
                            <option value="signer.{{ $r }}.unit.parent.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.unit.parent.name")>Penandatangan {{ $r }}: Jurusan (legacy) - signer.{{ $r }}.unit.parent.name</option>
                            <option value="signer.{{ $r }}.unit.parent.parent.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.unit.parent.parent.name")>Penandatangan {{ $r }}: Fakultas (legacy) - signer.{{ $r }}.unit.parent.parent.name</option>
                          @endforeach
                        </optgroup>

                        <option value="__custom__" @selected(!$isKnown)>Isi manual…</option>
                      </select>

                      <input
                        type="text"
                        class="as-input hidden mt-2 {{ !$isKnown ? '' : 'hidden' }}"
                        value="{{ $currentRef }}"
                        placeholder="Isi manual (contoh: signer.DEKAN.name / user.user_number)"
                        data-ph-source-ref-custom
                        @disabled($locked || !$canEditPlaceholders)
                      >

                      @php
                        $isFormCustom = $currentSourceType === 'FORM' && $currentRef !== '' && !in_array($currentRef, $knownFormValues, true);
                        $isProfileCustom = $currentSourceType === 'PROFILE' && $currentRef !== '' && !in_array($currentRef, $knownProfileValues, true);
                      @endphp
                      <div class="space-y-2" data-ph-source-ref-ui>
                        <div class="{{ $currentSourceType === 'FORM' ? '' : 'hidden' }}" data-ph-source-ref-form-wrap>
                          <div class="rounded-2xl border border-[rgb(var(--c-border))] bg-[rgb(var(--c-card))] p-3">
                            <div class="text-xs font-semibold text-muted">Otomatis dari Form Pemohon</div>
                            <div class="mt-1 text-sm">
                              <span class="font-semibold">{{ $autoFormLabel }}</span>
                              <span class="text-muted">—</span>
                              <span class="doc-mono">{{ $autoFormKey }}</span>
                            </div>
                            <div class="mt-1 text-xs text-muted">Pemohon akan mengisi field ini saat mengajukan layanan.</div>
                            <div class="mt-2 text-xs">
	                      <a href="#doc-form" class="text-[rgb(var(--c-primary))] font-semibold hover:underline">Pengaturan lanjutan</a>
	                    </div>
	                  </div>

                          <select class="as-input hidden" data-ph-source-ref-form @disabled($locked || !$canEditPlaceholders)>
                            <option value="" @selected($currentSourceType === 'FORM' && $currentRef === '')>(kosong)</option>
                            @foreach($formFields as $f)
                              @php
                                $optKey = (string) ($f->key ?? '');
                                $optLabel = trim((string) ($f->label_id ?? ''));
                                $optText = $optLabel !== '' ? "{$optLabel} - {$optKey}" : $optKey;
                              @endphp
                              @if($optKey !== '')
                                <option value="{{ $optKey }}" @selected($currentSourceType === 'FORM' && $currentRef === $optKey)>{{ $optText }}</option>
                              @endif
                            @endforeach
                            <option value="__custom__" @selected($isFormCustom)>Isi manual&hellip;</option>
                          </select>
                          <input
                            type="text"
                            class="as-input mt-2 {{ $isFormCustom ? '' : 'hidden' }}"
                            value="{{ $currentRef }}"
                        placeholder="Isi manual (contoh: FIELD_KEY)"
                        data-ph-source-ref-form-custom
                        @disabled($locked || !$canEditPlaceholders)
                      >
                        </div>

                        <div class="{{ $currentSourceType === 'PROFILE' ? '' : 'hidden' }}" data-ph-source-ref-profile-wrap>
                          <select class="as-input" data-ph-source-ref-profile @disabled($locked || !$canEditPlaceholders)>
                            <option value="" @selected($currentSourceType === 'PROFILE' && $currentRef === '')>(kosong)</option>
                            <optgroup label="Profil Pemohon (pilih cepat)">
                              @foreach($profileShortcuts as $val => $label)
                                <option value="{{ $val }}" @selected($currentSourceType === 'PROFILE' && $currentRef === $val)>{{ $label }} - {{ $val }}</option>
                              @endforeach
                            </optgroup>
                            <optgroup label="Profil Pemohon (detail)">
                              @foreach($profileRefs as $val => $label)
                                <option value="{{ $val }}" @selected($currentSourceType === 'PROFILE' && $currentRef === $val)>{{ $label }} - {{ $val }}</option>
                              @endforeach
                            </optgroup>
                            @if(!empty($sourceRefSignerRoles))
                              <optgroup label="Profil Penandatangan (signer)">
                                @foreach($sourceRefSignerRoles as $r)
                                  <option value="signer.{{ $r }}.jabatan" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.jabatan")>Penandatangan {{ $r }}: Jabatan - signer.{{ $r }}.jabatan</option>
                                  <option value="signer.{{ $r }}.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.name")>Penandatangan {{ $r }}: Nama - signer.{{ $r }}.name</option>
                                  <option value="signer.{{ $r }}.user_number" @selected($currentSourceType === 'PROFILE' && ($currentRef === "signer.$r.user_number" || $currentRef === "signer.$r.student_number"))>Penandatangan {{ $r }}: Nomor Induk (NIP/NPM) - signer.{{ $r }}.user_number</option>
                                  <option value="signer.{{ $r }}.unit.prodi.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.unit.prodi.name")>Penandatangan {{ $r }}: Program Studi - signer.{{ $r }}.unit.prodi.name</option>
                                  <option value="signer.{{ $r }}.unit.jurusan.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.unit.jurusan.name")>Penandatangan {{ $r }}: Jurusan - signer.{{ $r }}.unit.jurusan.name</option>
                                  <option value="signer.{{ $r }}.unit.fakultas.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.unit.fakultas.name")>Penandatangan {{ $r }}: Fakultas - signer.{{ $r }}.unit.fakultas.name</option>
                                  <option value="signer.{{ $r }}.unit.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.unit.name")>Penandatangan {{ $r }}: Unit (legacy) - signer.{{ $r }}.unit.name</option>
                                  <option value="signer.{{ $r }}.unit.parent.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.unit.parent.name")>Penandatangan {{ $r }}: Jurusan (legacy) - signer.{{ $r }}.unit.parent.name</option>
                                  <option value="signer.{{ $r }}.unit.parent.parent.name" @selected($currentSourceType === 'PROFILE' && $currentRef === "signer.$r.unit.parent.parent.name")>Penandatangan {{ $r }}: Fakultas (legacy) - signer.{{ $r }}.unit.parent.parent.name</option>
                                @endforeach
                              </optgroup>
                            @endif
                            <option value="__custom__" @selected($isProfileCustom)>Isi manual&hellip;</option>
                          </select>
                          <input
                            type="text"
                            class="as-input mt-2 {{ $isProfileCustom ? '' : 'hidden' }}"
                            value="{{ $currentRef }}"
                            placeholder="Isi manual (contoh: signer.DEKAN.name / user.user_number)"
                            data-ph-source-ref-profile-custom
                            @disabled($locked || !$canEditPlaceholders)
                          >
                        </div>
                      </div>
                      <div class="doc-hint">
                        <span class="font-semibold">Form Pemohon:</span> ambil dari isian pemohon.
                        <span class="font-semibold ml-2">Profil:</span> ambil dari profil akun/unit/penandatangan.
                        <span class="font-semibold ml-2">Shortcut:</span> <span class="doc-mono">nama</span>/<span class="doc-mono">email</span>/<span class="doc-mono">npm</span>/<span class="doc-mono">nip</span>/<span class="doc-mono">jurusan</span>/<span class="doc-mono">prodi</span>/<span class="doc-mono">fakultas</span>.
                      </div>
                    </td>
                    <td>
                      <label class="as-check">
                        <input type="checkbox" name="items[{{ $i }}][is_required]" value="1" class="as-check__box" @checked($ph->is_required) @disabled($locked || !$canEditPlaceholders)>
                        <span class="as-check__label">req</span>
                      </label>
                    </td>
                    <td>
                      <input name="items[{{ $i }}][notes]" class="as-input" value="{{ old("items.$i.notes",$ph->notes) }}" placeholder="catatan opsional" @disabled(!$canEditPlaceholders)>
                    </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <div class="doc-card__footer">
            <div class="doc-hint">Tip: hindari spasi pada placeholder key, gunakan <span class="doc-mono">A_Z0_9</span>.</div>
            @if($canEditPlaceholders)
              <x-button type="submit" variant="secondary">Simpan Mapping</x-button>
            @else
              <div class="doc-hint">Mode lihat saja: butuh permission <span class="doc-mono">doc_placeholders.manage</span> untuk menyimpan mapping.</div>
            @endif
          </div>
        </form>
      @endif
    </x-card>

	    <x-card class="doc-card doc-anchor" id="doc-form">
	      <div class="doc-card__head">
	        <div class="doc-card__heading">
	          <div class="admin-card-title">Form Pemohon</div>
	          <div class="admin-card-subtitle">
	            Untuk placeholder <span class="doc-chip">FORM</span>, field form pemohon akan dibuat otomatis saat mapping disimpan.
	            Form Builder ini hanya untuk pengaturan lanjutan (label/tipe/validasi/opsi). Placeholder yang mengandung <span class="doc-mono">PASPHOTO/PAS_FOTO/FOTO/PHOTO</span> otomatis disiapkan sebagai upload gambar.
	          </div>
	        </div>
	      </div>

	      <details class="doc-details" data-doc-form-details>
	        <summary class="doc-details__summary">Pengaturan Form (lanjutan)</summary>
	        <div class="doc-details__body">
          <form method="POST" action="{{ route('admin.layanan.dokumen.fields.create',$service) }}" class="ars-form" data-doc-form-builder x-data="{
            rulesLines: '',
            optionsLines: '',
            get rulesJson() {
              return JSON.stringify(this.rulesLines.split(/\\r?\\n/g).map(s => s.trim()).filter(Boolean));
            },
            get optionsJson() {
              return JSON.stringify(this.optionsLines.split(/\\r?\\n/g).map(s => s.trim()).filter(Boolean));
            }
	          }">
	            @csrf
	            <div class="as-form-grid">
	              <x-input name="key" label="Kunci field (huruf/angka/_)" help="Contoh: nama_mhs, npm, semester" required />
	              <x-input name="label_id" label="Label (ID)" help="Teks yang tampil di form pemohon" required />
	            </div>
            <div class="as-form-grid">
              <x-select name="type" label="Tipe input">
                @foreach(['text','textarea','richtext','number','date','select','checkbox','json','file'] as $t)
                  <option value="{{ $t }}">{{ $t === 'richtext' ? 'richtext (WYSIWYG)' : $t }}</option>
                @endforeach
              </x-select>
              <x-input name="maps_to_placeholder_key" label="Hubungkan ke placeholder (opsional)" help="Agar nilai field ini mengisi placeholder FORM pada DOCX." />
            </div>
            <div class="as-form-grid">
              <x-input name="sort_order" type="number" label="Urutan" value="0" help="Semakin kecil, semakin atas." />
              <label class="as-check">
                <input type="checkbox" name="required" value="1" class="as-check__box">
                <span class="as-check__label">Wajib diisi</span>
              </label>
            </div>
            <input type="hidden" name="rules_json" :value="rulesJson">
            <div class="space-y-1">
              <label class="text-sm font-medium">Aturan validasi (1 baris = 1 aturan)</label>
              <textarea rows="3" class="as-input" x-model="rulesLines"></textarea>
            </div>
            <div class="doc-hint">Contoh: <span class="doc-mono">max:255</span> atau <span class="doc-mono">regex:/^[0-9]+$/</span>.</div>

            <input type="hidden" name="options_json" :value="optionsJson">
            <div class="space-y-1" data-doc-fb-options>
              <label class="text-sm font-medium">Pilihan dropdown (1 baris = 1 opsi)</label>
              <textarea rows="3" class="as-input" x-model="optionsLines"></textarea>
            </div>
            <div class="doc-hint">Hanya dipakai untuk type <span class="doc-mono">select</span>.</div>
            <div class="doc-card__footer">
              <div class="doc-hint">Jika sudah pakai mapping FORM otomatis, kamu biasanya tidak perlu menambah field manual di sini.</div>
              <x-button type="submit" variant="secondary">Tambah Field</x-button>
            </div>
          </form>

          <div class="doc-divider"></div>

          <div class="doc-section">
            <div class="admin-card-subtitle">Daftar field</div>
            <div class="doc-table">
              <div class="doc-table__scroll">
                <table class="doc-table__table">
                  <thead>
                  <tr class="text-left">
                    <th>Key</th>
                    <th>Label</th>
                    <th>Type</th>
                    <th>Required</th>
                    <th>Maps</th>
                    <th>Order</th>
                    <th></th>
                  </tr>
                  </thead>
                  <tbody>
                  @foreach($service->fields as $f)
                    <tr>
                      <td>
                        <div class="doc-keycell">
                          <span class="doc-mono">{{ $f->key }}</span>
                          <button
                            type="button"
                            class="doc-iconbtn"
                            data-copy-text="{{ $f->key }}"
                            aria-label="Copy field key {{ $f->key }}"
                            title="Copy"
                          >
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                              <path d="M9 9h10v10H9V9Z" stroke="currentColor" stroke-width="2" />
                              <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1" stroke="currentColor" stroke-width="2" />
                            </svg>
                          </button>
                        </div>
                      </td>
                      <td>{{ $f->label_id }}</td>
                      <td>{{ $f->type }}</td>
                      <td>{{ $f->required ? 'yes' : 'no' }}</td>
                      <td class="doc-mono">{{ $f->maps_to_placeholder_key ?? '-' }}</td>
                      <td>{{ $f->sort_order }}</td>
                      <td class="doc-actions-cell">
                        <form method="POST" action="{{ route('admin.layanan.dokumen.fields.update',[$service,$f]) }}" class="doc-inline-update">
                          @csrf
                          @method('PATCH')
                          <input name="label_id" class="as-input doc-inline-update__label" value="{{ $f->label_id }}">
                          <select name="type" class="as-input doc-inline-update__type">
                            @foreach(['text','textarea','richtext','number','date','select','checkbox','json','file'] as $t)
                              <option value="{{ $t }}" @selected($f->type === $t)>{{ $t === 'richtext' ? 'richtext (WYSIWYG)' : $t }}</option>
                            @endforeach
                          </select>
                          <input name="maps_to_placeholder_key" class="as-input font-mono doc-inline-update__maps" value="{{ $f->maps_to_placeholder_key }}" placeholder="PLACEHOLDER">
                          <input name="sort_order" type="number" class="as-input doc-inline-update__order" value="{{ $f->sort_order }}">
                          <label class="as-check doc-inline-update__req">
                            <input type="checkbox" name="required" value="1" class="as-check__box" @checked($f->required)>
                            <span class="as-check__label">req</span>
                          </label>
                          <input type="hidden" name="rules_json" value="{{ json_encode($f->rules_json ?? []) }}">
                          <input type="hidden" name="options_json" value="{{ json_encode($f->options_json ?? []) }}">
                          <x-button type="submit" variant="ghost">Update</x-button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </details>
    </x-card>
  </div>

  <aside class="doc-setup-side" aria-label="Pengaturan lanjutan">
    <x-card class="doc-card doc-anchor" id="doc-gate">
      <div class="doc-card__head">
        <div class="doc-card__heading">
          <div class="admin-card-title">Gate Nomor Surat</div>
          <div class="admin-card-subtitle">Gate tidak bisa dimatikan. Pastikan steps berisi <span class="doc-mono">VERIFY_INITIAL</span> dan <span class="doc-mono">INPUT_NOMOR_SURAT</span>.</div>
        </div>
      </div>
      <form method="POST" action="{{ route('admin.layanan.dokumen.gate',$service) }}" class="ars-form">
        @csrf
        @method('PUT')
        @php
          $gateStepsInit = old('gate_steps_json') ? json_decode(old('gate_steps_json'), true) : ($service->workflow?->gate_steps_json ?? ['VERIFY_INITIAL','INPUT_NOMOR_SURAT']);
          if (!is_array($gateStepsInit)) $gateStepsInit = ($service->workflow?->gate_steps_json ?? ['VERIFY_INITIAL','INPUT_NOMOR_SURAT']);
          if (!is_array($gateStepsInit)) $gateStepsInit = ['VERIFY_INITIAL','INPUT_NOMOR_SURAT'];
          $gateRoleSelected = trim((string) old('gate_role', $service->workflow?->gate_role ?? 'Admin Jurusan'));
          $gateRoleSelected = match (strtoupper(str_replace(' ', '_', $gateRoleSelected))) {
            'ADMIN_JURUSAN', 'ADMIN_JURUSAN_PER_PRODI', 'ADMIN_PRODI' => 'Admin Jurusan',
            'STAF_ULT', 'STAFF_ULT' => 'Staf ULT',
            default => in_array($gateRoleSelected, ['Admin Jurusan', 'Staf ULT'], true) ? $gateRoleSelected : 'Admin Jurusan',
          };
        @endphp

        <div class="space-y-1">
          <label class="text-sm font-medium" for="gate-role-default">Petugas gate awal</label>
          <select id="gate-role-default" name="gate_role" class="as-input">
            <option value="Admin Jurusan" @selected($gateRoleSelected === 'Admin Jurusan')>Admin Jurusan/Prodi</option>
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
        <x-button type="submit" variant="secondary">Simpan Gate</x-button>
      </form>
    </x-card>

    <x-card class="doc-card doc-anchor" id="doc-signers">
      <div class="doc-card__head">
        <div class="doc-card__heading">
          <div class="admin-card-title">Rantai Penandatangan</div>
          <div class="admin-card-subtitle">Atur urutan penandatangan. Jika perlu, aktifkan kewajiban upload tanda tangan per role.</div>
        </div>
      </div>
      @can('doc_signers.manage')
        @php
          $signersFromService = $service->signers->map(fn($s)=>[
            'role'=>$s->role,
            'custom_label'=>$s->custom_label,
            'order_index'=>$s->order_index,
            'is_required'=>$s->is_required,
            'requires_signature_upload'=>$s->requires_signature_upload,
            'signature_file_types'=>$s->signature_file_types,
            'signature_max_size_kb'=>$s->signature_max_size_kb,
          ])->values()->all();
          $signersInit = old('signers_json') ? json_decode(old('signers_json'), true) : $signersFromService;
          if (!is_array($signersInit)) $signersInit = $signersFromService;
        @endphp

        <form method="POST" action="{{ route('admin.layanan.dokumen.signers',$service) }}" class="ars-form">
          @csrf
          @method('PUT')

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

          <x-button type="submit" variant="secondary">Simpan Signers</x-button>
        </form>
      @else
        <div class="doc-callout doc-callout--warn">
          <div class="doc-callout__label">Akses dibatasi</div>
          <div class="doc-callout__value">Anda tidak memiliki permission untuk mengubah signer chain.</div>
        </div>
      @endcan
    </x-card>

    <x-card class="doc-card doc-anchor" id="doc-readiness">
      <div class="doc-card__head">
        <div class="doc-card__heading">
          <div class="admin-card-title">Publish Readiness</div>
          <div class="admin-card-subtitle">Pastikan template, placeholder, dan workflow sudah siap sebelum publish.</div>
        </div>
      </div>
      @if(empty(\Illuminate\Support\Arr::flatten($readinessErrors)))
        <div class="doc-callout doc-callout--ok">
          <div class="doc-callout__label">Siap publish</div>
          <div class="doc-callout__value">Tidak ada error readiness.</div>
        </div>
        @can('doc_services.publish')
          <form method="POST" action="{{ route('admin.layanan.dokumen.publish',$service) }}" class="ars-form">
            @csrf
            <x-button type="submit">Publish</x-button>
          </form>
        @else
          <div class="doc-hint">Permission <span class="doc-mono">doc_services.publish</span> diperlukan untuk publish.</div>
        @endcan
      @else
        <div class="doc-callout doc-callout--warn">
          <div class="doc-callout__label">Belum siap publish</div>
          <div class="doc-callout__value">Perbaiki item berikut.</div>
        </div>
        <ul class="doc-issues">
          @foreach($readinessErrors as $cat => $msgs)
            @foreach($msgs as $m)
              <li><span class="doc-issues__cat">[{{ $cat }}]</span> {{ $m }}</li>
            @endforeach
          @endforeach
        </ul>
      @endif
    </x-card>
  </aside>
</div>
@endif

