<?php

namespace App\Actions\Commercial;

use App\Enums\Sales\OpportunityMessageDirection;
use App\Enums\Sales\OpportunityMessageType;
use App\Enums\Sales\OpportunitySource;
use App\Enums\Sales\OpportunityStatus;
use App\Models\Opportunity;
use App\Models\OpportunityMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrUpdateOpportunityFromWhatsAppAction
{
    /**
     * Normaliza la consulta entrante y la vincula a una oportunidad existente cuando es posible.
     * La integración es incremental: por ahora prioriza teléfono, nombre y texto sin crear proyectos.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Opportunity
    {
        return DB::transaction(function () use ($data) {
            if (! empty($data['external_message_id'])) {
                $existingMessage = OpportunityMessage::query()
                    ->with('opportunity')
                    ->where('external_message_id', $data['external_message_id'])
                    ->first();

                if ($existingMessage?->opportunity) {
                    return $existingMessage->opportunity;
                }
            }

            $phone = $this->normalizePhone($data['contact_phone'] ?? null);
            $contactId = trim((string) ($data['whatsapp_contact_id'] ?? ''));
            $receivedAt = $data['messaged_at'] instanceof Carbon
                ? $data['messaged_at']
                : Carbon::parse((string) ($data['messaged_at'] ?? now()));

            $opportunity = Opportunity::query()
                ->when($contactId !== '', fn ($query) => $query->where('whatsapp_contact_id', $contactId))
                ->when($phone !== null, fn ($query) => $query->orWhere('contact_phone', $phone))
                ->latest('id')
                ->first();

            if (! $opportunity) {
                $opportunity = new Opportunity();
                $opportunity->name = $this->buildOpportunityName($data['contact_name'] ?? null, $phone);
                $opportunity->status = OpportunityStatus::New;
                $opportunity->source = OpportunitySource::Whatsapp;
                $opportunity->first_contact_at = $receivedAt->toDateString();
                $opportunity->initial_message = $data['content'] ?? null;
            }

            $opportunity->source = OpportunitySource::Whatsapp;
            $opportunity->contact_phone = $phone ?? $opportunity->contact_phone;
            $opportunity->whatsapp_contact_id = $contactId !== '' ? $contactId : $opportunity->whatsapp_contact_id;
            $opportunity->external_conversation_id = $data['external_conversation_id'] ?? $opportunity->external_conversation_id;
            $opportunity->contact_name = $this->preferIncomingValue($opportunity->contact_name, $data['contact_name'] ?? null);
            $opportunity->contact_handle = $this->preferIncomingValue($opportunity->contact_handle, $data['contact_phone'] ?? null);
            $opportunity->syncCustomerServiceWindow($receivedAt);
            $opportunity->save();

            if (! $opportunity->statusLogs()->exists()) {
                $opportunity->statusLogs()->create([
                    'status' => OpportunityStatus::New->value,
                    'created_at' => $receivedAt,
                ]);
            }

            $opportunity->messages()->create([
                'direction' => OpportunityMessageDirection::Inbound,
                'type' => $data['type'] ?? OpportunityMessageType::Unknown->value,
                'content' => $data['content'] ?? null,
                'external_message_id' => $data['external_message_id'] ?? null,
                'raw_payload' => $data['raw_payload'] ?? null,
                'messaged_at' => $receivedAt,
                'status' => $data['status'] ?? null,
            ]);

            return $opportunity->fresh(['messages']);
        });
    }

    private function normalizePhone(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        $normalized = preg_replace('/\D+/', '', $phone);

        return $normalized !== '' ? $normalized : null;
    }

    private function buildOpportunityName(?string $contactName, ?string $phone): string
    {
        if ($contactName && trim($contactName) !== '') {
            return 'Consulta WhatsApp · '.trim($contactName);
        }

        return 'Consulta WhatsApp · '.($phone ?: 'Sin identificar');
    }

    private function preferIncomingValue(?string $currentValue, ?string $incomingValue): ?string
    {
        return filled($currentValue) ? $currentValue : (filled($incomingValue) ? trim((string) $incomingValue) : $currentValue);
    }
}
