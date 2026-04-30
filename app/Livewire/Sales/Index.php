<?php

namespace App\Livewire\Sales;

use App\Actions\Sales\AddOpportunityNoteAction;
use App\Actions\Sales\CreateOpportunityAction;
use App\Enums\Sales\OpportunitySource;
use App\Enums\Sales\OpportunityStatus;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $source = '';

    public string $responsibleUserId = '';

    public bool $showCreateModal = false;

    public bool $showNoteModal = false;

    public int $createStep = 1;

    public ?int $noteOpportunityId = null;

    public string $noteContent = '';

    public array $createForm = [];

    public function mount(): void
    {
        $this->search = (string) request()->query('search', '');
        $this->status = (string) request()->query('status', '');
        $this->source = (string) request()->query('source', '');
        $this->responsibleUserId = (string) request()->query('responsible', '');
        $this->resetCreateForm();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSource(): void
    {
        $this->resetPage();
    }

    public function updatingResponsibleUserId(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetValidation();
        $this->resetCreateForm();
        $this->createStep = 1;
        $this->showCreateModal = true;
    }

    public function closeCreate(): void
    {
        $this->showCreateModal = false;
    }

    public function goToCreateStep(int $step): void
    {
        if ($step === 2) {
            $this->validate($this->stepOneRules());
        }

        $this->createStep = max(1, min(2, $step));
    }

    public function nextCreateStep(): void
    {
        $this->validate($this->stepOneRules());
        $this->createStep = 2;
    }

    public function previousCreateStep(): void
    {
        $this->createStep = 1;
    }

    public function updatedCreateFormClientMode(string $value): void
    {
        if ($value !== 'link_existing') {
            $this->createForm['client_id'] = '';
        }

        if ($value === 'create_new') {
            $this->syncReferenceDataToNewClientForm();
        }
    }

    public function fillNewClientFromReference(): void
    {
        $this->syncReferenceDataToNewClientForm(force: true);
    }

    public function openQuickNote(int $opportunityId): void
    {
        $this->resetValidation();
        $this->noteOpportunityId = $opportunityId;
        $this->noteContent = '';
        $this->showNoteModal = true;
    }

    public function closeQuickNote(): void
    {
        $this->showNoteModal = false;
        $this->noteOpportunityId = null;
        $this->noteContent = '';
    }

    public function create(CreateOpportunityAction $createOpportunityAction): void
    {
        $data = $this->validate($this->createRules())['createForm'];

        $opportunity = $createOpportunityAction->execute([
            'client_mode' => $data['client_mode'],
            'client_id' => $data['client_mode'] === 'link_existing' && $data['client_id'] !== '' ? (int) $data['client_id'] : null,
            'responsible_user_id' => $data['responsible_user_id'] !== '' ? (int) $data['responsible_user_id'] : null,
            'name' => $data['name'],
            'status' => $data['status'],
            'source' => $data['source'],
            'first_contact_at' => $data['first_contact_at'] !== '' ? $data['first_contact_at'] : null,
            'contact_name' => $data['contact_name'] ?: null,
            'contact_phone' => $data['contact_phone'] ?: null,
            'contact_email' => $data['contact_email'] ?: null,
            'contact_handle' => $data['contact_handle'] ?: null,
            'initial_message' => $data['initial_message'] ?: null,
            'initial_note' => $data['initial_note'] ?: null,
            'new_client' => $data['client_mode'] === 'create_new' ? [
                'first_name' => $data['new_client_first_name'],
                'last_name' => $data['new_client_last_name'],
                'email' => $data['new_client_email'],
                'phone' => $data['new_client_phone'] ?: null,
                'organization_name' => $data['new_client_organization_name'],
                'industry' => $data['new_client_industry'] ?: null,
                'company_size' => $data['new_client_company_size'],
                'notes' => $this->buildNewClientNotes($data),
            ] : null,
        ], Auth::user());

        $this->showCreateModal = false;
        $this->dispatch('toast', type: 'success', message: 'Oportunidad creada');

        $this->redirectRoute('ventas.show', $opportunity);
    }

    public function saveQuickNote(AddOpportunityNoteAction $addOpportunityNoteAction): void
    {
        $this->validate([
            'noteContent' => ['required', 'string', 'min:3'],
        ]);

        $opportunity = Opportunity::query()->findOrFail($this->noteOpportunityId);

        $addOpportunityNoteAction->execute($opportunity, $this->noteContent, Auth::user());

        $this->closeQuickNote();
        $this->dispatch('toast', type: 'success', message: 'Nota agregada');
    }

    public function getClientsProperty()
    {
        return Client::query()
            ->with('contact')
            ->orderBy('organization_name')
            ->get();
    }

    public function getUsersProperty()
    {
        return User::query()->orderBy('name', 'asc')->get();
    }

    public function selectedClientPreview(): ?Client
    {
        if (($this->createForm['client_mode'] ?? 'unlinked') !== 'link_existing') {
            return null;
        }

        $clientId = (int) ($this->createForm['client_id'] ?? 0);

        if ($clientId <= 0) {
            return null;
        }

        return Client::query()
            ->with('contact')
            ->find($clientId);
    }

    public function newClientPreview(): ?array
    {
        if (($this->createForm['client_mode'] ?? 'unlinked') !== 'create_new') {
            return null;
        }

        return [
            'full_name' => trim(($this->createForm['new_client_first_name'] ?? '').' '.($this->createForm['new_client_last_name'] ?? '')),
            'organization_name' => trim((string) ($this->createForm['new_client_organization_name'] ?? '')),
            'email' => trim((string) ($this->createForm['new_client_email'] ?? '')),
            'phone' => trim((string) ($this->createForm['new_client_phone'] ?? '')),
            'industry' => trim((string) ($this->createForm['new_client_industry'] ?? '')),
            'company_size' => $this->createForm['new_client_company_size'] ?? 'small',
            'company_size_label' => Client::COMPANY_SIZES[$this->createForm['new_client_company_size'] ?? 'small'] ?? 'Sin definir',
            'notes' => $this->buildNewClientNotes($this->createForm),
        ];
    }

    public function referencePreview(): array
    {
        return [
            'name' => trim((string) ($this->createForm['contact_name'] ?? '')),
            'phone' => trim((string) ($this->createForm['contact_phone'] ?? '')),
            'email' => trim((string) ($this->createForm['contact_email'] ?? '')),
            'handle' => trim((string) ($this->createForm['contact_handle'] ?? '')),
        ];
    }

    public function getOpportunitiesProperty()
    {
        return Opportunity::query()
            ->with(['client.contact', 'responsibleUser', 'notes.byUser', 'statusLogs.byUser'])
            ->search($this->search)
            ->when($this->status !== '', fn ($query) => $query->where('status', $this->status))
            ->when($this->source !== '', fn ($query) => $query->where('source', $this->source))
            ->when($this->responsibleUserId !== '', fn ($query) => $query->where('responsible_user_id', (int) $this->responsibleUserId))
            ->latest('id')
            ->paginate(10);
    }

    protected function stepOneRules(): array
    {
        return [
            'createForm.responsible_user_id' => ['nullable', 'exists:users,id'],
            'createForm.name' => ['required', 'string', 'max:255'],
            'createForm.status' => ['required', 'in:'.implode(',', array_column($this->initialStatusOptions(), 'value'))],
            'createForm.source' => ['required', 'in:'.implode(',', array_column(OpportunitySource::options(), 'value'))],
            'createForm.first_contact_at' => ['nullable', 'date'],
            'createForm.initial_message' => ['nullable', 'string'],
            'createForm.initial_note' => ['nullable', 'string'],
        ];
    }

    protected function createRules(): array
    {
        return $this->stepOneRules() + [
            'createForm.client_mode' => ['required', 'in:unlinked,link_existing,create_new'],
            'createForm.client_id' => ['required_if:createForm.client_mode,link_existing', 'nullable', 'exists:clients,id'],
            'createForm.contact_name' => ['nullable', 'string', 'max:255'],
            'createForm.contact_phone' => ['nullable', 'string', 'max:255'],
            'createForm.contact_email' => ['nullable', 'email', 'max:255'],
            'createForm.contact_handle' => ['nullable', 'string', 'max:255'],
            'createForm.new_client_first_name' => ['required_if:createForm.client_mode,create_new', 'nullable', 'string', 'max:255'],
            'createForm.new_client_last_name' => ['required_if:createForm.client_mode,create_new', 'nullable', 'string', 'max:255'],
            'createForm.new_client_email' => ['required_if:createForm.client_mode,create_new', 'nullable', 'email', 'max:255', 'unique:contacts,email'],
            'createForm.new_client_phone' => ['nullable', 'string', 'max:255'],
            'createForm.new_client_organization_name' => ['required_if:createForm.client_mode,create_new', 'nullable', 'string', 'max:255'],
            'createForm.new_client_industry' => ['nullable', 'string', 'max:255'],
            'createForm.new_client_company_size' => ['required_if:createForm.client_mode,create_new', 'nullable', 'in:small,medium,large'],
        ];
    }

    protected function initialStatusOptions(): array
    {
        return array_values(array_filter(
            OpportunityStatus::options(),
            fn (array $option) => in_array($option['value'], [
                OpportunityStatus::New->value,
                OpportunityStatus::Contacted->value,
                OpportunityStatus::Qualified->value,
                OpportunityStatus::ProposalSent->value,
                OpportunityStatus::Negotiation->value,
            ], true),
        ));
    }

    protected function resetCreateForm(): void
    {
        $this->createForm = [
            'client_mode' => 'unlinked',
            'client_id' => '',
            'responsible_user_id' => '',
            'name' => '',
            'status' => OpportunityStatus::New->value,
            'source' => OpportunitySource::Manual->value,
            'first_contact_at' => now()->toDateString(),
            'contact_name' => '',
            'contact_phone' => '',
            'contact_email' => '',
            'contact_handle' => '',
            'initial_message' => '',
            'initial_note' => '',
            'new_client_first_name' => '',
            'new_client_last_name' => '',
            'new_client_email' => '',
            'new_client_phone' => '',
            'new_client_organization_name' => '',
            'new_client_industry' => '',
            'new_client_company_size' => 'small',
        ];
    }

    protected function syncReferenceDataToNewClientForm(bool $force = false): void
    {
        $this->fillIfAllowed('new_client_phone', $this->createForm['contact_phone'] ?? '', $force);
        $this->fillIfAllowed('new_client_email', $this->createForm['contact_email'] ?? '', $force);
    }

    protected function fillIfAllowed(string $targetKey, ?string $value, bool $force = false): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        $currentValue = trim((string) ($this->createForm[$targetKey] ?? ''));

        if ($force || $currentValue === '') {
            $this->createForm[$targetKey] = $value;
        }
    }

    protected function buildNewClientNotes(array $data): ?string
    {
        $notes = [];

        if (! empty($data['name'])) {
            $notes[] = 'Referencia de consulta: '.$data['name'];
        }

        if (! empty($data['contact_name'])) {
            $notes[] = 'Nombre de referencia: '.$data['contact_name'];
        }

        if (! empty($data['contact_handle'])) {
            $notes[] = 'Usuario/red social de referencia: '.$data['contact_handle'];
        }

        if (! empty($data['initial_message'])) {
            $notes[] = 'Mensaje inicial: '.$data['initial_message'];
        }

        if (! empty($data['initial_note'])) {
            $notes[] = 'Notas comerciales iniciales: '.$data['initial_note'];
        }

        $manualNotes = trim((string) ($data['new_client_notes'] ?? ''));

        if ($manualNotes !== '') {
            $notes[] = 'Notas adicionales: '.$manualNotes;
        }

        return empty($notes) ? null : implode(PHP_EOL.PHP_EOL, $notes);
    }

    public function render()
    {
        return view('livewire.sales.index', [
            'opportunities' => $this->opportunities,
            'statusOptions' => OpportunityStatus::options(),
            'initialStatusOptions' => $this->initialStatusOptions(),
            'sourceOptions' => OpportunitySource::options(),
        ]);
    }
}
