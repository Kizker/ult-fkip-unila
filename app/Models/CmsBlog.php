<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsBlog extends Model
{
    protected $table = 'cms_blogs';

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
