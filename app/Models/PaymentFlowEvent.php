<?php

namespace App\Models;
use App\Enums\Cobros\PaymentFlowEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentFlowEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'payment_flow_id',
        'event_type',
        'title',
        'description',
        'meta',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'event_type' => PaymentFlowEventType::class,
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public function paymentFlow(): BelongsTo
    {
        return $this->belongsTo(PaymentFlow::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}