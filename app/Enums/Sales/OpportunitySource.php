<?php

namespace App\Enums\Sales;

enum OpportunitySource: string
{
    case Manual = 'manual';
    case Whatsapp = 'whatsapp';
    case Instagram = 'instagram';
    case Website = 'website';
    case Chatbot = 'chatbot';
    case Referral = 'referral';
    case Email = 'email';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::Whatsapp => 'WhatsApp',
            self::Instagram => 'Instagram',
            self::Website => 'Website',
            self::Chatbot => 'Chatbot',
            self::Referral => 'Referido',
            self::Email => 'Email',
            self::Other => 'Otro',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $source) => ['value' => $source->value, 'label' => $source->label()],
            self::cases(),
        );
    }
}
