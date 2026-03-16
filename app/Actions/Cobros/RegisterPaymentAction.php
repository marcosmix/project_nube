<?php

namespace App\Actions\Cobros;

use App\Enums\Cobros\PaymentMethod;
use App\Enums\Cobros\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentInstallment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RegisterPaymentAction
{
    public function __construct(
        protected RecalculateInstallmentTotalsAction $recalculateInstallmentTotalsAction,
        protected UpdateInstallmentStatusAction $updateInstallmentStatusAction,
    ) {
    }

    public function execute(
        PaymentInstallment $installment,
        float $amount,
        PaymentMethod|string $paymentMethod,
        ?Carbon $paidAt = null,
        ?string $receiptPath = null,
        ?string $reference = null,
        ?string $notes = null,
        ?int $createdBy = null,
    ): Payment {
        if ($amount <= 0) {
            throw new InvalidArgumentException('El monto del pago debe ser mayor a cero.');
        }

        if ($installment->cancelled_at) {
            throw new InvalidArgumentException('No se puede registrar un pago sobre una cuota cancelada.');
        }

        $paymentMethod = $paymentMethod instanceof PaymentMethod
            ? $paymentMethod
            : PaymentMethod::from($paymentMethod);

        return DB::transaction(function () use (
            $installment,
            $amount,
            $paymentMethod,
            $paidAt,
            $receiptPath,
            $reference,
            $notes,
            $createdBy
        ) {
            $installment->refresh();

            $currentTotalDue = (float) $installment->total_due_amount;
            $currentOutstanding = (float) $installment->outstanding_amount;

            if ($currentTotalDue < 0) {
                $currentTotalDue = 0;
            }

            if ($currentOutstanding < 0) {
                $currentOutstanding = 0;
            }

            $capitalRemaining = max(
                (float) $installment->capital_amount - (float) $installment->paid_amount,
                0
            );

            $capitalAppliedAmount = min($amount, $capitalRemaining);

            $remainingAfterCapital = max($amount - $capitalAppliedAmount, 0);

            $interestAndExtrasOutstanding = max($currentOutstanding - $capitalRemaining, 0);

            $interestAppliedAmount = min($remainingAfterCapital, $interestAndExtrasOutstanding);

            $carriedForwardAmount = max($amount - $currentOutstanding, 0);

            $installment->paid_amount = round(
                (float) $installment->paid_amount + $amount,
                2
            );

            $installment->save();

            $this->recalculateInstallmentTotalsAction->execute($installment);
            $this->updateInstallmentStatusAction->execute($installment);

            return Payment::query()->create([
                'payment_flow_id' => $installment->payment_flow_id,
                'payment_installment_id' => $installment->id,
                'status' => PaymentStatus::Recorded,
                'payment_method' => $paymentMethod,
                'amount' => round($amount, 2),
                'capital_applied_amount' => round($capitalAppliedAmount, 2),
                'interest_applied_amount' => round($interestAppliedAmount, 2),
                'surcharge_applied_amount' => 0,
                'discount_applied_amount' => 0,
                'carried_forward_amount' => round($carriedForwardAmount, 2),
                'paid_at' => $paidAt ?? now(),
                'receipt_path' => $receiptPath,
                'reference' => $reference,
                'notes' => $notes,
                'created_by' => $createdBy,
            ]);
        });
    }
}