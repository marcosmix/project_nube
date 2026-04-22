<?php

namespace App\Models;

use App\Enums\Cobros\PaymentFlowStatus;
use App\Enums\Cobros\PaymentFrequency;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentFlow extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status' => PaymentFlowStatus::class,
        'frequency' => PaymentFrequency::class,
        'total_amount' => 'decimal:2',
        'start_date' => 'date',
        'activated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function installments(): HasMany
    {
        return $this->hasMany(PaymentInstallment::class)->orderBy('number');
    }

    protected function totalPaid(): Attribute
    {
        return Attribute::get(fn () => (float) $this->installments->sum('paid_amount'));
    }

    protected function totalBalance(): Attribute
    {
        return Attribute::get(fn () => (float) $this->installments->sum('balance_due'));
    }

    protected function paidInstallmentsCount(): Attribute
    {
        return Attribute::get(
            fn () => $this->installments->filter(
                fn (PaymentInstallment $installment) => $installment->status?->value === 'paid'
            )->count()
        );
    }
}
