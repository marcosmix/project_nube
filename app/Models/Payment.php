<?php

namespace App\Models;

use App\Enums\Cobros\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'remaining_after_payment' => 'decimal:2',
        'voided_at' => 'datetime',
        'status' => PaymentStatus::class,
    ];

    public function installment(): BelongsTo
    {
        return $this->belongsTo(PaymentInstallment::class, 'payment_installment_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class);
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }
}
