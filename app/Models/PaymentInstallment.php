<?php

namespace App\Models;

use App\Enums\Cobros\InstallmentStatus;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentInstallment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'locked_at' => 'datetime',
        'status' => InstallmentStatus::class,
    ];

    public function flow(): BelongsTo
    {
        return $this->belongsTo(PaymentFlow::class, 'payment_flow_id')->withTrashed();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_at')->orderBy('id');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(PaymentInstallmentStatusLog::class)->latest('changed_at');
    }

    public function isOverdue(?CarbonInterface $reference = null): bool
    {
        $reference ??= now();

        return $this->balance_due > 0 && $this->due_date !== null && $this->due_date->lt($reference->startOfDay());
    }
}
