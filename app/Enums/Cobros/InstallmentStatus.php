<?php

namespace App\Enums\Cobros;

enum InstallmentStatus: string
{
    case Pending = 'pending';
    case DueToday = 'due_today';
    case Grace = 'grace';
    case AccruingInterest = 'accruing_interest';
    case PartiallyPaid = 'partially_paid';
    case Paid = 'paid';
    case Overpaid = 'overpaid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendiente',
            self::DueToday => 'Vence hoy',
            self::Grace => 'En gracia',
            self::AccruingInterest => 'En mora',
            self::PartiallyPaid => 'Pago parcial',
            self::Paid => 'Pagada',
            self::Overpaid => 'Pagada con excedente',
            self::Cancelled => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'gray',
            self::DueToday => 'blue',
            self::Grace => 'yellow',
            self::AccruingInterest => 'red',
            self::PartiallyPaid => 'orange',
            self::Paid => 'green',
            self::Overpaid => 'green',
            self::Cancelled => 'gray',
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