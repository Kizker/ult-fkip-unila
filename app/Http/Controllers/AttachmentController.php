<?php

namespace App\Http\Controllers;

use App\Enums\AttachmentKind;
use App\Enums\AttachmentVerifiedStatus;
use App\Models\Attachment;
use App\Models\Request as UltRequest;
use App\Services\AuditLogger;
use App\Services\Uploads\UniqueUploadNamer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly UniqueUploadNamer $uploadNamer,
    ) {}

    public function uploadInput(Request $request, UltRequest $ult)
    {
        Gate::authorize('update', $ult);

        $file = $request->validate([
            'file' => ['required','file','max:'.(config('ult.upload.max_size_mb')*1024)],
        ])['file'];

        $this->assertAllowed($file->getClientOriginalExtension(), $file->getMimeType());

        $disk = config('ult.private_disk');
        $serviceFieldId = $request->input('service_field_id');
        $fieldKey = null;
        if (is_numeric($serviceFieldId)) {
            $ult->loadMissing('service.fields');
            $field = $ult->service?->fields?->firstWhere('id', (int) $serviceFieldId);
            $fieldKey = $field?->key;
        }
        $dataType = $fieldKey ? "input_{$fieldKey}" : 'input_attachment';
        $path = $this->uploadNamer->makePathForUploadedFile(
            $disk,
            "requests/{$ult->id}/input",
            $dataType,
            $file,
        );

        $stream = fopen($file->getRealPath(), 'rb');
        Storage::disk($disk)->put($path, $stream);
        if (is_resource($stream)) fclose($stream);

        $sha = hash_file('sha256', $file->getRealPath());

        $attachment = Attachment::create([
            'request_id' => $ult->id,
            'uploaded_by' => $request->user()->id,
            'kind' => AttachmentKind::input,
            'service_field_id' => $request->input('service_field_id'),
            'original_name' => $file->getClientOriginalName(),
            'stored_path' => $path,
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
            'sha256' => $sha,
            'verified_status' => AttachmentVerifiedStatus::pending,
        ]);

        $this->audit->log('attachment.upload_input', 'attachments', (string) $attachment->id, [
            'request_id' => $ult->id,
            'path' => $path,
        ]);

        return back()->with('status', __('app.upload_ok'));
    }

    public function download(Request $request, Attachment $attachment)
    {
        Gate::authorize('download', $attachment);

        $disk = config('ult.private_disk');

        $this->audit->log('attachment.download', 'attachments', (string) $attachment->id, [
            'request_id' => $attachment->request_id,
            'path' => $attachment->stored_path,
        ]);

        return Storage::disk($disk)->download($attachment->stored_path, $attachment->original_name);
    }

    private function assertAllowed(string $ext, string $mime): void
    {
        $ext = strtolower($ext);
        $allowedExt = config('ult.upload.allowed_ext', []);
        $allowedMime = config('ult.upload.allowed_mimes', []);

        if (!in_array($ext, $allowedExt, true) || !in_array($mime, $allowedMime, true)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'File type not allowed.');
        }

        // Block executable-like extensions regardless of mime
        $blocked = ['php','phtml','phar','exe','sh','bat','cmd','js','html','htm'];
        if (in_array($ext, $blocked, true)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'File type blocked.');
        }
    }
}
