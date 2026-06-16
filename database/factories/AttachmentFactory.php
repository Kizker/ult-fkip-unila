<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Request;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'request_id' => Request::factory(),
            'uploaded_by' => User::factory(),
            'kind' => 'input',
            'service_field_id' => null,
            'original_name' => 'dummy.pdf',
            'stored_path' => 'requests/dummy.pdf',
            'mime' => 'application/pdf',
            'size' => 1234,
            'sha256' => str_repeat('a', 64),
            'verified_status' => 'pending',
            'verified_by' => null,
            'verified_at' => null,
            'verification_note' => null,
        ];
    }
}
