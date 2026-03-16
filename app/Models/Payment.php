<?php

namespace App\Models;

use App\Enums\Cobros\PaymentMethod;
use App\Enums\Cobros\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_flow_id',
        'payment_installment_id',
        'status',
        'payment_method',
        'amount',
        'capital_applied_amount',
        'interest_applied_amount',
        'surcharge_applied_amount',
        'discount_applied_amount',
        'carried_forward_amount',
        'paid_at',
        'receipt_path',
        'reference',
        'notes',
        'created_by',
        'reversed_by',
        'reversed_at',
        'reversal_reason',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
        'amount' => 'decimal:2',
        'capital_applied_amount' => 'decimal:2',
        'interest_applied_amount' => 'decimal:2',
        'surcharge_applied_amount' => 'decimal:2',
        'discount_applied_amount' => 'decimal:2',
        'carried_forward_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    public function paymentFlow(): BelongsTo
    {
        return $this->belongsTo(PaymentFlow::class);
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(PaymentInstallment::class, 'payment_installment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }
}