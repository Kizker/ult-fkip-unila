<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsAnnouncement extends Model
{
    protected $table = 'cms_announcements';

    protected $fillable = [
        'slug',
        'title_id',
        'title_en',
        'image_path',
        'content_html_id',
        'content_html_en',
        'published_at',
        'is_published',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];
}
