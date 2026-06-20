<?php

namespace Database\Factories;

use App\Enums\Sales\OpportunityStatus;
use App\Models\Opportunity;
use App\Models\OpportunityStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpportunityStatusLogFactory extends Factory
{
    protected $model = OpportunityStatusLog::class;

    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::query()->inRandomOrder()->value('id'),
            'status' => fake()->randomElement(OpportunityStatus::cases())->value,
            'by_user_id' => User::query()->inRandomOrder()->value('id'),
            'created_at' => fake()->dateTimeBetween('-9 months', 'now'),
        ];
    }
}
