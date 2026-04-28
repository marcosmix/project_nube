<?php

namespace App\Livewire\Projects;

use App\Models\Client;
use App\Models\Developer;
use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\ProjectStatusLog;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Project $project;

    private array $allowedStatuses = ['prospection', 'interested', 'sale_closed', 'execution', 'paused', 'finished'];

    private array $progressionOrder = ['prospection', 'interested', 'sale_closed', 'execution', 'finished'];

    public array $form = [];

    public array $selectedDevelopers = [];

    public function mount(Project $project): void
    {
        $this->project = $project->load([
            'client.contact',
            'developers.contact',
            'statusLogs.byUser',
            'notes.byUser',
        ]);

        $this->hydrateFormFromModel();
    }

    private function hydrateFormFromModel(): void
    {
        $this->form = [
            'name' => $this->project->name,
            'status' => $this->project->status->value,
            'execution_sub_status' => $this->project->execution_sub_status?->value,
            'client_id' => $this->project->client_id,

            'prospection_notes' => $this->project->prospection_notes,

            'proposal_url' => $this->project->proposal_url,
            'excel_url' => $this->project->excel_url,
            'total_cost' => $this->project->total_cost,
            'estimated_start_date' => optional($this->project->estimated_start_date)->format('Y-m-d'),
            'estimated_end_date' => optional($this->project->estimated_end_date)->format('Y-m-d'),

            'sprint_close_day' => $this->project->sprint_close_day,
            'actual_start_date' => optional($this->project->actual_start_date)->format('Y-m-d'),

            'pause_reason' => $this->project->pause_reason,
        ];

        $this->selectedDevelopers = $this->project->developers->pluck('id')->all();
    }

    // Mantiene limpio el form cuando cambia de estado.
    public function updatedFormStatus(string $value): void
    {
        if ($value !== 'execution') {
            $this->form['execution_sub_status'] = null;
        }

        if ($value !== 'paused') {
            $this->form['pause_reason'] = null;
        }

        $this->resetValidation();
    }

    // Cambio de estado con guardado inmediato (sin botón Guardar).
    public function changeStatus(string $status): void
    {
        $currentStatus = $this->project->status->value;

        if ($currentStatus === 'finished') {
            return;
        }

        if (! in_array($status, $this->allowedStatuses, true)) {
            return;
        }

        $blockReason = $this->transitionBlockReason($status);

        if ($blockReason !== null) {
            $this->dispatch('toast', type: 'error', message: $blockReason);

            return;
        }

        $oldStatus = $currentStatus;
        $financialLocked = $this->isFinancialLocked($currentStatus);

        $this->form['status'] = $status;
        $this->updatedFormStatus($status);

        $this->project->update([
            'status' => $status,
            'execution_sub_status' => $status === 'execution' ? ($this->form['execution_sub_status'] ?? null) : null,
            'pause_reason' => $status === 'paused' ? ($this->form['pause_reason'] ?? null) : null,
            'paused_at' => $status === 'paused' ? now() : null,
            'proposal_url' => $financialLocked ? $this->project->proposal_url : ($this->form['proposal_url'] ?? null),
            'excel_url' => $financialLocked ? $this->project->excel_url : ($this->form['excel_url'] ?? null),
            'total_cost' => $financialLocked ? $this->project->total_cost : ($this->form['total_cost'] ?? null),
            'estimated_start_date' => $financialLocked ? $this->project->estimated_start_date : ($this->form['estimated_start_date'] ?? null),
            'estimated_end_date' => $financialLocked ? $this->project->estimated_end_date : ($this->form['estimated_end_date'] ?? null),
        ]);

        if ($oldStatus !== $status) {
            $this->logStatus($status);
        }

        $this->project->refresh()->load(['statusLogs.byUser']);
        $this->dispatch('toast', type: 'success', message: 'Estado actualizado automáticamente');
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        return $this->transitionBlockReason($targetStatus) === null;
    }

    public function currentStateIndex(): int
    {
        return array_search($this->project->status->value, $this->progressionOrder, true) ?: 0;
    }

    public function nextStatus(): ?string
    {
        $currentIdx = $this->currentStateIndex();

        if ($currentIdx >= count($this->progressionOrder) - 1) {
            return null;
        }

        return $this->progressionOrder[$currentIdx + 1];
    }

    public function previousStatus(): ?string
    {
        $currentIdx = $this->currentStateIndex();

        if ($currentIdx <= 0) {
            return null;
        }

        return $this->progressionOrder[$currentIdx - 1];
    }

    public function nextStatusLabel(): ?string
    {
        $next = $this->nextStatus();

        return $next ? $this->getStatusLabel($next) : null;
    }

    public function previousStatusLabel(): ?string
    {
        $prev = $this->previousStatus();

        return $prev ? $this->getStatusLabel($prev) : null;
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'prospection' => 'En Prospección',
            'interested' => 'Interesado',
            'sale_closed' => 'Venta Cerrada',
            'execution' => 'En Ejecución',
            'paused' => 'Frenado',
            'finished' => 'Finalizado',
            default => ucfirst($status),
        };
    }

    public function transitionBlockReason(string $targetStatus): ?string
    {
        $currentStatus = $this->project->status->value;

        if (! in_array($targetStatus, $this->allowedStatuses, true)) {
            return 'El estado solicitado no es válido.';
        }

        if ($currentStatus === 'finished') {
            return $targetStatus === 'finished'
                ? null
                : 'Un proyecto finalizado ya no puede cambiar de estado.';
        }

        if ($targetStatus === $currentStatus) {
            return null;
        }

        if ($targetStatus === 'sale_closed' && (int) ($this->form['total_cost'] ?? 0) <= 0) {
            return 'Para pasar a "Venta Cerrada" debes cargar antes el costo total en Información de Contratación.';
        }

        // Frenado es dinámico: desde/hacia paused se permite.
        if ($targetStatus === 'paused' || $currentStatus === 'paused') {
            return null;
        }

        $from = array_search($currentStatus, $this->progressionOrder, true);
        $to = array_search($targetStatus, $this->progressionOrder, true);

        if ($from === false || $to === false) {
            return 'No se pudo determinar la transición de estado.';
        }

        // Solo al estado siguiente inmediato (no saltos)
        if ($to !== $from + 1) {
            return 'Solo puedes avanzar al siguiente estado.';
        }

        // Solo avanzar, no retroceder (excepto desde paused)
        if ($to < $from) {
            return 'No puedes volver a un estado anterior.';
        }

        return null;
    }

    private function isFinancialLocked(string $status): bool
    {
        return in_array($status, ['sale_closed', 'execution', 'paused', 'finished'], true);
    }

    // Listas para selects (simple y efectivo)
    public function getClientsProperty()
    {
        return Client::with('contact')->orderBy('organization_name')->get();
    }

    public function getDevelopersProperty()
    {
        return Developer::with('contact')
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get();
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

    private function logStatus(string $status): void
    {
        ProjectStatusLog::create([
            'project_id' => $this->project->id,
            'status' => $status,
            'by_user_id' => Auth::id(),
        ]);
    }

    public function save(): void
    {
        $this->resetValidation();

        $status = $this->form['status'] ?? $this->project->status->value;
        $financialLocked = $this->isFinancialLocked($this->project->status->value);

        $this->validate($this->rulesFor($status));

        $oldStatus = $this->project->status->value;

        $this->project->update([
            'name' => $this->form['name'],
            'status' => $this->form['status'],
            'execution_sub_status' => $this->form['execution_sub_status'] ?? null,
            'client_id' => $this->form['client_id'],

            'prospection_notes' => $this->form['prospection_notes'] ?? null,

            'proposal_url' => $financialLocked ? $this->project->proposal_url : ($this->form['proposal_url'] ?? null),
            'excel_url' => $financialLocked ? $this->project->excel_url : ($this->form['excel_url'] ?? null),
            'total_cost' => $financialLocked ? $this->project->total_cost : ($this->form['total_cost'] ?? null),
            'estimated_start_date' => $financialLocked ? $this->project->estimated_start_date : ($this->form['estimated_start_date'] ?? null),
            'estimated_end_date' => $financialLocked ? $this->project->estimated_end_date : ($this->form['estimated_end_date'] ?? null),

            'sprint_close_day' => $this->form['sprint_close_day'] ?? null,
            'actual_start_date' => $this->form['actual_start_date'] ?? null,

            'pause_reason' => $this->form['pause_reason'] ?? null,
            'paused_at' => $status === 'paused' ? now() : null,
        ]);

        // Equipo solo desde venta cerrada en adelante
        if (in_array($status, ['sale_closed', 'execution', 'paused', 'finished'], true)) {
            $this->project->developers()->sync($this->selectedDevelopers);
        } else {
            $this->project->developers()->sync([]);
        }

        // Log si cambió el estado
        if ($oldStatus !== $status) {
            $this->logStatus($status);
        }

        // Activity note liviana (opcional pero útil)
        ProjectNote::create([
            'project_id' => $this->project->id,
            'content' => "Actualización del proyecto (estado: {$status}).",
            'status' => $status,
            'by_user_id' => Auth::id(),
        ]);

        // refresco
        $this->project->refresh()->load([
            'client.contact',
            'developers.contact',
            'statusLogs.byUser',
            'notes.byUser',
        ]);

        // re-hidratar por si hay casts/enums
        $this->hydrateFormFromModel();

        $this->dispatch('toast', type: 'success', message: 'Cambios guardados');
    }

    public function render()
    {
        return view('livewire.projects.show')->layout('layouts.app');
    }
}
