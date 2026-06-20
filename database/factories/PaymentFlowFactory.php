<?php

namespace Database\Factories;

use App\Enums\Cobros\PaymentFlowStatus;
use App\Enums\Cobros\PaymentFrequency;
use App\Models\PaymentFlow;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFlowFactory extends Factory
{
    protected $model = PaymentFlow::class;

    public function definition(): array
    {
        $status = fake()->randomElement(PaymentFlowStatus::cases());
        $startDate = fake()->dateTimeBetween('-8 months', '+2 months');

        return [
            'project_id' => Project::query()->inRandomOrder()->value('id'),
            'created_by' => User::query()->inRandomOrder()->value('id'),
            'total_amount' => fake()->randomFloat(2, 200000, 4500000),
            'installments_count' => fake()->numberBetween(3, 8),
            'frequency' => fake()->randomElement(PaymentFrequency::cases())->value,
            'start_date' => $startDate,
            'grace_days' => fake()->numberBetween(0, 10),
            'notes' => fake()->optional()->sentence(14),
            'status' => $status->value,
            'auto_send_enabled' => fake()->boolean(35),
            'auto_send_email' => fake()->boolean(50) ? fake()->safeEmail() : null,
            'activated_at' => in_array($status, [PaymentFlowStatus::Active, PaymentFlowStatus::Completed, PaymentFlowStatus::Cancelled], true) ? fake()->dateTimeBetween('-8 months', 'now') : null,
            'completed_at' => $status === PaymentFlowStatus::Completed ? fake()->dateTimeBetween('-4 months', 'now') : null,
        ];
    }
}
