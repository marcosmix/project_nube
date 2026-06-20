<?php

namespace Database\Factories;

use App\Enums\Cobros\InstallmentStatus;
use App\Models\PaymentInstallment;
use App\Models\PaymentInstallmentStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentInstallmentStatusLogFactory extends Factory
{
    protected $model = PaymentInstallmentStatusLog::class;

    public function definition(): array
    {
        $from = fake()->optional()->randomElement(InstallmentStatus::cases());
        $to = fake()->randomElement(InstallmentStatus::cases());

        return [
            'payment_installment_id' => PaymentInstallment::query()->inRandomOrder()->value('id'),
            'changed_by' => User::query()->inRandomOrder()->value('id'),
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'changed_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
