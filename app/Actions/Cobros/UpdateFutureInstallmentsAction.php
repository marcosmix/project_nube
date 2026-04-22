<?php

namespace App\Actions\Cobros;

use App\Models\PaymentInstallment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateFutureInstallmentsAction
{
    public function __construct(
        protected SyncInstallmentStatusAction $syncInstallmentStatusAction
    ) {}

    /**
     * @param array<int, array{
     *     id:int,
     *     due_date:string,
     *     amount:numeric
     * }> $items
     */
    public function execute(PaymentInstallment $baseInstallment, array $items, ?User $user = null): void
    {
        DB::transaction(function () use ($baseInstallment, $items, $user) {
            $flow = $baseInstallment->flow()->with('installments.payments')->firstOrFail();

            $indexed = collect($items)->keyBy('id');

            $installments = $flow->installments()
                ->where('number', '>=', $baseInstallment->number)
                ->orderBy('number')
                ->get();

            foreach ($installments as $installment) {
                if (! $indexed->has($installment->id)) {
                    continue;
                }

                $payload = $indexed->get($installment->id);

                $this->validateInstallmentCanBeEdited($installment);
                $this->validatePayload($payload);

                $newAmount = round((float) $payload['amount'], 2);

                $installment->update([
                    'due_date' => $payload['due_date'],
                    'amount' => $newAmount,
                    'balance_due' => $newAmount,
                ]);

                $this->syncInstallmentStatusAction->execute($installment, $user);
            }

            $flow->refresh();

            $flow->update([
                'total_amount' => $flow->installments()->sum('amount'),
                'installments_count' => $flow->installments()->count(),
            ]);
        });
    }

    protected function validateInstallmentCanBeEdited(PaymentInstallment $installment): void
    {
        if ($installment->payments()->exists()) {
            throw ValidationException::withMessages([
                'installments' => "La cuota #{$installment->number} no se puede editar porque ya tiene pagos registrados.",
            ]);
        }

        if ((float) $installment->paid_amount > 0) {
            throw ValidationException::withMessages([
                'installments' => "La cuota #{$installment->number} no se puede editar porque ya tiene monto pagado.",
            ]);
        }

        if ((float) $installment->balance_due <= 0) {
            throw ValidationException::withMessages([
                'installments' => "La cuota #{$installment->number} no se puede editar porque ya está cerrada.",
            ]);
        }
    }

    protected function validatePayload(array $payload): void
    {
        if (empty($payload['due_date'])) {
            throw ValidationException::withMessages([
                'due_date' => 'La fecha de vencimiento es obligatoria.',
            ]);
        }

        if (! isset($payload['amount']) || (float) $payload['amount'] <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'El monto debe ser mayor a cero.',
            ]);
        }
    }
}