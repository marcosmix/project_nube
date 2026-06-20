<?php

namespace App\Livewire\Sales;

use App\Actions\Sales\AddOpportunityNoteAction;
use App\Actions\Sales\ConvertOpportunityToProjectAction;
use App\Actions\Sales\CreateClientFromOpportunityReferenceAction;
use App\Actions\Sales\UpdateOpportunityStatusAction;
use App\Enums\Sales\OpportunitySource;
use App\Enums\Sales\OpportunityStatus;
use App\Models\Opportunity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Show extends Component
{
    use WithFileUploads;

    public Opportunity $opportunity;

    public ?string $pendingStatus = null;

    public bool $showStatusConfirmationModal = false;

    public string $newNote = '';

    public array $commercialForm = [
        'estimated_ticket_amount' => '',
        'attachment_label' => '',
    ];

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $attachmentUpload = null;

    public bool $showClientModal = false;

    public array $clientForm = [
        'mode' => 'unlinked',
        'client_id' => '',
        'new_client_first_name' => '',
        'new_client_last_name' => '',
        'new_client_email' => '',
        'new_client_phone' => '',
        'new_client_organization_name' => '',
        'new_client_industry' => '',
        'new_client_company_size' => 'small',
    ];

    public function mount(Opportunity $opportunity): void
    {
        $this->opportunity = $opportunity;
        $this->refreshOpportunity();
        $this->syncCommercialForm();
    }

    protected function allowedStatusTransitionsCollection(): \Illuminate\Support\Collection
    {
        $action = app(UpdateOpportunityStatusAction::class);

        return collect(
            $action->getAllowedTransitions($this->opportunity->status)
        )->map(fn (OpportunityStatus $status) => $this->statusTransitionData($status));
    }

    public function getPendingStatusTransitionProperty(): ?array
    {
        $status = OpportunityStatus::tryFrom((string) $this->pendingStatus);

        return $status ? $this->statusTransitionData($status) : null;
    }

    public function getCanEditClientProperty(): bool
    {
        return in_array($this->opportunity->status, [
            OpportunityStatus::New,
            OpportunityStatus::Contacted,
            OpportunityStatus::Qualified,
        ], true);
    }

    public function openStatusConfirmation(string $status): void
    {
        if (! $this->isAllowedStatusTransition($status)) {
            return;
        }

        $this->resetValidation();
        $this->pendingStatus = $status;
        $this->showStatusConfirmationModal = true;
    }

    public function closeStatusConfirmation(): void
    {
        $this->showStatusConfirmationModal = false;
        $this->pendingStatus = null;
        $this->resetValidation();
    }

    public function confirmStatusChange(UpdateOpportunityStatusAction $updateOpportunityStatusAction): void
    {
        if (! $this->pendingStatus) {
            return;
        }

        $this->updateStatus($this->pendingStatus, $updateOpportunityStatusAction);
        $this->closeStatusConfirmation();
    }

    public function updateStatus(string $status, UpdateOpportunityStatusAction $updateOpportunityStatusAction): void
    {
        if (! $this->isAllowedStatusTransition($status)) {
            return;
        }

        if ($this->shouldSaveCommercialDataForStatus($status)) {
            $this->saveCommercialData(silent: true, refreshOpportunity: true);
        }

        $this->opportunity = $updateOpportunityStatusAction->execute(
            $this->opportunity,
            $status,
            Auth::user(),
        );

        $this->refreshOpportunity();
        $this->syncCommercialForm();

        $this->dispatch('toast', type: 'success', message: 'Estado comercial actualizado');
    }

    public function openClientModal(): void
    {
        if (! $this->canEditClient) {
            return;
        }

        $this->resetValidation();
        $this->clientForm = [
            'mode' => $this->opportunity->client_id ? 'link_existing' : 'unlinked',
            'client_id' => $this->opportunity->client_id ?? '',
            'new_client_first_name' => '',
            'new_client_last_name' => '',
            'new_client_email' => $this->opportunity->contact_email ?? '',
            'new_client_phone' => $this->opportunity->contact_phone ?? '',
            'new_client_organization_name' => '',
            'new_client_industry' => '',
            'new_client_company_size' => 'small',
        ];

        $this->showClientModal = true;
    }

    public function closeClientModal(): void
    {
        $this->showClientModal = false;
        $this->resetValidation('clientForm');
    }

    public function saveClient(CreateClientFromOpportunityReferenceAction $createClientAction): void
    {
        $validated = $this->validate($this->clientFormRules(), [], $this->clientFormAttributes());

        $clientId = null;

        if ($validated['clientForm']['mode'] === 'link_existing') {
            $clientId = $validated['clientForm']['client_id'] !== '' ? (int) $validated['clientForm']['client_id'] : null;
        } elseif ($validated['clientForm']['mode'] === 'create_new') {
            $client = $createClientAction->execute([
                'first_name' => $validated['clientForm']['new_client_first_name'],
                'last_name' => $validated['clientForm']['new_client_last_name'],
                'email' => $validated['clientForm']['new_client_email'],
                'phone' => $validated['clientForm']['new_client_phone'] ?? null,
                'organization_name' => $validated['clientForm']['new_client_organization_name'],
                'industry' => $validated['clientForm']['new_client_industry'] ?? null,
                'company_size' => $validated['clientForm']['new_client_company_size'],
                'notes' => null,
            ]);
            $clientId = $client->id;
        }

        $this->opportunity->update(['client_id' => $clientId]);

        $this->refreshOpportunity();
        $this->closeClientModal();

        $this->dispatch('toast', type: 'success', message: 'Cliente actualizado');
    }

    public function fillNewClientFromReference(): void
    {
        if (($this->clientForm['mode'] ?? '') !== 'create_new') {
            return;
        }

        $this->clientForm['new_client_first_name'] = $this->opportunity->contact_name ? explode(' ', $this->opportunity->contact_name, 2)[0] : '';
        $this->clientForm['new_client_last_name'] = $this->opportunity->contact_name ? implode(' ', array_slice(explode(' ', $this->opportunity->contact_name), 1)) : '';
        $this->clientForm['new_client_email'] = $this->opportunity->contact_email ?? '';
        $this->clientForm['new_client_phone'] = $this->opportunity->contact_phone ?? '';
    }

    public function getClientsProperty()
    {
        return \App\Models\Client::query()
            ->with('contact')
            ->orderBy('organization_name')
            ->get();
    }

    protected function clientFormRules(): array
    {
        return [
            'clientForm.mode' => ['required', 'in:unlinked,link_existing,create_new'],
            'clientForm.client_id' => ['required_if:clientForm.mode,link_existing', 'nullable', 'exists:clients,id'],
            'clientForm.new_client_first_name' => ['required_if:clientForm.mode,create_new', 'nullable', 'string', 'max:255'],
            'clientForm.new_client_last_name' => ['required_if:clientForm.mode,create_new', 'nullable', 'string', 'max:255'],
            'clientForm.new_client_email' => ['required_if:clientForm.mode,create_new', 'nullable', 'email', 'max:255', 'unique:contacts,email'],
            'clientForm.new_client_phone' => ['nullable', 'string', 'max:255'],
            'clientForm.new_client_organization_name' => ['required_if:clientForm.mode,create_new', 'nullable', 'string', 'max:255'],
            'clientForm.new_client_industry' => ['nullable', 'string', 'max:255'],
            'clientForm.new_client_company_size' => ['required_if:clientForm.mode,create_new', 'nullable', 'in:small,medium,large'],
        ];
    }

    protected function clientFormAttributes(): array
    {
        return [
            'clientForm.mode' => 'modo de cliente',
            'clientForm.client_id' => 'cliente existente',
            'clientForm.new_client_first_name' => 'nombre',
            'clientForm.new_client_last_name' => 'apellido',
            'clientForm.new_client_email' => 'email',
            'clientForm.new_client_phone' => 'teléfono',
            'clientForm.new_client_organization_name' => 'organización',
            'clientForm.new_client_industry' => 'industria',
            'clientForm.new_client_company_size' => 'tamaño de empresa',
        ];
    }

    public function saveCommercialData(bool $silent = false, bool $refreshOpportunity = true): void
    {
        $validated = $this->validate($this->commercialRules(), [], $this->commercialAttributes());

        $amount = $this->normalizeMoneyInput($validated['commercialForm']['estimated_ticket_amount'] ?? null);
        $label = $this->normalizeOptionalText($validated['commercialForm']['attachment_label'] ?? null);

        if ($amount !== null || $this->opportunity->estimated_ticket_amount !== null) {
            $this->opportunity->update([
                'estimated_ticket_amount' => $amount,
            ]);
        }

        if ($this->attachmentUpload) {
            $path = $this->attachmentUpload->store('ventas/propuestas', 'public');

            $this->opportunity->attachments()->create([
                'uploaded_by' => Auth::id(),
                'label' => $label,
                'disk' => 'public',
                'path' => $path,
                'original_name' => $this->attachmentUpload->getClientOriginalName(),
                'mime_type' => $this->attachmentUpload->getMimeType(),
                'size' => $this->attachmentUpload->getSize(),
            ]);

            $this->attachmentUpload = null;
            $this->commercialForm['attachment_label'] = '';
        }

        if ($refreshOpportunity) {
            $this->refreshOpportunity();
        }

        $this->syncCommercialForm();

        if (! $silent) {
            $this->dispatch('toast', type: 'success', message: 'Propuesta comercial actualizada');
        }
    }

    public function removeAttachment(int $attachmentId): void
    {
        $attachment = $this->opportunity->attachments()->findOrFail($attachmentId);

        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();

        $this->refreshOpportunity();

        $this->dispatch('toast', type: 'success', message: 'Adjunto eliminado');
    }

    public function addNote(AddOpportunityNoteAction $addOpportunityNoteAction): void
    {
        $this->validate([
            'newNote' => ['required', 'string', 'min:3'],
        ]);

        $addOpportunityNoteAction->execute($this->opportunity, $this->newNote, Auth::user());

        $this->newNote = '';
        $this->refreshOpportunity();

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

    public function canShowCommercialSection(): bool
    {
        return $this->opportunity->status->supportsCommercialProposalData();
    }

    public function formattedEstimatedTicketAmount(): ?string
    {
        if ($this->commercialForm['estimated_ticket_amount'] === '') {
            return null;
        }

        $amount = $this->normalizeMoneyInput($this->commercialForm['estimated_ticket_amount']);

        if ($amount === null) {
            return null;
        }

        return '$'.number_format($amount, 2, ',', '.');
    }

    public function render()
    {
        $allowedStatusTransitions = $this->allowedStatusTransitionsCollection();

        return view('livewire.sales.show', [
            'sourceOptions' => OpportunitySource::options(),
            'allowedStatusTransitions' => $allowedStatusTransitions,
            'pendingStatusTransition' => $this->pendingStatusTransition,
        ])->layout('layouts.app');
    }

    protected function refreshOpportunity(): void
    {
        $this->opportunity = $this->opportunity->fresh([
            'client.contact',
            'responsibleUser',
            'notes.byUser',
            'statusLogs.byUser',
            'project',
            'messages',
            'attachments.uploadedBy',
        ]) ?? $this->opportunity;
    }

    protected function syncCommercialForm(): void
    {
        $this->commercialForm['estimated_ticket_amount'] = $this->opportunity->estimated_ticket_amount !== null
            ? number_format((float) $this->opportunity->estimated_ticket_amount, 2, '.', '')
            : '';
    }

    protected function commercialRules(): array
    {
        return [
            'commercialForm.estimated_ticket_amount' => ['nullable', 'numeric', 'min:0.01'],
            'commercialForm.attachment_label' => ['nullable', 'string', 'max:255'],
            'attachmentUpload' => ['nullable', 'file', 'max:10240', 'mimes:pdf,png,jpg,jpeg,webp,ppt,pptx,odp'],
        ];
    }

    protected function commercialAttributes(): array
    {
        return [
            'commercialForm.estimated_ticket_amount' => 'monto estimado',
            'commercialForm.attachment_label' => 'etiqueta del adjunto',
            'attachmentUpload' => 'archivo adjunto',
        ];
    }

    protected function shouldSaveCommercialDataForStatus(string $status): bool
    {
        return (OpportunityStatus::tryFrom($status)?->supportsCommercialProposalData() ?? false)
            || $this->attachmentUpload !== null;
    }

    protected function isAllowedStatusTransition(string $status): bool
    {
        return $this->allowedStatusTransitionsCollection()->pluck('value')->contains($status);
    }

    protected function statusTransitionData(OpportunityStatus $status): array
    {
        return [
            'value' => $status->value,
            'label' => $status->label(),
            'buttonLabel' => 'Pasar a '.$status->label(),
            'description' => $this->statusTransitionDescription($status),
            'variant' => $this->statusTransitionVariant($status),
            'requiresCommercialData' => $status === OpportunityStatus::ProposalSent,
            'confirmationHint' => $status === OpportunityStatus::ProposalSent
                ? 'Si hay cambios comerciales pendientes, se guardarán antes de avanzar.'
                : 'Podés confirmar o cancelar este cambio de estado.',
        ];
    }

    protected function statusTransitionDescription(OpportunityStatus $status): string
    {
        return match ($status) {
            OpportunityStatus::Contacted => 'Registrá que ya hubo contacto directo y abrí el seguimiento activo.',
            OpportunityStatus::Qualified => 'Confirmá que el lead califica y dejalo listo para propuesta.',
            OpportunityStatus::ProposalSent => 'Marcá que la propuesta quedó enviada y habilitá la siguiente decisión comercial.',
            OpportunityStatus::Negotiation => 'Llevá la oportunidad a negociación para continuar con el cierre.',
            OpportunityStatus::Won => 'Cerrá la oportunidad como ganada y dejala lista para la conversión.',
            OpportunityStatus::Lost => 'Cerrá la oportunidad como perdida cuando ya no avance.',
            OpportunityStatus::Discarded => 'Descartá la oportunidad y detené el seguimiento comercial.',
            OpportunityStatus::New => 'Conservá la oportunidad como una consulta recién ingresada.',
        };
    }

    protected function statusTransitionVariant(OpportunityStatus $status): string
    {
        return match ($status) {
            OpportunityStatus::Won => 'success',
            OpportunityStatus::Lost, OpportunityStatus::Discarded => 'danger',
            OpportunityStatus::ProposalSent => 'accent',
            OpportunityStatus::Negotiation => 'warning',
            default => 'primary',
        };
    }

    protected function normalizeMoneyInput(mixed $value): ?float
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        return round((float) $normalized, 2);
    }

    protected function normalizeOptionalText(mixed $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
