<?php

namespace App\Actions\Cobros;

use App\Models\PaymentInstallment;

class RecalculateInstallmentTotalsAction
{
    public function execute(PaymentInstallment $installment): PaymentInstallment
    {
        $capitalAmount = (float) $installment->capital_amount;
        $interestDeferredInAmount = (float) $installment->interest_deferred_in_amount;
        $interestGeneratedAmount = (float) $installment->interest_generated_amount;
        $interestAdjustmentsAmount = (float) $installment->interest_adjustments_amount;
        $surchargesAmount = (float) $installment->surcharges_amount;
        $discountsAmount = (float) $installment->discounts_amount;
        $carriedOverPaymentAmount = (float) $installment->carried_over_payment_amount;
        $paidAmount = (float) $installment->paid_amount;

        $totalDueAmount = round(
            $capitalAmount
            + $interestDeferredInAmount
            + $interestGeneratedAmount
            + $interestAdjustmentsAmount
            + $surchargesAmount
            - $discountsAmount
            - $carriedOverPaymentAmount,
            2
        );

        if ($totalDueAmount < 0) {
            $totalDueAmount = 0;
        }

        $outstandingAmount = round($totalDueAmount - $paidAmount, 2);

        if ($outstandingAmount < 0) {
            $outstandingAmount = 0;
        }

        $installment->forceFill([
            'total_due_amount' => $totalDueAmount,
            'outstanding_amount' => $outstandingAmount,
        ])->save();

        return $installment->refresh();
    }
}