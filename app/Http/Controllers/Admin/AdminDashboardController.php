<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Request as UltRequest;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $queue = UltRequest::query()
            ->with(['service','student','currentUnit'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $rawKpi = UltRequest::query()
            ->selectRaw('current_status, COUNT(*) as c')
            ->groupBy('current_status')
            ->pluck('c', 'current_status');

        $kpiGroups = [];
        foreach ($rawKpi as $status => $count) {
            $mapped = match($status) {
                'COMPLETED' => 'SELESAI',
                'READY_FOR_FINAL' => 'IN_SIGNING',
                default => $status,
            };
            $kpiGroups[$mapped] = ($kpiGroups[$mapped] ?? 0) + $count;
        }

        $order = [
            'DIAJUKAN' => 1,
            'REVIEW_ULT' => 2,
            'PERLU_PERBAIKAN' => 3,
            'IN_SIGNING' => 4,
            'SELESAI' => 5,
            'DITOLAK_ADMIN' => 6,
        ];

        uksort($kpiGroups, function($a, $b) use ($order) {
            $orderA = $order[$a] ?? 99;
            $orderB = $order[$b] ?? 99;
            if ($orderA === $orderB) {
                return strcmp($a, $b);
            }
            return $orderA <=> $orderB;
        });

        $kpi = $kpiGroups;

        return view('admin.dashboard', compact('queue', 'kpi'));
    }
}
