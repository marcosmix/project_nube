<?php

namespace App\Livewire\Cobros\Forms;

use App\Actions\Cobros\RegisterPaymentAction;
use App\Models\Payment;
use App\Models\PaymentInstallment;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegisterPaymentForm extends Component
{
    use WithFileUploads;

    public PaymentInstallment $installment;

    public string $paid_at = '';
    public string $amount = '';
    public ?string $notes = null;

    /**
     * @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile>
     */
    public array $receipts = [];

    public function mount(PaymentInstallment $installment): void
    {
        $this->installment = $installment->loadMissing('flow');
        $this->paid_at = now()->toDateString();
        $this->amount = number_format((float) $this->installment->balance_due, 2, '.', '');
    }

    protected function rules(): array
    {
        return [
            'paid_at' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'receipts' => ['array'],
            'receipts.*' => ['file', 'mimes:jpg,jpeg,png,pdf,webp', 'max:10240'],
        ];
    }

    protected function messages(): array
    {
        return [
            'paid_at.required' => 'La fecha de pago es obligatoria.',
            'amount.required' => 'El monto es obligatorio.',
            'amount.numeric' => 'El monto debe ser numérico.',
            'amount.gt' => 'El monto debe ser mayor a cero.',
            'receipts.*.mimes' => 'Los comprobantes deben ser imagen o PDF.',
            'receipts.*.max' => 'Cada comprobante puede pesar hasta 10 MB.',
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

        DB::transaction(function () use ($validated, $registerPaymentAction) {
            $payments = $registerPaymentAction->execute(
                $this->installment->fresh(),
                [
                    'amount' => (float) $validated['amount'],
                    'paid_at' => $validated['paid_at'],
                    'notes' => $validated['notes'] ?? null,
                ],
                auth()->user()
            );

            // Para mantener la primera versión simple:
            // adjuntamos todos los comprobantes al primer pago generado.
            /** @var Payment|null $firstPayment */
            $firstPayment = $payments->first();

            if ($firstPayment && ! empty($this->receipts)) {
                foreach ($this->receipts as $file) {
                    $path = $file->store('cobros/comprobantes', 'public');

                    $firstPayment->receipts()->create([
                        'uploaded_by' => auth()->id(),
                        'disk' => 'public',
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }
        });

        $this->dispatch('payment-registered');
        $this->dispatch('notify', type: 'success', message: 'Pago registrado correctamente.');

        $this->installment->refresh();
        $this->amount = number_format((float) $this->installment->balance_due, 2, '.', '');
        $this->paid_at = now()->toDateString();
        $this->notes = null;
        $this->receipts = [];
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.cobros.forms.register-payment-form');
    }
}
