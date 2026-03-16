<?php

namespace App\Enums\Cobros;

enum NotificationType: string
{
    case InstallmentDueToday = 'installment_due_today';
    case InstallmentOverdue = 'installment_overdue';
    case FlowOverdue = 'flow_overdue';
    case PaymentRegistered = 'payment_registered';
    case EmailFailed = 'email_failed';
    case SystemAlert = 'system_alert';

    public function label(): string
    {
        return match ($this) {
            self::InstallmentDueToday => 'Cuota vence hoy',
            self::InstallmentOverdue => 'Cuota en mora',
            self::FlowOverdue => 'Flujo en mora',
            self::PaymentRegistered => 'Pago registrado',
            self::EmailFailed => 'Fallo en envío de email',
            self::SystemAlert => 'Alerta del sistema',
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