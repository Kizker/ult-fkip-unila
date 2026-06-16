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
$template = UltRequest::query()
    ->with(['fieldValues', 'signoffs'])
    ->findOrFail(18);

function firstOrCreateGuideRequest(
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
            'submitted_at' => Carbon::now()->subDays(2),
        ]
    );

    $request->forceFill([
        'current_status' => $status,
        'current_step_key' => $template->current_step_key,
        'current_unit_id' => $student->unit_id,
    ])->save();

    if ($request->fieldValues()->count() === 0) {
        foreach ($template->fieldValues as $fieldValue) {
            RequestFieldValue::query()->create([
                'request_id' => $request->id,
                'service_field_id' => $fieldValue->service_field_id,
                'value_text' => $fieldValue->value_text,
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
                'signed_at' => strtoupper((string) $signoff->signer_role) === 'PEMOHON' ? Carbon::now()->subDay() : null,
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
            [
                'request_id' => $request->id,
                'body' => $body,
            ],
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

$detailRequest = firstOrCreateGuideRequest(
    $student,
    $template,
    '[DOC GUIDE] Surat Persyaratan Wisuda - Detail Setelah Submit',
    RequestStatus::DIAJUKAN->value,
    [
        [
            'to_status' => RequestStatus::DIAJUKAN->value,
            'note' => 'Permohonan berhasil dikirim oleh pemohon.',
            'created_at' => Carbon::now()->subDays(2),
        ],
    ],
);

$revisionRequest = firstOrCreateGuideRequest(
    $student,
    $template,
    '[DOC GUIDE] Surat Persyaratan Wisuda - Perlu Perbaikan',
    RequestStatus::PERLU_PERBAIKAN->value,
    [
        [
            'to_status' => RequestStatus::DIAJUKAN->value,
            'note' => 'Permohonan berhasil dikirim oleh pemohon.',
            'created_at' => Carbon::now()->subDays(2),
        ],
        [
            'from_status' => RequestStatus::DIAJUKAN->value,
            'to_status' => RequestStatus::PERLU_PERBAIKAN->value,
            'note' => 'Mohon lengkapi data signer, periksa kembali tanda tangan, dan tambahkan catatan pendukung.',
            'created_at' => Carbon::now()->subDay(),
        ],
    ],
    [
        'Mohon lengkapi data signer, periksa kembali tanda tangan, dan tambahkan catatan pendukung.',
    ],
);

echo json_encode([
    'detail_request_id' => $detailRequest->id,
    'revision_request_id' => $revisionRequest->id,
], JSON_UNESCAPED_UNICODE);
