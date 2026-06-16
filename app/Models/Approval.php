<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'request_id',
        'step_key',
        'role_name',
        'unit_id_scope',
        'approver_id',
        'status',
        'note',
        'decided_at',
        //  signature_file_path nullable (dokumen bertanda tangan manual).
        'signature_file_path',
    ];

    protected $casts = [
        'status' => ApprovalStatus::class,
        'decided_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function unitScope()
    {
        return $this->belongsTo(Unit::class, 'unit_id_scope');
    }
}
