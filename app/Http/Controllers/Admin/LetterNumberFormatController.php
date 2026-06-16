<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLetterNumberFormatRequest;
use App\Models\LetterNumber;
use App\Models\LetterNumberFormat;
use App\Models\LetterNumberSequence;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LetterNumberFormatController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:letter_numbers.manage_formats');
    }

    private function scopedUnits(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        if ($user->can('requests.view_any')) {
            return Unit::query()->orderBy('type')->orderBy('name')->get();
        }
        $unit = $user->unit;
        if (! $unit) {
            return collect();
        }

        $ids = array_merge([$unit->id], $unit->descendantIdsCached(600));

        return Unit::query()->whereIn('id', $ids)->orderBy('type')->orderBy('name')->get();
    }

    /**
     * Units visible in index filter/listing, including inherited parent scope.
     */
    private function viewScopeUnits(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        if ($user->can('requests.view_any')) {
            return Unit::query()->orderBy('type')->orderBy('name')->get();
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
        if (empty($allIds)) {
            return collect();
        }

        return Unit::query()->whereIn('id', $allIds)->orderBy('type')->orderBy('name')->get();
    }

    public function index(Request $request)
    {
        if (! Schema::hasTable('letter_number_formats')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Tabel template nomor surat belum ada. Jalankan: php artisan migrate');
        }

        $units = $this->viewScopeUnits();
        $unitId = $request->integer('unit_id');
        $q = LetterNumberFormat::query()->with('unit');

        $selectedUnit = null;
        if ($unitId > 0) {
            $selectedUnit = $units->firstWhere('id', $unitId);
            if ($selectedUnit instanceof Unit) {
                $applicableIds = $this->applicableTemplateUnitIds($selectedUnit);
                $q->whereIn('unit_id', $applicableIds);
                $q->orderByRaw($this->caseOrderSql($applicableIds, 'unit_id'));
            } else {
                $q->whereRaw('1=0');
            }
        } else {
            $q->whereIn('unit_id', $units->pluck('id')->all());
            $q->orderBy('unit_id');
        }

        $formats = $q->orderBy('format_key')->paginate(20)->withQueryString();

        return view('admin/letter_numbers/formats/index', [
            'formats' => $formats,
            'units' => $units,
            'unitId' => $unitId,
        ]);
    }

    public function create()
    {
        if (! Schema::hasTable('letter_number_formats')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Tabel template nomor surat belum ada. Jalankan: php artisan migrate');
        }

        $units = $this->scopedUnits();

        return view('admin/letter_numbers/formats/create', [
            'units' => $units,
            'defaults' => [
                'format_key' => 'default',
                'name' => 'Default',
                'template' => '{SEQ:5}/UN26.13/PN.01.00/{YYYY}',
                'seq_padding' => 5,
                'is_active' => true,
            ],
        ]);
    }

    public function store(StoreLetterNumberFormatRequest $request)
    {
        if (! Schema::hasTable('letter_number_formats')) {
            abort(500, 'Tabel template nomor surat belum ada. Jalankan: php artisan migrate');
        }

        $data = $request->validated();

        $this->assertTemplateSafe($data['template'] ?? '');
        $this->assertUnitInScope((int) $data['unit_id']);

        $data['seq_padding'] = (int) ($data['seq_padding'] ?? 3);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        LetterNumberFormat::updateOrCreate(
            ['unit_id' => $data['unit_id'], 'format_key' => $data['format_key']],
            $data
        );

        return redirect()->route('admin.letter_formats.index')->with('success', 'Template nomor surat disimpan.');
    }

    public function show(Request $request, LetterNumberFormat $letter_format)
    {
        if (! Schema::hasTable('letter_numbers')) {
            abort(500, 'Tabel history nomor surat belum ada. Jalankan: php artisan migrate');
        }

        $this->authorize('view', $letter_format);

        $year = $this->normalizeYear($request->query('year'));

        $items = $this->historyQuery($letter_format, $year)
            ->with(['issuer', 'request.student'])
            ->orderByDesc('issued_at')
            ->paginate(20)
            ->withQueryString();

        $usedCount = (int) $this->historyQuery($letter_format, $year)->count();

        $maxUsed = (int) ($this->historyQuery($letter_format, $year)->max('number_seq') ?? 0);

        $seq = LetterNumberSequence::query()
            ->where('format_id', $letter_format->id)
            ->where('year', $year)
            ->first();

        $lastSeq = (int) ($seq?->last_seq ?? $maxUsed);

        return view('admin/letter_numbers/formats/show', [
            'format' => $letter_format,
            'items' => $items,
            'year' => $year,
            'usedCount' => $usedCount,
            'maxUsed' => $maxUsed,
            'lastSeq' => $lastSeq,
        ]);
    }

    public function export(Request $request, LetterNumberFormat $letter_format): StreamedResponse
    {
        if (! Schema::hasTable('letter_numbers')) {
            abort(500, 'Tabel history nomor surat belum ada. Jalankan: php artisan migrate');
        }

        $this->authorize('view', $letter_format);

        $year = $this->normalizeYear($request->query('year'));
        $letter_format->loadMissing('unit');

        $items = $this->historyQuery($letter_format, $year)
            ->with(['issuer', 'request.student', 'request.service'])
            ->orderByDesc('issued_at')
            ->get();

        $exportedAt = now();
        $safeUnitCode = $this->sanitizeFilenameSegment((string) ($letter_format->unit?->code ?? ''), 'unit');
        $safeFormatKey = $this->sanitizeFilenameSegment((string) $letter_format->format_key, 'default');
        $filename = "history_nomor_surat_{$safeUnitCode}_{$safeFormatKey}_{$year}_{$exportedAt->format('Ymd_His')}.xlsx";

        return response()->streamDownload(function () use ($items, $letter_format, $year, $exportedAt): void {
            $spreadsheet = new Spreadsheet;
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('History Nomor Surat');

            $sheet->setCellValue('A1', 'Export History Nomor Surat');
            $sheet->mergeCells('A1:J1');
            $sheet->setCellValue('A2', 'Unit: '.($letter_format->unit?->type?->value ?? '-').' - '.($letter_format->unit?->name ?? '-').' ('.($letter_format->unit?->code ?? '-').')');
            $sheet->mergeCells('A2:J2');
            $sheet->setCellValue('A3', 'Format Key: '.$letter_format->format_key.' | Tahun: '.$year.' | Total Data: '.$items->count());
            $sheet->mergeCells('A3:J3');
            $sheet->setCellValue('A4', 'Diekspor: '.$exportedAt->format('Y-m-d H:i:s'));
            $sheet->mergeCells('A4:J4');

            $headerRow = 5;
            $headers = [
                'A' => 'No',
                'B' => 'Tanggal Terbit',
                'C' => 'Nomor Surat',
                'D' => 'Seq',
                'E' => 'Pemohon',
                'F' => 'NPM',
                'G' => 'Diinput Oleh',
                'H' => 'ID Permohonan',
                'I' => 'Layanan',
                'J' => 'Sumber',
            ];
            foreach ($headers as $col => $label) {
                $sheet->setCellValue($col.$headerRow, $label);
            }

            $sheet->getStyle('A1:J1')->applyFromArray([
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => '0F172A'],
                ],
            ]);
            $sheet->getStyle('A2:J4')->applyFromArray([
                'font' => [
                    'size' => 11,
                    'color' => ['rgb' => '334155'],
                ],
            ]);
            $sheet->getStyle("A{$headerRow}:J{$headerRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F4E78'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ]);

            $row = $headerRow + 1;
            $no = 1;
            foreach ($items as $item) {
                $requestModel = $item->request;
                $student = $requestModel?->student;
                $serviceTitle = (string) ($requestModel?->service?->title_id ?: $requestModel?->service?->title_en ?: '-');

                $sheet->setCellValue("A{$row}", $no++);
                $sheet->setCellValue("B{$row}", (string) ($item->issued_at?->format('Y-m-d H:i:s') ?? '-'));
                $sheet->setCellValueExplicit("C{$row}", (string) $item->number_text, DataType::TYPE_STRING);
                $sheet->setCellValue("D{$row}", (int) $item->number_seq);
                $sheet->setCellValue("E{$row}", (string) ($student?->name ?? '-'));
                $sheet->setCellValueExplicit("F{$row}", (string) ($student?->student_number ?? '-'), DataType::TYPE_STRING);
                $sheet->setCellValue("G{$row}", (string) ($item->issuer?->name ?? '-'));
                $sheet->setCellValue("H{$row}", (string) ($requestModel?->id ?? '-'));
                $sheet->setCellValue("I{$row}", $serviceTitle);
                $sheet->setCellValue("J{$row}", $item->is_manual_override ? 'Manual' : 'Template');
                $row++;
            }

            if ($items->isEmpty()) {
                $sheet->setCellValue("A{$row}", 'Belum ada nomor surat yang diterbitkan untuk filter ini.');
                $sheet->mergeCells("A{$row}:J{$row}");
                $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['rgb' => '6B7280']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $row++;
            }

            $lastRow = max($headerRow + 1, $row - 1);

            $sheet->setAutoFilter("A{$headerRow}:J{$headerRow}");
            $sheet->freezePane('A'.($headerRow + 1));

            $sheet->getStyle("A{$headerRow}:J{$lastRow}")
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN)
                ->getColor()
                ->setRGB('D1D5DB');

            $sheet->getStyle('A'.($headerRow + 1).":J{$lastRow}")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle('C'.($headerRow + 1).":C{$lastRow}")
                ->getAlignment()
                ->setWrapText(true);
            $sheet->getStyle('I'.($headerRow + 1).":I{$lastRow}")
                ->getAlignment()
                ->setWrapText(true);

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(20);
            $sheet->getColumnDimension('C')->setWidth(46);
            $sheet->getColumnDimension('D')->setWidth(10);
            $sheet->getColumnDimension('E')->setWidth(28);
            $sheet->getColumnDimension('F')->setWidth(16);
            $sheet->getColumnDimension('G')->setWidth(24);
            $sheet->getColumnDimension('H')->setWidth(20);
            $sheet->getColumnDimension('I')->setWidth(36);
            $sheet->getColumnDimension('J')->setWidth(14);

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
        ]);
    }

    public function updateSequence(Request $request, LetterNumberFormat $letter_format)
    {
        if (! Schema::hasTable('letter_number_sequences')) {
            abort(500, 'Tabel sequence nomor surat belum ada. Jalankan: php artisan migrate');
        }

        $this->authorize('update', $letter_format);

        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'last_seq' => ['required', 'integer', 'min:0'],
        ]);

        $year = (int) $data['year'];
        $lastSeq = (int) $data['last_seq'];

        $maxUsed = (int) (LetterNumber::query()
            ->where('format_id', $letter_format->id)
            ->where('year', $year)
            ->max('number_seq') ?? 0);
        if ($lastSeq < $maxUsed) {
            return back()->withErrors(['last_seq' => "Tidak boleh lebih kecil dari nomor terbesar yang sudah dipakai ({$maxUsed})."])->withInput();
        }

        LetterNumberSequence::updateOrCreate(
            ['format_id' => $letter_format->id, 'year' => $year],
            ['last_seq' => $lastSeq]
        );

        return back()->with('success', 'Nomor terakhir (sequence) diperbarui.');
    }

    public function edit(LetterNumberFormat $letter_format)
    {
        if (! Schema::hasTable('letter_number_formats')) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Tabel template nomor surat belum ada. Jalankan: php artisan migrate');
        }

        $this->authorize('update', $letter_format);
        $units = $this->scopedUnits();

        return view('admin/letter_numbers/formats/edit', [
            'format' => $letter_format,
            'units' => $units,
        ]);
    }

    public function update(StoreLetterNumberFormatRequest $request, LetterNumberFormat $letter_format)
    {
        if (! Schema::hasTable('letter_number_formats')) {
            abort(500, 'Tabel template nomor surat belum ada. Jalankan: php artisan migrate');
        }

        $this->authorize('update', $letter_format);

        $data = $request->validated();
        $this->assertTemplateSafe($data['template'] ?? '');
        $this->assertUnitInScope((int) $data['unit_id']);

        $data['seq_padding'] = (int) ($data['seq_padding'] ?? 3);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);

        if ((int) $letter_format->unit_id !== (int) $data['unit_id'] || $letter_format->format_key !== $data['format_key']) {
            $letter_format->delete();
            LetterNumberFormat::updateOrCreate(
                ['unit_id' => $data['unit_id'], 'format_key' => $data['format_key']],
                $data
            );
        } else {
            $letter_format->fill($data)->save();
        }

        return redirect()->route('admin.letter_formats.index')->with('success', 'Template nomor surat diperbarui.');
    }

    public function destroy(LetterNumberFormat $letter_format)
    {
        if (! Schema::hasTable('letter_number_formats')) {
            abort(500, 'Tabel template nomor surat belum ada. Jalankan: php artisan migrate');
        }

        $this->authorize('delete', $letter_format);
        $letter_format->delete();

        return redirect()->route('admin.letter_formats.index')->with('success', 'Template nomor surat dihapus.');
    }

    private function historyQuery(LetterNumberFormat $format, int $year)
    {
        return LetterNumber::query()
            ->where('format_id', $format->id)
            ->where('year', $year);
    }

    private function normalizeYear(mixed $year): int
    {
        $yearInt = (int) ($year ?: now()->format('Y'));
        if ($yearInt < 2000 || $yearInt > 2100) {
            return (int) now()->format('Y');
        }

        return $yearInt;
    }

    private function sanitizeFilenameSegment(string $value, string $fallback): string
    {
        $clean = (string) preg_replace('/[^A-Za-z0-9_-]+/', '_', trim($value));
        $clean = trim($clean, '_');

        return $clean !== '' ? $clean : $fallback;
    }

    private function assertUnitInScope(int $unitId): void
    {
        $user = auth()->user();
        if ($user->can('requests.view_any')) {
            return;
        }

        $unit = $user->unit;
        if (! $unit) {
            abort(403);
        }

        $allowed = array_merge([$unit->id], $unit->descendantIdsCached(600));
        if (! in_array($unitId, $allowed, true)) {
            abort(403);
        }
    }

    private function assertTemplateSafe(string $template): void
    {
        if (! preg_match('/\\{SEQ(?::\\d+)?\\}/', $template)) {
            abort(422, 'Template wajib memiliki placeholder {SEQ} atau {SEQ:n}.');
        }

        if (preg_match('/<\\?php|<script|`/i', $template)) {
            abort(422, 'Template mengandung karakter yang tidak diizinkan.');
        }

        $allowed = ['SEQ', 'UNIT_CODE', 'UNIT', 'YYYY', 'MM'];
        preg_match_all('/\\{([A-Z_]+)(?::\\d+)?\\}/', $template, $m);
        foreach ($m[1] as $ph) {
            if (! in_array($ph, $allowed, true)) {
                abort(422, 'Placeholder tidak dikenal: {'.$ph.'}.');
            }
        }
    }

    /**
     * @return array<int,int>
     */
    private function applicableTemplateUnitIds(Unit $unit): array
    {
        $ids = [];
        $node = $unit;
        $guard = 0;

        while ($node && $guard < 20) {
            $ids[] = (int) $node->id;
            $node = $node->parent;
            $guard++;
        }

        return array_values(array_unique($ids));
    }

    /**
     * Build deterministic CASE order by preferred unit IDs.
     *
     * @param  array<int,int>  $ids
     */
    private function caseOrderSql(array $ids, string $column): string
    {
        if (empty($ids)) {
            return '1';
        }

        $sql = "CASE {$column} ";
        foreach (array_values($ids) as $idx => $id) {
            $id = (int) $id;
            $sql .= "WHEN {$id} THEN {$idx} ";
        }
        $sql .= 'ELSE 999 END';

        return $sql;
    }
}
