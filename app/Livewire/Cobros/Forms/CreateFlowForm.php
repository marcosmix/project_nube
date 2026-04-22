<?php

namespace App\Livewire\Cobros\Forms;

use App\Actions\Cobros\CreatePaymentFlowAction;
use App\Enums\Cobros\PaymentFlowStatus;
use App\Enums\Cobros\PaymentFrequency;
use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class CreateFlowForm extends Component
{
    public int $currentStep = 1;

    public ?int $client_id = null;
    public ?int $project_id = null;

    public string $total_amount = '0.00';
    public string $installments_count = '1';
    public string $frequency = 'monthly';
    public string $start_date = '';
    public string $grace_days = '0';
    public ?string $notes = null;
    public string $status = 'active';

    public bool $auto_send_enabled = false;
    public array $installmentRows = [];

    public function mount(): void
    {
        $this->start_date = now()->toDateString();
        $this->frequency = PaymentFrequency::Monthly->value;
        $this->status = PaymentFlowStatus::Active->value;
        $this->regenerateInstallments();
    }

    public function updatedClientId($value): void
    {
        $this->client_id = $value !== null && $value !== '' ? (int) $value : null;
        $this->project_id = null;
        $this->total_amount = '0.00';
        $this->installments_count = '1';
        $this->notes = null;
        $this->auto_send_enabled = false;
        $this->resetValidation();
        $this->regenerateInstallments();
    }

    public function updatedInstallmentsCount($value): void
    {
        $count = max(1, min(240, (int) $value));
        $this->installments_count = (string) $count;
        $this->regenerateInstallments();
    }

    public function updatedStartDate(): void
    {
        $this->syncInstallmentDueDates();
    }

    public function updatedGraceDays(): void
    {
        $this->syncInstallmentDueDates();
    }

    public function updatedFrequency(): void
    {
        $this->syncInstallmentDueDates();
    }

    public function selectProject(int $projectId): void
    {
        $project = $this->clientProjects->firstWhere('id', $projectId);

        if (! $project || $this->projectDisabledReason($project) !== null) {
            return;
        }

        $this->project_id = $project->id;
        $this->hydrateFromProject($project);
        $this->resetValidation();
    }

    public function goToStep(int $step): void
    {
        if ($step < 1 || $step > 3) {
            return;
        }

        if ($step > $this->currentStep) {
            foreach (range($this->currentStep, $step - 1) as $stepToValidate) {
                $this->validateStep($stepToValidate);
            }
        }

        $this->currentStep = $step;
    }

    public function nextStep(): void
    {
        $this->validateStep($this->currentStep);

        if ($this->currentStep < 3) {
            $this->currentStep++;
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function save(CreatePaymentFlowAction $createPaymentFlowAction)
    {
        $this->validateStep(1);
        $this->validateStep(2);
        $this->validateStep(3);

        $project = $this->selectedProject;

        if (! $project) {
            throw ValidationException::withMessages([
                'project_id' => 'Debés seleccionar un proyecto válido.',
            ]);
        }

        $flow = $createPaymentFlowAction->execute(
            $project,
            [
                'total_amount' => (float) $this->total_amount,
                'installments_count' => (int) $this->installments_count,
                'frequency' => $this->frequency,
                'start_date' => $this->start_date,
                'grace_days' => (int) $this->grace_days,
                'notes' => $this->notes,
                'status' => $this->status,
                'auto_send_enabled' => $this->auto_send_enabled,
                'auto_send_email' => $this->autoSendEmail,
                'custom_installments' => collect($this->installmentRows)
                    ->map(fn (array $row) => [
                        'amount' => round((float) $row['amount'], 2),
                        'due_date' => $row['due_date'],
                    ])
                    ->all(),
            ],
            auth()->user()
        );

        session()->flash('success', 'Flujo de cobro creado correctamente.');

        return redirect()->route('cobros.show', $flow);
    }

    protected function validateStep(int $step): void
    {
        $rules = match ($step) {
            1 => $this->stepOneRules(),
            2 => $this->stepTwoRules(),
            3 => $this->stepThreeRules(),
            default => [],
        };

        if ($rules === []) {
            return;
        }

        $validated = $this->validate($rules, $this->messages());

        if ($step === 1) {
            $project = $this->selectedProject;

            if (! $project || $this->projectDisabledReason($project) !== null) {
                throw ValidationException::withMessages([
                    'project_id' => 'Seleccioná un proyecto habilitado para crear el flujo.',
                ]);
            }
        }

        if ($step === 2) {
            $this->ensureInstallmentSumMatchesProjectTotal($validated);
        }
    }

    protected function stepOneRules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'project_id' => ['required', 'exists:projects,id'],
        ];
    }

    protected function stepTwoRules(): array
    {
        $count = max(1, (int) $this->installments_count);

        return [
            'project_id' => ['required', 'exists:projects,id'],
            'total_amount' => ['required', 'numeric', 'gt:0'],
            'installments_count' => ['required', 'integer', 'min:1', 'max:240'],
            'frequency' => ['required', 'in:weekly,biweekly,monthly'],
            'start_date' => ['required', 'date'],
            'grace_days' => ['required', 'integer', 'min:0', 'max:365'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:draft,active'],
            'installmentRows' => ['required', 'array', 'size:'.$count],
            'installmentRows.*.number' => ['required', 'integer', 'min:1'],
            'installmentRows.*.amount' => ['required', 'numeric', 'gt:0'],
            'installmentRows.*.due_date' => ['required', 'date'],
        ];
    }

    protected function stepThreeRules(): array
    {
        return [
            'auto_send_enabled' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'client_id.required' => 'Debés seleccionar un cliente.',
            'project_id.required' => 'Debés seleccionar un proyecto.',
            'project_id.exists' => 'El proyecto seleccionado no es válido.',
            'total_amount.required' => 'No se encontró un monto total válido para el proyecto.',
            'total_amount.gt' => 'El monto total del flujo debe ser mayor a cero.',
            'installments_count.required' => 'La cantidad de cuotas es obligatoria.',
            'installments_count.integer' => 'La cantidad de cuotas debe ser un número entero.',
            'installments_count.min' => 'La cantidad mínima de cuotas es 1.',
            'frequency.required' => 'La frecuencia es obligatoria.',
            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'grace_days.required' => 'Los días de gracia son obligatorios.',
            'installmentRows.required' => 'Debés generar al menos una cuota.',
            'installmentRows.size' => 'La cantidad de cuotas generadas no coincide con lo configurado.',
            'installmentRows.*.amount.required' => 'Cada cuota debe tener un monto.',
            'installmentRows.*.amount.gt' => 'Cada cuota debe tener un monto mayor a cero.',
            'installmentRows.*.due_date.required' => 'Cada cuota debe tener una fecha estimada.',
        ];
    }

    protected function ensureInstallmentSumMatchesProjectTotal(array $validated): void
    {
        $sum = round(collect($validated['installmentRows'])->sum(fn ($row) => (float) $row['amount']), 2);
        $total = round((float) $validated['total_amount'], 2);

        if (abs($sum - $total) > 0.009) {
            throw ValidationException::withMessages([
                'installmentRows' => 'La suma de las cuotas debe coincidir exactamente con el monto total del proyecto.',
            ]);
        }
    }

    protected function hydrateFromProject(Project $project): void
    {
        $amount = round((float) ($project->total_cost ?? 0), 2);

        $this->total_amount = number_format($amount, 2, '.', '');
        $this->notes ??= $project->prospection_notes;
        $this->installments_count = $this->installments_count !== '' ? $this->installments_count : '1';
        $this->regenerateInstallments();
    }

    protected function regenerateInstallments(bool $preserveAmounts = false): void
    {
        $count = max(1, (int) $this->installments_count);
        $amounts = $preserveAmounts && count($this->installmentRows) === $count
            ? collect($this->installmentRows)->pluck('amount')->map(fn ($amount) => number_format((float) $amount, 2, '.', ''))->all()
            : $this->splitAmountIntoInstallments((float) $this->total_amount, $count);

        $rows = [];

        foreach ($amounts as $index => $amount) {
            $rows[] = [
                'number' => $index + 1,
                'amount' => $amount,
                'due_date' => $this->buildDueDate($index),
            ];
        }

        $this->installmentRows = $rows;
    }

    protected function syncInstallmentDueDates(): void
    {
        if (empty($this->installmentRows)) {
            $this->regenerateInstallments();
            return;
        }

        foreach ($this->installmentRows as $index => $row) {
            $this->installmentRows[$index]['due_date'] = $this->buildDueDate($index);
        }
    }

    protected function splitAmountIntoInstallments(float $totalAmount, int $count): array
    {
        if ($count <= 0) {
            return [];
        }

        $totalCents = (int) round($totalAmount * 100);
        $base = intdiv($totalCents, $count);
        $remainder = $totalCents % $count;
        $amounts = [];

        for ($i = 0; $i < $count; $i++) {
            $cents = $base + ($i < $remainder ? 1 : 0);
            $amounts[] = number_format($cents / 100, 2, '.', '');
        }

        return $amounts;
    }

    protected function buildDueDate(int $index): string
    {
        $startDate = $this->start_date !== ''
            ? Carbon::parse($this->start_date)->startOfDay()
            : now()->startOfDay();

        $startDate->addDays((int) $this->grace_days);

        return match ($this->frequency) {
            PaymentFrequency::Weekly->value => $startDate->copy()->addWeeks($index)->toDateString(),
            PaymentFrequency::Biweekly->value => $startDate->copy()->addWeeks($index * 2)->toDateString(),
            default => $startDate->copy()->addMonthsNoOverflow($index)->toDateString(),
        };
    }

    public function projectDisabledReason(Project $project): ?string
    {
        $hasOpenFlow = $project->paymentFlows
            ->contains(fn ($flow) => in_array($flow->status->value, [PaymentFlowStatus::Draft->value, PaymentFlowStatus::Active->value], true));

        if ($hasOpenFlow) {
            return 'Flujo creado';
        }

        if ($project->status !== ProjectStatus::SaleClosed) {
            return 'Estado no permitido';
        }

        if ((float) ($project->total_cost ?? 0) <= 0) {
            return 'Sin monto';
        }

        return null;
    }

    public function getClientsProperty(): Collection
    {
        return Client::query()
            ->with('contact')
            ->withCount('projects')
            ->orderBy('organization_name')
            ->get();
    }

    public function getClientProjectsProperty(): Collection
    {
        if (! $this->client_id) {
            return collect();
        }

        return Project::query()
            ->with(['paymentFlows', 'client.contact'])
            ->where('client_id', $this->client_id)
            ->orderBy('name')
            ->get();
    }

    public function getSelectedProjectProperty(): ?Project
    {
        if (! $this->project_id) {
            return null;
        }

        return Project::query()
            ->with(['client.contact', 'paymentFlows'])
            ->find($this->project_id);
    }

    public function getSelectedClientProperty(): ?Client
    {
        if (! $this->client_id) {
            return null;
        }

        return $this->clients->firstWhere('id', $this->client_id);
    }

    public function getInstallmentsSumProperty(): float
    {
        return round(collect($this->installmentRows)->sum(fn ($row) => (float) $row['amount']), 2);
    }

    public function getInstallmentsDifferenceProperty(): float
    {
        return round($this->installmentsSum - (float) $this->total_amount, 2);
    }

    public function getInstallmentSummaryToneProperty(): string
    {
        if ($this->installmentsDifference > 0) {
            return 'border-amber-200 bg-amber-50 text-amber-700';
        }

        if (abs($this->installmentsDifference) <= 0.009) {
            return 'border-emerald-200 bg-emerald-50 text-emerald-700';
        }

        return 'border-slate-200 bg-slate-50 text-slate-700';
    }

    public function getAutoSendEmailProperty(): ?string
    {
        return $this->selectedProject?->client?->contact?->email;
    }

    public function render()
    {
        return view('livewire.cobros.forms.create-flow-form', [
            'clients' => $this->clients,
            'clientProjects' => $this->clientProjects,
            'selectedClient' => $this->selectedClient,
            'selectedProject' => $this->selectedProject,
            'frequencies' => PaymentFrequency::cases(),
        ]);
    }
}
