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

        if (! $this->isValidTransition($opportunity->status, $statusEnum)) {
            throw ValidationException::withMessages([
                'status' => 'No es posible cambiar de '.$opportunity->status->label().' a '.$statusEnum->label().'.',
            ]);
        }

        return DB::transaction(function () use ($opportunity, $statusEnum, $user) {
            if ($opportunity->status === $statusEnum) {
                return $opportunity;
            }

            $opportunity->loadMissing(['attachments', 'client']);

            if ($statusEnum === OpportunityStatus::ProposalSent) {
                $this->ensureProposalRequirements($opportunity);
            }

            $opportunity->update([
                'status' => $statusEnum->value,
            ]);

            $opportunity->statusLogs()->create([
                'status' => $statusEnum->value,
                'by_user_id' => $user?->id,
                'created_at' => now(),
            ]);

            return $opportunity->fresh(['client.contact', 'responsibleUser', 'notes.byUser', 'statusLogs.byUser', 'attachments']);
        });
    }

    public function getAllowedTransitions(OpportunityStatus $current): array
    {
        return match ($current) {
            OpportunityStatus::New => [OpportunityStatus::Contacted],
            OpportunityStatus::Contacted => [
                OpportunityStatus::Qualified,
                OpportunityStatus::Discarded,
            ],
            OpportunityStatus::Qualified => [OpportunityStatus::ProposalSent],
            OpportunityStatus::ProposalSent => [
                OpportunityStatus::Won,
                OpportunityStatus::Lost,
                OpportunityStatus::Negotiation,
            ],
            OpportunityStatus::Negotiation => [
                OpportunityStatus::Won,
                OpportunityStatus::Lost,
            ],
            OpportunityStatus::Won,
            OpportunityStatus::Lost,
            OpportunityStatus::Discarded => [],
        };
    }

    public function isValidTransition(OpportunityStatus $current, OpportunityStatus $target): bool
    {
        if ($current === $target) {
            return true;
        }

        $allowed = $this->getAllowedTransitions($current);

        return in_array($target, $allowed, true);
    }

    protected function ensureProposalRequirements(Opportunity $opportunity): void
    {
        $errors = [];

        if ((float) ($opportunity->estimated_ticket_amount ?? 0) <= 0) {
            $errors['estimated_ticket_amount'] = 'Cargá el monto estimado antes de pasar a Propuesta enviada.';
        }

        if ($opportunity->attachments->isEmpty()) {
            $errors['attachments'] = 'Adjuntá al menos un archivo comercial antes de pasar a Propuesta enviada.';
        }

        if (! $opportunity->client_id) {
            $errors['client_id'] = 'Antes de enviar la propuesta, asociá un cliente a la oportunidad.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
