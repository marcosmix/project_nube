<?php

namespace App\Livewire\Sales;

use App\Actions\Sales\AddOpportunityNoteAction;
use App\Actions\Sales\ConvertOpportunityToProjectAction;
use App\Actions\Sales\UpdateOpportunityStatusAction;
use App\Enums\Sales\OpportunitySource;
use App\Enums\Sales\OpportunityStatus;
use App\Models\Opportunity;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Opportunity $opportunity;

    public string $selectedStatus = '';

    public string $newNote = '';

    public function mount(Opportunity $opportunity): void
    {
        $this->opportunity = $opportunity->load([
            'client.contact',
            'responsibleUser',
            'notes.byUser',
            'statusLogs.byUser',
            'project',
        ]);

        $this->selectedStatus = $this->opportunity->status->value;
    }

    public function updateStatus(UpdateOpportunityStatusAction $updateOpportunityStatusAction): void
    {
        $this->validate([
            'selectedStatus' => ['required', 'in:'.implode(',', array_column(OpportunityStatus::options(), 'value'))],
        ]);

        $this->opportunity = $updateOpportunityStatusAction->execute(
            $this->opportunity,
            $this->selectedStatus,
            Auth::user(),
        );

        $this->dispatch('toast', type: 'success', message: 'Estado comercial actualizado');
    }

    public function addNote(AddOpportunityNoteAction $addOpportunityNoteAction): void
    {
        $this->validate([
            'newNote' => ['required', 'string', 'min:3'],
        ]);

        $addOpportunityNoteAction->execute($this->opportunity, $this->newNote, Auth::user());

        $this->newNote = '';
        $this->opportunity->refresh()->load(['client.contact', 'responsibleUser', 'notes.byUser', 'statusLogs.byUser', 'project']);

        $this->dispatch('toast', type: 'success', message: 'Nota agregada');
    }

    public function convertBlockedReason(): ?string
    {
        if ($this->opportunity->project) {
            return 'La oportunidad ya fue convertida en proyecto.';
        }

        if ($this->opportunity->status !== OpportunityStatus::Won) {
            return 'Para convertirla, la oportunidad debe estar en estado Ganada.';
        }

        if (! $this->opportunity->client_id) {
            return 'Antes de convertirla, asociá un cliente a la oportunidad.';
        }

        return null;
    }

    public function convertToProject(ConvertOpportunityToProjectAction $convertOpportunityToProjectAction): void
    {
        $project = $convertOpportunityToProjectAction->execute($this->opportunity, Auth::user());

        $this->dispatch('toast', type: 'success', message: 'Oportunidad convertida en proyecto');

        $this->redirectRoute('proyectos.show', $project, navigate: true);
    }

    public function render()
    {
        return view('livewire.sales.show', [
            'statusOptions' => OpportunityStatus::options(),
            'sourceOptions' => OpportunitySource::options(),
        ])->layout('layouts.app');
    }
}
