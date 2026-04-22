<?php

namespace App\Livewire\Cobros\Forms;

use App\Actions\Cobros\UpdateFutureInstallmentsAction;
use App\Models\PaymentInstallment;
use Livewire\Component;

class EditFutureInstallmentsForm extends Component
{
    public PaymentInstallment $installment;

    /**
     * @var array<int, array{id:int, number:int, due_date:string, amount:string, locked:bool}>
     */
    public array $rows = [];

    public function mount(PaymentInstallment $installment): void
    {
        $this->installment = $installment->loadMissing('flow');

        $this->loadRows();
    }

    public function loadRows(): void
    {
        $flow = $this->installment->flow()->with('installments.payments')->firstOrFail();

        $this->rows = $flow->installments
            ->where('number', '>=', $this->installment->number)
            ->map(function ($item) {
                $locked = $item->payments->isNotEmpty()
                    || (float) $item->paid_amount > 0
                    || (float) $item->balance_due <= 0;

                return [
                    'id' => $item->id,
                    'number' => $item->number,
                    'due_date' => $item->due_date?->toDateString(),
                    'amount' => number_format((float) $item->amount, 2, '.', ''),
                    'locked' => $locked,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function rules(): array
    {
        return [
            'rows' => ['array', 'min:1'],
            'rows.*.id' => ['required', 'integer', 'exists:payment_installments,id'],
            'rows.*.due_date' => ['required', 'date'],
            'rows.*.amount' => ['required', 'numeric', 'gt:0'],
            'rows.*.locked' => ['boolean'],
        ];
    }

    public function save(UpdateFutureInstallmentsAction $action): void
    {
        $validated = $this->validate();

        $editableRows = collect($validated['rows'])
            ->reject(fn ($row) => (bool) $row['locked'])
            ->map(fn ($row) => [
                'id' => (int) $row['id'],
                'due_date' => $row['due_date'],
                'amount' => (float) $row['amount'],
            ])
            ->values()
            ->all();

        if (empty($editableRows)) {
            $this->addError('rows', 'No hay cuotas editables para guardar.');
            return;
        }

        $action->execute(
            $this->installment->fresh(),
            $editableRows,
            auth()->user()
        );

        $this->loadRows();

        $this->dispatch('installments-updated');
        $this->dispatch('notify', type: 'success', message: 'Cuotas futuras actualizadas correctamente.');
    }

    public function render()
    {
        return view('livewire.cobros.forms.edit-future-installments-form');
    }
}