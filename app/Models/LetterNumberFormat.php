<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LetterNumberFormat extends Model
{
    protected $fillable = [
        'unit_id',
        'format_key',
        'name',
        'template',
        'seq_padding',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'seq_padding' => 'integer',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }
}

