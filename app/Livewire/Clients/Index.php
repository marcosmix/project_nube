<?php

namespace App\Livewire\Clients;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;

use App\Models\Client;
use App\Models\Contact;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    public string $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    // ✅ Modal state
    public bool $modalOpen = false;

    // IDs para editar
    public ?int $clientId = null;
    public ?int $contactId = null;

    // Contact fields
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?string $birthdate = null; // YYYY-MM-DD
    public ?string $job_title = null;

    // Client fields
    public string $organization_name = '';
    public ?string $industry = null;
    public ?string $address = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $company_logo_upload = null;
    public ?string $company_logo_path = null;

    public string $company_size = 'small'; // small|medium|large
    public ?int $score = null; // 1..10
    public ?string $notes = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ✅ Open modal create
    public function create(): void
    {
        $this->resetForm();
        $this->modalOpen = true;
    }

    // ✅ Tu edit original, pero ahora abre modal
    public function edit(int $id): void
    {
        $client = Client::with('contact')->findOrFail($id);

        $this->clientId = $client->id;
        $this->contactId = $client->contact_id;

        $this->first_name = $client->contact->first_name;
        $this->last_name  = $client->contact->last_name;
        $this->email      = $client->contact->email;
        $this->phone      = $client->contact->phone;
        $this->birthdate  = optional($client->contact->birthdate)->format('Y-m-d');
        $this->job_title  = $client->contact->job_title;

        $this->organization_name = $client->organization_name;
        $this->industry          = $client->industry;
        $this->address           = $client->address;
        $this->company_size      = $client->company_size;
        $this->score             = $client->score;
        $this->notes             = $client->notes;

        $this->company_logo_upload = null;
        $this->company_logo_path = $client->company_logo;

        $this->resetValidation();
        $this->modalOpen = true;
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
        $this->resetForm();
        $this->resetValidation();
    }

    public function delete(int $id): void
    {
        $client = Client::findOrFail($id);
        $client->delete(); // soft delete ✅
        // opcional: si borraste el último de la página, podés caer en una página vacía.
        // si te molesta eso, lo ajustamos después con un helper.
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $logoPath = null;
        if ($this->company_logo_upload) {
            $logoPath = $this->company_logo_upload->store('company-logos', 'public');
        }

        if ($this->clientId) {
            // UPDATE
            $client = Client::with('contact')->findOrFail($this->clientId);

            $client->contact->update([
                'first_name' => $this->first_name,
                'last_name'  => $this->last_name,
                'email'      => $this->email,
                'phone'      => $this->phone,
                'birthdate'  => $this->birthdate,
                'job_title'  => $this->job_title,
            ]);

            $client->update([
                'organization_name' => $this->organization_name,
                'industry'          => $this->industry,
                'address'           => $this->address,
                'company_size'      => $this->company_size,
                'score'             => $this->score,
                'notes'             => $this->notes,
                ...( $logoPath ? ['company_logo' => $logoPath] : [] ),
            ]);
        } else {
            // CREATE (contact + client)
            $contact = Contact::create([
                'first_name' => $this->first_name,
                'last_name'  => $this->last_name,
                'email'      => $this->email,
                'phone'      => $this->phone,
                'birthdate'  => $this->birthdate,
                'job_title'  => $this->job_title,
            ]);

            Client::create([
                'contact_id'        => $contact->id,
                'organization_name' => $this->organization_name,
                'industry'          => $this->industry,
                'address'           => $this->address,
                'company_logo'      => $logoPath,
                'company_size'      => $this->company_size,
                'score'             => $this->score,
                'notes'             => $this->notes,
            ]);
        }

        $this->closeModal();
        // Si querés “toast” lindo, lo metemos con Alpine en 2 líneas.
    }

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => [
                'required', 'email', 'max:255',
                Rule::unique('contacts', 'email')->ignore($this->contactId),
            ],
            'phone'      => ['nullable', 'string', 'max:50'],
            'birthdate'  => ['nullable', 'date'],
            'job_title'  => ['nullable', 'string', 'max:255'],

            'organization_name' => ['required', 'string', 'max:255'],
            'industry'          => ['nullable', 'string', 'max:255'],
            'address'           => ['nullable', 'string', 'max:255'],
            'company_logo_upload' => ['nullable', 'image', 'max:2048'],

            'company_size' => ['required', Rule::in(['small','medium','large'])],
            'score'        => ['nullable', 'integer', 'min:1', 'max:10'],
            'notes'        => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function resetForm(): void
    {
        $this->clientId = null;
        $this->contactId = null;

        $this->first_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->phone = null;
        $this->birthdate = null;
        $this->job_title = null;

        $this->organization_name = '';
        $this->industry = null;
        $this->address = null;

        $this->company_logo_upload = null;
        $this->company_logo_path = null;

        $this->company_size = 'small';
        $this->score = null;
        $this->notes = null;
    }

    public function render()
    {
        // ✅ tu query original, mismo paginate(9)
        $clients = Client::query()
            ->with('contact')
            ->when($this->search !== '', function ($q) {
                $q->where('organization_name', 'like', "%{$this->search}%")
                  ->orWhereHas('contact', function ($c) {
                      $c->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%");
                  });
            })
            ->latest()
            ->paginate(9);

        $stats = [
            'total' => Client::count(),
            'with_score' => Client::whereNotNull('score')->count(),
            'avg_score' => round((float) Client::avg('score'), 1),
        ];

        return view('livewire.clients.index', [
            'clients' => $clients,
            'stats' => $stats,
            'sizes' => Client::COMPANY_SIZES, // labels ES para UI
        ]);
    }
}
