<?php

namespace App\Livewire\Cobros\Forms;

use App\Actions\Cobros\CreatePaymentFlowAction;
use App\Enums\Cobros\PaymentFlowStatus;
use App\Enums\Cobros\PaymentFrequency;
use App\Models\Project;
use Livewire\Component;

class CreateFlowForm extends Component
{
    public string $projectSearch = '';
    public ?int $project_id = null;

    public string $total_amount = '';
    public string $installments_count = '1';
    public string $frequency = 'monthly';
    public string $start_date = '';
    public string $grace_days = '0';
    public ?string $notes = null;
    public string $status = 'active';

    public function mount(): void
    {
        $this->start_date = now()->toDateString();
        $this->frequency = PaymentFrequency::Monthly->value;
        $this->status = PaymentFlowStatus::Active->value;
    }

    protected function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'total_amount' => ['required', 'numeric', 'gt:0'],
            'installments_count' => ['required', 'integer', 'min:1', 'max:240'],
            'frequency' => ['required', 'in:weekly,biweekly,monthly'],
            'start_date' => ['required', 'date'],
            'grace_days' => ['required', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:draft,active'],
        ];
    }

    protected function messages(): array
    {
        return [
            'project_id.required' => 'Debés seleccionar un proyecto.',
            'project_id.exists' => 'El proyecto seleccionado no es válido.',
            'total_amount.required' => 'El monto total es obligatorio.',
            'total_amount.numeric' => 'El monto total debe ser numérico.',
            'total_amount.gt' => 'El monto total debe ser mayor a cero.',
            'installments_count.required' => 'La cantidad de cuotas es obligatoria.',
            'installments_count.integer' => 'La cantidad de cuotas debe ser un número entero.',
            'installments_count.min' => 'La cantidad mínima de cuotas es 1.',
            'frequency.required' => 'La frecuencia es obligatoria.',
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'grace_days.required' => 'Los días de gracia son obligatorios.',
        ];
    }

    public function getProjectsProperty()
    {
        return Project::query()
            ->with(['client.contact'])
            ->when($this->projectSearch !== '', function ($query) {
                $search = $this->projectSearch;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhereHas('client.contact', function ($contactQuery) use ($search) {
                            $contactQuery->where('organization', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                        });
                });
            })
            ->whereDoesntHave('paymentFlows', function ($query) {
                $query->whereIn('status', ['draft', 'active']);
            })
            ->orderBy('name')
            ->limit(12)
            ->get();
    }

    public function selectProject(int $projectId): void
    {
        $this->project_id = $projectId;
    }

    public function create(CreatePaymentFlowAction $createPaymentFlowAction)
    {
        $validated = $this->validate();

        $project = Project::findOrFail($validated['project_id']);

        $flow = $createPaymentFlowAction->execute(
            $project,
            [
                'total_amount' => (float) $validated['total_amount'],
                'installments_count' => (int) $validated['installments_count'],
                'frequency' => $validated['frequency'],
                'start_date' => $validated['start_date'],
                'grace_days' => (int) $validated['grace_days'],
                'notes' => $validated['notes'] ?? null,
                'status' => $validated['status'],
            ],
            auth()->user()
        );

        session()->flash('success', 'Flujo de cobro creado correctamente.');

        return redirect()->route('cobros.show', $flow);
    }

    public function render()
    {
        return view('livewire.cobros.forms.create-flow-form', [
            'projects' => $this->projects,
            'selectedProject' => $this->project_id
                ? Project::with(['client.contact'])->find($this->project_id)
                : null,
            'frequencies' => PaymentFrequency::cases(),
        ]);
    }
}
