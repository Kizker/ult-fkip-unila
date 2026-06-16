<?php

namespace App\Models;

use App\Enums\PlaceholderSourceType;
use Illuminate\Database\Eloquent\Model;

class ServicePlaceholder extends Model
{
    protected $fillable = [
        'service_id',
        'placeholder_key',
        'source_type',
        'source_ref',
        'is_required',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'source_type' => PlaceholderSourceType::class,
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
