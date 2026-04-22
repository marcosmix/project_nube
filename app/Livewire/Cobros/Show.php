<?php

namespace App\Livewire\Cobros;

use App\Models\PaymentFlow;
use App\Models\PaymentInstallment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public PaymentFlow $paymentFlow;
    public ?int $selectedInstallmentId = null;

    public function mount(PaymentFlow $paymentFlow): void
    {
        $this->paymentFlow = $paymentFlow;
        $this->reloadFlow();
    }

    public function selectInstallment(int $installmentId): void
    {
        $this->selectedInstallmentId = $installmentId;
        $this->reloadFlow();
    }

    #[On('payment-registered')]
    #[On('installments-updated')]
    public function reloadFlow(): void
    {
        $selectedId = $this->selectedInstallmentId;

        $this->paymentFlow = PaymentFlow::query()
            ->with([
            'project.client.contact',
            'project.developers',
            'installments.payments.receipts',
            'installments.statusLogs.user',
            ])
            ->find($this->paymentFlow->getKey())
            ?? throw (new ModelNotFoundException())->setModel(PaymentFlow::class, [$this->paymentFlow->getKey()]);

        if ($selectedId) {
            $this->selectedInstallmentId = $selectedId;
        }
    }

    public function getSelectedInstallmentProperty(): ?PaymentInstallment
    {
        if (! $this->selectedInstallmentId) {
            return null;
        }

        return $this->paymentFlow->installments->firstWhere('id', $this->selectedInstallmentId);
    }

    public function render()
    {
        return view('livewire.cobros.show', [
            'flow' => $this->paymentFlow,
            'selectedInstallment' => $this->selectedInstallment,
        ]);
    }
}
