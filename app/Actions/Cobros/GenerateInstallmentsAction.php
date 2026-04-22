<?php

namespace App\Actions\Cobros;

use App\Enums\Cobros\InstallmentStatus;
use App\Enums\Cobros\PaymentFrequency;
use App\Models\PaymentFlow;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GenerateInstallmentsAction
{
    public function execute(PaymentFlow $flow): Collection
    {
        $this->validateFlow($flow);

        return DB::transaction(function () use ($flow) {
            $flow->loadMissing('installments.payments');

            // Si ya hay cuotas con pagos, no permitimos regenerar
            $hasPayments = $flow->installments->contains(
                fn ($installment) => $installment->payments->isNotEmpty()
            );

            if ($hasPayments) {
                throw ValidationException::withMessages([
                    'installments' => 'No se pueden regenerar cuotas porque ya existen pagos registrados.',
                ]);
            }

            // Si existen cuotas sin pagos, se borran y se recrean
            if ($flow->installments->isNotEmpty()) {
                $flow->installments()->delete();
            }

            $amounts = $this->splitAmountIntoInstallments(
                (float) $flow->total_amount,
                (int) $flow->installments_count
            );

            $startDate = Carbon::parse($flow->start_date)->startOfDay();
            $items = collect();

            foreach ($amounts as $index => $amount) {
                $number = $index + 1;
                $dueDate = $this->calculateDueDate(
                    clone $startDate,
                    $flow->frequency->value,
                    $index
                );

                $items->push(
                    $flow->installments()->create([
                        'number' => $number,
                        'due_date' => $dueDate->toDateString(),
                        'amount' => $amount,
                        'paid_amount' => 0,
                        'balance_due' => $amount,
                        'status' => InstallmentStatus::Pending->value,
                    ])
                );
            }

            return $items;
        });
    }

    protected function validateFlow(PaymentFlow $flow): void
    {
        if ((int) $flow->installments_count <= 0) {
            throw ValidationException::withMessages([
                'installments_count' => 'La cantidad de cuotas debe ser mayor a cero.',
            ]);
        }

        if ((float) $flow->total_amount <= 0) {
            throw ValidationException::withMessages([
                'total_amount' => 'El monto total debe ser mayor a cero.',
            ]);
        }
    }

    /**
     * Divide un monto total en N cuotas,
     * ajustando centavos en las primeras cuotas.
     *
     * Ejemplo:
     * 1000 / 3 => [333.34, 333.33, 333.33]
     */
    protected function splitAmountIntoInstallments(float $totalAmount, int $count): array
    {
        $totalCents = (int) round($totalAmount * 100);
        $base = intdiv($totalCents, $count);
        $remainder = $totalCents % $count;

        $amounts = [];

        for ($i = 0; $i < $count; $i++) {
            $cents = $base + ($i < $remainder ? 1 : 0);
            $amounts[] = number_format($cents / 100, 2, '.', '');
        }

        return $amounts;
    }

    protected function calculateDueDate(Carbon $startDate, string $frequency, int $index): Carbon
    {
        return match ($frequency) {
            PaymentFrequency::Weekly->value => $startDate->addWeeks($index),
            PaymentFrequency::Biweekly->value => $startDate->addWeeks($index * 2),
            PaymentFrequency::Monthly->value => $startDate->addMonthsNoOverflow($index),
            default => $startDate->addMonthsNoOverflow($index),
        };
    }
}