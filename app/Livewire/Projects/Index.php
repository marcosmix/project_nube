<?php

namespace App\Livewire\Projects;

use App\Actions\Sales\ConvertOpportunityToProjectAction;
use App\Actions\Projects\DeleteProjectAction;
use App\Enums\Sales\OpportunityStatus;
use App\Models\Opportunity;
use App\Models\Client;
use App\Models\Developer;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public bool $showCreateModal = false;

    public int $createStep = 1;

    public string $opportunitySearch = '';

    public ?int $selectedWonOpportunityId = null;

    public ?int $pendingDeleteProjectId = null;

    public string $pendingDeleteProjectName = '';

    public int $pendingDeleteProjectFlows = 0;

    public int $pendingDeleteProjectOpenFlows = 0;

    // Form (simple)
    public array $form = [
        'name' => '',
        'status' => 'sale_closed',
        'execution_sub_status' => null,
        'client_id' => null,

        'prospection_notes' => null,

        'proposal_url' => null,
        'excel_url' => null,
        'total_cost' => null,
        'estimated_start_date' => null,
        'estimated_end_date' => null,

        'sprint_close_day' => null,
        'actual_start_date' => null,

        'pause_reason' => null,
    ];

    public array $selectedDevelopers = [];

    public function mount(): void
    {
        $this->search = (string) request()->query('search', '');
        $this->statusFilter = (string) request()->query('status', 'all');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->createStep = 1;
        $this->opportunitySearch = '';
        $this->selectedWonOpportunityId = null;
        $this->resetPage('wonOpportunitiesPage');
        $this->showCreateModal = true;
    }

    public function closeCreate(): void
    {
        $this->showCreateModal = false;
        $this->createStep = 1;
        $this->opportunitySearch = '';
        $this->selectedWonOpportunityId = null;
        $this->resetValidation();
    }

    public function updatedOpportunitySearch(): void
    {
        $this->selectedWonOpportunityId = null;
        $this->resetPage('wonOpportunitiesPage');
    }

    public function selectWonOpportunity(int $opportunityId): void
    {
        $opportunity = $this->availableWonOpportunitiesQuery()
            ->whereKey($opportunityId)
            ->first();

        if (! $opportunity) {
            return;
        }

        $this->selectedWonOpportunityId = $opportunity->id;
    }

    public function goToCreateReview(): void
    {
        $this->validate([
            'selectedWonOpportunityId' => ['required', 'integer'],
        ], [], [
            'selectedWonOpportunityId' => 'venta ganada seleccionada',
        ]);

        if (! $this->selectedWonOpportunity) {
            return;
        }

        $this->createStep = 2;
    }

    public function backToCreateSelection(): void
    {
        $this->createStep = 1;
    }

    public function confirmDelete(int $projectId): void
    {
        $project = Project::query()
            ->withCount([
                'paymentFlows',
                'paymentFlows as open_payment_flows_count' => fn ($query) => $query->whereIn('status', ['draft', 'active']),
            ])
            ->findOrFail($projectId);

        $this->pendingDeleteProjectId = $project->id;
        $this->pendingDeleteProjectName = $project->name;
        $this->pendingDeleteProjectFlows = (int) $project->payment_flows_count;
        $this->pendingDeleteProjectOpenFlows = (int) $project->open_payment_flows_count;
    }

    public function cancelDelete(): void
    {
        $this->pendingDeleteProjectId = null;
        $this->pendingDeleteProjectName = '';
        $this->pendingDeleteProjectFlows = 0;
        $this->pendingDeleteProjectOpenFlows = 0;
    }

    public function deleteKeepingFlows(DeleteProjectAction $deleteProjectAction): void
    {
        $project = $this->resolvePendingProject();

        $deleteProjectAction->execute($project, false);

        session()->flash(
            'status',
            "Operación {$project->name} eliminada de forma logica. Los {$this->pendingDeleteProjectFlows} flujo(s) de cobro siguen disponibles."
        );

        $this->finishDelete();
    }

    public function deleteWithFlows(DeleteProjectAction $deleteProjectAction): void
    {
        $project = $this->resolvePendingProject();

        $deleteProjectAction->execute($project, true);

        session()->flash(
            'status',
            "Operación {$project->name} y sus {$this->pendingDeleteProjectFlows} flujo(s) de cobro quedaron archivados con soft delete."
        );

        $this->finishDelete();
    }

    public function convertSelectedWonOpportunity(ConvertOpportunityToProjectAction $convertOpportunityToProjectAction): void
    {
        $this->validate([
            'selectedWonOpportunityId' => ['required', 'integer'],
        ], [], [
            'selectedWonOpportunityId' => 'venta ganada seleccionada',
        ]);

        $opportunity = $this->selectedWonOpportunity;

        if (! $opportunity) {
            return;
        }

        $project = $convertOpportunityToProjectAction->execute($opportunity, Auth::user());

        $this->closeCreate();

        $this->dispatch('toast', type: 'success', message: 'Venta pasada a Operaciones');

        $this->redirectRoute('proyectos.show', $project);
    }

    public function getClientsProperty()
    {
        return Client::query()
            ->with('contact')
            ->orderBy('organization_name')
            ->get();
    }

    public function getDevelopersProperty()
    {
        return Developer::query()
            ->with('contact')
            ->where('status', 'active')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getSelectedWonOpportunityProperty(): ?Opportunity
    {
        if (! $this->selectedWonOpportunityId) {
            return null;
        }

        return $this->availableWonOpportunitiesQuery()
            ->with(['attachments.uploadedBy', 'notes.byUser'])
            ->find($this->selectedWonOpportunityId);
    }

    public function getWonOpportunitiesProperty()
    {
        return $this->availableWonOpportunitiesQuery()
            ->paginate(8, ['*'], 'wonOpportunitiesPage');
    }

    protected function availableWonOpportunitiesQuery()
    {
        return Opportunity::query()
            ->with(['client.contact'])
            ->withCount(['attachments', 'notes'])
            ->search($this->opportunitySearch)
            ->where('status', OpportunityStatus::Won->value)
            ->whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('projects')
                    ->whereColumn('projects.opportunity_id', 'opportunities.id');
            })
            ->latest('updated_at');
    }

    private function resolvePendingProject(): Project
    {
        return Project::query()->findOrFail($this->pendingDeleteProjectId);
    }

    private function finishDelete(): void
    {
        $this->cancelDelete();
        $this->resetPage();
    }

    public function render()
    {
        $projects = Project::query()
            ->with(['client.contact', 'developers.contact'])
            ->withCount([
                'paymentFlows',
                'paymentFlows as open_payment_flows_count' => fn ($query) => $query->whereIn('status', ['draft', 'active']),
            ])
            ->search($this->search)
            ->statusFilter($this->statusFilter)
            ->latest()
            ->paginate(12);

        return view('livewire.projects.index', [
            'projects' => $projects,
        ]);
    }
}
