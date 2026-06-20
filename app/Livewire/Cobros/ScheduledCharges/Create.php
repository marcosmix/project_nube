<?php

namespace App\Livewire\Cobros\ScheduledCharges;

use App\Actions\Cobros\CreateScheduledChargeAction;
use App\Enums\Cobros\ScheduledChargeFrequency;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Create extends Component
{
    use WithFileUploads, WithPagination;

    public string $clientSearch = '';

    public ?int $client_id = null;

    public string $reference_name = '';

    public string $detail = '';

    public string $amount = '';

    public string $charge_date = '';

    public string $frequency = 'monthly';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $attachmentUpload = null;

    public function mount(): void
    {
        $this->charge_date = now()->toDateString();
        $this->frequency = ScheduledChargeFrequency::Monthly->value;
    }

    public function updatingClientSearch(): void
    {
        $this->resetPage('clientsPage');
    }

    public function selectClient(int $clientId): void
    {
        if (! $this->clientsQuery()->whereKey($clientId)->exists()) {
            return;
        }

        $this->client_id = $clientId;
        $this->resetValidation('client_id');
    }

    public function save(CreateScheduledChargeAction $createScheduledChargeAction)
    {
        $validated = $this->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'reference_name' => ['required', 'string', 'max:255'],
            'detail' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'charge_date' => ['required', 'date'],
            'frequency' => ['required', 'in:weekly,biweekly,monthly,yearly'],
            'attachmentUpload' => ['nullable', 'file', 'max:10240', 'mimes:pdf,png,jpg,jpeg,webp,xls,xlsx,doc,docx'],
        ], [], [
            'client_id' => 'cliente',
            'reference_name' => 'nombre de referencia',
            'detail' => 'detalle',
            'amount' => 'monto',
            'charge_date' => 'fecha de cobro',
            'frequency' => 'frecuencia',
            'attachmentUpload' => 'archivo asociado',
        ]);

        $client = Client::query()->findOrFail($validated['client_id']);

        $scheduledCharge = $createScheduledChargeAction->execute($client, [
            'reference_name' => trim($validated['reference_name']),
            'detail' => trim((string) ($validated['detail'] ?? '')) ?: null,
            'amount' => round((float) $validated['amount'], 2),
            'charge_date' => $validated['charge_date'],
            'frequency' => $validated['frequency'],
        ], Auth::user());

        if ($this->attachmentUpload) {
            $path = $this->attachmentUpload->store('cobros/programados', 'public');

            $scheduledCharge->attachments()->create([
                'uploaded_by' => Auth::id(),
                'label' => 'Archivo asociado',
                'disk' => 'public',
                'path' => $path,
                'original_name' => $this->attachmentUpload->getClientOriginalName(),
                'mime_type' => $this->attachmentUpload->getMimeType(),
                'size' => $this->attachmentUpload->getSize(),
            ]);
        }

        session()->flash('success', 'Cobro programado creado correctamente.');

        return redirect()->route('cobros.index', ['tab' => 'scheduled']);
    }

    public function getClientsProperty()
    {
        return $this->clientsQuery()
            ->with(['contact'])
            ->withCount(['projects', 'scheduledCharges'])
            ->orderBy('organization_name')
            ->paginate(10, ['*'], 'clientsPage');
    }

    public function getSelectedClientProperty(): ?Client
    {
        if (! $this->client_id) {
            return null;
        }

        return Client::query()->with('contact')->withCount(['projects', 'scheduledCharges'])->find($this->client_id);
    }

    protected function clientsQuery()
    {
        $search = trim($this->clientSearch);

        return Client::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('organization_name', 'like', "%{$search}%")
                        ->orWhereHas('contact', function ($contactQuery) use ($search) {
                            $contactQuery
                                ->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            });
    }

    public function render()
    {
        return view('livewire.cobros.scheduled-charges.create', [
            'clients' => $this->clients,
            'selectedClient' => $this->selectedClient,
            'frequencies' => ScheduledChargeFrequency::cases(),
        ]);
    }
}
