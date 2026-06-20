<?php

namespace Database\Factories;

use App\Enums\ExecutionSubStatus;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $status = fake()->randomElement([ProjectStatus::SaleClosed, ProjectStatus::Execution, ProjectStatus::Paused, ProjectStatus::Finished]);
        $isExecutionLike = in_array($status, [ProjectStatus::Execution, ProjectStatus::Paused], true);

        return [
            'name' => 'Operacion '.fake()->company(),
            'status' => $status->value,
            'execution_sub_status' => $isExecutionLike ? fake()->randomElement(ExecutionSubStatus::cases())->value : null,
            'client_id' => Client::query()->inRandomOrder()->value('id'),
            'opportunity_id' => Opportunity::query()->inRandomOrder()->value('id'),
            'prospection_notes' => fake()->optional()->paragraph(),
            'proposal_url' => fake()->optional()->url(),
            'excel_url' => fake()->optional()->url(),
            'total_cost' => fake()->numberBetween(200000, 4000000),
            'estimated_start_date' => fake()->optional()->dateTimeBetween('-5 months', '+2 months'),
            'estimated_end_date' => fake()->optional()->dateTimeBetween('+1 month', '+8 months'),
            'sprint_close_day' => $isExecutionLike || $status === ProjectStatus::Finished ? fake()->numberBetween(1, 28) : null,
            'actual_start_date' => $isExecutionLike || $status === ProjectStatus::Finished ? fake()->optional()->dateTimeBetween('-5 months', '-1 day') : null,
            'actual_end_date' => $status === ProjectStatus::Finished ? fake()->optional()->dateTimeBetween('-2 months', 'now') : null,
            'pause_reason' => $status === ProjectStatus::Paused ? fake()->sentence(10) : null,
            'paused_at' => $status === ProjectStatus::Paused ? fake()->dateTimeBetween('-2 months', 'now') : null,
        ];
    }
}
