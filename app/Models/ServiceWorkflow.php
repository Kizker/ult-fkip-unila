<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'require_prodi',
        'require_jurusan',
        'require_unit_signature',
        'require_org_chair_signature',
        'require_pemohon_signature',
        'require_org_secretary_signature',
        'require_kaprodi_signature',
        'require_kajur_signature',
        'require_other_lecturer_signature',
        'require_ult_review',
        'require_faculty_signature',
        'issue_number_at_step',
        'workflow_schema_version',
        'steps_json',
        'gate_enabled',
        'gate_role',
        'gate_steps_json',
    ];

    protected $casts = [
        'require_prodi' => 'boolean',
        'require_jurusan' => 'boolean',
        'require_unit_signature' => 'boolean',
        'require_org_chair_signature' => 'boolean',
        'require_pemohon_signature' => 'boolean',
        'require_org_secretary_signature' => 'boolean',
        'require_kaprodi_signature' => 'boolean',
        'require_kajur_signature' => 'boolean',
        'require_other_lecturer_signature' => 'boolean',
        'require_ult_review' => 'boolean',
        'require_faculty_signature' => 'boolean',
        'steps_json' => 'array',
        'gate_enabled' => 'boolean',
        'gate_steps_json' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
