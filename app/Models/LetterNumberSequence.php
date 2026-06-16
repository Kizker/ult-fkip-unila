<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterNumberSequence extends Model
{
    protected $fillable = [
        'format_id',
        'year',
        'last_seq',
    ];

    protected $casts = [
        'year' => 'integer',
        'last_seq' => 'integer',
    ];
}

