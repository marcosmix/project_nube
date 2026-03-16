<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tasa diaria de interés por mora
    |--------------------------------------------------------------------------
    |
    | Valor expresado en porcentaje.
    | Ejemplo: 0.2 = 0.2% diario
    |
    */
    'interest_daily_rate' => 0.2,

    /*
    |--------------------------------------------------------------------------
    | Días de gracia antes de comenzar a cobrar interés
    |--------------------------------------------------------------------------
    */
    'grace_days' => 15,

    /*
    |--------------------------------------------------------------------------
    | Moneda por defecto
    |--------------------------------------------------------------------------
    */
    'currency' => 'ARS',

    /*
    |--------------------------------------------------------------------------
    | Medios de pago disponibles
    |--------------------------------------------------------------------------
    |
    | La key se guarda en base de datos.
    | El value es el texto visible para el usuario.
    |
    */
    'payment_methods' => [
        'echeq' => 'E-Cheq',
        'cash' => 'Efectivo',
        'bank_transfer' => 'Transferencia',
        'qr' => 'QR',
        'mercado_pago' => 'Mercado Pago',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración base de automatización de emails
    |--------------------------------------------------------------------------
    */
    'email_automation' => [
        'enabled' => true,
        'before_due_days' => 3,
        'send_on_due_date' => true,
        'send_on_grace_end' => true,
        'send_on_interest_start' => true,
    ],

];