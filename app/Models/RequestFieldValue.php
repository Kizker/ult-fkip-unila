<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestFieldValue extends Model
{
    protected $fillable = [
        'request_id',
        'service_field_id',
        'value_text',
        'value_json',
        'value_date',
        'value_number',
    ];

    protected $casts = [
        'value_json' => 'array',
        'value_date' => 'date',
        'value_number' => 'float',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function field()
    {
        return $this->belongsTo(ServiceField::class, 'service_field_id');
    }
}
