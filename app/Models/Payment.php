<?php

namespace App\Models;

use App\Enums\Cobros\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public const METHOD_ECHEQ = 'echeq';

    public const METHOD_CASH = 'efectivo';

    public const METHOD_TRANSFER = 'transferencia';

    public const METHOD_MERCADO_PAGO = 'mercado_pago';

    public const METHODS = [
        self::METHOD_ECHEQ,
        self::METHOD_CASH,
        self::METHOD_TRANSFER,
        self::METHOD_MERCADO_PAGO,
    ];

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

    public function paymentMethodLabel(): ?string
    {
        return match ($this->payment_method) {
            self::METHOD_ECHEQ => 'Echeq',
            self::METHOD_CASH => 'Efectivo',
            self::METHOD_TRANSFER => 'Transferencia',
            self::METHOD_MERCADO_PAGO => 'Mercado Pago',
            default => null,
        };
    }
}
