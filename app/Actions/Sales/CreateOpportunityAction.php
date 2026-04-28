<?php

namespace App\Actions\Sales;

use App\Enums\Sales\OpportunitySource;
use App\Enums\Sales\OpportunityStatus;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateOpportunityAction
{
    public function __construct(
        protected CreateClientFromOpportunityReferenceAction $createClientFromOpportunityReferenceAction,
    ) {}

    /**
     * Crea la oportunidad y registra el estado inicial para conservar trazabilidad comercial.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data, ?User $user = null): Opportunity
    {
        return DB::transaction(function () use ($data, $user) {
            $status = $data['status'] ?? OpportunityStatus::New->value;
            $source = $data['source'] ?? OpportunitySource::Manual->value;
            $clientId = $data['client_id'] ?? null;

            if (($data['client_mode'] ?? 'unlinked') === 'create_new') {
                $client = $this->createClientFromOpportunityReferenceAction->execute($data['new_client']);
                $clientId = $client->id;
            }

            $opportunity = Opportunity::create([
                'client_id' => $clientId,
                'responsible_user_id' => $data['responsible_user_id'] ?? $user?->id,
                'name' => $data['name'],
                'status' => $status,
                'source' => $source,
                'first_contact_at' => $data['first_contact_at'] ?? null,
                'contact_name' => $data['contact_name'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'contact_handle' => $data['contact_handle'] ?? null,
                'initial_message' => $data['initial_message'] ?? null,
            ]);

            $opportunity->statusLogs()->create([
                'status' => $status,
                'by_user_id' => $user?->id,
                'created_at' => now(),
            ]);

            if (! empty($data['initial_note'])) {
                $opportunity->notes()->create([
                    'content' => $data['initial_note'],
                    'by_user_id' => $user?->id,
                ]);
            }

            return $opportunity->fresh(['client.contact', 'responsibleUser', 'notes.byUser', 'statusLogs.byUser']);
        });
    }
}
