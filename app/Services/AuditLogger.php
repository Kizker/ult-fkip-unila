<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function log(
        string $action,
        ?string $entityType = null,
        ?string $entityId = null,
        array $metadata = [],
        ?Request $request = null,
        ?Authenticatable $actor = null
    ): void {
        $actor = $actor ?: Auth::user();
        $request = $request ?: request();

        AuditLog::create([
            'actor_id' => $actor?->getAuthIdentifier(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'metadata' => $metadata,
            'ip' => $request?->ip(),
            'user_agent' => substr((string) $request?->userAgent(), 0, 255),
            'created_at' => now(),
        ]);
    }
}
