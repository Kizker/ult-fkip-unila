<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestNote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'actor_id',
        'body',
        'is_internal',
        'created_at',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
