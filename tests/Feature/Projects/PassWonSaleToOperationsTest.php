<?php

namespace Tests\Feature\Projects;

use App\Enums\ProjectStatus;
use App\Enums\Sales\OpportunityStatus;
use App\Livewire\Projects\Index as ProjectsIndex;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PassWonSaleToOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_won_opportunities_pending_conversion(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();

        $pendingWon = $this->makeOpportunity(OpportunityStatus::Won, [
            'name' => 'Venta pendiente',
            'client_id' => $client->id,
            'estimated_ticket_amount' => 2500,
        ]);

        $convertedWon = $this->makeOpportunity(OpportunityStatus::Won, [
            'name' => 'Venta ya convertida',
            'client_id' => $client->id,
        ]);
        $this->makeProjectForOpportunity($convertedWon, $client);

        $lost = $this->makeOpportunity(OpportunityStatus::Lost, [
            'name' => 'Venta perdida',
            'client_id' => $client->id,
        ]);

        $this->actingAs($user);

        Livewire::test(ProjectsIndex::class)
            ->call('openCreate')
            ->assertSet('createStep', 1)
            ->assertSee('Venta pendiente')
            ->assertDontSee('Venta perdida')
            ->assertSee('1 disponibles')
            ->call('selectWonOpportunity', $pendingWon->id)
            ->assertSet('selectedWonOpportunityId', $pendingWon->id)
            ->call('goToCreateReview')
            ->assertSet('createStep', 2)
            ->assertSee('Resumen previo')
            ->assertSee($client->organization_name);
    }

    public function test_it_converts_the_selected_won_opportunity_into_a_project(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create();
        $opportunity = $this->makeOpportunity(OpportunityStatus::Won, [
            'name' => 'Venta a convertir',
            'client_id' => $client->id,
            'estimated_ticket_amount' => 12500,
        ]);

        $this->actingAs($user);

        Livewire::test(ProjectsIndex::class)
            ->call('openCreate')
            ->call('selectWonOpportunity', $opportunity->id)
            ->call('goToCreateReview')
            ->call('convertSelectedWonOpportunity')
            ->assertHasNoErrors();

        $project = Project::query()->where('opportunity_id', $opportunity->id)->firstOrFail();

        $this->assertSame(ProjectStatus::SaleClosed, $project->status);
        $this->assertSame($client->id, $project->client_id);
        $this->assertSame('12500', (string) $project->total_cost);
        $this->assertDatabaseHas('project_status_logs', [
            'project_id' => $project->id,
            'status' => ProjectStatus::SaleClosed->value,
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

    private function makeProjectForOpportunity(Opportunity $opportunity, Client $client): Project
    {
        return Project::query()->create([
            'name' => $opportunity->name,
            'status' => ProjectStatus::SaleClosed->value,
            'execution_sub_status' => null,
            'client_id' => $client->id,
            'opportunity_id' => $opportunity->id,
            'prospection_notes' => null,
            'proposal_url' => null,
            'excel_url' => null,
            'total_cost' => $opportunity->estimated_ticket_amount,
            'estimated_start_date' => null,
            'estimated_end_date' => null,
            'sprint_close_day' => null,
            'actual_start_date' => null,
            'actual_end_date' => null,
            'pause_reason' => null,
            'paused_at' => null,
        ]);
    }
}
