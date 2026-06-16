<?php

namespace App\Models;

use App\Enums\DocumentSourceType;
use App\Enums\ServiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'slug',
        'title_id', 'title_en',
        'summary_id', 'summary_en',
        'requirements_html_id', 'requirements_html_en',
        'sop_html_id', 'sop_html_en',
        'sla_days',
        'status',
        'document_source_type',
        'allow_general_attachments',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_general_attachments' => 'boolean',
        'sla_days' => 'integer',
        'status' => ServiceStatus::class,
        'document_source_type' => DocumentSourceType::class,
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(ServiceField::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function allFields(): HasMany
    {
        return $this->hasMany(ServiceField::class)->orderBy('sort_order');
    }

    public function workflow(): HasOne
    {
        return $this->hasOne(ServiceWorkflow::class);
    }

    public function category()
    {
        return $this->belongsTo(CmsCategory::class, 'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function templates(): HasMany
    {
        return $this->hasMany(ServiceTemplate::class);
    }

    public function placeholders(): HasMany
    {
        return $this->hasMany(ServicePlaceholder::class)
            ->where('is_active', true);
    }

    public function allPlaceholders(): HasMany
    {
        return $this->hasMany(ServicePlaceholder::class);
    }

    public function signers(): HasMany
    {
        return $this->hasMany(ServiceSigner::class)->orderBy('order_index');
    }

    public function usesRequestPptxSource(): bool
    {
        return $this->document_source_type === DocumentSourceType::REQUEST_PPTX;
    }

    public function usesMainDocxTemplateSource(): bool
    {
        return !$this->usesRequestPptxSource();
    }
}
