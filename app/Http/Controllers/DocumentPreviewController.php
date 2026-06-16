<?php

namespace App\Http\Controllers;

use App\Models\Request as UltRequest;
use App\Services\Documents\DocumentAssemblerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DocumentPreviewController extends Controller
{
    public function __construct(private readonly DocumentAssemblerService $assembler) {}

    public function show(UltRequest $request, Request $http)
    {
        Gate::authorize('view', $request);

        $preview = $this->assembler->buildReviewPreview($request, $http->user());
        $disposition = $preview['mime'] === 'application/pdf'
            ? 'inline'
            : 'attachment';

        return response()->file($preview['path'], [
            'Content-Type' => $preview['mime'],
            'Content-Disposition' => $disposition.'; filename="'.$preview['filename'].'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ])->deleteFileAfterSend(true);
    }
}
