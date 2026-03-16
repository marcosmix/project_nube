<?php

namespace App\Enums\Cobros;

enum PaymentFlowStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Overdue = 'overdue';
    case Completed = 'completed';
    case Paused = 'paused';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Active => 'Activo',
            self::Overdue => 'En mora',
            self::Completed => 'Completado',
            self::Paused => 'Pausado',
            self::Cancelled => 'Cancelado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Active => 'blue',
            self::Overdue => 'red',
            self::Completed => 'green',
            self::Paused => 'yellow',
            self::Cancelled => 'gray',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn(self $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            self::cases()
        );
    }
}
