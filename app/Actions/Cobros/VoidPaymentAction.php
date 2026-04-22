<?php

namespace App\Actions\Cobros;

use App\Enums\Cobros\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VoidPaymentAction
{
    public function __construct(
        protected SyncInstallmentStatusAction $syncInstallmentStatusAction
    ) {}

    public function execute(Payment $payment, string $reason, ?User $user = null): Payment
    {
        return DB::transaction(function () use ($payment, $reason, $user) {
            $payment->loadMissing('installment.flow');

            $this->validate($payment, $reason, $user);

            $installment = $payment->installment;
            $amount = round((float) $payment->amount, 2);

            $payment->update([
                'status' => PaymentStatus::Voided->value,
                'voided_at' => now(),
                'voided_by' => $user?->id,
                'void_reason' => trim($reason),
            ]);

            $installment->update([
                'paid_amount' => max(0, round((float) $installment->paid_amount - $amount, 2)),
                'balance_due' => round((float) $installment->balance_due + $amount, 2),
            ]);

            if (
                ! $installment->payments()
                    ->where('status', PaymentStatus::Posted->value)
                    ->exists()
            ) {
                $installment->update([
                    'last_payment_at' => null,
                    'locked_at' => null,
                ]);
            } else {
                $lastPostedPayment = $installment->payments()
                    ->where('status', PaymentStatus::Posted->value)
                    ->latest('paid_at')
                    ->latest('id')
                    ->first();

                $installment->update([
                    'last_payment_at' => $lastPostedPayment?->paid_at,
                ]);
            }

            $this->syncInstallmentStatusAction->execute($installment, $user);

            return $payment->fresh(['installment', 'voidedBy']);
        });
    }

    protected function validate(Payment $payment, string $reason, ?User $user = null): void
    {
        if ($payment->status === PaymentStatus::Voided) {
            throw ValidationException::withMessages([
                'payment' => 'Este pago ya fue anulado.',
            ]);
        }

        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'El motivo de anulación es obligatorio.',
            ]);
        }

        if (! $user) {
            throw ValidationException::withMessages([
                'user' => 'Se requiere un usuario autenticado para anular pagos.',
            ]);
        }

        // Dejamos el check fino de permisos para policy/gate o Livewire.
        // Acá solo protegemos que exista usuario.
    }
}