<?php

namespace App\Enums\Cobros;

enum EmailType: string
{
    case ReminderBeforeDue = 'reminder_before_due';
    case ReminderDueToday = 'reminder_due_today';
    case ReminderGraceEnding = 'reminder_grace_ending';
    case ReminderInterestStarted = 'reminder_interest_started';
    case DebtSummary = 'debt_summary';
    case ManualMessage = 'manual_message';

    public function label(): string
    {
        return match ($this) {
            self::ReminderBeforeDue => 'Recordatorio previo al vencimiento',
            self::ReminderDueToday => 'Recordatorio de vencimiento',
            self::ReminderGraceEnding => 'Aviso fin de gracia',
            self::ReminderInterestStarted => 'Aviso inicio de interés',
            self::DebtSummary => 'Resumen de deuda',
            self::ManualMessage => 'Mensaje manual',
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