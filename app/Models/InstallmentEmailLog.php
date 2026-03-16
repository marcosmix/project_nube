<?php

namespace App\Models;

use App\Enums\Cobros\EmailLogStatus;
use App\Enums\Cobros\EmailTriggerType;
use App\Enums\Cobros\EmailType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentEmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_flow_id',
        'payment_installment_id',
        'email_type',
        'trigger_type',
        'recipient_email',
        'subject',
        'body',
        'status',
        'error_message',
        'sent_at',
        'created_by',
    ];

    protected $casts = [
        'email_type' => EmailType::class,
        'trigger_type' => EmailTriggerType::class,
        'status' => EmailLogStatus::class,
        'sent_at' => 'datetime',
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
}