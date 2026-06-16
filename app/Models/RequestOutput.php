<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestOutput extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'output_type',
        'file_path',
        'original_filename',
        'uploaded_by',
        'is_private',
        'created_at',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

