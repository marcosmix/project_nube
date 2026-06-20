<?php

namespace App\Enums\Cobros;

enum ScheduledChargeFrequency: string
{
    case Weekly = 'weekly';
    case Biweekly = 'biweekly';
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Weekly => 'Semanal',
            self::Biweekly => 'Cada 15 dias',
            self::Monthly => 'Mensual',
            self::Yearly => 'Anual',
        };
    }
}
