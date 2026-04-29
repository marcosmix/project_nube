<?php

namespace App\Enums\Sales;

enum OpportunityMessageType: string
{
    case Text = 'text';
    case Image = 'image';
    case Audio = 'audio';
    case Document = 'document';
    case Unknown = 'unknown';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Texto',
            self::Image => 'Imagen',
            self::Audio => 'Audio',
            self::Document => 'Documento',
            self::Unknown => 'Desconocido',
        };
    }
}
