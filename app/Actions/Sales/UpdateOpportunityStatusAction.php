<?php

namespace App\Actions\Sales;

use App\Enums\Sales\OpportunityStatus;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateOpportunityStatusAction
{
    public function execute(Opportunity $opportunity, string $status, ?User $user = null): Opportunity
    {
        $statusEnum = OpportunityStatus::tryFrom($status);

        if (! $statusEnum) {
            throw ValidationException::withMessages([
                'status' => 'El estado seleccionado no es válido.',
            ]);
        }

        return DB::transaction(function () use ($opportunity, $statusEnum, $user) {
            if ($opportunity->status === $statusEnum) {
                return $opportunity;
            }

            $opportunity->update([
                'status' => $statusEnum->value,
            ]);

            $opportunity->statusLogs()->create([
                'status' => $statusEnum->value,
                'by_user_id' => $user?->id,
                'created_at' => now(),
            ]);

            return $opportunity->fresh(['client.contact', 'responsibleUser', 'notes.byUser', 'statusLogs.byUser']);
        });
    }
}
