<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $action = trim((string) $request->query('action', ''));
        $entityType = trim((string) $request->query('entity_type', ''));
        $from = trim((string) $request->query('from', ''));
        $to = trim((string) $request->query('to', ''));

        $from = $from !== '' ? $from : null;
        $to = $to !== '' ? $to : null;

        $logs = AuditLog::query()
            ->with('actor')
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('action', 'like', "%{$q}%")
                        ->orWhere('entity_type', 'like', "%{$q}%")
                        ->orWhere('entity_id', 'like', "%{$q}%")
                        ->orWhereHas('actor', function ($u) use ($q) {
                            $u->where('name', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        });
                });
            })
            ->when($action !== '', fn($qr) => $qr->where('action', $action))
            ->when($entityType !== '', fn($qr) => $qr->where('entity_type', $entityType))
            ->when($from, fn($qr) => $qr->whereDate('created_at', '>=', $from))
            ->when($to, fn($qr) => $qr->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $actionOptions = AuditLog::query()
            ->whereNotNull('action')
            ->where('action', '!=', '')
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $entityTypeOptions = AuditLog::query()
            ->whereNotNull('entity_type')
            ->where('entity_type', '!=', '')
            ->select('entity_type')
            ->distinct()
            ->orderBy('entity_type')
            ->pluck('entity_type');

        return view('admin.audit.index', compact(
            'logs',
            'q',
            'action',
            'entityType',
            'from',
            'to',
            'actionOptions',
            'entityTypeOptions'
        ));
    }
}
