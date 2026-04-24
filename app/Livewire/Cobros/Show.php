<?php

namespace App\Livewire\Cobros;

use App\Actions\Cobros\VoidPaymentAction;
use App\Enums\Cobros\InstallmentStatus;
use App\Enums\Cobros\PaymentFlowStatus;
use App\Enums\Cobros\PaymentStatus;
use App\Models\Payment;
use App\Models\PaymentFlow;
use App\Models\PaymentInstallment;
use App\Models\PaymentReceipt;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public PaymentFlow $paymentFlow;

    public ?int $selectedInstallmentId = null;

    public bool $isInstallmentDrawerOpen = false;

    public bool $isRegisterPaymentModalOpen = false;

    public ?int $paymentModalInstallmentId = null;

    public int $registerPaymentModalNonce = 0;

    public bool $isVoidPaymentModalOpen = false;

    public ?int $paymentBeingVoidedId = null;

    public string $voidReason = '';

    public function mount(PaymentFlow $paymentFlow): void
    {
        $this->paymentFlow = $paymentFlow;
        $this->reloadFlow();
    }

    public function selectInstallment(int $installmentId): void
    {
        $this->selectedInstallmentId = $installmentId;
        $this->reloadFlow();
        $this->isInstallmentDrawerOpen = true;
    }

    public function closeInstallmentDrawer(): void
    {
        $this->isInstallmentDrawerOpen = false;
        $this->selectedInstallmentId = null;
    }

    public function handleEscape(): void
    {
        if ($this->isVoidPaymentModalOpen) {
            $this->closeVoidPaymentModal();

            return;
        }

        if ($this->isRegisterPaymentModalOpen) {
            $this->closeRegisterPaymentModal();

            return;
        }

        if ($this->isInstallmentDrawerOpen) {
            $this->closeInstallmentDrawer();
        }
    }

    public function openRegisterPaymentModal(int $installmentId): void
    {
        $installment = $this->paymentFlow->installments->firstWhere('id', $installmentId);

        if (! $installment || ! $this->canRegisterPayment($installment)) {
            return;
        }

        $this->paymentModalInstallmentId = $installment->id;
        $this->registerPaymentModalNonce++;
        $this->isRegisterPaymentModalOpen = true;
    }

    public function closeRegisterPaymentModal(): void
    {
        $this->isRegisterPaymentModalOpen = false;
        $this->paymentModalInstallmentId = null;
    }

    public function openVoidPaymentModal(int $paymentId): void
    {
        $payment = $this->findPaymentInFlow($paymentId);

        if (! $payment || $payment->status !== PaymentStatus::Posted) {
            return;
        }

        $this->paymentBeingVoidedId = $payment->id;
        $this->voidReason = '';
        $this->resetValidation();
        $this->isVoidPaymentModalOpen = true;
    }

    public function closeVoidPaymentModal(): void
    {
        $this->isVoidPaymentModalOpen = false;
        $this->paymentBeingVoidedId = null;
        $this->voidReason = '';
        $this->resetValidation();
    }

    public function voidPayment(VoidPaymentAction $voidPaymentAction): void
    {
        $validated = $this->validate([
            'voidReason' => ['required', 'string', 'max:1000'],
        ], [
            'voidReason.required' => 'El motivo de anulación es obligatorio.',
            'voidReason.max' => 'El motivo no puede superar los 1000 caracteres.',
        ]);

        $payment = $this->paymentBeingVoided;

        if (! $payment) {
            throw ValidationException::withMessages([
                'payment' => 'No se encontró el pago seleccionado.',
            ]);
        }

        $voidPaymentAction->execute($payment, $validated['voidReason'], Auth::user());

        $this->closeVoidPaymentModal();
        $this->reloadFlow();
        $this->dispatch('notify', type: 'success', message: 'Pago anulado correctamente.');
    }

    #[On('payment-registered')]
    public function handlePaymentRegistered(): void
    {
        $this->closeRegisterPaymentModal();
        $this->reloadFlow();
    }

    #[On('installments-updated')]
    public function reloadFlow(): void
    {
        $selectedId = $this->selectedInstallmentId;

        $this->paymentFlow = PaymentFlow::query()
            ->with([
                'project.client.contact',
                'project.developers',
                'installments.payments.paidBy',
                'installments.payments.receipts',
                'installments.payments.voidedBy',
                'installments.statusLogs.user',
            ])
            ->find($this->paymentFlow->getKey())
            ?? throw (new ModelNotFoundException)->setModel(PaymentFlow::class, [$this->paymentFlow->getKey()]);

        if (! $selectedId) {
            return;
        }

        $selectedInstallment = $this->paymentFlow->installments->firstWhere('id', $selectedId);

        if (! $selectedInstallment) {
            $this->selectedInstallmentId = null;
            $this->isInstallmentDrawerOpen = false;

            return;
        }

        $this->selectedInstallmentId = $selectedInstallment->id;
    }

    public function getSelectedInstallmentProperty(): ?PaymentInstallment
    {
        if (! $this->selectedInstallmentId) {
            return null;
        }

        return $this->paymentFlow->installments->firstWhere('id', $this->selectedInstallmentId);
    }

    public function getPaymentModalInstallmentProperty(): ?PaymentInstallment
    {
        if (! $this->paymentModalInstallmentId) {
            return null;
        }

        return $this->paymentFlow->installments->firstWhere('id', $this->paymentModalInstallmentId);
    }

    public function getPaymentBeingVoidedProperty(): ?Payment
    {
        if (! $this->paymentBeingVoidedId) {
            return null;
        }

        return $this->findPaymentInFlow($this->paymentBeingVoidedId);
    }

    public function getSelectedInstallmentTimelineProperty(): array
    {
        $installment = $this->selectedInstallment;

        if (! $installment) {
            return [];
        }

        $paymentItems = $installment->payments->map(function ($payment) {
            $author = $payment->paidBy?->name;
            $description = 'Pago de $'.number_format((float) $payment->amount, 2, ',', '.');
            $paymentMethod = $payment->paymentMethodLabel();

            if ($author) {
                $description .= ' por '.$author;
            }

            if ($paymentMethod) {
                $description .= ' · '.$paymentMethod;
            }

            if ($payment->status === PaymentStatus::Voided && $payment->void_reason) {
                $description .= ' · Motivo: '.$payment->void_reason;
            }

            return [
                'type' => 'payment',
                'payment_id' => $payment->id,
                'sort_at' => $payment->paid_at?->timestamp ?? 0,
                'occurred_at' => $payment->paid_at,
                'title' => $payment->status->label(),
                'description' => $description,
                'tone' => $payment->status->value === 'voided' ? 'slate' : 'emerald',
                'can_void' => $payment->status === PaymentStatus::Posted,
                'notes' => $payment->notes,
                'receipts' => $payment->receipts->map(fn (PaymentReceipt $receipt) => [
                    'id' => $receipt->id,
                    'original_name' => $receipt->original_name,
                    'mime_type' => $receipt->mime_type,
                    'type_label' => $receipt->isImage() ? 'Imagen' : ($receipt->isPdf() ? 'PDF' : 'Archivo'),
                    'size_label' => $this->formatFileSize((int) $receipt->size),
                    'is_image' => $receipt->isImage(),
                    'preview_url' => $receipt->isImage()
                        ? route('cobros.receipts.preview', ['paymentFlow' => $this->paymentFlow, 'receipt' => $receipt])
                        : null,
                    'download_url' => route('cobros.receipts.download', [
                        'paymentFlow' => $this->paymentFlow,
                        'receipt' => $receipt,
                    ]),
                ])->values()->all(),
            ];
        });

        $statusItems = $installment->statusLogs->map(function ($log) {
            $from = $this->formatInstallmentStatus($log->from_status);
            $to = $this->formatInstallmentStatus($log->to_status);

            return [
                'type' => 'status',
                'sort_at' => $log->changed_at?->timestamp ?? 0,
                'occurred_at' => $log->changed_at,
                'title' => 'Cambio de estado',
                'description' => $from ? $from.' -> '.$to : 'Estado actualizado a '.$to,
                'tone' => 'blue',
            ];
        });

        return $paymentItems
            ->concat($statusItems)
            ->sortByDesc('sort_at')
            ->values()
            ->all();
    }

    public function installmentStatusBadgeClasses(PaymentInstallment $installment): string
    {
        return match ($installment->status?->value) {
            'paid' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'partial' => 'border-amber-200 bg-amber-50 text-amber-700',
            'overdue' => 'border-rose-200 bg-rose-50 text-rose-700',
            default => 'border-slate-200 bg-slate-100 text-slate-700',
        };
    }

    public function canRegisterPayment(PaymentInstallment $installment): bool
    {
        return $this->paymentFlow->status !== PaymentFlowStatus::Cancelled
            && round((float) $installment->balance_due, 2) > 0;
    }

    protected function findPaymentInFlow(int $paymentId): ?Payment
    {
        foreach ($this->paymentFlow->installments as $installment) {
            $payment = $installment->payments->firstWhere('id', $paymentId);

            if ($payment) {
                return $payment;
            }
        }

        return null;
    }

    protected function formatInstallmentStatus(?string $status): string
    {
        if (! $status) {
            return '';
        }

        return InstallmentStatus::tryFrom($status)?->label()
            ?? str($status)->replace('_', ' ')->title()->toString();
    }

    protected function formatFileSize(int $size): string
    {
        if ($size < 1024) {
            return $size.' B';
        }

        if ($size < 1024 * 1024) {
            return number_format($size / 1024, 1, ',', '.').' KB';
        }

        return number_format($size / (1024 * 1024), 1, ',', '.').' MB';
    }

    public function render()
    {
        return view('livewire.cobros.show', [
            'flow' => $this->paymentFlow,
            'selectedInstallment' => $this->selectedInstallment,
            'paymentModalInstallment' => $this->paymentModalInstallment,
            'paymentBeingVoided' => $this->paymentBeingVoided,
            'installmentTimeline' => $this->selectedInstallmentTimeline,
        ]);
    }
}
