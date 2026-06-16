<?php

namespace App\Models;

use App\Enums\RequestSignoffStatus;
use Illuminate\Database\Eloquent\Model;

class RequestSignoff extends Model
{
    protected $fillable = [
        'request_id',
        'signer_role',
        'signer_user_id',
        'order_index',
        'is_required',
        'status',
        'decided_by',
        'decided_at',
        'note',
        'signature_file_path',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'is_required' => 'boolean',
        'status' => RequestSignoffStatus::class,
        'decided_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function signerUser()
    {
        return $this->belongsTo(User::class, 'signer_user_id');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
