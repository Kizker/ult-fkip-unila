<?php

namespace App\Policies;

use App\Models\LetterNumberFormat;
use App\Models\Unit;
use App\Models\User;

class LetterNumberFormatPolicy
{
    /**
     * @var array<string,array<int,int>>
     */
    private static array $viewScopeCache = [];

    public function viewAny(User $user): bool
    {
        return $user->can('letter_numbers.manage_formats');
    }

    public function view(User $user, LetterNumberFormat $format): bool
    {
        if ($this->manage($user, $format)) {
            return true;
        }

        if ($user->can('requests.view_any')) {
            return true;
        }

        if (!$user->can('letter_numbers.manage_formats')) {
            return false;
        }

        return in_array((int) $format->unit_id, $this->viewScopeUnitIds($user), true);
    }

    public function create(User $user): bool
    {
        return $user->can('letter_numbers.manage_formats');
    }

    public function update(User $user, LetterNumberFormat $format): bool
    {
        return $this->manage($user, $format);
    }

    public function delete(User $user, LetterNumberFormat $format): bool
    {
        return $this->manage($user, $format);
    }

    private function manage(User $user, LetterNumberFormat $format): bool
    {
        if ($user->can('requests.view_any')) return true;
        if (!$user->can('letter_numbers.manage_formats')) return false;

        $userUnit = $user->unit;
        if (!$userUnit) return false;

        $allowed = array_merge([$userUnit->id], $userUnit->descendantIdsCached(600));
        return in_array($format->unit_id, $allowed, true);
    }

    /**
     * View scope includes inherited parent units.
     *
     * @return array<int,int>
     */
    private function viewScopeUnitIds(User $user): array
    {
        $cacheKey = 'user_'.$user->id;
        if (array_key_exists($cacheKey, self::$viewScopeCache)) {
            return self::$viewScopeCache[$cacheKey];
        }

        $baseIds = [];
        try {
            $scope = \App\Models\Request::resolveUnitAccessScope($user);
            $baseIds = array_values(array_unique(array_map('intval', (array) ($scope['allowed_unit_ids'] ?? []))));
        } catch (\Throwable $e) {
            $baseIds = [];
        }

        if (empty($baseIds) && $user->unit_id) {
            $baseIds = [(int) $user->unit_id];
        }

        $allIds = [];
        foreach ($baseIds as $id) {
            $unit = Unit::query()->find((int) $id);
            $guard = 0;
            while ($unit && $guard < 20) {
                $allIds[] = (int) $unit->id;
                $unit = $unit->parent;
                $guard++;
            }
        }

        $allIds = array_values(array_unique(array_map('intval', $allIds)));
        self::$viewScopeCache[$cacheKey] = $allIds;

        return $allIds;
    }
}
