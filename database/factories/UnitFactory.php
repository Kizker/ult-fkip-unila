<?php

namespace Database\Factories;

use App\Enums\UnitType;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        $code = strtoupper(Str::random(5));
        return [
            'type' => UnitType::fakultas,
            'parent_id' => null,
            'code' => $code,
            'name' => 'Unit '.$code,
            'is_active' => true,
        ];
    }

    public function jurusan(Unit $parent): static
    {
        return $this->state(fn() => [
            'type' => UnitType::jurusan,
            'parent_id' => $parent->id,
        ]);
    }

    public function prodi(Unit $parent): static
    {
        return $this->state(fn() => [
            'type' => UnitType::prodi,
            'parent_id' => $parent->id,
        ]);
    }
}
