<?php

namespace App\Livewire\Cobros;

use App\Models\PaymentFlow;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function getFlowsProperty()
    {
        return PaymentFlow::query()
            ->with(['project.client.contact', 'installments'])
            ->when($this->search !== '', function ($query) {
                $query->whereHas('project', function ($projectQuery) {
                    $projectQuery->where('name', 'like', "%{$this->search}%")
                        ->orWhereHas('client.contact', function ($contactQuery) {
                            $contactQuery->where('first_name', 'like', "%{$this->search}%")
                                ->orWhere('last_name', 'like', "%{$this->search}%")
                                ->orWhere('organization', 'like', "%{$this->search}%");
                        });
                });
            })
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->latest('id')
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.cobros.index', [
            'flows' => $this->flows,
        ]);
    }
}