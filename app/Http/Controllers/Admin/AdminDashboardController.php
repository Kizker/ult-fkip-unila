<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardReportService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv as CsvWriter;
use Dompdf\Dompdf;

class AdminDashboardController extends Controller
{
    public function __construct(
        private DashboardReportService $reportService
    ) {}

    /**
     * Main dashboard page with all sections.
     */
    public function index(Request $request)
    {
        $period     = $request->input('period', 'this_month');
        $customFrom = $request->input('from');
        $customTo   = $request->input('to');

        $range = $this->reportService->resolvePeriod($period, $customFrom, $customTo);
        $from  = $range['from'];
        $to    = $range['to'];

        $summary      = $this->reportService->getSummaryCards($from, $to);
        $trendData    = $this->reportService->getTrendData($from, $to);
        $statusData   = $this->reportService->getStatusDistribution($from, $to);
        $kpi          = $this->reportService->getKpiStats($from, $to);
        $queue        = $this->reportService->getRecentQueue(8);
        $transactions = $this->reportService->getTransactionTable(
            $from,
            $to,
            $request->input('search'),
            $request->input('sort', 'created_at'),
            $request->input('dir', 'desc'),
            15
        );

        return view('admin.dashboard', compact(
            'period', 'range', 'summary', 'trendData', 'statusData',
            'kpi', 'queue', 'transactions'
        ));
    }

    /**
     * JSON endpoint for async chart reloading.
     */
    public function chartData(Request $request)
    {
        $period     = $request->input('period', 'this_month');
        $customFrom = $request->input('from');
        $customTo   = $request->input('to');

        $range      = $this->reportService->resolvePeriod($period, $customFrom, $customTo);
        $from       = $range['from'];
        $to         = $range['to'];

        return response()->json([
            'summary' => $this->reportService->getSummaryCards($from, $to),
            'trend'   => $this->reportService->getTrendData($from, $to),
            'status'  => $this->reportService->getStatusDistribution($from, $to),
            'kpi'     => $this->reportService->getKpiStats($from, $to),
            'label'   => $range['label'],
        ]);
    }

    /**
     * Export transaction data (csv, excel, pdf).
     */
    public function export(Request $request, string $format)
    {
        $period     = $request->input('period', 'this_month');
        $customFrom = $request->input('from');
        $customTo   = $request->input('to');
        $range      = $this->reportService->resolvePeriod($period, $customFrom, $customTo);
        $data       = $this->reportService->buildExportData($range['from'], $range['to']);

        $filename = 'laporan-transaksi-' . now()->format('Ymd-His');

        return match ($format) {
            'csv'   => $this->exportCsv($data, $filename),
            'excel' => $this->exportExcel($data, $filename),
            'pdf'   => $this->exportPdf($data, $filename, $range['label']),
            default => abort(404),
        };
    }

    private function exportCsv($data, string $filename)
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function () use ($data) {
            $out = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compat
            fwrite($out, "\xEF\xBB\xBF");

            if ($data->isNotEmpty()) {
                fputcsv($out, array_keys($data->first()));
            }

            foreach ($data as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportExcel($data, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Transaksi');

        if ($data->isNotEmpty()) {
            $headers = array_keys($data->first());
            foreach ($headers as $col => $header) {
                $sheet->setCellValue([$col + 1, 1], $header);
                $sheet->getStyleByColumnAndRow($col + 1, 1)->getFont()->setBold(true);
            }

            $row = 2;
            foreach ($data as $item) {
                $col = 1;
                foreach ($item as $value) {
                    $sheet->setCellValue([$col, $row], $value);
                    $col++;
                }
                $row++;
            }

            // Auto-size columns
            foreach (range(1, count($headers)) as $col) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $temp   = tempnam(sys_get_temp_dir(), 'export_');
        $writer->save($temp);

        return response()->download($temp, "{$filename}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function exportPdf($data, string $filename, string $periodLabel)
    {
        $html = view('admin.exports.transactions-pdf', [
            'data'        => $data,
            'periodLabel' => $periodLabel,
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
        ])->render();

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}.pdf\"",
        ]);
    }
}
