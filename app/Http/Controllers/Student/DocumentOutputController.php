<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Request as UltRequest;
use App\Services\Documents\DocumentAssemblerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DocumentOutputController extends Controller
{
    public function __construct(private readonly DocumentAssemblerService $assembler) {}

    public function download(UltRequest $request, Request $http)
    {
        Gate::authorize('view', $request);

        // Prefer existing generated PDF to keep student download consistent
        // with previously generated output/preview artifacts.
        $output = $request->outputs()
            ->where('output_type', 'PDF')
            ->orderByDesc('id')
            ->first();

        if (!$output) {
            $latestDocx = $request->outputs()
                ->where('output_type', 'DOCX')
                ->orderByDesc('id')
                ->first();
            if (!$latestDocx) {
                abort(404);
            }

            $output = $this->assembler->ensurePdfOutput($latestDocx);
            if (!$output) {
                abort(422, 'Output PDF belum tersedia. Hubungi admin untuk mengaktifkan LibreOffice (soffice).');
            }
        }

        Gate::authorize('download', $output);

        $disk = config('ult.private_disk');

        app(\App\Services\AuditLogger::class)->log('doc.output.download', 'request_outputs', (string) $output->id, [
            'request_id' => $request->id,
            'path' => $output->file_path,
        ]);

        $filename = $this->assembler->resolveOutputDownloadFilename($output);

        return Storage::disk($disk)->download($output->file_path, $filename);
    }
}
