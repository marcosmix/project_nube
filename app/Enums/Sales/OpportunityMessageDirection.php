<?php

namespace App\Enums\Sales;

enum OpportunityMessageDirection: string
{
    case Inbound = 'inbound';
    case Outbound = 'outbound';

    public function label(): string
    {
        return match ($this) {
            self::Inbound => 'Entrante',
            self::Outbound => 'Saliente',
        };
    }
}
