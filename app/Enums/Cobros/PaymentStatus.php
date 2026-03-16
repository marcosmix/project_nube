<?php

namespace App\Enums\Cobros;

enum PaymentStatus: string
{
    case Recorded = 'recorded';
    case Reversed = 'reversed';

    public function label(): string
    {
        return match ($this) {
            self::Recorded => 'Registrado',
            self::Reversed => 'Revertido',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Recorded => 'green',
            self::Reversed => 'red',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            self::cases()
        );
    }
}