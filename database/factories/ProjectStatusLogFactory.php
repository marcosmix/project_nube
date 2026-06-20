<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectStatusLogFactory extends Factory
{
    protected $model = ProjectStatusLog::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::query()->inRandomOrder()->value('id'),
            'status' => fake()->randomElement(['sale_closed', 'execution', 'paused', 'finished']),
            'by_user_id' => User::query()->inRandomOrder()->value('id'),
            'created_at' => fake()->dateTimeBetween('-8 months', 'now'),
        ];
    }
}
