<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case SaleClosed  = 'sale_closed'; // <- Venta Cerrada (antes closed)
    case Execution   = 'execution';
    case Paused      = 'paused';
    case Finished    = 'finished';
    case Cancelled   = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::SaleClosed  => 'Listo para ejecutar',
            self::Execution   => 'En Ejecución',
            self::Paused      => 'Frenado',
            self::Finished    => 'Finalizado',
            self::Cancelled   => 'Cancelado',
        };
    }
}


//testeo si todo esta bien
