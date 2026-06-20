<?php

namespace Tests\Feature\Cobros;

use App\Enums\Cobros\PaymentFlowStatus;
use App\Enums\ProjectStatus;
use App\Livewire\Cobros\Forms\CreateFlowForm;
use App\Models\Client;
use App\Models\PaymentFlow;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateFlowFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_lists_only_operations_without_any_flow(): void
    {
        $executionClient = Client::factory()->create(['organization_name' => 'Cliente Ejecucion']);
        $saleClosedClient = Client::factory()->create(['organization_name' => 'Cliente Espera']);
        $pausedClient = Client::factory()->create(['organization_name' => 'Cliente Frenado']);
        $finishedClient = Client::factory()->create(['organization_name' => 'Cliente Finalizado']);
        $withoutAmountClient = Client::factory()->create(['organization_name' => 'Cliente Sin Monto']);
        $withFlowClient = Client::factory()->create(['organization_name' => 'Cliente Con Flujo']);

        $this->makeProject($executionClient, ProjectStatus::Execution, 'Operacion Ejecucion');
        $this->makeProject($saleClosedClient, ProjectStatus::SaleClosed, 'Operacion Espera');
        $this->makeProject($pausedClient, ProjectStatus::Paused, 'Operacion Frenada');
        $this->makeProject($finishedClient, ProjectStatus::Finished, 'Operacion Finalizada');
        $this->makeProject($withoutAmountClient, ProjectStatus::SaleClosed, 'Operacion Sin Monto', null);
        $projectWithFlow = $this->makeProject($withFlowClient, ProjectStatus::Execution, 'Operacion Con Flujo');

        PaymentFlow::factory()->create([
            'project_id' => $projectWithFlow->id,
            'created_by' => User::factory(),
            'status' => PaymentFlowStatus::Completed->value,
        ]);

        Livewire::test(CreateFlowForm::class)
            ->assertSee('Operacion Ejecucion')
            ->assertSee('Operacion Espera')
            ->assertSee('Operacion Frenada')
            ->assertSee('Operacion Finalizada')
            ->assertSee('Operacion Sin Monto')
            ->assertDontSee('Operacion Con Flujo');
    }

    public function test_it_can_select_a_paused_project_without_flow(): void
    {
        $client = Client::factory()->create();
        $project = $this->makeProject($client, ProjectStatus::Paused);

        Livewire::test(CreateFlowForm::class)
            ->call('selectProject', $project->id)
            ->assertSet('client_id', $client->id)
            ->assertSet('project_id', $project->id)
            ->assertSet('operation_amount', '100000.00')
            ->assertSet('total_amount', '100000.00');
    }

    public function test_it_does_not_allow_selecting_a_project_with_any_flow(): void
    {
        $client = Client::factory()->create();
        $project = $this->makeProject($client, ProjectStatus::Execution);

        PaymentFlow::factory()->create([
            'project_id' => $project->id,
            'created_by' => User::factory(),
            'status' => PaymentFlowStatus::Cancelled->value,
        ]);

        Livewire::test(CreateFlowForm::class)
            ->call('selectProject', $project->id)
            ->assertSet('client_id', null)
            ->assertSet('project_id', null);
    }

    public function test_it_selects_an_operation_without_amount_but_blocks_financial_step(): void
    {
        $client = Client::factory()->create();
        $project = $this->makeProject($client, ProjectStatus::Finished, 'Operacion Sin Monto', null);

        Livewire::test(CreateFlowForm::class)
            ->assertSee('Operacion Sin Monto')
            ->call('selectProject', $project->id)
            ->assertSet('client_id', $client->id)
            ->assertSet('project_id', $project->id)
            ->assertSet('operation_amount', '0.00')
            ->call('nextStep')
            ->assertSet('currentStep', 2)
            ->assertSee('Esta operación todavía no tiene monto cargado')
            ->call('nextStep')
            ->assertHasErrors(['operation_amount' => 'gt']);
    }

    public function test_it_adds_fixed_interest_to_flow_total_and_installments(): void
    {
        $client = Client::factory()->create();
        $project = $this->makeProject($client, ProjectStatus::Execution);

        Livewire::test(CreateFlowForm::class)
            ->call('selectProject', $project->id)
            ->set('installments_count', '2')
            ->set('interest_amount', '10000')
            ->assertSet('operation_amount', '100000.00')
            ->assertSet('total_amount', '110000.00')
            ->assertSet('installmentRows.0.amount', '55000.00')
            ->assertSet('installmentRows.1.amount', '55000.00');
    }

    public function test_it_saves_flow_total_with_fixed_interest(): void
    {
        $client = Client::factory()->create();
        $project = $this->makeProject($client, ProjectStatus::Execution);

        Livewire::test(CreateFlowForm::class)
            ->call('selectProject', $project->id)
            ->set('installments_count', '2')
            ->set('interest_amount', '10000')
            ->call('save')
            ->assertRedirect();

        $flow = PaymentFlow::query()->where('project_id', $project->id)->firstOrFail();

        $this->assertSame(110000.0, (float) $flow->total_amount);
        $this->assertDatabaseHas('payment_installments', [
            'payment_flow_id' => $flow->id,
            'number' => 1,
            'amount' => 55000.00,
        ]);
        $this->assertDatabaseHas('payment_installments', [
            'payment_flow_id' => $flow->id,
            'number' => 2,
            'amount' => 55000.00,
        ]);
    }

    private function makeProject(Client $client, ProjectStatus $status, ?string $name = null, ?int $totalCost = 100000): Project
    {
        return Project::query()->create([
            'name' => $name ?? 'Operacion '.$client->organization_name,
            'status' => $status->value,
            'client_id' => $client->id,
            'total_cost' => $totalCost,
        ]);
    }
}
