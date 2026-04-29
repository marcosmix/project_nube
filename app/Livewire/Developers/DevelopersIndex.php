<?php

namespace App\Livewire\Developers;

use App\Models\Contact;
use App\Models\Developer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]

class DevelopersIndex extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $availabilityFilter = '';

    public string $levelFilter = '';

    public bool $open = false;

    public ?int $developerId = null;

    public ?int $contactId = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public ?string $phone = null;

    public ?string $birthdate = null;

    public ?string $puesto = null;

    public array $skills = [];

    public array $skins = [];

    public string $newSkill = '';

    public ?string $github_username = null;

    public ?string $github_url = null;

    public ?string $linkedin_url = null;

    public ?string $alias = null;

    public ?string $cbu = null;

    public ?string $phrase = null;

    public ?int $score = null;

    public string $level = 'junior';

    public string $status = 'active';

    public string $availability = 'full_time';

    public ?string $notes = null;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $profile_photo_upload = null;

    public ?string $profile_photo = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'availabilityFilter' => ['except' => ''],
        'levelFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingAvailabilityFilter(): void
    {
        $this->resetPage();
    }

    public function updatingLevelFilter(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->open = true;
    }

    public function openEdit(int $id): void
    {
        $developer = Developer::query()->with('contact')->findOrFail($id);

        $this->fillFromDeveloper($developer);
        $this->resetValidation();
        $this->open = true;
    }

    public function delete(int $id): void
    {
        Developer::query()->findOrFail($id)->delete();
        $this->resetPage();
    }

    public function close(): void
    {
        $this->open = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules(), $this->messages(), $this->attributes());

        $skills = $this->sanitizeArrayValues($this->skills);
        $skins = $this->sanitizeArrayValues($this->skins);

        $contactData = [
            'first_name' => trim($this->first_name),
            'last_name' => trim($this->last_name),
            'email' => trim($this->email),
            'phone' => $this->nullableTrim($this->phone),
            'birthdate' => $this->nullableTrim($this->birthdate),
        ];

        if ($this->contactHasJobTitleColumn()) {
            $contactData['job_title'] = $this->nullableTrim($this->puesto);
        }

        $contact = $this->contactId
            ? Contact::query()->findOrFail($this->contactId)
            : new Contact;

        $contact->fill($contactData);
        $contact->save();

        $notes = $this->nullableTrim($this->notes);
        if (! $this->contactHasJobTitleColumn()) {
            $notes = $this->mergePuestoInNotes($notes, $this->nullableTrim($this->puesto));
        }

        $githubUrl = $this->nullableTrim($this->github_url);
        $githubUsername = $this->nullableTrim($this->github_username) ?: $this->extractGithubUsername($githubUrl);

        $developerData = [
            'contact_id' => $contact->id,
            'skills' => $skills,
            'skins' => $skins,
            'github_username' => $githubUsername,
            'github_url' => $githubUrl,
            'linkedin_url' => $this->nullableTrim($this->linkedin_url),
            'alias' => $this->nullableTrim($this->alias),
            'cbu' => $this->nullableTrim($this->cbu),
            'phrase' => $this->nullableTrim($this->phrase),
            'score' => $validated['score'] ?? null,
            'level' => $validated['level'],
            'status' => $validated['status'],
            'availability' => $validated['availability'],
            'notes' => $notes,
        ];

        if ($this->profile_photo_upload) {
            if ($this->profile_photo) {
                Storage::disk('public')->delete($this->profile_photo);
            }

            $developerData['profile_photo'] = $this->profile_photo_upload->store('developers', 'public');
        } else {
            $developerData['profile_photo'] = $this->profile_photo;
        }

        if ($this->developerId) {
            $developer = Developer::query()->findOrFail($this->developerId);
            $developer->update($developerData);
        } else {
            $developer = Developer::query()->updateOrCreate(
                ['contact_id' => $contact->id],
                $developerData,
            );
        }

        $this->close();
        $this->resetForm();
        $this->resetPage();
    }

    public function addSkill(string $skill = ''): void
    {
        $value = $this->normalizeItem($skill !== '' ? $skill : $this->newSkill);

        if ($value === '' || in_array($value, $this->skills, true)) {
            $this->newSkill = '';

            return;
        }

        if (! $this->isAllowedSkill($value)) {
            $this->addError('skills', 'Solo podés agregar skills permitidas de la lista.');

            return;
        }

        $this->resetErrorBag('skills');
        $this->skills[] = $value;
        $this->skills = $this->sanitizeArrayValues($this->skills);
        $this->newSkill = '';
    }

    public function removeSkill(string $skill): void
    {
        $this->skills = array_values(array_filter(
            $this->skills,
            fn (string $item) => $item !== $skill,
        ));
    }

    #[Computed]
    public function canAddTypedSkill(): bool
    {
        $value = $this->normalizeItem($this->newSkill);

        return $value !== ''
            && $this->isAllowedSkill($value)
            && ! in_array($value, $this->skills, true);
    }

    #[Computed]
    public function filteredSuggestions(): array
    {
        $needle = mb_strtolower(trim($this->newSkill));

        if ($needle === '') {
            return [];
        }

        return collect($this->allowedSkills())
            ->reject(fn (string $item) => in_array($item, $this->skills, true))
            ->filter(fn (string $item) => str_contains(mb_strtolower($item), $needle))
            ->take(8)
            ->values()
            ->all();
    }

    private function resetForm(): void
    {
        $this->developerId = null;
        $this->contactId = null;

        $this->reset([
            'first_name',
            'last_name',
            'email',
            'phone',
            'birthdate',
            'puesto',
            'skills',
            'skins',
            'newSkill',
            'github_username',
            'github_url',
            'linkedin_url',
            'alias',
            'cbu',
            'phrase',
            'score',
            'level',
            'status',
            'availability',
            'notes',
            'profile_photo_upload',
            'profile_photo',
        ]);

        $this->level = 'junior';
        $this->status = 'active';
        $this->availability = 'full_time';
        $this->resetValidation();
    }

    private function fillFromDeveloper(Developer $developer): void
    {
        $this->developerId = $developer->id;
        $this->contactId = $developer->contact_id;

        $this->first_name = (string) $developer->contact?->first_name;
        $this->last_name = (string) $developer->contact?->last_name;
        $this->email = (string) $developer->contact?->email;
        $this->phone = $developer->contact?->phone;
        $this->birthdate = optional($developer->contact?->birthdate)->format('Y-m-d');
        $this->puesto = $this->contactHasJobTitleColumn() ? $developer->contact?->job_title : $this->extractPuestoFromNotes($developer->notes);

        $this->skills = $developer->skills ?? [];
        $this->skins = $developer->skins ?? [];
        $this->newSkill = '';
        $this->github_username = $developer->github_username;
        $this->github_url = $developer->github_url;
        $this->linkedin_url = $developer->linkedin_url;
        $this->alias = $developer->alias;
        $this->cbu = $developer->cbu;
        $this->phrase = $developer->phrase;
        $this->score = $developer->score;
        $this->level = $developer->level;
        $this->status = $developer->status;
        $this->availability = $developer->availability;
        $this->notes = $developer->notes;
        $this->profile_photo = $developer->profile_photo;
        $this->profile_photo_upload = null;
    }

    private function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('contacts', 'email')->ignore($this->contactId)],
            'phone' => ['nullable', 'string', 'max:50'],
            'birthdate' => ['nullable', 'date'],
            'puesto' => ['nullable', 'string', 'max:255'],

            'skills' => ['required', 'array', 'min:1'],
            'skills.*' => ['required', 'string', Rule::in(config('developers.skills', []))],
            'skins' => ['nullable', 'array'],
            'skins.*' => ['required', 'string'],
            'github_username' => ['nullable', 'string', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'alias' => ['nullable', 'string', 'max:255', Rule::unique('developers', 'alias')->ignore($this->developerId)],
            'cbu' => ['nullable', 'string', 'max:255'],
            'phrase' => ['nullable', 'string', 'max:255'],
            'score' => ['nullable', 'integer', 'between:1,10'],
            'level' => ['required', Rule::in(array_keys(Developer::LEVELS))],
            'status' => ['required', Rule::in(array_keys(Developer::STATUSES))],
            'availability' => ['required', Rule::in(array_keys(Developer::AVAILABILITIES))],
            'notes' => ['nullable', 'string'],
            'profile_photo_upload' => ['nullable', 'image', 'max:2048'],
        ];
    }

    private function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'email.email' => 'Ingresá un email válido.',
            'email.unique' => 'Este email ya está registrado.',
            'skills.min' => 'Debés agregar al menos una skill.',
            'skills.*.in' => 'La skill seleccionada no está permitida.',
            'alias.unique' => 'Este alias bancario ya está en uso.',
            'score.between' => 'La puntuación debe estar entre 1 y 10.',
            'github_url.url' => 'La URL de GitHub no es válida.',
            'linkedin_url.url' => 'La URL de LinkedIn no es válida.',
            'profile_photo_upload.image' => 'La foto debe ser una imagen.',
            'profile_photo_upload.max' => 'La foto no puede superar 2048 KB.',
        ];
    }

    private function attributes(): array
    {
        return [
            'first_name' => 'nombre',
            'last_name' => 'apellido',
            'email' => 'email',
            'phone' => 'celular',
            'birthdate' => 'fecha de nacimiento',
            'puesto' => 'puesto',
            'skills' => 'skills',
            'github_url' => 'URL de GitHub',
            'linkedin_url' => 'URL de LinkedIn',
            'alias' => 'alias bancario',
            'cbu' => 'CBU',
            'phrase' => 'frase',
            'score' => 'puntuación',
            'level' => 'nivel',
            'status' => 'estado',
            'availability' => 'disponibilidad',
            'notes' => 'notas',
            'profile_photo_upload' => 'foto de perfil',
        ];
    }

    private function contactHasJobTitleColumn(): bool
    {
        return Schema::hasColumn('contacts', 'job_title');
    }

    private function sanitizeArrayValues(array $values): array
    {
        return collect($values)
            ->map(fn (string $item) => $this->normalizeItem($item))
            ->filter(fn (string $item) => $item !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeItem(string $value): string
    {
        return preg_replace('/\s+/', ' ', trim($value)) ?? '';
    }

    private function isAllowedSkill(string $value): bool
    {
        return in_array($value, $this->allowedSkills(), true);
    }

    private function allowedSkills(): array
    {
        return collect(config('developers.skills', []))
            ->map(fn (string $item) => $this->normalizeItem($item))
            ->filter(fn (string $item) => $item !== '')
            ->unique()
            ->values()
            ->all();
    }

    private function nullableTrim(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function extractGithubUsername(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $path = (string) parse_url($url, PHP_URL_PATH);
        $segments = array_values(array_filter(explode('/', $path)));

        return $segments[0] ?? null;
    }

    private function mergePuestoInNotes(?string $notes, ?string $puesto): ?string
    {
        $base = $notes ?? '';
        $withoutOldPuesto = preg_replace('/^Puesto:\s.*(?:\R|$)/mi', '', $base) ?? $base;
        $withoutOldPuesto = trim($withoutOldPuesto);

        if ($puesto === null) {
            return $withoutOldPuesto !== '' ? $withoutOldPuesto : null;
        }

        $lines = ["Puesto: {$puesto}"];
        if ($withoutOldPuesto !== '') {
            $lines[] = $withoutOldPuesto;
        }

        return implode(PHP_EOL, $lines);
    }

    private function extractPuestoFromNotes(?string $notes): ?string
    {
        if (! $notes) {
            return null;
        }

        preg_match('/^Puesto:\s*(.+)$/mi', $notes, $matches);

        return $matches[1] ?? null;
    }

    private function availableDevelopersBySkill(): array
    {
        $availableDevelopers = Developer::query()
            ->whereDoesntHave('projects')
            ->get(['skills']);

        return $availableDevelopers
            ->flatMap(function (Developer $developer) {
                return collect($developer->skills ?? [])
                    ->map(fn (string $skill) => $this->normalizeItem($skill))
                    ->filter(fn (string $skill) => $skill !== '')
                    ->unique();
            })
            ->countBy()
            ->sortDesc()
            ->all();
    }

    public function render()
    {
        $developers = Developer::query()
            ->with(['contact', 'projects:id,name'])
            ->withCount('projects')
            ->when($this->search !== '', function ($query) {
                $term = '%'.trim($this->search).'%';

                $query->where(function ($subQuery) use ($term) {
                    $subQuery
                        ->whereHas('contact', function ($contactQuery) use ($term) {
                            $contactQuery
                                ->where('first_name', 'like', $term)
                                ->orWhere('last_name', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        })
                        ->orWhere('alias', 'like', $term)
                        ->orWhere('github_username', 'like', $term)
                        ->orWhere('github_url', 'like', $term);
                });
            })
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->availabilityFilter !== '', fn ($query) => $query->where('availability', $this->availabilityFilter))
            ->when($this->levelFilter !== '', fn ($query) => $query->where('level', $this->levelFilter))
            ->latest()
            ->paginate(12);

        return view('livewire.developers.index', [
            'developers' => $developers,
            'total' => Developer::count(),
            'activos' => Developer::where('status', 'active')->count(),
            'fullTime' => Developer::where('availability', 'full_time')->count(),
            'freelance' => Developer::where('availability', 'freelance')->count(),
            'availableBySkill' => $this->availableDevelopersBySkill(),
            'statuses' => Developer::STATUSES,
            'availabilities' => Developer::AVAILABILITIES,
            'levels' => Developer::LEVELS,
        ])->layout('layouts.app');
    }
}
