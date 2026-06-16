<?php

namespace App\Policies;

use App\Models\RequestOutput;
use App\Models\User;

class RequestOutputPolicy
{
    public function download(User $user, RequestOutput $output): bool
    {
        if (!$user->can('attachments.download_private')) return false;

        return $user->can('view', $output->request);
    }
}

