<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterNumber extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'format_id',
        'unit_id',
        'format_key',
        'number_seq',
        'year',
        'number_text',
        'template_snapshot',
        'is_manual_override',
        'issued_at',
        'issued_by',
    ];

    protected $casts = [
        'number_seq' => 'integer',
        'year' => 'integer',
        'is_manual_override' => 'boolean',
        'issued_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function format(): BelongsTo
    {
        return $this->belongsTo(LetterNumberFormat::class, 'format_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}

