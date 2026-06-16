<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\Documents\DocumentAssemblerService;

class ServiceDocumentPreviewController extends Controller
{
    public function __construct(private readonly DocumentAssemblerService $assembler) {}

    public function show(Service $service)
    {
        abort_unless($service->is_active, 404);
        abort_unless($service->status === null || $service->status?->value === 'PUBLISHED', 404);
        abort_if($service->usesRequestPptxSource(), 404);

        $preview = $this->assembler->buildServiceTemplatePreview($service);

        return response()->file($preview['path'], [
            'Content-Type' => $preview['mime'],
            'Content-Disposition' => 'inline; filename="'.$preview['filename'].'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Frame-Options' => 'SAMEORIGIN',
        ])->deleteFileAfterSend(true);
    }
}
