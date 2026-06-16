<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestSignaturePlacement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'placements_json',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'placements_json' => 'array',
        'created_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

