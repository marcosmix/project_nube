<?php

namespace App\Actions\Cobros;

use App\Enums\Cobros\PaymentFlowStatus;
use App\Models\PaymentFlow;
use App\Models\PaymentFlowStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelPaymentFlowAction
{
    public function execute(PaymentFlow $flow, string $reason, ?User $user = null): PaymentFlow
    {
        if (in_array($flow->status, [PaymentFlowStatus::Completed, PaymentFlowStatus::Cancelled], true)) {
            throw ValidationException::withMessages(['flow' => 'Este flujo ya no puede cancelarse.']);
        }

        return DB::transaction(function () use ($flow, $reason, $user) {
            $from = $flow->status->value;
            $flow->update(['status' => PaymentFlowStatus::Cancelled, 'cancelled_at' => now(), 'cancelled_reason' => trim($reason), 'cancelled_by' => $user?->id]);
            PaymentFlowStatusLog::create(['payment_flow_id' => $flow->id, 'from_status' => $from, 'to_status' => PaymentFlowStatus::Cancelled->value, 'reason' => trim($reason), 'by_user_id' => $user?->id, 'changed_at' => now()]);
            return $flow->fresh();
        });
    }
}
