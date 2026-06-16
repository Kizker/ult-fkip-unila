<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroBanner extends Model
{
    protected $fillable = [
        'title_id', 'title_en',
        'subtitle_id', 'subtitle_en',
        'image_path',
        'cta_label_id', 'cta_label_en',
        'cta_url',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
