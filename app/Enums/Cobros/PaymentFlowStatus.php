<?php

namespace App\Enums\Cobros;

enum PaymentFlowStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Active => 'Activo',
            self::Completed => 'Completado',
            self::Cancelled => 'Cancelado',
        };
    }
}