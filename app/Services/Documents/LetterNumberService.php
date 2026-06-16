<?php

namespace App\Services\Documents;

use App\Models\LetterNumber;
use App\Models\LetterNumberFormat;
use App\Models\LetterNumberSequence;
use App\Models\Request as UltRequest;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LetterNumberService
{
    public function issue(UltRequest $request, Unit $unit, LetterNumberFormat $format, User $actor, ?int $seqOverride = null): LetterNumber
    {
        return DB::transaction(function () use ($request, $unit, $format, $actor, $seqOverride) {
            $request->refresh();

            $year = (int) now()->format('Y');

            $existing = LetterNumber::query()
                ->where('request_id', $request->id)
                ->first();

            $seq = LetterNumberSequence::query()
                ->where('format_id', $format->id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (!$seq) {
                $seq = LetterNumberSequence::create([
                    'format_id' => $format->id,
                    'year' => $year,
                    'last_seq' => 0,
                ]);
                $seq = LetterNumberSequence::query()->whereKey($seq->id)->lockForUpdate()->first();
            }

            $candidate = $seqOverride !== null ? (int) $seqOverride : ((int) $seq->last_seq) + 1;
            if ($candidate <= 0) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Nomor urut tidak valid.');
            }

            $collision = LetterNumber::query()
                ->where('format_id', $format->id)
                ->where('year', $year)
                ->where('number_seq', $candidate)
                ->when($existing, fn ($q) => $q->where('id', '!=', $existing->id))
                ->exists();
            if ($collision) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Nomor urut sudah pernah digunakan pada template dan tahun yang sama.');
            }

            $numberText = $this->render($format, $unit, $candidate, $year);
            if (strlen($numberText) > 120) {
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(422, 'Nomor surat terlalu panjang (maks 120 karakter).');
            }

            $seq->last_seq = max((int) $seq->last_seq, $candidate);
            $seq->save();

            $row = LetterNumber::updateOrCreate(
                ['request_id' => $request->id],
                [
                    'format_id' => $format->id,
                    'unit_id' => $unit->id,
                    'format_key' => $format->format_key,
                    'number_seq' => $candidate,
                    'year' => $year,
                    'number_text' => $numberText,
                    'template_snapshot' => $format->template,
                    'is_manual_override' => $seqOverride !== null,
                    'issued_at' => now(),
                    'issued_by' => $actor->id,
                ]
            );

            $request->nomor_surat = $numberText;
            $request->save();

            return $row;
        });
    }

    public function render(LetterNumberFormat $format, Unit $unit, int $seq, int $year): string
    {
        $template = (string) $format->template;

        $padding = (int) ($format->seq_padding ?? 3);

        $seqPad = $padding;
        if (preg_match('/\\{SEQ:(\\d+)\\}/', $template, $m)) {
            $seqPad = max(1, min(10, (int) $m[1]));
            $template = preg_replace('/\\{SEQ:(\\d+)\\}/', '{SEQ}', $template);
        }

        $month = (string) str_pad((string) (int) now()->format('m'), 2, '0', STR_PAD_LEFT);
        $seqText = str_pad((string) $seq, $seqPad, '0', STR_PAD_LEFT);
        $unitCode = $unit->code ?: 'UNIT';

        return str_replace(
            ['{SEQ}', '{UNIT_CODE}', '{UNIT}', '{YYYY}', '{MM}'],
            [$seqText, $unitCode, $unitCode, (string) $year, $month],
            $template
        );
    }
}

