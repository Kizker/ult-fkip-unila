<?php

namespace App\Models;

use App\Enums\CmsCategoryType;
use Illuminate\Database\Eloquent\Model;

class CmsCategory extends Model
{
    protected $fillable = [
        'type',
        'slug',
        'name_id',
        'name_en',
    ];

    protected $casts = [
        'type' => CmsCategoryType::class,
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id');
    }
}
