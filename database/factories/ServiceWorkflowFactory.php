<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceWorkflow;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceWorkflowFactory extends Factory
{
    protected $model = ServiceWorkflow::class;

    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'require_prodi' => true,
            'require_jurusan' => false,
            'require_unit_signature' => false,
            'require_org_chair_signature' => false,
            'require_pemohon_signature' => false,
            'require_org_secretary_signature' => false,
            'require_kaprodi_signature' => false,
            'require_kajur_signature' => false,
            'require_other_lecturer_signature' => false,
            'require_ult_review' => true,
            'require_faculty_signature' => false,
            'issue_number_at_step' => 'issue_number',
            'workflow_schema_version' => 1,
            'steps_json' => [
                [
                    'key' => 'submitted',
                    'label_id' => 'Diajukan',
                    'label_en' => 'Submitted',
                    'role_required' => 'Admin Jurusan',
                    'unit_scope' => 'by_request',
                    'actions_allowed' => ['verify'],
                    'next_on_approve' => 'ult_review',
                    'next_on_reject' => 'rejected',
                    'can_request_revision' => true,
                ],
                [
                    'key' => 'ult_review',
                    'label_id' => 'Review ULT',
                    'label_en' => 'ULT Review',
                    'role_required' => 'Staf ULT',
                    'unit_scope' => 'fakultas',
                    'actions_allowed' => ['forward_faculty','complete','reject','revision'],
                    'next_on_approve' => 'completed',
                    'next_on_reject' => 'rejected',
                    'can_request_revision' => true,
                ],
            ],
        ];
    }
}
