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
    /**
     * @param  array<int, array{amount:numeric, due_date:string|\DateTimeInterface}>|null  $customInstallments
     */
    public function execute(PaymentFlow $flow, ?array $customInstallments = null): Collection
    {
        $this->validateFlow($flow);
        $customInstallments = $this->normalizeCustomInstallments($flow, $customInstallments);

        return DB::transaction(function () use ($flow, $customInstallments) {
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

            $items = collect();

            if ($customInstallments !== null) {
                foreach ($customInstallments as $index => $item) {
                    $number = $index + 1;
                    $amount = number_format((float) $item['amount'], 2, '.', '');
                    $dueDate = Carbon::parse($item['due_date'])->startOfDay();

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
            }

            $amounts = $this->splitAmountIntoInstallments(
                (float) $flow->total_amount,
                (int) $flow->installments_count
            );

            $startDate = Carbon::parse($flow->start_date)
                ->addDays((int) $flow->grace_days)
                ->startOfDay();

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
     * @param  array<int, array{amount:numeric, due_date:string|\DateTimeInterface}>|null  $customInstallments
     * @return array<int, array{amount:float, due_date:string}>|null
     */
    protected function normalizeCustomInstallments(PaymentFlow $flow, ?array $customInstallments): ?array
    {
        if ($customInstallments === null) {
            return null;
        }

        if (count($customInstallments) !== (int) $flow->installments_count) {
            throw ValidationException::withMessages([
                'installments' => 'La cantidad de cuotas personalizadas no coincide con el flujo.',
            ]);
        }

        $normalized = collect($customInstallments)
            ->values()
            ->map(function (array $item) {
                if (! array_key_exists('amount', $item) || ! array_key_exists('due_date', $item)) {
                    throw ValidationException::withMessages([
                        'installments' => 'Cada cuota personalizada debe incluir monto y fecha de vencimiento.',
                    ]);
                }

                $amount = round((float) $item['amount'], 2);

                if ($amount <= 0) {
                    throw ValidationException::withMessages([
                        'installments' => 'Cada cuota debe tener un monto mayor a cero.',
                    ]);
                }

                return [
                    'amount' => $amount,
                    'due_date' => Carbon::parse($item['due_date'])->toDateString(),
                ];
            });

        $sum = round((float) $normalized->sum('amount'), 2);
        $expected = round((float) $flow->total_amount, 2);

        if (abs($sum - $expected) > 0.009) {
            throw ValidationException::withMessages([
                'installments' => 'La suma de cuotas debe coincidir con el monto total del flujo.',
            ]);
        }

        return $normalized->all();
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
