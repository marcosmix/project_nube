<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectNoteFactory extends Factory
{
    protected $model = ProjectNote::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::query()->inRandomOrder()->value('id'),
            'content' => fake()->paragraph(),
            'status' => fake()->randomElement(['sale_closed', 'execution', 'paused', 'finished']),
            'by_user_id' => User::query()->inRandomOrder()->value('id'),
        ];
    }
}
