<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentInstallmentStatusLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function installment(): BelongsTo
    {
        return $this->belongsTo(PaymentInstallment::class, 'payment_installment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
