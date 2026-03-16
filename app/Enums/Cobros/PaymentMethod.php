<?php

namespace App\Enums\Cobros;

enum PaymentMethod: string
{
    case Echeq = 'echeq';
    case Cash = 'cash';
    case BankTransfer = 'bank_transfer';
    case Qr = 'qr';
    case MercadoPago = 'mercado_pago';

    public function label(): string
    {
        return match ($this) {
            self::Echeq => 'E-Cheq',
            self::Cash => 'Efectivo',
            self::BankTransfer => 'Transferencia',
            self::Qr => 'QR',
            self::MercadoPago => 'Mercado Pago',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $method) => [
                'value' => $method->value,
                'label' => $method->label(),
            ],
            self::cases()
        );
    }

    public static function values(): array
    {
        return array_map(
            fn (self $method) => $method->value,
            self::cases()
        );
    }
}