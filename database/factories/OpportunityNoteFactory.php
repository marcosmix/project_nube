<?php

namespace Database\Factories;

use App\Models\Opportunity;
use App\Models\OpportunityNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpportunityNoteFactory extends Factory
{
    protected $model = OpportunityNote::class;

    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::query()->inRandomOrder()->value('id'),
            'by_user_id' => User::query()->inRandomOrder()->value('id'),
            'content' => fake()->paragraph(),
        ];
    }
}
