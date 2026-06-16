<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Enums\RequestStatus;
use App\Models\Request as UltRequest;
use Illuminate\Support\Carbon;

$action = $argv[1] ?? 'prepare';
$requestId = 18;
$tag = '[DOC_GUIDE_TMP_REVISION]';
$backupPath = __DIR__ . '/../tmp/pemohon_revision_request_backup.json';

if (!is_dir(dirname($backupPath))) {
    mkdir(dirname($backupPath), 0777, true);
}

/** @var UltRequest|null $request */
$request = UltRequest::query()->with(['histories', 'notes'])->find($requestId);

if (!$request) {
    fwrite(STDERR, "Request {$requestId} tidak ditemukan.\n");
    exit(1);
}

if ($action === 'prepare') {
    $backup = [
        'request_id' => $request->id,
        'current_status' => $request->current_status instanceof RequestStatus
            ? $request->current_status->value
            : (string) $request->current_status,
        'current_step_key' => $request->current_step_key,
    ];

    file_put_contents($backupPath, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    $request->forceFill([
        'current_status' => RequestStatus::PERLU_PERBAIKAN->value,
    ])->save();

    $request->histories()->firstOrCreate(
        [
            'to_status' => RequestStatus::PERLU_PERBAIKAN->value,
            'note' => $tag . ' Mohon perbarui data pemohon, signer, dan lampiran pendukung.',
        ],
        [
            'from_status' => $backup['current_status'],
            'step_key' => $request->current_step_key,
            'actor_id' => $request->student_id,
            'created_at' => Carbon::now()->subMinutes(5),
            'updated_at' => Carbon::now()->subMinutes(5),
        ]
    );

    $request->notes()->firstOrCreate(
        [
            'body' => $tag . ' Lengkapi data permohonan dan periksa kembali pilihan signer sebelum mengirim revisi.',
        ],
        [
            'actor_id' => $request->student_id,
            'is_internal' => false,
            'created_at' => Carbon::now()->subMinutes(4),
            'updated_at' => Carbon::now()->subMinutes(4),
        ]
    );

    echo "Prepared request {$request->id} for revision screenshots.\n";
    exit(0);
}

if ($action === 'restore') {
    if (file_exists($backupPath)) {
        $backup = json_decode((string) file_get_contents($backupPath), true);
        if (is_array($backup)) {
            $request->forceFill([
                'current_status' => $backup['current_status'] ?? RequestStatus::DIAJUKAN->value,
                'current_step_key' => $backup['current_step_key'] ?? $request->current_step_key,
            ])->save();
        }

        @unlink($backupPath);
    }

    $request->histories()
        ->where('note', 'like', $tag . '%')
        ->delete();

    $request->notes()
        ->where('body', 'like', $tag . '%')
        ->delete();

    echo "Restored request {$request->id} after revision screenshots.\n";
    exit(0);
}

fwrite(STDERR, "Aksi tidak dikenal: {$action}\n");
exit(1);
