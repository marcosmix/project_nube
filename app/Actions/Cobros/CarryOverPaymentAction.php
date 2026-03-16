<?php

namespace App\Actions\Cobros;

use App\Models\Payment;
use App\Models\PaymentInstallment;
use Illuminate\Support\Facades\DB;

class CarryOverPaymentAction
{
    public function __construct(
        protected RecalculateInstallmentTotalsAction $recalculateInstallmentTotalsAction,
        protected UpdateInstallmentStatusAction $updateInstallmentStatusAction,
    ) {
    }

    public function execute(Payment $payment): ?PaymentInstallment
    {
        $payment->refresh();

        $carriedForwardAmount = (float) $payment->carried_forward_amount;

        if ($carriedForwardAmount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($payment, $carriedForwardAmount) {
            $currentInstallment = $payment->installment()->first();

            if (! $currentInstallment) {
                return null;
            }

            $nextInstallment = PaymentInstallment::query()
                ->where('payment_flow_id', $currentInstallment->payment_flow_id)
                ->where('number', '>', $currentInstallment->number)
                ->whereNull('cancelled_at')
                ->orderBy('number')
                ->first();

            if (! $nextInstallment) {
                return null;
            }

            $nextInstallment->carried_over_payment_amount = round(
                (float) $nextInstallment->carried_over_payment_amount + $carriedForwardAmount,
                2
            );

            $nextInstallment->save();

            $this->recalculateInstallmentTotalsAction->execute($nextInstallment);
            $this->updateInstallmentStatusAction->execute($nextInstallment);

            return $nextInstallment->refresh();
        });
    }
}