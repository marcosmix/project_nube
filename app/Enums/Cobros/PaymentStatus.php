<?php

namespace App\Enums\Cobros;

enum PaymentStatus: string
{
    case Posted = 'posted';
    case Voided = 'voided';

    public function label(): string
    {
        return match ($this) {
            self::Posted => 'Registrado',
            self::Voided => 'Anulado',
        };
    }
}