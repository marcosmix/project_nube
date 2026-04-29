<?php

namespace App\Jobs\WhatsApp;

use App\Actions\Commercial\CreateOrUpdateOpportunityFromWhatsAppAction;
use App\Enums\Sales\OpportunityMessageType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class ProcessIncomingWhatsAppMessageJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public array $payload) {}

    public function handle(CreateOrUpdateOpportunityFromWhatsAppAction $createOrUpdateOpportunityFromWhatsAppAction): void
    {
        foreach ($this->extractIncomingMessages($this->payload) as $messageData) {
            $createOrUpdateOpportunityFromWhatsAppAction->execute($messageData);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function extractIncomingMessages(array $payload): array
    {
        $messages = [];

        foreach (($payload['entry'] ?? []) as $entry) {
            foreach (($entry['changes'] ?? []) as $change) {
                $value = $change['value'] ?? [];
                $contacts = $value['contacts'] ?? [];
                $contactName = $contacts[0]['profile']['name'] ?? null;
                $waId = $contacts[0]['wa_id'] ?? null;
                $metadata = $value['metadata'] ?? [];

                foreach (($value['messages'] ?? []) as $message) {
                    $messages[] = [
                        'whatsapp_contact_id' => $waId ?: ($message['from'] ?? null),
                        'contact_phone' => $message['from'] ?? null,
                        'contact_name' => $contactName,
                        'content' => $this->extractContent($message),
                        'type' => $this->extractType($message)->value,
                        'external_message_id' => $message['id'] ?? null,
                        'external_conversation_id' => $metadata['phone_number_id'] ?? null,
                        'messaged_at' => isset($message['timestamp']) ? Carbon::createFromTimestamp((int) $message['timestamp']) : now(),
                        'raw_payload' => $message,
                    ];
                }
            }
        }

        return $messages;
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function extractContent(array $message): ?string
    {
        return $message['text']['body']
            ?? $message['image']['caption']
            ?? $message['document']['caption']
            ?? null;
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function extractType(array $message): OpportunityMessageType
    {
        return match ($message['type'] ?? null) {
            'text' => OpportunityMessageType::Text,
            'image' => OpportunityMessageType::Image,
            'audio' => OpportunityMessageType::Audio,
            'document' => OpportunityMessageType::Document,
            default => OpportunityMessageType::Unknown,
        };
    }
}
