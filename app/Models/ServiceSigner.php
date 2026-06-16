<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSigner extends Model
{
    protected $fillable = [
        'service_id',
        'role',
        'custom_label',
        'order_index',
        'is_required',
        'requires_signature_upload',
        'signature_file_types',
        'signature_max_size_kb',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'is_required' => 'boolean',
        'requires_signature_upload' => 'boolean',
        'signature_file_types' => 'array',
        'signature_max_size_kb' => 'integer',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
