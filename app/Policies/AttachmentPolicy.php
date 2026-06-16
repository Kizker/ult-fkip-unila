<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;

class AttachmentPolicy
{
    public function download(User $user, Attachment $attachment): bool
    {
        // Security-by-default:
        // Download allowed ONLY if:
        // 1) user has explicit download permission, AND
        // 2) user is authorized to view the parent request (ownership/unit scope/policy).
        if (!$user->can('attachments.download_private')) return false;

        return $user->can('view', $attachment->request);
    }
}
