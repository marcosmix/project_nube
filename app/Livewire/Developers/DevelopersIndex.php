<?php

namespace App\Livewire\Developers;

use App\Models\Developer;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class DevelopersIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $availabilityFilter = '';
    public string $levelFilter = '';

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
        $this->dispatch('developer-form-create');
    }

    public function edit(int $id): void
    {
        $this->dispatch('developer-form-edit', id: $id);
    }

    public function delete(int $id): void
    {
        Developer::query()->findOrFail($id)->delete();

        $this->dispatch('developer-deleted');
    }

    #[On('developer-saved')]
    #[On('developer-deleted')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $developers = Developer::query()
            ->with('contact')
            ->when($this->search !== '', function ($query) {
                $term = '%' . trim($this->search) . '%';

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
            'statuses' => Developer::STATUSES,
            'availabilities' => Developer::AVAILABILITIES,
            'levels' => Developer::LEVELS,
        ]);
    }
}
