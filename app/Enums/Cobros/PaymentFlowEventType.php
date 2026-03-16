<?php

namespace App\Enums\Cobros;

enum PaymentFlowEventType: string
{
    case Created = 'created';
    case Activated = 'activated';
    case Paused = 'paused';
    case Cancelled = 'cancelled';
    case Completed = 'completed';

    case InstallmentGenerated = 'installment_generated';

    case PaymentRegistered = 'payment_registered';
    case PaymentReversed = 'payment_reversed';

    case InterestGenerated = 'interest_generated';
    case AdjustmentApplied = 'adjustment_applied';

    case EmailSent = 'email_sent';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Flujo creado',
            self::Activated => 'Flujo activado',
            self::Paused => 'Flujo pausado',
            self::Cancelled => 'Flujo cancelado',
            self::Completed => 'Flujo finalizado',

            self::InstallmentGenerated => 'Cuotas generadas',

            self::PaymentRegistered => 'Pago registrado',
            self::PaymentReversed => 'Pago revertido',

            self::InterestGenerated => 'Interés generado',
            self::AdjustmentApplied => 'Ajuste aplicado',

            self::EmailSent => 'Email enviado',
        };
    }
}