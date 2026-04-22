<?php

namespace App\Actions\Cobros;

use App\Enums\Cobros\InstallmentStatus;
use App\Enums\Cobros\PaymentFlowStatus;
use App\Models\PaymentFlow;
use App\Models\PaymentInstallment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SyncInstallmentStatusAction
{
    public function execute(PaymentInstallment $installment, ?User $user = null): PaymentInstallment
    {
        return DB::transaction(function () use ($installment, $user) {
            $installment->refresh();
            $installment->loadMissing('flow');

            $previousStatus = $installment->status?->value ?? $installment->status;

            $newStatus = $this->resolveStatus($installment);

            $updates = [
                'status' => $newStatus->value,
            ];

            if ((float) $installment->balance_due <= 0) {
                $updates['balance_due'] = 0;
                $updates['paid_at'] = $installment->paid_at ?? now();
            } else {
                $updates['paid_at'] = null;
            }

            if ((float) $installment->paid_amount > 0 && $installment->locked_at === null) {
                $updates['locked_at'] = now();
            }

            $installment->update($updates);

            if ($previousStatus !== $newStatus->value) {
                $installment->statusLogs()->create([
                    'changed_by' => $user?->id,
                    'from_status' => $previousStatus,
                    'to_status' => $newStatus->value,
                    'changed_at' => now(),
                ]);
            }

            $this->syncFlowStatus($installment->flow);

            return $installment->fresh(['flow', 'payments', 'statusLogs']);
        });
    }

    protected function resolveStatus(PaymentInstallment $installment): InstallmentStatus
    {
        $balance = round((float) $installment->balance_due, 2);
        $paid = round((float) $installment->paid_amount, 2);

        if ($balance <= 0) {
            return InstallmentStatus::Paid;
        }

        if ($paid > 0 && $balance > 0) {
            return InstallmentStatus::Partial;
        }

        if ($installment->isOverdue()) {
            return InstallmentStatus::Overdue;
        }

        return InstallmentStatus::Pending;
    }

    protected function syncFlowStatus(PaymentFlow $flow): void
    {
        $flow->loadMissing('installments');

        $allPaid = $flow->installments->isNotEmpty()
            && $flow->installments->every(
                fn ($item) => $item->status?->value === InstallmentStatus::Paid->value
            );

        if ($allPaid) {
            $flow->update([
                'status' => PaymentFlowStatus::Completed->value,
                'completed_at' => now(),
            ]);

            return;
        }

        // Si todavía hay cuotas pendientes/parciales/vencidas, y el flujo no está cancelado,
        // mantenelo activo.
        if ($flow->status !== PaymentFlowStatus::Cancelled) {
            $flow->update([
                'status' => PaymentFlowStatus::Active->value,
                'completed_at' => null,
            ]);
        }
    }
}