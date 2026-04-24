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
     * @param  array{
     *     amount:numeric,
     *     paid_at:string|\DateTimeInterface,
     *     notes?:string|null,
     *     payment_method?:string|null
     * }  $data
     * @return Collection<int, Payment>
     */
    public function execute(PaymentInstallment $installment, array $data, ?User $user = null): Collection
    {
        return DB::transaction(function () use ($installment, $data, $user) {
            $installment->refresh();
            $installment->loadMissing('flow');

            $this->validateInstallment($installment);
            $this->validateInput($installment, $data);

            $amount = round((float) $data['amount'], 2);
            $paidAt = $data['paid_at'];
            $notes = $data['notes'] ?? null;
            $paymentMethod = $data['payment_method'] ?? null;
            $remainingAfterPayment = round((float) $installment->balance_due - $amount, 2);

            $payment = $installment->payments()->create([
                'paid_by' => $user?->id,
                'paid_at' => $paidAt,
                'amount' => $amount,
                'remaining_after_payment' => $remainingAfterPayment,
                'status' => PaymentStatus::Posted->value,
                'payment_method' => $paymentMethod,
                'notes' => $notes,
            ]);

            $installment->update([
                'paid_amount' => round((float) $installment->paid_amount + $amount, 2),
                'balance_due' => $remainingAfterPayment,
                'last_payment_at' => $paidAt,
                'locked_at' => $installment->locked_at ?? now(),
            ]);

            $this->syncInstallmentStatusAction->execute($installment, $user);

            return collect([$payment]);
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
        $balanceDue = round((float) $installment->balance_due, 2);
        $paymentMethod = $data['payment_method'] ?? null;

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

        if ($balanceDue <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'La cuota seleccionada ya no tiene saldo pendiente.',
            ]);
        }

        if ($amount > $balanceDue) {
            throw ValidationException::withMessages([
                'amount' => 'El monto no puede ser mayor al saldo pendiente de la cuota.',
            ]);
        }

        if ($paymentMethod !== null && $paymentMethod !== '' && ! in_array($paymentMethod, Payment::METHODS, true)) {
            throw ValidationException::withMessages([
                'payment_method' => 'El tipo de pago seleccionado no es válido.',
            ]);
        }
    }
}
