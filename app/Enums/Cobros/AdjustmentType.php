<?php
namespace App\Enums\Cobros;

enum AdjustmentType: string
{
    case Discount = 'discount';
    case Surcharge = 'surcharge';

    public function label(): string
    {
        return match ($this) {
            self::Discount => 'Descuento',
            self::Surcharge => 'Recargo',
        };
    }

    public function sign(): int
    {
        return match ($this) {
            self::Discount => -1,
            self::Surcharge => 1,
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