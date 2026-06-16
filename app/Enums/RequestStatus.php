<?php

namespace App\Enums;

enum RequestStatus: string
{
    case DIAJUKAN = 'DIAJUKAN';
    case PERLU_PERBAIKAN = 'PERLU_PERBAIKAN';
    case DIVERIFIKASI_UNIT = 'DIVERIFIKASI_UNIT';
    case MENUNGGU_TTD_UNIT = 'MENUNGGU_TTD_UNIT';
    case REVIEW_ULT = 'REVIEW_ULT';
    case MENUNGGU_TTD_FAKULTAS = 'MENUNGGU_TTD_FAKULTAS';
    case NOMOR_DOKUMEN_TERBIT = 'NOMOR_DOKUMEN_TERBIT';
    case DIPROSES = 'DIPROSES';
    case SELESAI = 'SELESAI';
    case DITOLAK = 'DITOLAK';

    // Document module (layanan dokumen) — additional strict states
    case GATE_VERIFIED = 'GATE_VERIFIED';
    case NOMOR_SURAT_FILLED = 'NOMOR_SURAT_FILLED';
    case IN_SIGNING = 'IN_SIGNING';
    case REJECTED_IN_SIGNING = 'REJECTED_IN_SIGNING';
    case READY_FOR_FINAL = 'READY_FOR_FINAL';
    case COMPLETED = 'COMPLETED';
    case DITOLAK_ADMIN = 'DITOLAK_ADMIN';
}
