<?php

namespace Database\Factories;

use App\Enums\RequestStatus;
use App\Models\Request;
use App\Models\Service;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RequestFactory extends Factory
{
    protected $model = Request::class;

    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'student_id' => User::factory(),
            'current_status' => RequestStatus::DIAJUKAN,
            'current_step_key' => 'submitted',
            'current_unit_id' => Unit::factory(),
            'submitted_at' => now(),
            'completed_at' => null,
            'rejected_at' => null,
        ];
    }
}
