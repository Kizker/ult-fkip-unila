@extends('layouts.app')
@section('section','Signer Decide')
@section('content')
@php
  $status = $req->current_status->value ?? $req->current_status;
  $activeSignoff = $req->signoffs->firstWhere('order_index', (int)$req->current_signer_order_index);
  $activeSigner = $req->service->signers->firstWhere('order_index', (int)$req->current_signer_order_index);
  $activeRole = $activeSigner?->role ?? $activeSignoff?->signer_role ?? '-';
  $activeRoleRaw = strtoupper(trim((string) $activeRole));
  $activeRoleMap = [
    'KAPRODI_SCOPE' => 'Ketua Program Studi',
    'KAPRODI' => 'Ketua Program Studi',
    'KAJUR_SCOPE' => 'Ketua Jurusan',
    'KAJUR' => 'Ketua Jurusan',
    'SEKJUR_SCOPE' => 'Sekretaris Jurusan',
    'SEKJUR' => 'Sekretaris Jurusan',
    'DOSEN' => 'Dosen',
    'DEKAN' => 'Dekan',
    'WD_AKADEMIK' => 'Wakil Dekan Bidang Akademik',
    'WD_UMUM' => 'Wakil Dekan Bidang Umum',
    'WD_KEMAHASISWAAN' => 'Wakil Dekan Bidang Kemahasiswaan',
    'PEMOHON' => 'Pemohon',
    'CUSTOM' => 'Penandatangan Khusus',
    'CERT_INTERNAL' => 'Sertifikat Internal',
  ];
  $activeRoleLabel = $activeRoleMap[$activeRoleRaw] ?? null;
  $activeCustomLabel = trim((string) ($activeSigner?->custom_label ?? ''));
  if ($activeCustomLabel !== '' && in_array($activeRoleRaw, ['CUSTOM', 'DOSEN', 'PEMOHON'], true)) {
    $activeRoleLabel = $activeCustomLabel;
  }
  if (!$activeRoleLabel && $activeRoleRaw !== '' && $activeRoleRaw !== '-') {
    $activeRoleLabel = \Illuminate\Support\Str::of(strtolower(str_replace('_', ' ', $activeRoleRaw)))
      ->title()
      ->value();
  }
  if (!$activeRoleLabel) {
    $activeRoleLabel = '-';
  }
  $activeStepText = $activeRoleLabel !== '-'
    ? $activeRoleLabel . ' (Tahap ' . $req->current_signer_order_index . ')'
    : 'Tahap ' . $req->current_signer_order_index;
  $isDocService = (bool) (
    $req->service?->usesRequestPptxSource()
    || $req->service?->templates?->firstWhere('type', \App\Enums\ServiceTemplateType::MAIN_DOCX)
  );
  $requiresSignatureUpload = (bool) (
    $activeSigner?->requires_signature_upload
    || strtoupper((string) ($activeSignoff?->signer_role ?? '')) === 'CERT_INTERNAL'
  );
  $snapshotData = is_array($req->data?->data_json) ? $req->data->data_json : [];
  $attachmentMap = $req->attachments->keyBy('id');
  $fileFieldKeys = $req->service->fields
    ->filter(static fn ($field) => (string) ($field->type ?? '') === 'file')
    ->pluck('key')
    ->filter()
    ->map(static fn ($key) => strtolower((string) $key))
    ->values()
    ->all();
  $canDownloadAttachment = auth()->user()?->can('attachments.download_private') ?? false;
  $formatBytes = static function (?int $bytes): string {
    if (!$bytes || $bytes < 1) {
      return '-';
    }
    if ($bytes >= 1048576) {
      return number_format($bytes / 1048576, 2).' MB';
    }
    if ($bytes >= 1024) {
      return number_format($bytes / 1024, 1).' KB';
    }
    return $bytes.' B';
  };
  $normalizeSnapshotValue = static function ($input) use (&$normalizeSnapshotValue) {
    if (is_string($input)) {
      $trimmed = trim($input);
      if ($trimmed !== '' && in_array($trimmed[0], ['{', '['], true)) {
        $decoded = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE) {
          return $normalizeSnapshotValue($decoded);
        }
      }

      return $input;
    }

    if (is_array($input)) {
      $out = [];
      foreach ($input as $k => $v) {
        $out[$k] = $normalizeSnapshotValue($v);
      }
      return $out;
    }

    return $input;
  };
  $snapshotLabel = static function ($raw): string {
    return (string) \Illuminate\Support\Str::of((string) $raw)
      ->replace(['_', '-'], ' ')
      ->squish()
      ->title();
  };
  $snapshotScalar = static function ($raw): string {
    return match (true) {
      is_bool($raw) => $raw ? 'Ya' : 'Tidak',
      is_null($raw) => '-',
      is_scalar($raw) => trim((string) $raw) !== '' ? (string) $raw : '-',
      default => '[data]',
    };
  };
  $snapshotIsAssoc = static function (array $arr): bool {
    return array_keys($arr) !== range(0, count($arr) - 1);
  };
@endphp

<div class="page-signer-show page-admin-requests-show" data-signer-request-show-page>
  <header class="admin-page-header">
    <div class="admin-page-heading" style="text-align:left;justify-items:start;">
      <div class="admin-page-kicker">Permohonan {{ $req->request_code }}</div>
      <h1 class="admin-page-title" style="text-align:left;margin-inline:0;">{{ $req->display_title }}</h1>
      <p class="admin-page-subtitle" style="text-align:left;margin-inline:0;">Keputusan signer untuk step aktif.</p>
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
      <x-button class="ss-back-btn !border-[rgb(var(--c-primary))] !bg-[rgb(var(--c-primary))] !text-white hover:!border-[rgb(var(--c-primary))] hover:!bg-transparent hover:!text-[rgb(var(--c-primary))] dark:hover:!border-white dark:hover:!bg-transparent dark:hover:!text-white" variant="ghost" href="{{ route('signer.requests.inbox') }}">&larr; Kembali</x-button>
    </div>
  </header>

  <div class="ars-layout">
    <div class="ars-main" data-ss-main-column>
      <x-card>
        <div class="ars-card-header">
          <div class="ars-card-title">Ringkasan Permohonan</div>
          <div class="ars-card-subtitle">Data utama dokumen yang sedang Anda review.</div>
        </div>

        <div class="ars-kvgrid">
          @if(filled($req->activity_title))
            <div class="ars-kv">
              <div class="ars-kv__label">Judul permohonan</div>
              <div class="ars-kv__value">{{ $req->display_title }}</div>
            </div>
          @endif
          <div class="ars-kv">
            <div class="ars-kv__label">Mahasiswa</div>
            <div class="ars-kv__value">{{ $req->student?->name }} ({{ $req->student?->email }})</div>
          </div>
          <div class="ars-kv">
            <div class="ars-kv__label">Nomor Surat</div>
            <div class="ars-kv__value">{{ $req->nomor_surat ?? '-' }}</div>
          </div>
          <div class="ars-kv">
            <div class="ars-kv__label">Step aktif</div>
            <div class="ars-kv__value">{{ $activeStepText }}</div>
          </div>
          <div class="ars-kv">
            <div class="ars-kv__label">Status</div>
            <div class="ars-kv__value"><x-status-badge :status="$status" /></div>
          </div>
        </div>

        @if($isDocService)
          <div class="mt-4">
            <x-button variant="secondary" href="{{ route('requests.preview', $req) }}" target="_blank">Preview Dokumen</x-button>
          </div>
        @endif
      </x-card>

      <x-card>
        <div class="ars-card-header">
          <div class="ars-card-title">Keputusan Signer</div>
          <div class="ars-card-subtitle">Pilih keputusan sesuai hasil tinjauan dokumen.</div>
        </div>

        <form method="POST" action="{{ route('signer.requests.decide',$req) }}" enctype="multipart/form-data" class="ars-form mt-3">
          @csrf
          <x-select name="decision" label="Decision">
            <option value="APPROVE">APPROVE</option>
            <option value="REVISION">REVISION</option>
            <option value="REJECT">REJECT</option>
          </x-select>
          <x-textarea name="note" rows="3" label="Catatan (opsional)">{{ old('note') }}</x-textarea>
          @if($requiresSignatureUpload)
            <x-input type="file" name="signature_file" label="Signature file (wajib untuk APPROVE)" />
            <div class="text-xs text-muted">
              Allowed:
              {{ $activeSigner ? implode(', ', $activeSigner->signature_file_types ?? []) : 'image/png, image/jpeg, image/webp' }},
              max {{ $activeSigner?->signature_max_size_kb ?? 1024 }} KB
            </div>
          @endif
          <x-button type="submit" class="ss-submit">Kirim Keputusan</x-button>
        </form>
      </x-card>
    </div>

    <div class="ars-side">
      <x-card class="ss-sticky" data-ss-snapshot-card>
        <div class="ars-card-header">
          <div class="ars-card-title">Data Snapshot</div>
          <div class="ars-card-subtitle">Ringkasan data input pemohon saat pengajuan.</div>
        </div>

        <div class="ss-snapshot-scroll" data-ss-snapshot-scroll>
        <div class="ss-snapshot-list">
          @forelse($snapshotData as $key => $value)
            @php
              $label = $snapshotLabel((string) $key);
              $parsedValue = $normalizeSnapshotValue($value);

              $attachment = null;
              $isFileValue = false;
              $display = '-';
              $isCertificateBlock = strtolower((string) $key) === 'certificate' && is_array($parsedValue);

              if (is_array($parsedValue)) {
                $attachmentId = isset($parsedValue['attachment_id']) && is_numeric($parsedValue['attachment_id'])
                  ? (int) $parsedValue['attachment_id']
                  : null;
                $attachment = $attachmentId ? $attachmentMap->get($attachmentId) : null;
                $isFileField = in_array(strtolower((string) $key), $fileFieldKeys, true);
                $isFileValue = $attachment !== null || $isFileField || array_key_exists('attachment_id', $parsedValue);

                if ($isFileValue) {
                  $display = $attachment?->original_name
                    ?: trim((string) ($parsedValue['original'] ?? ''))
                    ?: 'File terunggah';
                }
              } else {
                $display = $snapshotScalar($parsedValue);
              }

              $fileMeta = '';
              if ($isFileValue && $attachment) {
                $meta = [];
                if ($attachment->mime) {
                  $mimeRaw = strtoupper((string) $attachment->mime);
                  $mimeParts = explode('/', $mimeRaw, 2);
                  $mimeLabel = trim((string) ($mimeParts[0] ?? ''));
                  if (!empty($mimeParts[1])) {
                    $mimeLabel .= ' / '.trim((string) $mimeParts[1]);
                  }
                  if ($mimeLabel !== '') {
                    $meta[] = $mimeLabel;
                  }
                }
                if ($attachment->size) {
                  $meta[] = $formatBytes((int) $attachment->size);
                }
                $fileMeta = !empty($meta) ? implode(' | ', $meta) : 'Lampiran tersimpan.';
              } elseif ($isFileValue) {
                $fileMeta = 'Lampiran belum tersedia.';
              }
            @endphp
            <div class="ss-snapshot-item">
              <div class="ss-snapshot-item__label">{{ $label }}</div>
              @if($isFileValue)
                <div class="ss-snapshot-item__value">{{ $display }}</div>
                <div class="ss-snapshot-item__muted">{{ $fileMeta }}</div>
                @if($attachment && $canDownloadAttachment)
                  <div class="ss-snapshot-item__actions">
                    <a class="ss-snapshot-item__link" href="{{ route('attachments.download', $attachment) }}" target="_blank" rel="noopener">Lihat file</a>
                  </div>
                @endif
              @elseif($isCertificateBlock)
                @include('signer.requests._snapshot_certificate', [
                  'value' => $parsedValue,
                  'snapshotLabel' => $snapshotLabel,
                  'snapshotScalar' => $snapshotScalar,
                ])
              @elseif(is_array($parsedValue))
                @include('signer.requests._snapshot_pairs', [
                  'value' => $parsedValue,
                  'snapshotLabel' => $snapshotLabel,
                  'snapshotScalar' => $snapshotScalar,
                  'snapshotIsAssoc' => $snapshotIsAssoc,
                ])
              @else
                <div class="ss-snapshot-item__value">{{ $display }}</div>
              @endif
            </div>
          @empty
            <div class="ss-empty">Belum ada data snapshot untuk permohonan ini.</div>
          @endforelse
        </div>
        </div>
      </x-card>
    </div>
  </div>
</div>
@endsection
