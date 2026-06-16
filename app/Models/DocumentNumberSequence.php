<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentNumberSequence extends Model
{
    protected $fillable = [
        'format_key',
        'year',
        'unit_id',
        'last_seq',
    ];
}
