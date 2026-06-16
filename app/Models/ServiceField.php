<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceField extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'key',
        'maps_to_placeholder_key',
        'label_id', 'label_en',
        'type',
        'required',
        'rules_json',
        'options_json',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'required' => 'boolean',
        'rules_json' => 'array',
        'options_json' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
