<?php

namespace App\Models;

use App\Enums\Cobros\AdjustmentType;
use App\Enums\Cobros\AdjustmentValueType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_installment_id',
        'type',
        'value_type',
        'value',
        'applied_amount',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'type' => AdjustmentType::class,
        'value_type' => AdjustmentValueType::class,
        'value' => 'decimal:4',
        'applied_amount' => 'decimal:2',
    ];

    public function installment(): BelongsTo
    {
        return $this->belongsTo(PaymentInstallment::class, 'payment_installment_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}