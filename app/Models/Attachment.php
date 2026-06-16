<?php

namespace App\Models;

use App\Enums\AttachmentKind;
use App\Enums\AttachmentVerifiedStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_id',
        'uploaded_by',
        'kind',
        'service_field_id',
        'original_name',
        'stored_path',
        'mime',
        'size',
        'sha256',
        'verified_status',
        'verified_by',
        'verified_at',
        'verification_note',
    ];

    protected $casts = [
        'kind' => AttachmentKind::class,
        'verified_status' => AttachmentVerifiedStatus::class,
        'size' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function field()
    {
        return $this->belongsTo(ServiceField::class, 'service_field_id');
    }
}
