<?php

namespace App\Services;

use App\Models\DocumentNumber;
use App\Models\DocumentNumberSequence;
use App\Models\DocumentNumberFormat;
use App\Models\Request as UltRequest;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Issue a document number for a request (concurrency-safe).
     *
     * Strategy:
     * - MySQL: advisory lock (GET_LOCK) around a per-unit/year key.
     * - Non-MySQL (e.g. sqlite for tests): sequence row with SELECT ... FOR UPDATE.
     *
     * Format:
     * - Default : {SEQ}/ULT-FKIP/{UNIT}/{YYYY}
     * - Fallback kode dipakai hanya jika master template nomor surat per unit belum tersedia.
     */
    public function issue(UltRequest $request, Unit $unit, string $formatKey = 'default'): DocumentNumber
    {
        return DB::transaction(function () use ($request, $unit, $formatKey) {
            if ($request->documentNumber) {
                return $request->documentNumber;
            }

            $year = (int) now()->format('Y');
            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                $lockKey = "docnum:{$formatKey}:{$unit->id}:{$year}";
                $got = DB::selectOne('SELECT GET_LOCK(?, 10) AS l', [$lockKey]);
                if (!((int) ($got->l ?? 0))) {
                    throw new \RuntimeException('Cannot acquire lock for document number');
                }

                try {
                    $next = (int) (DocumentNumber::query()
                        ->where('format_key', $formatKey)
                        ->where('year', $year)
                        ->where('unit_id', $unit->id)
                        ->max('number_seq') ?? 0) + 1;
                } finally {
                    DB::selectOne('SELECT RELEASE_LOCK(?) AS r', [$lockKey]);
                }
            } else {
                $seq = DocumentNumberSequence::query()
                    ->where('format_key', $formatKey)
                    ->where('year', $year)
                    ->where('unit_id', $unit->id)
                    ->lockForUpdate()
                    ->first();

                if (!$seq) {
                    $seq = DocumentNumberSequence::create([
                        'format_key' => $formatKey,
                        'year' => $year,
                        'unit_id' => $unit->id,
                        'last_seq' => 0,
                    ]);
                    $seq = DocumentNumberSequence::query()->whereKey($seq->id)->lockForUpdate()->first();
                }

                $next = ((int) $seq->last_seq) + 1;
                $seq->last_seq = $next;
                $seq->save();
            }

            return DocumentNumber::create([
                'request_id' => $request->id,
                'format_key' => $formatKey,
                'number_seq' => $next,
                'year' => $year,
                'unit_id' => $unit->id,
                'issued_at' => now(),
                'issued_by' => auth()->id(),
            ]);
        });
    }

    public function renderNumber(DocumentNumber $doc): string
    {
        $unitCode = $doc->unit?->code ?? 'UNIT';
        $year = (int) $doc->year;
        $month = (string) str_pad((string) (int) ($doc->issued_at?->format('m') ?? now()->format('m')), 2, '0', STR_PAD_LEFT);

        // 1) Prefer per-unit master data (document_number_formats)
        $formatRow = DocumentNumberFormat::query()
            ->active()
            ->where('unit_id', $doc->unit_id)
            ->where('format_key', $doc->format_key)
            ->first();

        $template = $formatRow?->template;

        // 2) Fallback to built-in default when unit master data has no active template.
        if (!$template) {
            $template = '{SEQ:3}/ULT-FKIP/{UNIT_CODE}/{YYYY}';
        }

        $padding = $formatRow?->seq_padding ?? 3;

        // Support {SEQ:n}
        $seqPad = $padding;
        if (preg_match('/\{SEQ:(\d+)\}/', $template, $m)) {
            $seqPad = max(1, min(10, (int) $m[1]));
            $template = preg_replace('/\{SEQ:(\d+)\}/', '{SEQ}', $template);
        }

        $seq = str_pad((string) $doc->number_seq, $seqPad, '0', STR_PAD_LEFT);

        return str_replace(
            ['{SEQ}', '{UNIT_CODE}', '{UNIT}', '{YYYY}', '{MM}'],
            [$seq, $unitCode, $unitCode, (string) $year, $month],
            $template
        );
    }
}
