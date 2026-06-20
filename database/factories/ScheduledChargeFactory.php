<?php

namespace Database\Factories;

use App\Enums\Cobros\ScheduledChargeFrequency;
use App\Enums\Cobros\ScheduledChargeStatus;
use App\Models\Client;
use App\Models\ScheduledCharge;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledChargeFactory extends Factory
{
    protected $model = ScheduledCharge::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::query()->inRandomOrder()->value('id') ?? Client::factory(),
            'created_by' => User::query()->inRandomOrder()->value('id'),
            'reference_name' => 'Cobro '.fake()->words(3, true),
            'detail' => fake()->optional()->sentence(14),
            'amount' => fake()->randomFloat(2, 50000, 2500000),
            'charge_date' => fake()->dateTimeBetween('now', '+2 months'),
            'frequency' => fake()->randomElement(ScheduledChargeFrequency::cases())->value,
            'status' => fake()->randomElement(ScheduledChargeStatus::cases())->value,
        ];
    }
}
