<?php
namespace App\Enums\Cobros;

enum GenerationMode: string
{
    case Automatic = 'automatic';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Automatic => 'Automático',
            self::Manual => 'Manual',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $mode) => [
                'value' => $mode->value,
                'label' => $mode->label(),
            ],
            self::cases()
        );
    }
}