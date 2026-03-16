<?php

namespace App\Models;

use App\Enums\Cobros\InterestEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentInterestEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'payment_installment_id',
        'event_type',
        'previous_amount',
        'new_amount',
        'delta_amount',
        'source_installment_id',
        'target_installment_id',
        'reason',
        'meta',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'event_type' => InterestEventType::class,
        'previous_amount' => 'decimal:2',
        'new_amount' => 'decimal:2',
        'delta_amount' => 'decimal:2',
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function installment(): BelongsTo
    {
        return $this->belongsTo(PaymentInstallment::class, 'payment_installment_id');
    }

    public function sourceInstallment(): BelongsTo
    {
        return $this->belongsTo(PaymentInstallment::class, 'source_installment_id');
    }

    public function targetInstallment(): BelongsTo
    {
        return $this->belongsTo(PaymentInstallment::class, 'target_installment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}