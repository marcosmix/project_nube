<?php

namespace Database\Factories;

use App\Enums\Cobros\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentInstallment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 1000, 150000);
        $voided = fake()->boolean(10);

        return [
            'payment_installment_id' => PaymentInstallment::query()->inRandomOrder()->value('id'),
            'paid_by' => User::query()->inRandomOrder()->value('id'),
            'paid_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'amount' => $amount,
            'remaining_after_payment' => fake()->randomFloat(2, 0, 200000),
            'status' => ($voided ? PaymentStatus::Voided : PaymentStatus::Posted)->value,
            'payment_method' => fake()->randomElement(Payment::METHODS),
            'notes' => fake()->optional()->sentence(10),
            'voided_at' => $voided ? fake()->dateTimeBetween('-3 months', 'now') : null,
            'voided_by' => $voided ? User::query()->inRandomOrder()->value('id') : null,
            'void_reason' => $voided ? fake()->sentence(8) : null,
        ];
    }
}
