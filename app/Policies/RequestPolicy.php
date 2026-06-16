<?php

namespace App\Policies;

use App\Models\Request as UltRequest;
use App\Models\User;
use App\Models\Unit;

class RequestPolicy
{
    public function view(User $user, UltRequest $request): bool
    {
        if ($user->can('requests.view_any')) return true;

        if ($user->can('requests.view_own') && $request->student_id === $user->id) return true;

        if ($user->can('requests.view_unit') && $this->inUnitScope($user, $request)) return true;

        // ULT gatekeeper should at least be able to view items it can review.
        if ($user->can('requests.review_ult') && $this->inUltScope($user, $request)) return true;

        // Document module signer access: allow viewing only if user is the active signer step.
        if ($this->isActiveDocumentSigner($user, $request)) return true;

        return false;
    }

    public function update(User $user, UltRequest $request): bool
    {
        // Student revision only on own request
        if ($user->can('requests.update_own') && $request->student_id === $user->id) return true;
        return false;
    }

    public function process(User $user, UltRequest $request): bool
    {
        if ($user->can('requests.process_unit') && $this->inUnitScope($user, $request)) return true;
        if ($user->can('requests.review_ult') && $this->inUltScope($user, $request)) return true; // ULT gatekeeper (faculty-scoped)
        return false;
    }

    public function approve(User $user, UltRequest $request): bool
    {
        // Approver unit/faculty depending on assigned permissions and unit scope
        if ($user->can('approvals.unit.sign') && $this->inUnitScope($user, $request)) return true;
        if ($user->can('approvals.faculty.sign') && $this->isFaculty($user) && $this->inUltScope($user, $request)) return true;
        return false;
    }

    public function reviewUlt(User $user, UltRequest $request): bool
    {
        return $user->can('requests.review_ult') && $this->inUltScope($user, $request);
    }

    public function issueNumber(User $user, UltRequest $request): bool
    {
        return $user->can('document_numbers.issue') && $this->inUltScope($user, $request);
    }

    private function inUnitScope(User $user, UltRequest $request): bool
    {
        if (!$request->current_unit_id) return false;

        $scope = UltRequest::resolveUnitAccessScope($user);
        $allowed = $scope['allowed_unit_ids'] ?? [];
        if (empty($allowed)) return false;
        if (!in_array((int) $request->current_unit_id, $allowed, true)) return false;

        $scopedProdi = $scope['scoped_prodi_ids'] ?? [];
        if (empty($scopedProdi)) return true;

        $studentUnitId = (int) ($request->student?->unit_id ?? 0);
        return $studentUnitId > 0 && in_array($studentUnitId, $scopedProdi, true);
    }

    private function inUltScope(User $user, UltRequest $request): bool
    {
        if (!$user->unit_id || !$request->current_unit_id) return false;
        $unit = $user->unit;
        if (!$unit) return false;

        $fakultas = $unit->ancestorOfType(\App\Enums\UnitType::fakultas) ?? $unit;
        $allowed = array_values(array_unique(array_merge([$fakultas->id], $fakultas->descendantIdsCached())));
        return in_array($request->current_unit_id, $allowed, true);
    }

    private function isFaculty(User $user): bool
    {
        return $user->unit?->type?->value === 'fakultas';
    }

    private function isActiveDocumentSigner(User $user, UltRequest $request): bool
    {
        if (!$request->service_id) return false;
        if (!$request->current_signer_order_index) return false;

        $request->loadMissing(['service.signers', 'signoffs']);

        $currentIndex = (int) $request->current_signer_order_index;
        $signoff = $request->signoffs?->firstWhere('order_index', $currentIndex);
        if ($signoff?->signer_user_id && (int) $signoff->signer_user_id === (int) $user->id) {
            return true;
        }

        $signer = $request->service?->signers?->firstWhere('order_index', $currentIndex);
        if (!$signer) return false;

        return $user->can('doc_signoffs.decide') && $user->matchesSignerRole((string) $signer->role);
    }
}
