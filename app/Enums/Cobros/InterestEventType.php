<?php

namespace App\Enums\Cobros;

enum InterestEventType: string
{
    case Generated = 'generated';
    case DeferredOut = 'deferred_out';
    case DeferredIn = 'deferred_in';
    case ManualIncrease = 'manual_increase';
    case ManualReduction = 'manual_reduction';
    case Recalculated = 'recalculated';

    public function label(): string
    {
        return match ($this) {
            self::Generated => 'Interés generado',
            self::DeferredOut => 'Interés trasladado a siguiente cuota',
            self::DeferredIn => 'Interés recibido de cuota anterior',
            self::ManualIncrease => 'Aumento manual de interés',
            self::ManualReduction => 'Reducción manual de interés',
            self::Recalculated => 'Interés recalculado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Generated => 'red',
            self::DeferredOut => 'yellow',
            self::DeferredIn => 'blue',
            self::ManualIncrease => 'orange',
            self::ManualReduction => 'green',
            self::Recalculated => 'gray',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            self::cases()
        );
    }
}