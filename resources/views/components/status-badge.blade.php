@props(['status'])
@php
  $map = [
    'DIAJUKAN' => ['primary','Diajukan'],
    'PERLU_PERBAIKAN' => ['warning','Perlu Perbaikan'],
    'DIVERIFIKASI_UNIT' => ['primary','Diverifikasi Unit'],
    'MENUNGGU_TTD_UNIT' => ['warning','Menunggu TTD Unit'],
    'REVIEW_ULT' => ['primary','Review ULT'],
    'MENUNGGU_TTD_FAKULTAS' => ['warning','Menunggu TTD Fakultas'],
    'NOMOR_DOKUMEN_TERBIT' => ['primary','Nomor Terbit'],
    'DIPROSES' => ['primary','Diproses'],
    'SELESAI' => ['success','Selesai'],
    'DITOLAK' => ['danger','Ditolak'],

    // Document module (layanan dokumen)
    'GATE_VERIFIED' => ['primary','Gate Verified'],
    'NOMOR_SURAT_FILLED' => ['primary','Nomor Surat Diisi'],
    'IN_SIGNING' => ['warning','Penandatanganan'],
    'REJECTED_IN_SIGNING' => ['danger','Ditolak TTD'],
    'READY_FOR_FINAL' => ['warning','Penandatanganan'],
    // Backward compatibility for old records before status unification.
    'COMPLETED' => ['success','Selesai'],
    'DITOLAK_ADMIN' => ['danger','Ditolak Admin'],
  ];
  $v = $map[$status] ?? ['default',$status];
@endphp
<x-badge :variant="$v[0]" {{ $attributes }}>{{ $v[1] }}</x-badge>
