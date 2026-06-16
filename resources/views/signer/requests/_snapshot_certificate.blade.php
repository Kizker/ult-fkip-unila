@php
  $value = is_array($value ?? null) ? $value : [];
  $snapshotLabel = $snapshotLabel ?? static fn ($raw) => (string) $raw;
  $snapshotScalar = $snapshotScalar ?? static fn ($raw) => is_scalar($raw) ? (string) $raw : '[data]';

  $rawSigners = is_array($value['signers'] ?? null) ? $value['signers'] : [];
  $signers = [];
  foreach ($rawSigners as $row) {
    if (is_array($row)) {
      $signers[] = $row;
    }
  }

  $sourceName = trim((string) ($value['source_original_name'] ?? ''));
  $sourceAttachmentId = isset($value['source_attachment_id']) && is_numeric($value['source_attachment_id'])
    ? (int) $value['source_attachment_id']
    : null;

  $sourceUploadedAt = '-';
  $sourceUploadedRaw = trim((string) ($value['source_uploaded_at'] ?? ''));
  if ($sourceUploadedRaw !== '') {
    try {
      $sourceUploadedAt = \Carbon\Carbon::parse($sourceUploadedRaw)->format('d M Y H:i');
    } catch (\Throwable $e) {
      $sourceUploadedAt = $sourceUploadedRaw;
    }
  }

  $signerTypeLabel = static function (string $type): string {
    $normalized = strtoupper(trim($type));
    return match ($normalized) {
      'INTERNAL' => 'Internal',
      'PEMOHON' => 'Pemohon',
      'CUSTOM' => 'Custom',
      default => $normalized !== '' ? $normalized : '-',
    };
  };
  $activityTitle = trim((string) ($value['activity_title'] ?? ''));
@endphp

<div class="ss-cert-clean">
  <div class="ars-kvgrid mt-2">
    @if($activityTitle !== '')
      <div class="ars-kv">
        <div class="ars-kv__label">Judul kegiatan</div>
        <div class="ars-kv__value">{{ $activityTitle }}</div>
      </div>
    @endif
    <div class="ars-kv">
      <div class="ars-kv__label">Jumlah Signer</div>
      <div class="ars-kv__value">{{ count($signers) }}</div>
    </div>
    <div class="ars-kv">
      <div class="ars-kv__label">Attachment ID</div>
      <div class="ars-kv__value">{{ $sourceAttachmentId ?: '-' }}</div>
    </div>
  </div>

  <div class="mt-3">
    <div class="ss-snapshot-item__label">Daftar signer</div>
    @if(empty($signers))
      <div class="ss-snapshot-item__value mt-1">Belum ada data signer.</div>
    @else
      <div class="ss-cert-signers-clean mt-2">
        @foreach($signers as $index => $signer)
          @php
            $name = trim((string) ($signer['name'] ?? ''));
            $idNumber = trim((string) ($signer['id_number'] ?? ''));
            $jabatan = trim((string) ($signer['jabatan'] ?? ''));
            $type = $signerTypeLabel((string) ($signer['signer_type'] ?? $signer['type'] ?? ''));
            $userId = isset($signer['signer_user_id']) && is_numeric($signer['signer_user_id']) ? (int) $signer['signer_user_id'] : null;
          @endphp
          <details class="ss-disclosure" @if($index === 0) open @endif>
            <summary class="ss-disclosure__summary">
              <span class="ss-disclosure__title">Signer {{ $index + 1 }}</span>
              <span class="ss-disclosure__meta">{{ $type }} @if($name !== '') • {{ $name }} @endif</span>
            </summary>
            <div class="ars-kvgrid mt-2">
              <div class="ars-kv">
                <div class="ars-kv__label">Nama</div>
                <div class="ars-kv__value">{{ $name !== '' ? $name : '-' }}</div>
              </div>
              <div class="ars-kv">
                <div class="ars-kv__label">ID</div>
                <div class="ars-kv__value">{{ $idNumber !== '' ? $idNumber : '-' }}</div>
              </div>
              <div class="ars-kv">
                <div class="ars-kv__label">Jabatan</div>
                <div class="ars-kv__value">{{ $jabatan !== '' ? $jabatan : '-' }}</div>
              </div>
              <div class="ars-kv">
                <div class="ars-kv__label">User ID</div>
                <div class="ars-kv__value">{{ $userId ?: '-' }}</div>
              </div>
            </div>
          </details>
        @endforeach
      </div>
    @endif
  </div>

  <div class="mt-3">
    <div class="ss-snapshot-item__label">Dokumen sumber</div>
    <div class="ars-kvgrid mt-2">
      <div class="ars-kv">
        <div class="ars-kv__label">Nama file</div>
        <div class="ars-kv__value">{{ $sourceName !== '' ? $sourceName : '-' }}</div>
      </div>
      <div class="ars-kv">
        <div class="ars-kv__label">Waktu upload</div>
        <div class="ars-kv__value">{{ $sourceUploadedAt }}</div>
      </div>
    </div>
  </div>
</div>
