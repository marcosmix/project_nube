<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentFlowStatusLog extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['changed_at' => 'datetime'];
    public function flow(): BelongsTo { return $this->belongsTo(PaymentFlow::class, 'payment_flow_id'); }
    public function byUser(): BelongsTo { return $this->belongsTo(User::class, 'by_user_id'); }
}
