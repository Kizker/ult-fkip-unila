<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\RequestStatus;
use App\Enums\ServiceTemplateType;
use App\Enums\UnitType;
use App\Http\Controllers\Controller;
use App\Models\LetterNumberFormat;
use App\Models\Request as UltRequest;
use App\Models\Service;
use App\Models\Unit;
use App\Models\User;
use App\Services\RequestWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;

class RequestAdminController extends Controller
{
    public function __construct(
        private readonly RequestWorkflowService $workflow,
    ) {}

    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));
        $allowedStatuses = array_map(static fn (RequestStatus $st) => $st->value, RequestStatus::cases());
        if ($status !== '' && !in_array($status, $allowedStatuses, true)) {
            $status = '';
        }
        $serviceId = $request->query('service_id');
        $unitId = $request->query('unit_id');
        $from = $request->query('from');
        $to = $request->query('to');

        $items = UltRequest::query()
            ->forUser($request->user())
            ->with(['service','student','currentUnit'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('requests.nomor_surat', 'like', "%{$q}%")
                        ->orWhere('requests.current_status', 'like', "%{$q}%")
                        ->orWhere('requests.activity_title', 'like', "%{$q}%")
                        ->orWhereHas('service', function ($s) use ($q) {
                            $s->where('title_id', 'like', "%{$q}%")
                                ->orWhere('title_en', 'like', "%{$q}%");
                        })
                        ->orWhereHas('student', function ($u) use ($q) {
                            $u->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%")
                                ->orWhere('student_number', 'like', "%{$q}%");
                        })
                        ->orWhereHas('currentUnit', fn($unit) => $unit->where('name', 'like', "%{$q}%"));

                    if (ctype_digit($q)) {
                        $w->orWhere('requests.id', (int) $q);
                    }
                });
            })
            ->when($status !== '', fn($q) => $q->where('current_status', $status))
            ->when($serviceId, fn($q) => $q->where('service_id', $serviceId))
            ->when($unitId, fn($q) => $q->where('current_unit_id', $unitId))
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $services = Service::query()->orderBy('title_id')->get();

        return view('admin.requests.index', compact('items','services','q','status','serviceId','unitId','from','to'));
    }

    public function show(UltRequest $request)
    {
        Gate::authorize('view', $request);

        $request->load([
            'service.workflow',
            'service.fields',
            'service.templates',
            'service.placeholders',
            'service.signers',
            'student.unit.parent',
            'attachments',
            'fieldValues.field',
            'histories.actor',
            'notes.actor',
            'approvals.approver',
            'documentNumber',
            'letterNumber',
            'data',
            'signoffs',
            'outputs',
        ]);

        $snapshot = is_array($request->data?->document_snapshot_json) ? $request->data->document_snapshot_json : [];
        $snapshotTemplatePath = trim((string) data_get($snapshot, 'template.file_path', ''));

        $isDocService = (bool) (
            $request->service?->usesRequestPptxSource()
            || $request->service?->templates?->firstWhere('type', ServiceTemplateType::MAIN_DOCX)
            || $snapshotTemplatePath !== ''
        );
        $letterFormats = collect();
        $letterApplicableUnits = collect();
        $canInitialGateActions = false;
        $initialGateRoleLabel = '';

        if ($isDocService && Schema::hasTable('letter_number_formats')) {
            $letterApplicableUnits = collect($this->resolveLetterFormatScopeUnits($request->student?->unit));
            if ($letterApplicableUnits->isNotEmpty()) {
                $unitIds = $letterApplicableUnits->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
                $letterFormats = LetterNumberFormat::query()
                    ->active()
                    ->with('unit')
                    ->whereIn('unit_id', $unitIds)
                    ->orderByRaw($this->caseOrderSql($unitIds, 'unit_id'))
                    ->orderBy('format_key')
                    ->get();
            }
        }

        if ($isDocService) {
            $request->loadMissing('service.workflow');
            $statusValue = $request->current_status instanceof RequestStatus
                ? $request->current_status->value
                : (string) $request->current_status;
            $initialGateRoleLabel = $this->normalizeGateRole((string) ($request->service?->workflow?->gate_role ?? ''));

            if (in_array($statusValue, [RequestStatus::DIAJUKAN->value, RequestStatus::PERLU_PERBAIKAN->value], true)) {
                $actor = auth()->user();
                if ($actor) {
                    $canInitialGateActions = Gate::forUser($actor)->allows('process', $request);
                    if (
                        $canInitialGateActions
                        && !$actor->hasRole('Superadmin')
                        && $initialGateRoleLabel !== ''
                    ) {
                        $canInitialGateActions = $this->actorMatchesGateRole($actor, $initialGateRoleLabel);
                    }
                }
            }
        }

        return view('admin.requests.show', [
            'req' => $request,
            'letterFormats' => $letterFormats,
            'letterApplicableUnits' => $letterApplicableUnits,
            'canInitialGateActions' => $canInitialGateActions,
            'initialGateRoleLabel' => $initialGateRoleLabel,
        ]);
    }

    public function changeStatus(UltRequest $request, Request $http)
    {
        Gate::authorize('process', $request);

        $data = $http->validate([
            'to_status' => ['required','string'],
            'note' => ['nullable','string','max:2000'],
        ]);

        $to = RequestStatus::from($data['to_status']);

        $this->workflow->transition($request, $to, $http->user(), $data['note'], $request->current_step_key, $request->currentUnit);

        return back()->with('status', __('app.status_updated'));
    }

    /**
     * Preferred endpoint: apply a workflow action based on steps_json.
     * Prevents arbitrary status jumps.
     */
    public function action(UltRequest $request, Request $http)
    {
        $data = $http->validate([
            'action' => ['required','string','max:64'],
            'note' => ['nullable','string','max:2000'],
        ]);

        // Authorize based on action type (avoid over-permissioning)
        if ($data['action'] === 'sign') {
            Gate::authorize('approve', $request);
        } elseif ($data['action'] === 'forward_faculty') {
            Gate::authorize('reviewUlt', $request);
        } elseif ($data['action'] === 'issue_number') {
            Gate::authorize('issueNumber', $request);
        } else {
            Gate::authorize('process', $request);
        }

        // Some actions are ULT-only; policy is enforced in workflow service gatekeeper and permissions.
        $this->workflow->applyAction($request, $data['action'], $http->user(), $data['note'] ?? null);

        return back()->with('status', __('app.status_updated'));
    }

    public function approve(UltRequest $request, Request $http)
    {
        $request->loadMissing('service.templates');
        $isDocService = (bool) (
            $request->service?->usesRequestPptxSource()
            || $request->service?->templates?->firstWhere('type', ServiceTemplateType::MAIN_DOCX)
        );

        if ($isDocService) {
            // Doc workflow approval assignment is ULT-only by default.
            // Non-ULT roles can be allowed by granting requests.review_ult permission.
            Gate::authorize('reviewUlt', $request);
        } else {
            Gate::authorize('approve', $request);
        }

        $data = $http->validate([
            'status' => ['required','in:approved,rejected'],
            'note' => ['nullable','string','max:2000'],
            'role_name' => ['required','string','max:120'],
        ]);

        $status = ApprovalStatus::from($data['status']);
        $approval = $this->workflow->recordApproval($request, $request->current_step_key ?? 'step', $data['role_name'], $http->user(), $status, $data['note'], $request->current_unit_id);

        // naive next status decision:
        if ($status === ApprovalStatus::approved) {
            // if current status waiting for faculty signature, move to DIPROSES
            if ($request->current_status === RequestStatus::MENUNGGU_TTD_FAKULTAS || $request->current_status === RequestStatus::MENUNGGU_TTD_UNIT) {
                $this->workflow->transition($request, RequestStatus::DIPROSES, $http->user(), 'Approval approved', $request->current_step_key, $request->currentUnit);
            }
        } else {
            $this->workflow->transition($request, RequestStatus::DITOLAK, $http->user(), 'Approval rejected: '.$data['note'], $request->current_step_key, $request->currentUnit);
        }

        return back()->with('status', __('app.approval_saved'));
    }

    public function forwardFaculty(UltRequest $request, Request $http)
    {
        Gate::authorize('reviewUlt', $request);

        $data = $http->validate(['note' => ['nullable','string','max:2000']]);

        $this->workflow->transition($request, RequestStatus::MENUNGGU_TTD_FAKULTAS, $http->user(), $data['note'] ?? 'Diteruskan ke fakultas', $request->current_step_key, $request->currentUnit);

        return back()->with('status', __('app.forwarded_faculty'));
    }

    public function issueNumber(UltRequest $request, Request $http)
    {
        Gate::authorize('issueNumber', $request);

        $num = $this->workflow->maybeIssueDocumentNumber($request, $http->user());

        return back()->with('status', $num ? __('app.number_issued').': '.$num : __('app.number_not_issued'));
    }

    /**
     * Resolve applicable numbering scope for applicant hierarchy.
     *
     * Priority order: prodi -> jurusan -> fakultas.
     *
     * @return array<int,Unit>
     */
    private function resolveLetterFormatScopeUnits(?Unit $studentUnit): array
    {
        if (!$studentUnit) {
            return [];
        }

        $units = [
            $studentUnit->ancestorOfType(UnitType::prodi),
            $studentUnit->ancestorOfType(UnitType::jurusan),
            $studentUnit->ancestorOfType(UnitType::fakultas),
        ];

        $out = [];
        foreach ($units as $unit) {
            if (!$unit) {
                continue;
            }

            $exists = false;
            foreach ($out as $item) {
                if ((int) $item->id === (int) $unit->id) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $out[] = $unit;
            }
        }

        return $out;
    }

    /**
     * Build deterministic CASE order by preferred unit IDs.
     *
     * @param array<int,int> $ids
     */
    private function caseOrderSql(array $ids, string $column): string
    {
        if (empty($ids)) {
            return '1';
        }

        $sql = "CASE {$column} ";
        foreach (array_values($ids) as $idx => $id) {
            $id = (int) $id;
            $sql .= "WHEN {$id} THEN {$idx} ";
        }
        $sql .= 'ELSE 999 END';

        return $sql;
    }

    private function normalizeGateRole(string $raw): string
    {
        $normalized = strtoupper(str_replace(' ', '_', trim($raw)));
        return match ($normalized) {
            'ADMIN_JURUSAN',
            'ADMIN_JURUSAN_PER_PRODI',
            'ADMIN_PRODI' => 'Admin Jurusan',
            'STAF_ULT',
            'STAFF_ULT' => 'Staf ULT',
            default => trim($raw),
        };
    }

    private function actorMatchesGateRole(User $actor, string $requiredRole): bool
    {
        $normalized = strtoupper(str_replace(' ', '_', trim($requiredRole)));

        return match ($normalized) {
            'ADMIN_JURUSAN' => $actor->hasAnyRole([
                'Admin Jurusan',
                'Admin Jurusan per Prodi',
                'Admin Prodi',
            ]),
            'STAF_ULT' => $actor->hasAnyRole([
                'Staf ULT',
                'Staff ULT',
            ]),
            default => $actor->hasRole($requiredRole),
        };
    }
}
