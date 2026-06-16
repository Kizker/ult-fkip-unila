<?php

namespace App\Policies;

use App\Models\DocumentNumberFormat;
use App\Models\User;

class DocumentNumberFormatPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('document_numbers.manage_formats');
    }

    public function view(User $user, DocumentNumberFormat $format): bool
    {
        return $this->manage($user, $format);
    }

    public function create(User $user): bool
    {
        return $user->can('document_numbers.manage_formats');
    }

    public function update(User $user, DocumentNumberFormat $format): bool
    {
        return $this->manage($user, $format);
    }

    public function delete(User $user, DocumentNumberFormat $format): bool
    {
        return $this->manage($user, $format);
    }

    private function manage(User $user, DocumentNumberFormat $format): bool
    {
        if ($user->can('requests.view_any')) return true; // Superadmin or high-priv
        if (!$user->can('document_numbers.manage_formats')) return false;

        $userUnit = $user->unit;
        if (!$userUnit) return false;

        $allowed = array_merge([$userUnit->id], $userUnit->descendantIdsCached(600));
        return in_array($format->unit_id, $allowed, true);
    }
}
