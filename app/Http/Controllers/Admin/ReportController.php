<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UltRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));

        // Transaksi Bulanan
        $monthlyData = UltRequest::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $monthlyChartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyChartData[] = $monthlyData[$i] ?? 0;
        }

        // Status Distribusi
        $statusData = UltRequest::select(
            'status',
            DB::raw('COUNT(*) as total')
        )
            ->whereYear('created_at', $year)
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        return view('admin.reports.index', compact('year', 'monthlyChartData', 'statusData'));
    }
}
