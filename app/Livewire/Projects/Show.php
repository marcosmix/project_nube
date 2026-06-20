<?php

namespace App\Livewire\Projects;

use App\Models\Client;
use App\Models\Developer;
use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\ProjectStatusLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Project $project;

    private array $allowedStatuses = ['sale_closed', 'execution', 'paused', 'finished'];

    private array $operationalStatuses = ['sale_closed', 'execution', 'paused', 'finished'];

    private array $progressionOrder = ['sale_closed', 'execution', 'finished'];

    public array $form = [];

    public array $selectedDevelopers = [];

    public bool $showTeamModal = false;

    public bool $showPauseModal = false;

    public bool $showStatusConfirmationModal = false;

    public bool $showExecutionSubStatusModal = false;

    public ?string $pendingStatus = null;

    public ?string $pendingExecutionSubStatus = null;

    public string $pauseReasonDraft = '';

    public string $newNote = '';

    public string $attachmentLabel = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $attachmentUpload = null;

    public function mount(Project $project): void
    {
        $this->project = $project->load([
            'client.contact',
            'developers.contact',
            'statusLogs.byUser',
            'notes.byUser',
            'opportunity.client.contact',
            'opportunity.notes.byUser',
            'opportunity.attachments.uploadedBy',
            'attachments.uploadedBy',
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
        if ($value === 'finished') {
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
            'execution_sub_status' => $status === 'finished' ? null : ($this->form['execution_sub_status'] ?? null),
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

        $this->refreshProject();
        $this->dispatch('toast', type: 'success', message: 'Estado actualizado automáticamente');
    }

    public function openStatusConfirmation(string $status): void
    {
        if (! $this->canTransitionTo($status)) {
            return;
        }

        $this->pendingStatus = $status;
        $this->showStatusConfirmationModal = true;
    }

    public function closeStatusConfirmation(): void
    {
        $this->pendingStatus = null;
        $this->showStatusConfirmationModal = false;
    }

    public function confirmStatusChange(): void
    {
        if (! $this->pendingStatus) {
            return;
        }

        $status = $this->pendingStatus;
        $this->closeStatusConfirmation();
        $this->changeStatus($status);
    }

    public function requestExecutionSubStatusChange(string $value): void
    {
        if (! in_array($this->project->status->value, ['execution', 'paused'], true)) {
            return;
        }

        $value = $value !== '' ? $value : null;
        $current = $this->project->execution_sub_status?->value;

        if ($value === $current) {
            $this->form['execution_sub_status'] = $current;

            return;
        }

        $this->pendingExecutionSubStatus = $value;
        $this->form['execution_sub_status'] = $value;
        $this->resetValidation('form.execution_sub_status');
        $this->showExecutionSubStatusModal = true;
    }

    public function cancelExecutionSubStatusChange(): void
    {
        $this->pendingExecutionSubStatus = null;
        $this->form['execution_sub_status'] = $this->project->execution_sub_status?->value;
        $this->showExecutionSubStatusModal = false;
        $this->resetValidation('form.execution_sub_status');
    }

    public function confirmExecutionSubStatusChange(): void
    {
        if (! in_array($this->project->status->value, ['execution', 'paused'], true)) {
            return;
        }

        $validated = $this->validate([
            'pendingExecutionSubStatus' => ['nullable', 'in:on_track,with_debt,delayed'],
        ], [], [
            'pendingExecutionSubStatus' => 'subestado de ejecución',
        ]);

        $subStatus = $validated['pendingExecutionSubStatus'] ?? null;

        $this->project->update([
            'execution_sub_status' => $subStatus,
        ]);

        $this->showExecutionSubStatusModal = false;
        $this->pendingExecutionSubStatus = null;
        $this->refreshProject();

        $this->dispatch('toast', type: 'success', message: 'Subestado de ejecución actualizado');
    }

    public function openPauseModal(): void
    {
        if ($this->project->status->value !== 'execution') {
            return;
        }

        $this->pauseReasonDraft = trim((string) ($this->form['pause_reason'] ?? $this->project->pause_reason ?? ''));
        $this->resetValidation('pauseReasonDraft');
        $this->showPauseModal = true;
    }

    public function closePauseModal(): void
    {
        $this->showPauseModal = false;
        $this->resetValidation('pauseReasonDraft');
    }

    public function pauseOperation(): void
    {
        if ($this->project->status->value !== 'execution') {
            return;
        }

        $validated = $this->validate([
            'pauseReasonDraft' => ['required', 'string', 'min:3'],
        ], [], [
            'pauseReasonDraft' => 'motivo de freno',
        ]);

        $this->form['pause_reason'] = trim($validated['pauseReasonDraft']);
        $this->closePauseModal();
        $this->changeStatus('paused');
    }

    public function resumeOperation(): void
    {
        if ($this->project->status->value !== 'paused') {
            return;
        }

        $this->changeStatus('execution');
    }

    public function openTeamModal(): void
    {
        if (! in_array($this->project->status->value, ['sale_closed', 'execution', 'paused'], true)) {
            return;
        }

        $this->selectedDevelopers = $this->project->developers->pluck('id')->all();
        $this->resetValidation();
        $this->showTeamModal = true;
    }

    public function closeTeamModal(): void
    {
        $this->showTeamModal = false;
        $this->selectedDevelopers = $this->project->developers->pluck('id')->all();
        $this->resetValidation('selectedDevelopers');
        $this->resetValidation('selectedDevelopers.*');
    }

    public function saveTeamAssignments(): void
    {
        if (! in_array($this->project->status->value, ['sale_closed', 'execution', 'paused'], true)) {
            return;
        }

        $this->validate([
            'selectedDevelopers' => ['array'],
            'selectedDevelopers.*' => ['exists:developers,id'],
        ]);

        $this->project->developers()->sync($this->selectedDevelopers);

        $this->refreshProject();

        $this->hydrateFormFromModel();
        $this->showTeamModal = false;

        $this->dispatch('toast', type: 'success', message: 'Equipo actualizado');
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        return $this->transitionBlockReason($targetStatus) === null;
    }

    public function getAllowedStatusTransitionsProperty(): array
    {
        $transitions = [];

        foreach ($this->availableOperationalTransitions() as $status) {
            if ($this->canTransitionTo($status)) {
                $transitions[] = $this->statusTransitionData($status);
            }
        }

        return $transitions;
    }

    public function getPendingStatusTransitionProperty(): ?array
    {
        if (! $this->pendingStatus || ! in_array($this->pendingStatus, $this->allowedStatuses, true)) {
            return null;
        }

        return $this->statusTransitionData($this->pendingStatus);
    }

    public function currentStateIndex(): int
    {
        $status = $this->project->status->value;
        $index = array_search($status, $this->progressionOrder, true);

        return $index === false ? 0 : $index;
    }

    public function nextStatus(): ?string
    {
        if (! in_array($this->project->status->value, $this->progressionOrder, true)) {
            return null;
        }

        $currentIdx = $this->currentStateIndex();

        if ($currentIdx >= count($this->progressionOrder) - 1) {
            return null;
        }

        return $this->progressionOrder[$currentIdx + 1];
    }

    public function previousStatus(): ?string
    {
        if (! in_array($this->project->status->value, $this->progressionOrder, true)) {
            return null;
        }

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

    public function getNextStatusButtonLabelProperty(): ?string
    {
        $next = $this->nextStatus();

        if (! $next) {
            return null;
        }

        return match ($next) {
            'execution' => 'Comenzar',
            'finished' => 'Finalizar',
            default => 'Pasar a '.$this->getStatusLabel($next),
        };
    }

    public function getNextStatusButtonVariantProperty(): string
    {
        return match ($this->nextStatus()) {
            'finished' => 'warning',
            'execution' => 'success',
            default => 'primary',
        };
    }

    public function getSalesNotesProperty()
    {
        return $this->project->opportunity?->notes ?? collect();
    }

    public function addNote(): void
    {
        $this->validate([
            'newNote' => ['required', 'string', 'min:3'],
        ]);

        $this->project->notes()->create([
            'content' => $this->newNote,
            'status' => $this->project->status->value,
            'by_user_id' => Auth::id(),
        ]);

        $this->newNote = '';
        $this->refreshProject();

        $this->dispatch('toast', type: 'success', message: 'Nota agregada');
    }

    public function saveAttachment(): void
    {
        $this->validate([
            'attachmentLabel' => ['nullable', 'string', 'max:255'],
            'attachmentUpload' => ['required', 'file', 'max:10240', 'mimes:pdf,png,jpg,jpeg,webp,ppt,pptx,odp'],
        ], [], [
            'attachmentLabel' => 'etiqueta del archivo',
            'attachmentUpload' => 'archivo de la operación',
        ]);

        $path = $this->attachmentUpload->store('operaciones', 'public');

        $this->project->attachments()->create([
            'uploaded_by' => Auth::id(),
            'label' => trim($this->attachmentLabel) !== '' ? trim($this->attachmentLabel) : null,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $this->attachmentUpload->getClientOriginalName(),
            'mime_type' => $this->attachmentUpload->getMimeType(),
            'size' => $this->attachmentUpload->getSize(),
        ]);

        $this->attachmentUpload = null;
        $this->attachmentLabel = '';

        $this->refreshProject();

        $this->dispatch('toast', type: 'success', message: 'Archivo agregado a la operación');
    }

    public function removeAttachment(int $attachmentId): void
    {
        $attachment = $this->project->attachments()->findOrFail($attachmentId);

        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        $this->refreshProject();

        $this->dispatch('toast', type: 'success', message: 'Archivo eliminado');
    }

    public function previousStatusLabel(): ?string
    {
        $prev = $this->previousStatus();

        return $prev ? $this->getStatusLabel($prev) : null;
    }

    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'sale_closed' => 'Listo para ejecutar',
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
            return 'Para dejar la operación lista para ejecutar debes cargar antes el costo total en Información de Contratación.';
        }

        if (in_array($currentStatus, $this->operationalStatuses, true)) {
            $allowedTransitions = match ($currentStatus) {
                'sale_closed' => ['execution'],
                'execution' => ['paused', 'finished'],
                'paused' => ['execution'],
                'finished' => [],
                default => [],
            };

            if (! in_array($targetStatus, $allowedTransitions, true)) {
                return 'La operación solo permite avanzar según el flujo operativo definido.';
            }

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
            ->get()
            ->sortBy([
                fn (Developer $developer) => blank($developer->contact?->job_title) ? 'zzz' : mb_strtolower((string) $developer->contact?->job_title),
                fn (Developer $developer) => array_search($developer->level, ['lead', 'senior', 'semi_senior', 'junior'], true) ?: 99,
                fn (Developer $developer) => mb_strtolower(trim(($developer->contact?->last_name ?? '').' '.($developer->contact?->first_name ?? ''))),
            ])
            ->values();
    }

    public function getDevelopersGroupedByJobTitleProperty()
    {
        return $this->developers
            ->groupBy(function (Developer $developer) {
                $jobTitle = trim((string) ($developer->contact?->job_title ?? ''));

                return $jobTitle !== '' ? $jobTitle : 'Sin puesto definido';
            })
            ->sortKeysUsing(fn (string $left, string $right) => strcasecmp($left, $right));
    }

    private function rulesFor(string $status): array
    {
        $base = [
            'form.name' => ['required', 'string', 'max:255'],
            'form.client_id' => ['required', 'exists:clients,id'],
            'form.status' => ['required', 'in:sale_closed,execution,paused,finished'],
            'form.prospection_notes' => ['nullable', 'string'],
        ];

        $base += [
            'form.total_cost' => ['nullable', 'integer', 'min:0'],
            'form.estimated_start_date' => ['nullable', 'date'],
            'form.estimated_end_date' => ['nullable', 'date', 'after_or_equal:form.estimated_start_date'],
            'form.proposal_url' => ['nullable', 'url'],
            'form.excel_url' => ['nullable', 'url'],
        ];

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

    protected function refreshProject(): void
    {
        $this->project->refresh()->load([
            'client.contact',
            'developers.contact',
            'statusLogs.byUser',
            'notes.byUser',
            'attachments.uploadedBy',
            'opportunity.client.contact',
            'opportunity.notes.byUser',
            'opportunity.attachments.uploadedBy',
        ]);

        $this->hydrateFormFromModel();
    }

    protected function statusTransitionData(string $status): array
    {
        return [
            'value' => $status,
            'label' => $this->getStatusLabel($status),
            'buttonLabel' => match ($status) {
                'execution' => 'Comenzar',
                'paused' => 'Frenar',
                'finished' => 'Finalizar',
                default => 'Pasar a '.$this->getStatusLabel($status),
            },
            'description' => match ($status) {
                'execution' => 'La operación pasará a ejecución activa.',
                'finished' => 'La operación quedará cerrada y en solo lectura.',
                'paused' => 'La operación se frenará temporalmente.',
                default => 'Se avanzará al siguiente estado operativo disponible.',
            },
            'variant' => match ($status) {
                'finished' => 'warning',
                'execution' => 'success',
                'paused' => 'accent',
                default => 'primary',
            },
        ];
    }

    protected function availableOperationalTransitions(): array
    {
        return match ($this->project->status->value) {
            'sale_closed' => ['execution'],
            'execution' => ['paused', 'finished'],
            'paused' => [],
            default => [],
        };
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
            'execution_sub_status' => ($this->form['status'] ?? null) === 'finished' ? null : ($this->form['execution_sub_status'] ?? null),
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

        // refresco
        $this->project->refresh()->load([
            'client.contact',
            'developers.contact',
            'statusLogs.byUser',
            'notes.byUser',
            'attachments.uploadedBy',
        ]);

        // re-hidratar por si hay casts/enums
        $this->hydrateFormFromModel();

        $this->dispatch('toast', type: 'success', message: 'Cambios guardados');
    }

    public function render()
    {
        return view('livewire.projects.show', [
            'allowedStatusTransitions' => $this->allowedStatusTransitions,
            'pendingExecutionSubStatusLabel' => $this->pendingExecutionSubStatus ? $this->getExecutionSubStatusLabel($this->pendingExecutionSubStatus) : 'Sin subestado',
        ])->layout('layouts.app');
    }

    private function getExecutionSubStatusLabel(?string $status): string
    {
        return match ($status) {
            'on_track' => 'Al Dia',
            'with_debt' => 'Con Deuda',
            'delayed' => 'Con Demora',
            default => 'Sin subestado',
        };
    }
}
