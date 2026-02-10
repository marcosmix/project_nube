<?php
namespace App\Livewire\Developers;

use App\Models\Developer;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    // Modal
    public bool $modalOpen = false;
    public ?int $developerId = null;

    // Contact
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public ?string $phone = null;
    public ?string $birthdate = null;
    public ?string $job_title = null;

    // Developer
    public string $role = 'Full Stack'; // opcional: si querés mostrar “role” como en la UI
    public string $status = 'active';
    public string $availability = 'full_time';
    public string $level = 'junior';
    public ?int $score = null;

    public ?string $github_username = null;
    public ?string $github_url = null;
    public ?string $linkedin_url = null;
    public ?string $alias = null;
    public ?string $cbu = null;
    public ?string $phrase = null;
    public ?string $notes = null;

    public array $skills = []; // chips
    public array $skins = [];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->modalOpen = true;
    }

    public function edit(int $id): void
    {
        $dev = Developer::with('contact')->findOrFail($id);

        $this->developerId = $dev->id;

        $this->first_name = $dev->contact->first_name;
        $this->last_name = $dev->contact->last_name;
        $this->email = $dev->contact->email;
        $this->phone = $dev->contact->phone;
        $this->birthdate = optional($dev->contact->birthdate)->format('Y-m-d');
        $this->job_title = $dev->contact->job_title;

        $this->status = $dev->status;
        $this->availability = $dev->availability;
        $this->level = $dev->level;
        $this->score = $dev->score;

        $this->github_username = $dev->github_username;
        $this->github_url = $dev->github_url;
        $this->linkedin_url = $dev->linkedin_url;
        $this->alias = $dev->alias;
        $this->cbu = $dev->cbu;
        $this->phrase = $dev->phrase;
        $this->notes = $dev->notes;

        $this->skills = $dev->skills ?? [];
        $this->skins  = $dev->skins ?? [];

        $this->modalOpen = true;
    }

    public function delete(int $id): void
    {
        Developer::findOrFail($id)->delete();
    }

    public function closeModal(): void
    {
        $this->modalOpen = false;
    }

    public function save(): void
    {
        $rules = [
            'first_name' => ['required','string','max:80'],
            'last_name'  => ['required','string','max:80'],
            'email'      => ['required','email'],
            'status'     => ['required','in:active,inactive'],
            'availability'=> ['required','in:full_time,freelance'],
            'level'      => ['required','in:junior,semi_senior,senior,lead'],
            'score'      => ['nullable','integer','min:1','max:10'],
            'skills'     => ['array'],
            'skins'      => ['array'],
        ];

        $this->validate($rules);

        // Upsert contact by email (clave para evitar duplicados)
        $contact = \App\Models\Contact::updateOrCreate(
            ['email' => $this->email],
            [
                'first_name' => $this->first_name,
                'last_name'  => $this->last_name,
                'phone'      => $this->phone,
                'birthdate'  => $this->birthdate,
                'job_title'  => $this->job_title,
            ]
        );

        // Create/Update developer
        $dev = $this->developerId
            ? Developer::findOrFail($this->developerId)
            : new Developer();

        $dev->fill([
            'contact_id' => $contact->id,
            'status' => $this->status,
            'availability' => $this->availability,
            'level' => $this->level,
            'score' => $this->score,
            'github_username' => $this->github_username,
            'github_url' => $this->github_url,
            'linkedin_url' => $this->linkedin_url,
            'alias' => $this->alias,
            'cbu' => $this->cbu,
            'phrase' => $this->phrase,
            'notes' => $this->notes,
            'skills' => $this->skills,
            'skins' => $this->skins,
        ])->save();

        $this->closeModal();
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
            'developerId',
            'first_name','last_name','email','phone','birthdate','job_title',
            'status','availability','level','score',
            'github_username','github_url','linkedin_url','alias','cbu','phrase','notes',
            'skills','skins',
        ]);

        $this->status = 'active';
        $this->availability = 'full_time';
        $this->level = 'junior';
        $this->skills = [];
        $this->skins = [];
    }

    public function render()
    {
        $developers = Developer::query()
            ->with('contact')
            ->when($this->search !== '', function ($q) {
                $s = '%'.$this->search.'%';
                $q->whereHas('contact', fn($cq) =>
                    $cq->where('first_name','like',$s)
                       ->orWhere('last_name','like',$s)
                       ->orWhere('email','like',$s)
                       ->orWhere('job_title','like',$s)
                );
            })
            ->latest()
            ->paginate(12);

        $all = Developer::query();
        $total = (clone $all)->count();
        $activos = (clone $all)->where('status','active')->count();
        $fullTime = (clone $all)->where('availability','full_time')->count();
        $freelance = (clone $all)->where('availability','freelance')->count();

        return view('livewire.developers.index', compact(
            'developers','total','activos','fullTime','freelance'
        ));
    }
}