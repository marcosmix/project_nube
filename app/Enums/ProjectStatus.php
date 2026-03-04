<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Prospection = 'prospection';
    case Interested  = 'interested';
    case SaleClosed  = 'sale_closed'; // <- Venta Cerrada (antes closed)
    case Execution   = 'execution';
    case Paused      = 'paused';
    case Finished    = 'finished';

    public function label(): string
    {
        return match($this) {
            self::Prospection => 'En Prospección',
            self::Interested  => 'Interesado',
            self::SaleClosed  => 'Venta Cerrada',
            self::Execution   => 'En Ejecución',
            self::Paused      => 'Frenado',
            self::Finished    => 'Finalizado',
        };
    }
}