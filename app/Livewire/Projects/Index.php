<?php

namespace App\Livewire\Projects;

use App\Actions\Projects\DeleteProjectAction;
use App\Models\Client;
use App\Models\Developer;
use App\Models\Project;
use App\Models\ProjectStatusLog;
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

    public ?int $pendingDeleteProjectId = null;

    public string $pendingDeleteProjectName = '';

    public int $pendingDeleteProjectFlows = 0;

    public int $pendingDeleteProjectOpenFlows = 0;

    // Form (simple)
    public array $form = [
        'name' => '',
        'status' => 'prospection',
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
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreate(): void
    {
        $this->showCreateModal = false;
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
            "Proyecto {$project->name} eliminado de forma logica. Los {$this->pendingDeleteProjectFlows} flujo(s) de cobro siguen disponibles."
        );

        $this->finishDelete();
    }

    public function deleteWithFlows(DeleteProjectAction $deleteProjectAction): void
    {
        $project = $this->resolvePendingProject();

        $deleteProjectAction->execute($project, true);

        session()->flash(
            'status',
            "Proyecto {$project->name} y sus {$this->pendingDeleteProjectFlows} flujo(s) de cobro quedaron archivados con soft delete."
        );

        $this->finishDelete();
    }

    private function resetForm(): void
    {
        $this->form = [
            'name' => '',
            'status' => 'prospection',
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

        $this->selectedDevelopers = [];
    }

    private function rulesFor(string $status): array
    {
        $base = [
            'form.name' => ['required', 'string', 'max:255'],
            'form.client_id' => ['required', 'exists:clients,id'],
            'form.status' => ['required', 'in:prospection,interested,sale_closed,execution,paused,finished'],
            'form.prospection_notes' => ['nullable', 'string'],
        ];

        if ($status !== 'prospection') {
            $base += [
                'form.total_cost' => ['nullable', 'integer', 'min:0'],
                'form.estimated_start_date' => ['nullable', 'date'],
                'form.estimated_end_date' => ['nullable', 'date', 'after_or_equal:form.estimated_start_date'],
                'form.proposal_url' => ['nullable', 'url'],
                'form.excel_url' => ['nullable', 'url'],
            ];
        }

        if (in_array($status, ['sale_closed', 'execution', 'paused', 'finished'], true)) {
            $base['selectedDevelopers'] = ['array'];
            $base['selectedDevelopers.*'] = ['exists:developers,id'];
        }

        if (in_array($status, ['execution', 'paused', 'finished'], true)) {
            $base += [
                'form.sprint_close_day' => ['nullable', 'integer', 'min:1', 'max:31'],
                'form.actual_start_date' => ['nullable', 'date'],
            ];
        }

        if ($status === 'execution') {
            $base['form.execution_sub_status'] = ['nullable', 'in:on_track,with_debt,delayed'];
        }

        if ($status === 'paused') {
            $base['form.pause_reason'] = ['required', 'string', 'min:3'];
        }

        return $base;
    }

    public function create(): void
    {
        // Todo proyecto nuevo inicia en prospección.
        $status = 'prospection';
        $this->form['status'] = $status;
        $this->form['execution_sub_status'] = null;
        $this->form['pause_reason'] = null;

        $this->validate($this->rulesFor($status));

        $project = Project::create([
            'name' => $this->form['name'],
            'status' => $status,
            'execution_sub_status' => null,
            'client_id' => $this->form['client_id'],

            'prospection_notes' => $this->form['prospection_notes'],

            'proposal_url' => null,
            'excel_url' => null,
            'total_cost' => null,
            'estimated_start_date' => null,
            'estimated_end_date' => null,

            'sprint_close_day' => null,
            'actual_start_date' => null,

            'pause_reason' => null,
            'paused_at' => null,
        ]);

        $project->developers()->sync([]);

        ProjectStatusLog::create([
            'project_id' => $project->id,
            'status' => $project->status->value,
            'by_user_id' => Auth::id(),
            'created_at' => now(),
        ]);

        $this->showCreateModal = false;

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
