<?php

namespace App\Actions\Cobros;

use App\Enums\Cobros\PaymentFlowStatus;
use App\Enums\Cobros\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentInstallment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterPaymentAction
{
    public function __construct(
        protected SyncInstallmentStatusAction $syncInstallmentStatusAction
    ) {}

    /**
     * @param  PaymentInstallment  $installment
     * @param  array{
     *     amount:numeric,
     *     paid_at:string|\DateTimeInterface,
     *     notes?:string|null
     * }  $data
     * @return Collection<int, Payment>
     */
    public function execute(PaymentInstallment $installment, array $data, ?User $user = null): Collection
    {
        return DB::transaction(function () use ($installment, $data, $user) {
            $installment->loadMissing('flow');

            $this->validateInstallment($installment);
            $this->validateInput($installment, $data);

            $amountToApply = round((float) $data['amount'], 2);
            $paidAt = $data['paid_at'];
            $notes = $data['notes'] ?? null;

            $payments = collect();
            $currentInstallment = $installment;

            while ($amountToApply > 0 && $currentInstallment) {
                $currentInstallment->refresh();
                $currentInstallment->loadMissing('flow');

                $currentBalance = round((float) $currentInstallment->balance_due, 2);

                if ($currentBalance <= 0) {
                    $currentInstallment = $this->nextOpenInstallment($currentInstallment);
                    continue;
                }

                $applied = min($amountToApply, $currentBalance);
                $remainingAfterPayment = round($currentBalance - $applied, 2);

                $payment = $currentInstallment->payments()->create([
                    'paid_by' => $user?->id,
                    'paid_at' => $paidAt,
                    'amount' => $applied,
                    'remaining_after_payment' => $remainingAfterPayment,
                    'status' => PaymentStatus::Posted->value,
                    'notes' => $notes,
                ]);

                $currentInstallment->update([
                    'paid_amount' => round((float) $currentInstallment->paid_amount + $applied, 2),
                    'balance_due' => $remainingAfterPayment,
                    'last_payment_at' => $paidAt,
                    'locked_at' => $currentInstallment->locked_at ?? now(),
                ]);

                $this->syncInstallmentStatusAction->execute($currentInstallment, $user);

                $payments->push($payment);

                $amountToApply = round($amountToApply - $applied, 2);

                if ($amountToApply <= 0) {
                    break;
                }

                $currentInstallment = $this->nextOpenInstallment($currentInstallment);
            }

            // Por seguridad: si llegó acá con remanente, algo quedó mal
            if ($amountToApply > 0) {
                throw ValidationException::withMessages([
                    'amount' => 'No fue posible distribuir completamente el pago.',
                ]);
            }

            return $payments;
        });
    }

    protected function validateInstallment(PaymentInstallment $installment): void
    {
        $flow = $installment->flow;

        if (! $flow) {
            throw ValidationException::withMessages([
                'installment' => 'La cuota no pertenece a un flujo válido.',
            ]);
        }

        if ($flow->status === PaymentFlowStatus::Cancelled) {
            throw ValidationException::withMessages([
                'flow' => 'No se pueden registrar pagos en un flujo cancelado.',
            ]);
        }
    }

    protected function validateInput(PaymentInstallment $installment, array $data): void
    {
        $amount = round((float) ($data['amount'] ?? 0), 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'El monto del pago debe ser mayor a cero.',
            ]);
        }

        if (empty($data['paid_at'])) {
            throw ValidationException::withMessages([
                'paid_at' => 'La fecha de pago es obligatoria.',
            ]);
        }

        $availableBalance = $this->availableBalanceFromHere($installment);

        if ($amount > $availableBalance) {
            throw ValidationException::withMessages([
                'amount' => 'El monto excede el saldo disponible desde esta cuota en adelante.',
            ]);
        }
    }

    protected function availableBalanceFromHere(PaymentInstallment $installment): float
    {
        return round(
            (float) $installment->flow
                ->installments()
                ->where('number', '>=', $installment->number)
                ->sum('balance_due'),
            2
        );
    }

    protected function nextOpenInstallment(PaymentInstallment $installment): ?PaymentInstallment
    {
        return $installment->flow
            ->installments()
            ->where('number', '>', $installment->number)
            ->where('balance_due', '>', 0)
            ->orderBy('number')
            ->first();
    }
}