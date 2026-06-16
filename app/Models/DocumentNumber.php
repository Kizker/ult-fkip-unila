<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumber extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'format_key',
        'number_seq',
        'year',
        'unit_id',
        'issued_at',
        'issued_by',
    ];

    protected $casts = [
        'number_seq' => 'integer',
        'year' => 'integer',
        'issued_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
    public function getNumberTextAttribute(): string
    {
        $formatRow = DocumentNumberFormat::query()
            ->active()
            ->where('unit_id', $this->unit_id)
            ->where('format_key', $this->format_key ?: 'default')
            ->first();

        $format = $formatRow?->template ?: '{SEQ:3}/ULT-FKIP/{UNIT_CODE}/{YYYY}';
        $padding = (int) ($formatRow?->seq_padding ?? 3);

        $seqPadding = $padding;
        if (preg_match('/\{SEQ:(\d+)\}/', $format)) {
            preg_match('/\{SEQ:(\d+)\}/', $format, $matches);
            $seqPadding = max(1, min(10, (int) ($matches[1] ?? $padding)));
            $format = preg_replace('/\{SEQ:(\d+)\}/', '{SEQ}', $format);
        }

        $unitCode = $this->unit?->code ?? 'UNIT';

        return str_replace(
            ['{SEQ}', '{UNIT_CODE}', '{UNIT}', '{YYYY}', '{MM}'],
            [
                str_pad((string) $this->number_seq, $seqPadding, '0', STR_PAD_LEFT),
                $unitCode,
                $unitCode,
                (string) $this->year,
                str_pad((string) (int) ($this->issued_at?->format('m') ?? now()->format('m')), 2, '0', STR_PAD_LEFT),
            ],
            $format
        );
    }

}
