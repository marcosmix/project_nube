<?php

namespace Database\Seeders;

use App\Models\Developer;
use App\Models\Opportunity;
use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\ProjectStatusLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class OperationsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $distribution = [
            'sale_closed' => 10,
            'execution' => 12,
            'paused' => 8,
            'finished' => 10,
        ];

        $users = User::query()->pluck('id')->all();
        $developerIds = Developer::query()->where('status', 'active')->pluck('id')->all();
        $wonOpportunities = Opportunity::query()->where('status', 'won')->pluck('id')->all();
        $fallbackOpportunities = Opportunity::query()->pluck('id')->all();

        foreach ($distribution as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $project = Project::factory()->create([
                    'status' => $status,
                    'opportunity_id' => ($status === 'sale_closed' || $status === 'execution' || $status === 'paused' || $status === 'finished')
                        ? fake()->randomElement($wonOpportunities !== [] ? $wonOpportunities : $fallbackOpportunities)
                        : fake()->randomElement($fallbackOpportunities),
                    'execution_sub_status' => in_array($status, ['execution', 'paused'], true)
                        ? fake()->randomElement(['on_track', 'with_debt', 'delayed'])
                        : null,
                ]);

                $project->developers()->sync(fake()->randomElements($developerIds, fake()->numberBetween(1, min(4, count($developerIds)))));

                ProjectNote::factory()->count(fake()->numberBetween(2, 4))->create([
                    'project_id' => $project->id,
                    'status' => $status,
                    'by_user_id' => fake()->randomElement($users),
                ]);

                $this->createStatusLogs($project, fake()->randomElement($users));
            }
        }
    }

    private function createStatusLogs(Project $project, ?int $userId): void
    {
        $sequence = match ($project->status->value) {
            'sale_closed' => ['sale_closed'],
            'execution' => ['sale_closed', 'execution'],
            'paused' => ['sale_closed', 'execution', 'paused'],
            'finished' => ['sale_closed', 'execution', 'finished'],
            default => ['sale_closed'],
        };

        $baseTime = now()->subDays(count($sequence) + fake()->numberBetween(20, 180));

        foreach ($sequence as $index => $status) {
            ProjectStatusLog::create([
                'project_id' => $project->id,
                'status' => $status,
                'by_user_id' => $userId,
                'created_at' => $baseTime->copy()->addDays($index * 3),
            ]);
        }
    }
}
