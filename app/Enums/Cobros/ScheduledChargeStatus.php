<?php

namespace App\Enums\Cobros;

enum ScheduledChargeStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Activo',
            self::Paused => 'Frenado',
            self::Cancelled => 'Cancelado',
        };
    }
}
