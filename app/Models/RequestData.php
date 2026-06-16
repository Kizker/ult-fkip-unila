<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestData extends Model
{
    protected $table = 'request_data';

    protected $fillable = [
        'request_id',
        'data_json',
        'attachments_json',
        'document_snapshot_json',
    ];

    protected $casts = [
        'data_json' => 'array',
        'attachments_json' => 'array',
        'document_snapshot_json' => 'array',
    ];

    public function documentSnapshot(): array
    {
        return is_array($this->document_snapshot_json) ? $this->document_snapshot_json : [];
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }
}
