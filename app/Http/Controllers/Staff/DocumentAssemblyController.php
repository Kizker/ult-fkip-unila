<?php

namespace App\Http\Controllers\Staff;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Request as UltRequest;
use App\Models\RequestOutput;
use App\Services\Documents\DocumentAssemblerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DocumentAssemblyController extends Controller
{
    public function __construct(private readonly DocumentAssemblerService $assembler) {}

    public function show(UltRequest $request)
    {
        Gate::authorize('process', $request);
        if (!$this->canAssemble($request)) {
            return redirect()
                ->route('admin.requests.show', $request)
                ->with('warning', 'Finalisasi dokumen hanya bisa dilakukan saat status READY_FOR_FINAL.');
        }

        $request->load([
            'service.placeholders',
            'service.signers',
            'signoffs',
            'student',
            'data',
            'outputs' => fn ($q) => $q->orderByDesc('id'),
            'signaturePlacements' => fn ($q) => $q->orderByDesc('id'),
        ]);

        // Always use latest output to avoid stale links opening previously broken files.
        $selectedOutput = $request->outputs->first();
        if ($selectedOutput && strtoupper((string) $selectedOutput->output_type) !== 'PDF') {
            $selectedOutput = $this->assembler->ensurePdfOutput($selectedOutput) ?? $selectedOutput;
        }

        return view('staff.assemble.show', [
            'req' => $request,
            'selectedOutput' => $selectedOutput,
        ]);
    }

    public function preview(UltRequest $request, Request $http)
    {
        Gate::authorize('process', $request);
        if (!$this->canAssemble($request)) {
            return redirect()
                ->route('admin.requests.show', $request)
                ->with('warning', 'Finalisasi dokumen hanya bisa dilakukan saat status READY_FOR_FINAL.');
        }

        $placements = $this->parsePlacementsFromForm($http);

        $result = $this->assembler->preview($request, $http->user(), $placements);

        return redirect()
            ->route('staff.assemble.show', ['request' => $request])
            ->with('status', 'Preview generated.');
    }

    public function finalize(UltRequest $request, Request $http)
    {
        Gate::authorize('process', $request);
        if (!$this->canAssemble($request)) {
            return redirect()
                ->route('admin.requests.show', $request)
                ->with('warning', 'Finalisasi dokumen hanya bisa dilakukan saat status READY_FOR_FINAL.');
        }

        $placements = $this->parsePlacementsFromForm($http);

        $output = $this->assembler->finalize($request, $http->user(), $placements);
        $request->refresh();

        $message = 'Final output generated. Output ID: '.$output->id;

        return redirect()->route('admin.requests.show', $request)->with('status', $message);
    }

    public function inlineOutput(UltRequest $request, RequestOutput $output)
    {
        Gate::authorize('process', $request);
        abort_unless((int) $output->request_id === (int) $request->id, 404);

        // Force latest generated output to avoid serving stale broken files.
        $latest = $request->outputs()->orderByDesc('id')->first();
        if ($latest instanceof RequestOutput) {
            if (strtoupper((string) $latest->output_type) !== 'PDF') {
                $output = $this->assembler->ensurePdfOutput($latest) ?? $latest;
            } else {
                $output = $latest;
            }
        }

        $disk = config('ult.private_disk');
        $ext = strtolower(pathinfo((string) $output->file_path, PATHINFO_EXTENSION));
        $mime = $ext === 'pdf'
            ? 'application/pdf'
            : 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        $filename = $this->assembler->resolveOutputDownloadFilename($output);

        return response()->file(
            Storage::disk($disk)->path((string) $output->file_path),
            [
                'Content-Type' => $mime,
                'Content-Disposition' => 'inline; filename="'.$filename.'"',
                'X-Frame-Options' => 'SAMEORIGIN',
            ]
        );
    }

    /**
     * @return array<int,array{signer_role:string,page_number:int,x_pt:float,y_pt:float,width_pt:float,height_pt:float}>
     */
    private function parsePlacementsFromForm(Request $http): array
    {
        $useManualPlacement = $http->boolean('use_manual_placement');
        if (!$useManualPlacement) {
            return [];
        }

        $data = $http->validate([
            'use_manual_placement' => ['nullable', 'boolean'],
            'placements' => ['required', 'array', 'min:1'],
            'placements.*.signer_role' => ['required', 'string', 'max:80'],
            'placements.*.page_number' => ['required', 'integer', 'min:1'],
            'placements.*.x_pt' => ['required', 'numeric', 'min:0'],
            'placements.*.y_pt' => ['required', 'numeric', 'min:0'],
            'placements.*.width_pt' => ['required', 'numeric', 'min:1'],
            'placements.*.height_pt' => ['required', 'numeric', 'min:1'],
        ]);

        $placements = [];
        foreach (($data['placements'] ?? []) as $row) {
            $placements[] = [
                'signer_role' => (string) $row['signer_role'],
                'page_number' => (int) $row['page_number'],
                'x_pt' => (float) $row['x_pt'],
                'y_pt' => (float) $row['y_pt'],
                'width_pt' => (float) $row['width_pt'],
                'height_pt' => (float) $row['height_pt'],
            ];
        }

        return $placements;
    }

    private function canAssemble(UltRequest $request): bool
    {
        $status = $request->current_status;
        if ($status instanceof RequestStatus) {
            return $status === RequestStatus::READY_FOR_FINAL;
        }

        return (string) $status === RequestStatus::READY_FOR_FINAL->value;
    }
}
