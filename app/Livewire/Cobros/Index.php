<?php

namespace App\Livewire\Cobros;

use App\Models\PaymentFlow;
use App\Models\ScheduledCharge;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public string $tab = 'flows';

    public string $scheduledSearch = '';

    public function mount(): void
    {
        $this->search = (string) request()->query('search', '');
        $this->status = (string) request()->query('status', '');
        $this->tab = in_array(request()->query('tab'), ['flows', 'scheduled'], true)
            ? (string) request()->query('tab')
            : 'flows';
    }

    public function setTab(string $tab): void
    {
        if (! in_array($tab, ['flows', 'scheduled'], true)) {
            return;
        }

        $this->tab = $tab;
        $this->resetPage();
        $this->resetPage('scheduledPage');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingScheduledSearch(): void
    {
        $this->resetPage('scheduledPage');
    }

    public function getFlowsProperty()
    {
        return PaymentFlow::query()
            ->with(['project.client.contact', 'installments'])
            ->when($this->search !== '', function ($query) {
                $query->whereHas('project', function ($projectQuery) {
                    $projectQuery->where('name', 'like', "%{$this->search}%")
                        ->orWhereHas('client', function ($clientQuery) {
                            $clientQuery->where('organization_name', 'like', "%{$this->search}%")
                                ->orWhereHas('contact', function ($contactQuery) {
                                    $contactQuery->where('first_name', 'like', "%{$this->search}%")
                                        ->orWhere('last_name', 'like', "%{$this->search}%")
                                        ->orWhere('email', 'like', "%{$this->search}%");
                                });
                        });
                });
            })
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->latest('id')
            ->paginate(10);
    }

    public function getScheduledChargesProperty()
    {
        return ScheduledCharge::query()
            ->with(['client.contact', 'attachments'])
            ->when($this->scheduledSearch !== '', function ($query) {
                $query->where(function ($innerQuery) {
                    $innerQuery
                        ->where('reference_name', 'like', "%{$this->scheduledSearch}%")
                        ->orWhere('detail', 'like', "%{$this->scheduledSearch}%")
                        ->orWhereHas('client', function ($clientQuery) {
                            $clientQuery->where('organization_name', 'like', "%{$this->scheduledSearch}%")
                                ->orWhereHas('contact', function ($contactQuery) {
                                    $contactQuery->where('first_name', 'like', "%{$this->scheduledSearch}%")
                                        ->orWhere('last_name', 'like', "%{$this->scheduledSearch}%")
                                        ->orWhere('email', 'like', "%{$this->scheduledSearch}%");
                                });
                        });
                });
            })
            ->orderBy('charge_date')
            ->latest('id')
            ->paginate(10, ['*'], 'scheduledPage');
    }

    public function render()
    {
        return view('livewire.cobros.index', [
            'flows' => $this->flows,
            'scheduledCharges' => $this->scheduledCharges,
        ]);
    }
}
