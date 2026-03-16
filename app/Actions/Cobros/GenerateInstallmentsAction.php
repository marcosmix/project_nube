<?php

namespace App\Actions\Cobros;

use App\Enums\Cobros\InstallmentStatus;
use App\Models\PaymentFlow;
use App\Models\PaymentInstallment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class GenerateInstallmentsAction
{
    public function execute(PaymentFlow $flow): Collection
    {
        if ($flow->installments_count < 1) {
            throw new InvalidArgumentException('El flujo debe tener al menos una cuota.');
        }

        if (! $flow->first_due_date) {
            throw new InvalidArgumentException('El flujo debe tener una primera fecha de vencimiento.');
        }

        $flow->installments()->delete();

        $installments = collect();

        $baseAmount = round((float) $flow->total_amount / (int) $flow->installments_count, 2);
        $totalAssigned = 0;

        for ($i = 1; $i <= $flow->installments_count; $i++) {
            $amount = $i === $flow->installments_count
                ? round((float) $flow->total_amount - $totalAssigned, 2)
                : $baseAmount;

            $dueDate = $this->calculateDueDate($flow, $i);
            $billingDate = $this->calculateBillingDate($flow, $dueDate);

            $installment = $flow->installments()->create([
                'number' => $i,
                'label' => 'Cuota ' . $i,
                'status' => InstallmentStatus::Pending,
                'scheduled_amount' => $amount,
                'capital_amount' => $amount,
                'interest_deferred_in_amount' => 0,
                'interest_generated_amount' => 0,
                'interest_adjustments_amount' => 0,
                'discounts_amount' => 0,
                'surcharges_amount' => 0,
                'paid_amount' => 0,
                'carried_over_payment_amount' => 0,
                'outstanding_amount' => $amount,
                'total_due_amount' => $amount,
                'billing_date' => $billingDate,
                'due_date' => $dueDate,
                'grace_ends_at' => $dueDate->copy()->addDays((int) $flow->grace_days),
                'interest_starts_at' => $dueDate->copy()->addDays((int) $flow->grace_days + 1),
            ]);

            $installments->push($installment);
            $totalAssigned += $amount;
        }

        return $installments;
    }

    protected function calculateDueDate(PaymentFlow $flow, int $installmentNumber): Carbon
    {
        $firstDueDate = Carbon::parse($flow->first_due_date);

        if ($installmentNumber === 1) {
            return $firstDueDate->copy();
        }

        $candidate = $firstDueDate->copy()->addMonthsNoOverflow($installmentNumber - 1);

        if ($flow->due_day) {
            $day = min((int) $flow->due_day, $candidate->daysInMonth);
            $candidate->day = $day;
        }

        return $candidate;
    }

    protected function calculateBillingDate(PaymentFlow $flow, Carbon $dueDate): Carbon
    {
        if (! $flow->billing_day) {
            return $dueDate->copy();
        }

        $billingDate = $dueDate->copy();
        $day = min((int) $flow->billing_day, $billingDate->daysInMonth);
        $billingDate->day = $day;

        return $billingDate;
    }
}