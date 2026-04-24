<?php

namespace App\Livewire\Cobros\Forms;

use App\Actions\Cobros\RegisterPaymentAction;
use App\Models\Payment;
use App\Models\PaymentInstallment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;

class RegisterPaymentForm extends Component
{
    use WithFileUploads;

    public PaymentInstallment $installment;

    public string $paid_at = '';

    public string $amount = '';

    public ?string $payment_method = null;

    public ?string $notes = null;

    public $receipt = null;

    public function mount(PaymentInstallment $installment): void
    {
        $this->installment = $installment->loadMissing('flow');
        $this->paid_at = now()->toDateString();
        $this->amount = number_format((float) $this->installment->balance_due, 2, '.', '');
    }

    public function paymentMethodOptions(): array
    {
        return [
            Payment::METHOD_ECHEQ => 'Echeq',
            Payment::METHOD_CASH => 'Efectivo',
            Payment::METHOD_TRANSFER => 'Transferencia',
            Payment::METHOD_MERCADO_PAGO => 'Mercado Pago',
        ];
    }

    protected function rules(): array
    {
        return [
            'paid_at' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'payment_method' => ['nullable', 'in:'.implode(',', Payment::METHODS)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:10240'],
        ];
    }

    protected function messages(): array
    {
        return [
            'paid_at.required' => 'La fecha de pago es obligatoria.',
            'amount.required' => 'El monto es obligatorio.',
            'amount.numeric' => 'El monto debe ser numérico.',
            'amount.gt' => 'El monto debe ser mayor a cero.',
            'payment_method.in' => 'Seleccioná un tipo de pago válido.',
            'receipt.mimes' => 'El comprobante debe ser imagen o PDF.',
            'receipt.max' => 'El comprobante puede pesar hasta 10 MB.',
        ];
    }

    public function save(RegisterPaymentAction $registerPaymentAction): void
    {
        $this->installment->refresh();

        if ((float) $this->installment->balance_due <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'La cuota seleccionada ya no tiene saldo pendiente.',
            ]);
        }

        $validated = $this->validate();
        $balanceDue = round((float) $this->installment->balance_due, 2);
        $amount = round((float) $validated['amount'], 2);

        if ($amount > $balanceDue) {
            throw ValidationException::withMessages([
                'amount' => 'El monto no puede ser mayor al saldo pendiente de la cuota.',
            ]);
        }

        DB::transaction(function () use ($validated, $registerPaymentAction) {
            $payments = $registerPaymentAction->execute(
                $this->installment->fresh(),
                [
                    'amount' => (float) $validated['amount'],
                    'paid_at' => $validated['paid_at'],
                    'payment_method' => $validated['payment_method'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ],
                Auth::user()
            );

            /** @var Payment|null $firstPayment */
            $firstPayment = $payments->first();

            if ($firstPayment && $this->receipt) {
                $path = $this->receipt->store('cobros/comprobantes', 'public');

                $firstPayment->receipts()->create([
                    'uploaded_by' => Auth::id(),
                    'disk' => 'public',
                    'path' => $path,
                    'original_name' => $this->receipt->getClientOriginalName(),
                    'mime_type' => $this->receipt->getMimeType(),
                    'size' => $this->receipt->getSize(),
                ]);
            }
        });

        $this->dispatch('payment-registered');
        $this->dispatch('notify', type: 'success', message: 'Pago registrado correctamente.');

        $this->installment->refresh();
        $this->amount = number_format((float) $this->installment->balance_due, 2, '.', '');
        $this->paid_at = now()->toDateString();
        $this->payment_method = null;
        $this->notes = null;
        $this->receipt = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.cobros.forms.register-payment-form');
    }
}
