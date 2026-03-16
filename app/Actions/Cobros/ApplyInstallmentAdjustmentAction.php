<?php

namespace App\Actions\Cobros;

use App\Enums\Cobros\AdjustmentType;
use App\Enums\Cobros\AdjustmentValueType;
use App\Models\InstallmentAdjustment;
use App\Models\PaymentInstallment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ApplyInstallmentAdjustmentAction
{
    public function __construct(
        protected RecalculateInstallmentTotalsAction $recalculateInstallmentTotalsAction,
        protected UpdateInstallmentStatusAction $updateInstallmentStatusAction,
    ) {
    }

    public function execute(
        PaymentInstallment $installment,
        AdjustmentType|string $type,
        AdjustmentValueType|string $valueType,
        float $value,
        string $reason,
        ?int $createdBy = null,
    ): InstallmentAdjustment {
        $type = $type instanceof AdjustmentType
            ? $type
            : AdjustmentType::from($type);

        $valueType = $valueType instanceof AdjustmentValueType
            ? $valueType
            : AdjustmentValueType::from($valueType);

        if ($value <= 0) {
            throw new InvalidArgumentException('El valor del ajuste debe ser mayor a cero.');
        }

        if (blank(trim($reason))) {
            throw new InvalidArgumentException('El motivo del ajuste es obligatorio.');
        }

        return DB::transaction(function () use (
            $installment,
            $type,
            $valueType,
            $value,
            $reason,
            $createdBy
        ) {
            $appliedAmount = $this->resolveAppliedAmount(
                $installment,
                $type,
                $valueType,
                $value
            );

            $adjustment = InstallmentAdjustment::query()->create([
                'payment_installment_id' => $installment->id,
                'type' => $type,
                'value_type' => $valueType,
                'value' => $value,
                'applied_amount' => $appliedAmount,
                'reason' => trim($reason),
                'created_by' => $createdBy,
            ]);

            if ($type === AdjustmentType::Discount) {
                $installment->discounts_amount = round(
                    (float) $installment->discounts_amount + $appliedAmount,
                    2
                );
            }

            if ($type === AdjustmentType::Surcharge) {
                $installment->surcharges_amount = round(
                    (float) $installment->surcharges_amount + $appliedAmount,
                    2
                );
            }

            $installment->save();

            $this->recalculateInstallmentTotalsAction->execute($installment);
            $this->updateInstallmentStatusAction->execute($installment);

            return $adjustment->refresh();
        });
    }

    protected function resolveAppliedAmount(
        PaymentInstallment $installment,
        AdjustmentType $type,
        AdjustmentValueType $valueType,
        float $value,
    ): float {
        if ($valueType === AdjustmentValueType::Fixed) {
            return round($value, 2);
        }

        $baseAmount = (float) $installment->total_due_amount;

        if ($baseAmount <= 0) {
            $baseAmount = (float) $installment->capital_amount;
        }

        $appliedAmount = $baseAmount * ($value / 100);

        return round($appliedAmount, 2);
    }
}