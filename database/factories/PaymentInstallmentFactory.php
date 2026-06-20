<?php

namespace Database\Factories;

use App\Enums\Cobros\InstallmentStatus;
use App\Models\PaymentFlow;
use App\Models\PaymentInstallment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentInstallmentFactory extends Factory
{
    protected $model = PaymentInstallment::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 20000, 500000);
        $status = fake()->randomElement(InstallmentStatus::cases());
        $paidAmount = match ($status) {
            InstallmentStatus::Pending, InstallmentStatus::Overdue => 0,
            InstallmentStatus::Partial => fake()->randomFloat(2, 1000, (float) $amount - 1000),
            InstallmentStatus::Paid => $amount,
        };

        return [
            'payment_flow_id' => PaymentFlow::query()->inRandomOrder()->value('id'),
            'number' => fake()->numberBetween(1, 24),
            'due_date' => fake()->dateTimeBetween('-6 months', '+6 months'),
            'amount' => $amount,
            'paid_amount' => $paidAmount,
            'balance_due' => max($amount - $paidAmount, 0),
            'status' => $status->value,
            'paid_at' => $status === InstallmentStatus::Paid ? fake()->dateTimeBetween('-6 months', 'now') : null,
            'last_payment_at' => in_array($status, [InstallmentStatus::Partial, InstallmentStatus::Paid], true) ? fake()->dateTimeBetween('-6 months', 'now') : null,
            'locked_at' => fake()->optional()->dateTimeBetween('-5 months', 'now'),
        ];
    }
}
