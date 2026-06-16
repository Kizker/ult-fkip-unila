<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\RequestStatus;
use App\Models\Approval;
use App\Models\Request as UltRequest;
use App\Models\RequestStatusHistory;
use App\Models\ServiceWorkflow;
use App\Models\Unit;
use App\Models\User;
use App\Notifications\RequestStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;

class RequestWorkflowService
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly DocumentNumberService $docNumbers,
    ) {}

    /**
     * Workflow Design Choice (5.1):
     * Opsi A: steps_json di service_workflows (lebih fleksibel dan maintainable untuk perubahan tanpa ubah kode).
     * Konsekuensi: validasi schema steps_json harus ketat di backend; pengelolaan versi schema perlu disiplin.
     */
    public function getSteps(ServiceWorkflow $wf): array
    {
        return $wf->steps_json ?? [];
    }

    /**
     * Apply a workflow action (verify/review/sign/issue_number/upload_output/complete/request_revision/reject/forward_faculty)
     * based on service_workflows.steps_json.
     *
     * This avoids arbitrary status jumps and keeps workflow dynamic.
     */
    public function applyAction(UltRequest $request, string $action, User $actor, ?string $note = null): UltRequest
    {
        $wf = $request->service->workflow;
        if (!$wf) {
            throw new \RuntimeException('Workflow belum dikonfigurasi untuk layanan ini.');
        }

        $steps = $this->getSteps($wf);
        $currentKey = $request->current_step_key ?: ($steps[0]['key'] ?? null);
        if (!$currentKey) {
            throw new \RuntimeException('Step workflow tidak valid.');
        }

        $step = $this->findStep($steps, $currentKey);
        if (!$step) {
            throw new \RuntimeException('Step workflow tidak ditemukan: '.$currentKey);
        }

        $allowed = $step['actions_allowed'] ?? [];
        if (!in_array($action, $allowed, true)) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Aksi tidak diizinkan pada step ini.');
        }

        // Action branches
        if ($action === 'request_revision') {
            return $this->transition(
                $request,
                RequestStatus::PERLU_PERBAIKAN,
                $actor,
                $note ?: 'Perlu perbaikan',
                $currentKey,
                $this->resolveUnitForScope($request, $step['unit_scope'] ?? 'by_request')
            );
        }

        if ($action === 'reject') {
            return $this->transition(
                $request,
                RequestStatus::DITOLAK,
                $actor,
                $note ?: 'Ditolak',
                $currentKey,
                $this->resolveUnitForScope($request, $step['unit_scope'] ?? 'by_request')
            );
        }

        if ($action === 'forward_faculty') {
            // Gatekeeper enforcement handled in transition()
            return $this->transition(
                $request,
                RequestStatus::MENUNGGU_TTD_FAKULTAS,
                $actor,
                $note ?: 'Diteruskan ke fakultas',
                $currentKey,
                $this->resolveUnitForScope($request, 'fakultas')
            );
        }

        if ($action === 'issue_number') {
            // move to the correct step status first (if needed)
            $request = $this->transition(
                $request,
                $this->statusForStepKey('ult_issue'),
                $actor,
                $note ?: 'Penomoran',
                'ult_issue',
                $this->resolveUnitForScope($request, 'fakultas')
            );
            $this->maybeIssueDocumentNumber($request, $actor);

            // Decide next step after numbering
            $nextKey = $this->resolveNextKey($steps, $currentKey, $step);
            if ($nextKey) {
                return $this->transitionToStep($request, $nextKey, $actor, 'Lanjut setelah penomoran');
            }
            return $request;
        }

        if ($action === 'upload_output') {
            // upload happens via AttachmentController; this action just sets status progression if needed
            return $this->transition(
                $request,
                RequestStatus::DIPROSES,
                $actor,
                $note ?: 'Output diunggah',
                $currentKey,
                $this->resolveUnitForScope($request, $step['unit_scope'] ?? 'by_request')
            );
        }

        if ($action === 'complete') {
            // "Complete" can be used as a step completion (go to next step) or finalization if no next step.
            $nextKey = $this->resolveNextKey($steps, $currentKey, $step);
            if ($nextKey) {
                return $this->transitionToStep($request, $nextKey, $actor, $note ?: 'Selesai');
            }

            return $this->transition(
                $request,
                RequestStatus::SELESAI,
                $actor,
                $note ?: 'Selesai',
                'done',
                $this->resolveUnitForScope($request, $step['unit_scope'] ?? 'by_request')
            );
        }

        // Default approval-like actions: verify, review, sign
        $nextKey = $this->resolveNextKey($steps, $currentKey, $step);
        if (!$nextKey) {
            return $this->transition(
                $request,
                RequestStatus::SELESAI,
                $actor,
                $note ?: 'Selesai',
                'done',
                $this->resolveUnitForScope($request, $step['unit_scope'] ?? 'by_request')
            );
        }

        // Record approval where relevant (unit/faculty signature steps)
        if (in_array($action, ['sign'], true)) {
            $roleName = (string)($step['role_required'] ?? 'Approver');
            $this->recordApproval($request, $currentKey, $roleName, $actor, ApprovalStatus::approved, $note, $request->current_unit_id);
        }

        return $this->transitionToStep($request, $nextKey, $actor, $note);
    }

    private function resolveNextKey(array $steps, string $currentKey, array $step): ?string
    {
        $explicit = $step['next_on_approve'] ?? null;
        if (is_string($explicit) && trim($explicit) !== '') {
            return trim($explicit);
        }

        $idx = null;
        foreach ($steps as $i => $s) {
            if (($s['key'] ?? null) === $currentKey) {
                $idx = (int) $i;
                break;
            }
        }
        if ($idx === null) return null;

        $next = $steps[$idx + 1]['key'] ?? null;
        return is_string($next) && trim($next) !== '' ? trim($next) : null;
    }

    public function transitionToStep(UltRequest $request, string $toStepKey, User $actor, ?string $note = null): UltRequest
    {
        $wf = $request->service->workflow;
        $steps = $wf ? $this->getSteps($wf) : [];
        $toStep = $this->findStep($steps, $toStepKey);
        if (!$toStep) {
            throw new \RuntimeException('Target step tidak ditemukan: '.$toStepKey);
        }

        $toStatus = $this->statusForStepKey($toStepKey);
        $unit = $this->resolveUnitForScope($request, (string)($toStep['unit_scope'] ?? 'by_request'));

        return $this->transition($request, $toStatus, $actor, $note, $toStepKey, $unit, ['action' => 'auto_next']);
    }

    private function findStep(array $steps, string $key): ?array
    {
        foreach ($steps as $s) {
            if (($s['key'] ?? null) === $key) return $s;
        }
        return null;
    }

    private function statusForStepKey(string $key): RequestStatus
    {
        return match ($key) {
            'prodi_verify', 'jurusan_verify' => RequestStatus::DIVERIFIKASI_UNIT,
            'ult_review', 'ult_process' => RequestStatus::REVIEW_ULT,
            'faculty_sign' => RequestStatus::MENUNGGU_TTD_FAKULTAS,
            'ult_issue' => RequestStatus::DIPROSES,
            'output' => RequestStatus::DIPROSES,
            'done' => RequestStatus::SELESAI,
            default => RequestStatus::DIPROSES,
        };
    }

    private function resolveUnitForScope(UltRequest $request, string $scope): ?Unit
    {
        $studentUnit = $request->student?->unit;
        if (!$studentUnit) {
            return $request->currentUnit ?: Unit::where('type', 'fakultas')->first();
        }

        return match ($scope) {
            'prodi' => $studentUnit->ancestorOfType(\App\Enums\UnitType::prodi) ?? $studentUnit,
            'jurusan' => $studentUnit->ancestorOfType(\App\Enums\UnitType::jurusan) ?? $studentUnit->parent,
            'fakultas' => $studentUnit->ancestorOfType(\App\Enums\UnitType::fakultas) ?? Unit::where('type', 'fakultas')->first(),
            'by_request' => $request->currentUnit,
            default => $request->currentUnit,
        };
    }

    public function transition(
        UltRequest $request,
        RequestStatus $to,
        User $actor,
        ?string $note = null,
        ?string $stepKey = null,
        ?Unit $unitContext = null,
        array $meta = []
    ): UltRequest {
        return DB::transaction(function () use ($request, $to, $actor, $note, $stepKey, $unitContext, $meta) {
            $from = $request->current_status;
            $wf = $request->service->workflow;

            // Gatekeeper ULT: only Staf ULT can move REVIEW_ULT -> MENUNGGU_TTD_FAKULTAS when require_faculty_signature
            if ($wf && $wf->require_faculty_signature && $from === RequestStatus::REVIEW_ULT && $to === RequestStatus::MENUNGGU_TTD_FAKULTAS) {
                if (!$actor->can('requests.review_ult')) {
                    throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'Gatekeeper ULT: hanya Staf ULT.');
                }
            }

            // Document module hard rules: cannot enter signing without nomor_surat.
            if ($to === RequestStatus::IN_SIGNING && empty($request->nomor_surat)) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'NOMOR_SURAT wajib diisi sebelum masuk signing.');
            }

            $request->current_status = $to;
            if ($stepKey !== null) $request->current_step_key = $stepKey;
            if ($unitContext !== null) $request->current_unit_id = $unitContext->id;

            if ($to === RequestStatus::DIAJUKAN && !$request->submitted_at) {
                $request->submitted_at = now();
            }

            if ($to === RequestStatus::SELESAI || $to === RequestStatus::COMPLETED) {
                $request->completed_at = now();
            }
            if ($to === RequestStatus::DITOLAK || $to === RequestStatus::DITOLAK_ADMIN || $to === RequestStatus::REJECTED_IN_SIGNING) {
                $request->rejected_at = now();
            }

            $request->save();

            RequestStatusHistory::create([
                'request_id' => $request->id,
                'from_status' => $from->value,
                'to_status' => $to->value,
                'step_key' => $stepKey ?: $request->current_step_key,
                'note' => $note,
                'actor_id' => $actor->id,
                'created_at' => now(),
            ]);

            $this->audit->log('request.status_changed', 'requests', (string) $request->id, [
                'from' => $from->value,
                'to' => $to->value,
                'step_key' => $request->current_step_key,
                'meta' => $meta,
            ]);

            // Notify student + unit actors (minimal)
            Notification::send($this->notificationTargets($request), new RequestStatusChanged($request, $from, $to, $note));

            return $request->fresh(['service', 'student', 'currentUnit']);
        });
    }
    public function recordApproval(UltRequest $request, string $stepKey, string $roleName, User $actor, ApprovalStatus $status, ?string $note = null, ?int $unitScopeId = null): Approval
    {
        return DB::transaction(function () use ($request, $stepKey, $roleName, $actor, $status, $note, $unitScopeId) {
            $approval = Approval::updateOrCreate(
                ['request_id' => $request->id, 'step_key' => $stepKey, 'role_name' => $roleName],
                [
                    'unit_id_scope' => $unitScopeId,
                    'approver_id' => $actor->id,
                    'status' => $status->value,
                    'note' => $note,
                    'decided_at' => now(),
                ]
            );

            $this->audit->log('approval.decided', 'approvals', (string) $approval->id, [
                'request_id' => $request->id,
                'step_key' => $stepKey,
                'role_name' => $roleName,
                'status' => $status->value,
            ]);

            return $approval;
        });
    }

    public function maybeIssueDocumentNumber(UltRequest $request, User $actor): ?string
    {
        $wf = $request->service->workflow;
        if (!$wf || !$wf->issue_number_at_step) return null;

        if ($request->current_step_key !== $wf->issue_number_at_step) return null;

        // permission gate
        if (!$actor->can('document_numbers.issue')) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(403, 'Tidak punya izin terbitkan nomor.');
        }

        // Otomatis menautkan penomoran ke format Fakultas
        $unit = Unit::where('type', 'fakultas')->first();
        if (!$unit) {
            throw new \RuntimeException('Unit untuk penomoran tidak ditemukan.');
        }

        $doc = $this->docNumbers->issue($request, $unit, config('ult.doc_number_format_key', 'default'));

        // move status
        $this->transition($request, RequestStatus::NOMOR_DOKUMEN_TERBIT, $actor, 'Nomor dokumen diterbitkan', $request->current_step_key, $unit, [
            'doc_number' => $this->docNumbers->renderNumber($doc),
        ]);

        return $this->docNumbers->renderNumber($doc);
    }

    public function notificationTargets(UltRequest $request): array
    {
        $request->loadMissing([
            'student',
            'service.signers',
            'signoffs',
            'currentUnit.parent',
        ]);

        $studentId = (int) ($request->student_id ?? 0);
        $permissionNames = $this->notificationPermissionNames();
        $signerRoles = $request->service?->signers
            ?->pluck('role')
            ->filter(fn ($v) => is_string($v) && trim($v) !== '')
            ->map(fn ($v) => trim((string) $v))
            ->unique()
            ->values()
            ->all() ?? [];

        $explicitSignerIds = collect($request->signoffs ?? [])
            ->flatMap(function ($s) {
                return [
                    (int) ($s->signer_user_id ?? 0),
                    (int) ($s->decided_by ?? 0),
                ];
            })
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        $candidates = User::query()
            ->where(function ($q) use ($studentId, $explicitSignerIds, $signerRoles, $permissionNames) {
                if ($studentId > 0) {
                    $q->whereKey($studentId);
                }

                if (!empty($explicitSignerIds)) {
                    $q->orWhereIn('id', $explicitSignerIds);
                }

                if (!empty($signerRoles)) {
                    $q->orWhereHas('roles', fn ($r) => $r->whereIn('name', $signerRoles));
                }

                $q->orWhereHas('roles', fn ($r) => $r->where('name', 'Superadmin'));
                $q->orWhereHas('permissions', fn ($p) => $p->whereIn('name', $permissionNames));
                $q->orWhereHas('roles.permissions', fn ($p) => $p->whereIn('name', $permissionNames));
            })
            ->get();

        $targets = [];
        foreach ($candidates as $u) {
            $uid = (int) $u->id;
            $isStudent = $studentId > 0 && $uid === $studentId;
            $isExplicitSigner = in_array($uid, $explicitSignerIds, true);
            $isRoleSigner = !empty($signerRoles) && $u->matchesAnySignerRole($signerRoles) && $u->can('doc_signoffs.decide');
            $isOperator = $u->hasRole('Superadmin') || $u->hasAnyPermission($permissionNames);
            $hasScopedStudentAccess = $this->userHasScopedStudentAccess($u, $request);

            $canViewRelatedRequest = Gate::forUser($u)->allows('view', $request)
                || Gate::forUser($u)->allows('process', $request)
                || Gate::forUser($u)->allows('reviewUlt', $request);

            if (
                $isStudent
                || $isExplicitSigner
                || $isRoleSigner
                || ($isOperator && ($canViewRelatedRequest || $hasScopedStudentAccess))
            ) {
                $targets[$uid] = $u;
            }
        }

        if ($request->student) {
            $targets[(int) $request->student->id] = $request->student;
        }

        return array_values($targets);
    }

    /**
     * @return array<int,string>
     */
    private function notificationPermissionNames(): array
    {
        return [
            'requests.view_any',
            'requests.view_unit',
            'requests.review_ult',
            'requests.process_unit',
            'requests.forward_faculty',
            'approvals.unit.sign',
            'approvals.faculty.sign',
            'document_numbers.issue',
            'attachments.upload_output',
            'attachments.download_private',
            'doc_requests.gate',
            'doc_requests.assemble',
            'doc_signoffs.decide',
        ];
    }

    private function userHasScopedStudentAccess(User $user, UltRequest $request): bool
    {
        if (!$user->can('requests.view_unit')) {
            return false;
        }

        $studentUnitId = (int) ($request->student?->unit_id ?? 0);
        if ($studentUnitId < 1) {
            return false;
        }

        $scope = UltRequest::resolveUnitAccessScope($user);
        $scopedProdi = array_values(array_map('intval', (array) ($scope['scoped_prodi_ids'] ?? [])));
        if (empty($scopedProdi)) {
            return false;
        }

        return in_array($studentUnitId, $scopedProdi, true);
    }
}
