<?php

namespace Database\Factories;

use App\Models\JobPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobPositionFactory extends Factory
{
    protected $model = JobPosition::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->jobTitle(),
            'is_active' => fake()->boolean(85),
        ];
    }
}
