<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentFlowAutomation extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_flow_id',
        'is_enabled',
        'send_before_due_enabled',
        'send_before_due_days',
        'send_on_due_enabled',
        'send_on_grace_end_enabled',
        'send_on_interest_start_enabled',
        'last_run_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'send_before_due_enabled' => 'boolean',
        'send_on_due_enabled' => 'boolean',
        'send_on_grace_end_enabled' => 'boolean',
        'send_on_interest_start_enabled' => 'boolean',
        'last_run_at' => 'datetime',
    ];

    public function paymentFlow(): BelongsTo
    {
        return $this->belongsTo(PaymentFlow::class);
    }
}