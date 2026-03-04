<?php

return [
    'status' => [
        'prospection' => ['label'=>'En Prospección','bar'=>'bg-amber-400','badge'=>'bg-amber-100 text-amber-900','border'=>'border-amber-300'],
        'interested'  => ['label'=>'Interesado','bar'=>'bg-orange-500','badge'=>'bg-orange-100 text-orange-900','border'=>'border-orange-300'],
        'sale_closed' => ['label'=>'Venta Cerrada','bar'=>'bg-emerald-500','badge'=>'bg-emerald-100 text-emerald-900','border'=>'border-emerald-300'],
        'execution'   => ['label'=>'En Ejecución','bar'=>'bg-blue-600','badge'=>'bg-blue-100 text-blue-900','border'=>'border-blue-300'],
        'paused'      => ['label'=>'Frenado','bar'=>'bg-purple-500','badge'=>'bg-purple-100 text-purple-900','border'=>'border-purple-300'],
        'finished'    => ['label'=>'Finalizado','bar'=>'bg-cyan-600','badge'=>'bg-cyan-100 text-cyan-900','border'=>'border-cyan-300'],
    ],
    'execution_sub' => [
        'on_track'  => ['label'=>'Al Día','badge'=>'bg-blue-100 text-blue-800'],
        'with_debt' => ['label'=>'Con Deuda','badge'=>'bg-red-100 text-red-800'],
        'delayed'   => ['label'=>'Con Demora','badge'=>'bg-orange-100 text-orange-800'],
    ],
];
