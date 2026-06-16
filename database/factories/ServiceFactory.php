<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $title = 'Layanan '.fake()->words(3, true);
        return [
            'category_id' => null,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'title_id' => $title,
            'title_en' => $title,
            'summary_id' => fake()->sentence(),
            'summary_en' => fake()->sentence(),
            'requirements_html_id' => '<p>Syarat dummy.</p>',
            'requirements_html_en' => '<p>Dummy requirements.</p>',
            'sop_html_id' => '<p>SOP dummy.</p>',
            'sop_html_en' => '<p>Dummy SOP.</p>',
            'sla_days' => null,
            'is_active' => true,
        ];
    }
}
