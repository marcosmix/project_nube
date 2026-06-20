<?php

namespace Tests\Feature\Sales;

use App\Actions\Sales\UpdateOpportunityStatusAction;
use App\Enums\Sales\OpportunityStatus;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateOpportunityStatusActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_only_allows_contacted(): void
    {
        $action = app(UpdateOpportunityStatusAction::class);

        $this->assertSame([
            OpportunityStatus::Contacted->value,
        ], array_map(fn (OpportunityStatus $status) => $status->value, $action->getAllowedTransitions(OpportunityStatus::New)));
    }

    public function test_contacted_allows_qualified_or_discarded(): void
    {
        $action = app(UpdateOpportunityStatusAction::class);

        $this->assertSame([
            OpportunityStatus::Qualified->value,
            OpportunityStatus::Discarded->value,
        ], array_map(fn (OpportunityStatus $status) => $status->value, $action->getAllowedTransitions(OpportunityStatus::Contacted)));
    }

    public function test_qualified_allows_only_proposal_sent(): void
    {
        $action = app(UpdateOpportunityStatusAction::class);

        $this->assertSame([
            OpportunityStatus::ProposalSent->value,
        ], array_map(fn (OpportunityStatus $status) => $status->value, $action->getAllowedTransitions(OpportunityStatus::Qualified)));
    }

    public function test_qualified_does_not_allow_discarded(): void
    {
        $user = User::factory()->create();
        $action = app(UpdateOpportunityStatusAction::class);

        $opportunity = $this->makeOpportunity(OpportunityStatus::Qualified);

        $this->expectValidationException(
            fn () => $action->execute($opportunity, OpportunityStatus::Discarded->value, $user),
            ['status' => 'No es posible cambiar de Calificado a Descartada.'],
        );
    }

    public function test_proposal_sent_does_not_allow_discarded(): void
    {
        $user = User::factory()->create();
        $action = app(UpdateOpportunityStatusAction::class);

        $opportunity = $this->makeOpportunity(OpportunityStatus::ProposalSent);

        $this->expectValidationException(
            fn () => $action->execute($opportunity, OpportunityStatus::Discarded->value, $user),
            ['status' => 'No es posible cambiar de Propuesta enviada a Descartada.'],
        );
    }

    public function test_negotiation_does_not_allow_discarded(): void
    {
        $action = app(UpdateOpportunityStatusAction::class);

        $this->assertSame([
            OpportunityStatus::Won->value,
            OpportunityStatus::Lost->value,
        ], array_map(fn (OpportunityStatus $status) => $status->value, $action->getAllowedTransitions(OpportunityStatus::Negotiation)));
    }

    public function test_discarded_invalid_transitions_are_rejected(): void
    {
        $user = User::factory()->create();
        $action = app(UpdateOpportunityStatusAction::class);

        $opportunity = $this->makeOpportunity(OpportunityStatus::New);

        $this->expectValidationException(
            fn () => $action->execute($opportunity, OpportunityStatus::Discarded->value, $user),
            ['status' => 'No es posible cambiar de Nueva consulta a Descartada.'],
        );
    }

    public function test_negotiation_cannot_move_to_discarded(): void
    {
        $user = User::factory()->create();
        $action = app(UpdateOpportunityStatusAction::class);

        $opportunity = $this->makeOpportunity(OpportunityStatus::Negotiation);

        $this->expectValidationException(
            fn () => $action->execute($opportunity, OpportunityStatus::Discarded->value, $user),
            ['status' => 'No es posible cambiar de Negociacion a Descartada.'],
        );
    }

    public function test_proposal_sent_requires_client_amount_and_attachment(): void
    {
        $user = User::factory()->create();
        $action = app(UpdateOpportunityStatusAction::class);

        $opportunity = $this->makeOpportunity(OpportunityStatus::Qualified);

        $this->expectValidationException(
            fn () => $action->execute($opportunity, OpportunityStatus::ProposalSent->value, $user),
            [
                'estimated_ticket_amount' => 'Cargá el monto estimado antes de pasar a Propuesta enviada.',
                'attachments' => 'Adjuntá al menos un archivo comercial antes de pasar a Propuesta enviada.',
                'client_id' => 'Antes de enviar la propuesta, asociá un cliente a la oportunidad.',
            ],
        );
    }

    public function test_contacted_can_still_discard(): void
    {
        $user = User::factory()->create();
        $action = app(UpdateOpportunityStatusAction::class);

        $opportunity = $this->makeOpportunity(OpportunityStatus::Contacted);

        $updated = $action->execute($opportunity, OpportunityStatus::Discarded->value, $user);

        $this->assertSame(OpportunityStatus::Discarded, $updated->status);
    }

    public function test_proposal_sent_is_saved_when_requirements_are_met(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $action = app(UpdateOpportunityStatusAction::class);

        $opportunity = $this->makeOpportunity(OpportunityStatus::Qualified, [
            'client_id' => $client->id,
            'estimated_ticket_amount' => 1500,
        ]);

        $opportunity->attachments()->create([
            'uploaded_by' => $user->id,
            'label' => 'Propuesta comercial',
            'disk' => 'public',
            'path' => 'ventas/propuestas/propuesta.pdf',
            'original_name' => 'propuesta.pdf',
            'mime_type' => 'application/pdf',
            'size' => 1024,
        ]);

        $updated = $action->execute($opportunity, OpportunityStatus::ProposalSent->value, $user);

        $this->assertSame(OpportunityStatus::ProposalSent, $updated->status);
        $this->assertCount(2, $updated->statusLogs);
        $this->assertSame(OpportunityStatus::ProposalSent->value, $updated->statusLogs->first()->status);
    }

    private function makeOpportunity(OpportunityStatus $status, array $attributes = []): Opportunity
    {
        $opportunity = Opportunity::query()->create(array_merge([
            'name' => 'Oportunidad de prueba',
            'status' => $status->value,
            'source' => 'manual',
            'client_id' => null,
            'responsible_user_id' => null,
            'first_contact_at' => now()->toDateString(),
        ], $attributes));

        $opportunity->statusLogs()->create([
            'status' => $status->value,
            'by_user_id' => null,
            'created_at' => now(),
        ]);

        return $opportunity->fresh(['statusLogs']);
    }

    /**
     * @param  array<string, string>  $expectedErrors
     */
    private function expectValidationException(callable $callback, array $expectedErrors): void
    {
        try {
            $callback();

            $this->fail('Se esperaba una ValidationException.');
        } catch (ValidationException $exception) {
            $actualErrors = collect($exception->errors())
                ->map(fn (array $messages) => $messages[0] ?? null)
                ->all();

            $this->assertSame($expectedErrors, $actualErrors);
        }
    }
}
