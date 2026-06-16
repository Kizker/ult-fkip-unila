<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestStatusHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'request_id',
        'from_status',
        'to_status',
        'step_key',
        'note',
        'actor_id',
        'created_at',
    ];

    protected $casts = [
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
