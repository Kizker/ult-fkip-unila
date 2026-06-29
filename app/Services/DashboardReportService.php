<?php

namespace App\Services;

use App\Models\Request as UltRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardReportService
{
    /**
     * Resolve the date range from a period key.
     *
     * @return array{from: Carbon, to: Carbon, label: string}
     */
    public function resolvePeriod(string $period, ?string $customFrom = null, ?string $customTo = null): array
    {
        $now = Carbon::now();

        return match ($period) {
            '7d' => [
                'from'  => $now->copy()->subDays(6)->startOfDay(),
                'to'    => $now->copy()->endOfDay(),
                'label' => '7 Hari Terakhir',
            ],
            'this_month' => [
                'from'  => $now->copy()->startOfMonth(),
                'to'    => $now->copy()->endOfDay(),
                'label' => 'Bulan Ini (' . $now->translatedFormat('F Y') . ')',
            ],
            'last_month' => [
                'from'  => $now->copy()->subMonthNoOverflow()->startOfMonth(),
                'to'    => $now->copy()->subMonthNoOverflow()->endOfMonth(),
                'label' => 'Bulan Lalu (' . $now->copy()->subMonthNoOverflow()->translatedFormat('F Y') . ')',
            ],
            'custom' => [
                'from'  => $customFrom ? Carbon::parse($customFrom)->startOfDay() : $now->copy()->subDays(29)->startOfDay(),
                'to'    => $customTo ? Carbon::parse($customTo)->endOfDay() : $now->copy()->endOfDay(),
                'label' => 'Kustom',
            ],
            default => [
                'from'  => $now->copy()->startOfMonth(),
                'to'    => $now->copy()->endOfDay(),
                'label' => 'Bulan Ini',
            ],
        };
    }

    /**
     * Summary cards: total, berhasil, menunggu, ditolak.
     *
     * @return array<string, int>
     */
    public function getSummaryCards(Carbon $from, Carbon $to): array
    {
        $completedStatuses  = ['SELESAI', 'COMPLETED'];
        $rejectedStatuses   = ['DITOLAK', 'DITOLAK_ADMIN', 'REJECTED_IN_SIGNING'];
        $pendingStatuses    = [
            'DIAJUKAN', 'PERLU_PERBAIKAN', 'DIVERIFIKASI_UNIT', 'MENUNGGU_TTD_UNIT',
            'REVIEW_ULT', 'MENUNGGU_TTD_FAKULTAS', 'NOMOR_DOKUMEN_TERBIT', 'DIPROSES',
            'GATE_VERIFIED', 'NOMOR_SURAT_FILLED', 'IN_SIGNING', 'READY_FOR_FINAL',
        ];

        $query = UltRequest::query()
            ->whereBetween('created_at', [$from, $to]);

        $total     = (clone $query)->count();
        $completed = (clone $query)->whereIn('current_status', $completedStatuses)->count();
        $rejected  = (clone $query)->whereIn('current_status', $rejectedStatuses)->count();
        $pending   = (clone $query)->whereIn('current_status', $pendingStatuses)->count();

        return [
            'total'     => $total,
            'completed' => $completed,
            'pending'   => $pending,
            'rejected'  => $rejected,
        ];
    }

    /**
     * Time-series trend data for line chart.
     * Returns an array of {label, count} grouped by day or month.
     */
    public function getTrendData(Carbon $from, Carbon $to): array
    {
        $diffDays = $from->diffInDays($to);

        // Use daily grouping for ranges up to 60 days, monthly for longer.
        if ($diffDays <= 60) {
            $rows = UltRequest::query()
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('DATE(created_at) as date_key, COUNT(*) as total')
                ->groupBy('date_key')
                ->orderBy('date_key')
                ->get()
                ->keyBy('date_key');

            $labels = [];
            $values = [];
            $cursor = $from->copy();
            while ($cursor->lte($to)) {
                $key      = $cursor->format('Y-m-d');
                $labels[] = $cursor->translatedFormat('d M');
                $values[] = (int) ($rows[$key]->total ?? 0);
                $cursor->addDay();
            }
        } else {
            $rows = UltRequest::query()
                ->whereBetween('created_at', [$from, $to])
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month_key, COUNT(*) as total')
                ->groupBy('month_key')
                ->orderBy('month_key')
                ->get()
                ->keyBy('month_key');

            $labels = [];
            $values = [];
            $cursor = $from->copy()->startOfMonth();
            $end    = $to->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $key      = $cursor->format('Y-m');
                $labels[] = $cursor->translatedFormat('M Y');
                $values[] = (int) ($rows[$key]->total ?? 0);
                $cursor->addMonth();
            }
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    public function getStatusDistribution(Carbon $from, Carbon $to): array
    {
        // Get all statuses with 0 defaults
        $stats = $this->getKpiStats($from, $to);
        
        $labels = [];
        $values = [];
        $colors = [];
        
        foreach ($stats as $stat) {
            // Include all statuses or only > 0? User asked to "tampilkan semua status", so we include all.
            // But Doughnut chart with 15 items including 0s might be confusing. 
            // We'll include all to strictly follow "tampilkan semua".
            $labels[] = $stat['label'];
            $values[] = $stat['count'];
            $colors[] = $stat['color'];
        }

        return [
            'labels' => $labels,
            'values' => $values,
            'colors' => $colors,
        ];
    }

    public function getKpiStats(Carbon $from, Carbon $to): array
    {
        $order = [
            'DIAJUKAN' => 1, 'REVIEW_ULT' => 2, 'PERLU_PERBAIKAN' => 3,
            'DIVERIFIKASI_UNIT' => 4, 'GATE_VERIFIED' => 5, 'NOMOR_SURAT_FILLED' => 6,
            'MENUNGGU_TTD_UNIT' => 7, 'IN_SIGNING' => 8, 'MENUNGGU_TTD_FAKULTAS' => 9,
            'NOMOR_DOKUMEN_TERBIT' => 10, 'DIPROSES' => 11, 'SELESAI' => 12,
            'DITOLAK' => 13, 'DITOLAK_ADMIN' => 14, 'REJECTED_IN_SIGNING' => 15,
        ];

        // Initialize all defined statuses with 0 to show them all on KPI
        $groups = array_fill_keys(array_keys($order), 0);

        $raw = UltRequest::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('current_status, COUNT(*) as c')
            ->groupBy('current_status')
            ->pluck('c', 'current_status');

        foreach ($raw as $status => $count) {
            $mapped = match ($status) {
                'COMPLETED'      => 'SELESAI',
                'READY_FOR_FINAL' => 'IN_SIGNING',
                default          => $status,
            };
            
            // Just in case there's an obsolete status still lingering in old data
            if (!isset($groups[$mapped])) {
                $groups[$mapped] = 0;
            }
            $groups[$mapped] += (int) $count;
        }

        $total = array_sum($groups);

        $labelMap = [
            'DIAJUKAN'               => 'Diajukan',
            'PERLU_PERBAIKAN'        => 'Perlu Perbaikan',
            'DIVERIFIKASI_UNIT'      => 'Diverifikasi Unit',
            'MENUNGGU_TTD_UNIT'      => 'Menunggu TTD Unit',
            'REVIEW_ULT'             => 'Review ULT',
            'MENUNGGU_TTD_FAKULTAS'  => 'Menunggu TTD Fakultas',
            'NOMOR_DOKUMEN_TERBIT'   => 'Nomor Terbit',
            'DIPROSES'               => 'Diproses',
            'SELESAI'                => 'Selesai',
            'DITOLAK'                => 'Ditolak',
            'GATE_VERIFIED'          => 'Gate Verified',
            'NOMOR_SURAT_FILLED'     => 'Nomor Surat Diisi',
            'IN_SIGNING'             => 'Penandatanganan',
            'REJECTED_IN_SIGNING'    => 'Ditolak TTD',
            'DITOLAK_ADMIN'          => 'Ditolak Admin',
        ];

        $colorMap = [
            'DIAJUKAN'               => '#7c3aed', // violet
            'REVIEW_ULT'             => '#8b5cf6', // violet-500
            'PERLU_PERBAIKAN'        => '#f59e0b', // amber
            'DIVERIFIKASI_UNIT'      => '#06b6d4', // cyan
            'GATE_VERIFIED'          => '#0ea5e9', // sky
            'NOMOR_SURAT_FILLED'     => '#3b82f6', // blue
            'MENUNGGU_TTD_UNIT'      => '#eab308', // yellow-500
            'IN_SIGNING'             => '#f97316', // orange
            'MENUNGGU_TTD_FAKULTAS'  => '#d97706', // amber-600
            'NOMOR_DOKUMEN_TERBIT'   => '#6366f1', // indigo
            'DIPROSES'               => '#4f46e5', // indigo-600
            'SELESAI'                => '#10b981', // emerald
            'DITOLAK'                => '#ef4444', // red
            'DITOLAK_ADMIN'          => '#dc2626', // red-600
            'REJECTED_IN_SIGNING'    => '#b91c1c', // red-700
        ];

        uksort($groups, fn ($a, $b) => ($order[$a] ?? 99) <=> ($order[$b] ?? 99));

        $result = [];
        foreach ($groups as $status => $count) {
            if ($count > 0) {
                $result[] = [
                    'status'     => $status,
                    'label'      => $labelMap[$status] ?? str_replace('_', ' ', $status),
                    'count'      => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 1) : 0,
                    'color'      => $colorMap[$status] ?? '#64748b',
                ];
            }
        }

        return $result;
    }

    /**
     * Recent queue with eager-loaded relations.
     */
    public function getRecentQueue(int $limit = 8)
    {
        return UltRequest::query()
            ->with(['service', 'student', 'currentUnit'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Paginated transaction table data.
     */
    public function getTransactionTable(
        Carbon $from,
        Carbon $to,
        ?string $search = null,
        string $sortBy = 'created_at',
        string $sortDir = 'desc',
        int $perPage = 15
    ) {
        $allowedSorts = ['id', 'created_at', 'current_status', 'submitted_at'];
        $sortBy  = in_array($sortBy, $allowedSorts) ? $sortBy : 'created_at';
        $sortDir = $sortDir === 'asc' ? 'asc' : 'desc';

        $query = UltRequest::query()
            ->with(['service', 'student'])
            ->whereBetween('created_at', [$from, $to]);

        if ($search) {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->whereHas('student', fn ($s) => $s->where('name', 'like', $term))
                  ->orWhereHas('service', fn ($s) => $s->where('title_id', 'like', $term)->orWhere('title_en', 'like', $term));
            });
        }

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage)->withQueryString();
    }

    /**
     * Build raw data for export (CSV/Excel/PDF).
     *
     * @return \Illuminate\Support\Collection
     */
    public function buildExportData(Carbon $from, Carbon $to)
    {
        return UltRequest::query()
            ->with(['service', 'student'])
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($r, $i) {
                return [
                    'No'             => $i + 1,
                    'Kode'           => $r->request_code,
                    'Nama Mahasiswa' => $r->student->name ?? '-',
                    'Layanan'        => $r->service->title_id ?? '-',
                    'Status'         => str_replace('_', ' ', $r->current_status instanceof \BackedEnum ? $r->current_status->value : $r->current_status),
                    'Tanggal Ajukan' => $r->submitted_at ? $r->submitted_at->format('d/m/Y H:i') : ($r->created_at ? $r->created_at->format('d/m/Y H:i') : '-'),
                    'Selesai'        => $r->completed_at ? $r->completed_at->format('d/m/Y H:i') : '-',
                ];
            });
    }
}
