<?php

namespace App\Enums;

enum ExecutionSubStatus: string
{
    case OnTrack  = 'on_track';
    case WithDebt = 'with_debt';
    case Delayed  = 'delayed';

    public function label(): string
    {
        return match($this) {
            self::OnTrack  => 'Al Día',
            self::WithDebt => 'Con Deuda',
            self::Delayed  => 'Con Demora',
        };
    }
}
