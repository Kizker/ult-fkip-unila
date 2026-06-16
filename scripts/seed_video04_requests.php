<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Enums\RequestSignoffStatus;
use App\Enums\RequestStatus;
use App\Models\Request as UltRequest;
use App\Models\RequestFieldValue;
use App\Models\RequestNote;
use App\Models\RequestSignoff;
use App\Models\RequestStatusHistory;
use App\Models\User;
use Illuminate\Support\Carbon;

$student = User::query()->where('email', 'mahasiswa@demo.test')->firstOrFail();
$template = UltRequest::query()->with(['fieldValues', 'signoffs'])->first(); // Cari template bebas

function createVideo04Request(
    User $student,
    UltRequest $template,
    string $activityTitle,
    string $status,
    array $historyRows,
    array $noteBodies = [],
): UltRequest {
    $request = UltRequest::query()->firstOrCreate(
        [
            'student_id' => $student->id,
            'service_id' => $template->service_id,
            'activity_title' => $activityTitle,
        ],
        [
            'current_status' => $status,
            'current_step_key' => $template->current_step_key,
            'current_unit_id' => $student->unit_id,
            'submitted_at' => Carbon::now()->subDays(5),
        ]
    );

    $request->forceFill([
        'current_status' => $status,
        'current_step_key' => $template->current_step_key,
        'current_unit_id' => $student->unit_id,
    ])->save();

    if ($request->fieldValues()->count() === 0) {
        foreach ($template->fieldValues as $fieldValue) {
            $text = $fieldValue->value_text;
            if ($text && str_contains($text, '[VIDEO')) {
                $text = $activityTitle;
            } elseif ($text === 'SMA N 1') {
                $text = 'Dinas Pendidikan Provinsi Lampung';
            } elseif ($text === 'Kepala Sekolah') {
                $text = 'Kepala Dinas';
            }
            RequestFieldValue::query()->create([
                'request_id' => $request->id,
                'service_field_id' => $fieldValue->service_field_id,
                'value_text' => $text,
                'value_number' => $fieldValue->value_number,
                'value_date' => $fieldValue->value_date,
                'value_json' => $fieldValue->value_json,
            ]);
        }
    }

    if ($request->signoffs()->count() === 0) {
        foreach ($template->signoffs as $signoff) {
            RequestSignoff::query()->create([
                'request_id' => $request->id,
                'order_index' => $signoff->order_index,
                'signer_role' => $signoff->signer_role,
                'signer_user_id' => $signoff->signer_user_id,
                'signer_name' => $signoff->signer_name,
                'signer_position' => $signoff->signer_position,
                'signature_file_path' => $signoff->signature_file_path,
                'status' => strtoupper((string) $signoff->signer_role) === 'PEMOHON'
                    ? RequestSignoffStatus::APPROVED
                    : RequestSignoffStatus::PENDING,
                'signed_at' => strtoupper((string) $signoff->signer_role) === 'PEMOHON' ? Carbon::now()->subDays(5) : null,
            ]);
        }
    }

    if ($request->histories()->count() === 0) {
        foreach ($historyRows as $row) {
            RequestStatusHistory::query()->create([
                'request_id' => $request->id,
                'from_status' => $row['from_status'] ?? null,
                'to_status' => $row['to_status'],
                'step_key' => $row['step_key'] ?? $template->current_step_key,
                'note' => $row['note'] ?? null,
                'actor_id' => $student->id,
                'created_at' => $row['created_at'],
                'updated_at' => $row['created_at'],
            ]);
        }
    }

    foreach ($noteBodies as $index => $body) {
        RequestNote::query()->firstOrCreate(
            ['request_id' => $request->id, 'body' => $body],
            [
                'actor_id' => $student->id,
                'is_internal' => false,
                'created_at' => Carbon::now()->subHours(3 - $index),
                'updated_at' => Carbon::now()->subHours(3 - $index),
            ]
        );
    }

    return $request->fresh();
}

// 1. Diajukan
$reqDiajukan = createVideo04Request($student, $template, 'Penelitian Pendidikan Bahasa di SMAN 2', RequestStatus::DIAJUKAN->value, [
    ['to_status' => RequestStatus::DIAJUKAN->value, 'note' => 'Permohonan berhasil dikirim.', 'created_at' => Carbon::now()->subHours(1)],
]);

// 2. Diverifikasi Unit
$reqDiverifikasi = createVideo04Request($student, $template, 'Izin Riset SMPN 1 Bandar Lampung', RequestStatus::DIVERIFIKASI_UNIT->value, [
    ['to_status' => RequestStatus::DIVERIFIKASI_UNIT->value, 'note' => 'Lanjut ke tingkat fakultas.', 'created_at' => Carbon::now()->subHours(2)],
]);

// 3. Review ULT
$reqReview = createVideo04Request($student, $template, 'Pra Penelitian Disdikbud Provinsi Lampung', RequestStatus::REVIEW_ULT->value, [
    ['to_status' => RequestStatus::REVIEW_ULT->value, 'note' => 'Diperiksa oleh ULT.', 'created_at' => Carbon::now()->subHours(3)],
]);

// 4. Menunggu TTD
$reqMenungguTTD = createVideo04Request($student, $template, 'Observasi Pembelajaran Bahasa Inggris', RequestStatus::MENUNGGU_TTD_FAKULTAS->value, [
    ['to_status' => RequestStatus::MENUNGGU_TTD_FAKULTAS->value, 'note' => 'Menunggu persetujuan Pimpinan.', 'created_at' => Carbon::now()->subHours(4)],
]);

// 4.5. Nomor Terbit (MISSING PREVIOUSLY)
$reqNomorTerbit = createVideo04Request($student, $template, 'Studi Lapangan Kurikulum Merdeka', RequestStatus::NOMOR_DOKUMEN_TERBIT->value, [
    ['to_status' => RequestStatus::NOMOR_DOKUMEN_TERBIT->value, 'note' => 'Nomor surat telah diterbitkan.', 'created_at' => Carbon::now()->subHours(4)],
]);

// 5. Diproses
$reqDiproses = createVideo04Request($student, $template, 'Analisis Kinerja Guru SD Negeri 3', RequestStatus::DIPROSES->value, [
    ['to_status' => RequestStatus::DIPROSES->value, 'note' => 'Dokumen sedang diproses.', 'created_at' => Carbon::now()->subHours(5)],
]);

// 6. Penandatanganan
$reqPenandatanganan = createVideo04Request($student, $template, 'Pengambilan Data Skripsi Bappeda', RequestStatus::IN_SIGNING->value, [
    ['to_status' => RequestStatus::IN_SIGNING->value, 'note' => 'Proses tanda tangan sertifikat.', 'created_at' => Carbon::now()->subHours(6)],
]);

// 7. Perlu Perbaikan
$reqPerbaikan = createVideo04Request($student, $template, 'Permohonan Izin Penelitian Lapangan', RequestStatus::PERLU_PERBAIKAN->value, [
    ['to_status' => RequestStatus::PERLU_PERBAIKAN->value, 'note' => 'Dokumen dikembalikan ke pemohon.', 'created_at' => Carbon::now()->subHours(7)],
], ['Mohon ganti file lampiran dengan scan KTP asli yang berwarna dan terbaca jelas, bukan hitam putih.']);

// 8. Ditolak
$reqDitolak = createVideo04Request($student, $template, 'Pengajuan Pengurangan UKT', RequestStatus::DITOLAK->value, [
    ['to_status' => RequestStatus::DITOLAK->value, 'note' => 'Layanan tidak relevan.', 'created_at' => Carbon::now()->subHours(8)],
], ['Pengajuan Anda ditolak karena layanan ini khusus untuk pra penelitian, bukan pengajuan UKT.']);

// 9. Selesai
$reqSelesai = createVideo04Request($student, $template, 'Surat Persetujuan Pra Penelitian', RequestStatus::SELESAI->value, [
    ['to_status' => RequestStatus::SELESAI->value, 'note' => 'Dokumen selesai diterbitkan.', 'created_at' => Carbon::now()->subHours(9)],
]);

echo json_encode([
    'reqDiajukan' => $reqDiajukan->id,
    'reqDiverifikasi' => $reqDiverifikasi->id,
    'reqReview' => $reqReview->id,
    'reqMenungguTTD' => $reqMenungguTTD->id,
    'reqNomorTerbit' => $reqNomorTerbit->id,
    'reqDiproses' => $reqDiproses->id,
    'reqPenandatanganan' => $reqPenandatanganan->id,
    'reqPerbaikan' => $reqPerbaikan->id,
    'reqDitolak' => $reqDitolak->id,
    'reqSelesai' => $reqSelesai->id,
], JSON_UNESCAPED_UNICODE);

