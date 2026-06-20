<?php

namespace Database\Seeders;

use App\Enums\Cobros\InstallmentStatus;
use App\Models\Payment;
use App\Models\PaymentFlow;
use App\Models\PaymentInstallment;
use App\Models\PaymentInstallmentStatusLog;
use App\Models\PaymentReceipt;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

class CobrosDemoSeeder extends Seeder
{
    public function run(): void
    {
        $distribution = [
            'draft' => 6,
            'active' => 10,
            'completed' => 5,
            'cancelled' => 4,
        ];

        $users = User::query()->pluck('id')->all();
        $projectIds = Project::query()->pluck('id')->all();
        $availableProjectIds = $projectIds;

        foreach ($distribution as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                if ($availableProjectIds === []) {
                    break;
                }

                $projectId = array_shift($availableProjectIds);
                $installmentsCount = fake()->numberBetween(3, 8);
                $totalAmount = fake()->randomFloat(2, 250000, 5000000);

                $flow = PaymentFlow::factory()->create([
                    'project_id' => $projectId,
                    'created_by' => fake()->randomElement($users),
                    'status' => $status,
                    'installments_count' => $installmentsCount,
                    'total_amount' => $totalAmount,
                ]);

                $this->createInstallmentsAndPayments($flow, $status, $installmentsCount, $totalAmount, $users);
            }
        }
    }

    private function createInstallmentsAndPayments(PaymentFlow $flow, string $flowStatus, int $installmentsCount, float $totalAmount, array $users): void
    {
        $baseAmount = round($totalAmount / $installmentsCount, 2);
        $remaining = round($totalAmount, 2);

        for ($number = 1; $number <= $installmentsCount; $number++) {
            $amount = $number === $installmentsCount ? $remaining : $baseAmount;
            $remaining = round($remaining - $amount, 2);

            [$status, $paidAmount, $dueDate] = $this->resolveInstallmentState($flowStatus, $number);
            $paidAmount = match ($status) {
                InstallmentStatus::Paid->value => $amount,
                InstallmentStatus::Partial->value => round(min($paidAmount, max($amount - 1, 1)), 2),
                default => 0,
            };

            $installment = PaymentInstallment::create([
                'payment_flow_id' => $flow->id,
                'number' => $number,
                'due_date' => $dueDate,
                'amount' => $amount,
                'paid_amount' => $paidAmount,
                'balance_due' => round($amount - $paidAmount, 2),
                'status' => $status,
                'paid_at' => $status === InstallmentStatus::Paid->value ? $dueDate->copy()->addDays(fake()->numberBetween(0, 5)) : null,
                'last_payment_at' => in_array($status, [InstallmentStatus::Partial->value, InstallmentStatus::Paid->value], true) ? $dueDate->copy()->addDays(fake()->numberBetween(0, 5)) : null,
                'locked_at' => $flowStatus === 'cancelled' ? now()->subDays(fake()->numberBetween(1, 60)) : null,
            ]);

            PaymentInstallmentStatusLog::create([
                'payment_installment_id' => $installment->id,
                'changed_by' => fake()->randomElement($users),
                'from_status' => null,
                'to_status' => $status,
                'changed_at' => $dueDate->copy()->subDays(2),
            ]);

            if ($paidAmount > 0) {
                $payment = Payment::create([
                    'payment_installment_id' => $installment->id,
                    'paid_by' => fake()->randomElement($users),
                    'paid_at' => $installment->last_payment_at ?? now(),
                    'amount' => $paidAmount,
                    'remaining_after_payment' => max($amount - $paidAmount, 0),
                    'status' => 'posted',
                    'payment_method' => fake()->randomElement(Payment::METHODS),
                    'notes' => fake()->optional()->sentence(8),
                ]);

                PaymentReceipt::factory()->count(fake()->numberBetween(1, 2))->create([
                    'payment_id' => $payment->id,
                    'uploaded_by' => fake()->randomElement($users),
                ]);
            }
        }
    }

    private function resolveInstallmentState(string $flowStatus, int $number): array
    {
        $dueDate = now()->copy()->startOfDay()->subMonths(4)->addWeeks($number * 2);

        if ($flowStatus === 'draft') {
            return [InstallmentStatus::Pending->value, 0, $dueDate->copy()->addMonths(2)];
        }

        if ($flowStatus === 'completed') {
            return [InstallmentStatus::Paid->value, 0, $dueDate];
        }

        if ($flowStatus === 'cancelled') {
            $status = fake()->randomElement([InstallmentStatus::Pending->value, InstallmentStatus::Partial->value, InstallmentStatus::Overdue->value]);
            $paidAmount = $status === InstallmentStatus::Partial->value ? fake()->randomFloat(2, 5000, 60000) : 0;

            return [$status, $paidAmount, $dueDate];
        }

        $status = match (true) {
            $number <= 2 => InstallmentStatus::Paid->value,
            $number === 3 => InstallmentStatus::Partial->value,
            $number === 4 => InstallmentStatus::Overdue->value,
            default => InstallmentStatus::Pending->value,
        };

        $paidAmount = match ($status) {
            InstallmentStatus::Paid->value => 0,
            InstallmentStatus::Partial->value => fake()->randomFloat(2, 5000, 60000),
            default => 0,
        };

        return [$status, $paidAmount, $dueDate];
    }
}
