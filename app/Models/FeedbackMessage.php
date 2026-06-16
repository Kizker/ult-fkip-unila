<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeedbackMessage extends Model
{
    use HasFactory;

    public const CATEGORY_MASUKAN = 'MASUKAN';
    public const CATEGORY_SARAN = 'SARAN';
    public const CATEGORY_KOMPLAIN = 'KOMPLAIN';

    public const STATUS_BARU = 'BARU';
    public const STATUS_DIPROSES = 'DIPROSES';
    public const STATUS_SELESAI = 'SELESAI';

    public const CATEGORIES = [
        self::CATEGORY_MASUKAN,
        self::CATEGORY_SARAN,
        self::CATEGORY_KOMPLAIN,
    ];

    public const STATUSES = [
        self::STATUS_BARU,
        self::STATUS_DIPROSES,
        self::STATUS_SELESAI,
    ];

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'category',
        'message',
        'status',
        'admin_reply',
        'replied_by',
        'replied_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function repliedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by');
    }
}
