<?php

namespace Tests\Feature\Sales;

use App\Enums\Sales\OpportunityStatus;
use App\Livewire\Sales\Show as SalesShow;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class OpportunityShowComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_shows_the_allowed_next_statuses_and_opens_the_confirmation_modal(): void
    {
        $user = User::factory()->create();
        $opportunity = $this->makeOpportunity(OpportunityStatus::Contacted);

        $this->actingAs($user);

        Livewire::test(SalesShow::class, ['opportunity' => $opportunity])
            ->assertSee('Pasar a Calificado')
            ->assertSee('Pasar a Descartada')
            ->assertSet('showStatusConfirmationModal', false)
            ->assertSet('pendingStatus', null)
            ->call('openStatusConfirmation', OpportunityStatus::Qualified->value)
            ->assertSet('pendingStatus', OpportunityStatus::Qualified->value)
            ->assertSet('showStatusConfirmationModal', true);
    }

    public function test_it_confirms_the_status_change_through_the_modal(): void
    {
        $user = User::factory()->create();
        $opportunity = $this->makeOpportunity(OpportunityStatus::Contacted);

        $this->actingAs($user);

        Livewire::test(SalesShow::class, ['opportunity' => $opportunity])
            ->call('openStatusConfirmation', OpportunityStatus::Qualified->value)
            ->call('confirmStatusChange')
            ->assertSee('Pasar a Propuesta enviada')
            ->assertDontSee('Pasar a Descartada')
            ->assertSet('showStatusConfirmationModal', false)
            ->assertSet('pendingStatus', null);

        $this->assertDatabaseHas('opportunities', [
            'id' => $opportunity->id,
            'status' => OpportunityStatus::Qualified->value,
        ]);

        $this->assertDatabaseHas('opportunity_status_logs', [
            'opportunity_id' => $opportunity->id,
            'status' => OpportunityStatus::Qualified->value,
            'by_user_id' => $user->id,
        ]);
    }

    public function test_it_saves_pending_commercial_data_before_sending_the_proposal(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $client = Client::factory()->create();
        $opportunity = $this->makeOpportunity(OpportunityStatus::Qualified, [
            'client_id' => $client->id,
        ]);

        $this->actingAs($user);

        Livewire::test(SalesShow::class, ['opportunity' => $opportunity])
            ->set('commercialForm.estimated_ticket_amount', '1500')
            ->set('commercialForm.attachment_label', 'Propuesta comercial')
            ->set('attachmentUpload', UploadedFile::fake()->create('propuesta.pdf', 128, 'application/pdf'))
            ->call('openStatusConfirmation', OpportunityStatus::ProposalSent->value)
            ->call('confirmStatusChange')
            ->assertSet('showStatusConfirmationModal', false)
            ->assertSet('pendingStatus', null);

        $opportunity->refresh();

        $this->assertSame(OpportunityStatus::ProposalSent, $opportunity->status);
        $this->assertSame('1500.00', $opportunity->estimated_ticket_amount);

        $this->assertDatabaseHas('attachments', [
            'attachable_type' => Opportunity::class,
            'attachable_id' => $opportunity->id,
            'label' => 'Propuesta comercial',
            'original_name' => 'propuesta.pdf',
        ]);

        $this->assertDatabaseHas('opportunity_status_logs', [
            'opportunity_id' => $opportunity->id,
            'status' => OpportunityStatus::ProposalSent->value,
            'by_user_id' => $user->id,
        ]);
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
}
