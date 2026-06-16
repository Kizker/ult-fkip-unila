<?php

namespace App\Models;

use App\Enums\ServiceTemplateType;
use Illuminate\Database\Eloquent\Model;

class ServiceTemplate extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_id',
        'type',
        'file_path',
        'original_filename',
        'uploaded_by',
        'created_at',
    ];

    protected $casts = [
        'type' => ServiceTemplateType::class,
        'created_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

