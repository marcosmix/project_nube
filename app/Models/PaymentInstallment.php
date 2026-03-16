<?php

namespace App\Models;

use App\Enums\Cobros\InstallmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_flow_id',
        'number',
        'label',
        'status',
        'scheduled_amount',
        'capital_amount',
        'interest_deferred_in_amount',
        'interest_generated_amount',
        'interest_adjustments_amount',
        'discounts_amount',
        'surcharges_amount',
        'paid_amount',
        'carried_over_payment_amount',
        'outstanding_amount',
        'total_due_amount',
        'billing_date',
        'due_date',
        'grace_ends_at',
        'interest_starts_at',
        'paid_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'status' => InstallmentStatus::class,
        'scheduled_amount' => 'decimal:2',
        'capital_amount' => 'decimal:2',
        'interest_deferred_in_amount' => 'decimal:2',
        'interest_generated_amount' => 'decimal:2',
        'interest_adjustments_amount' => 'decimal:2',
        'discounts_amount' => 'decimal:2',
        'surcharges_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'carried_over_payment_amount' => 'decimal:2',
        'outstanding_amount' => 'decimal:2',
        'total_due_amount' => 'decimal:2',
        'billing_date' => 'date',
        'due_date' => 'date',
        'grace_ends_at' => 'date',
        'interest_starts_at' => 'date',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function paymentFlow(): BelongsTo
    {
        return $this->belongsTo(PaymentFlow::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('paid_at');
    }

    public function interestEvents(): HasMany
    {
        return $this->hasMany(InstallmentInterestEvent::class)->latest('created_at');
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(InstallmentAdjustment::class)->latest('created_at');
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(InstallmentEmailLog::class)->latest('created_at');
    }
}