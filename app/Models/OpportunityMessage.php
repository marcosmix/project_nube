<?php

namespace App\Models;

use App\Enums\Sales\OpportunityMessageDirection;
use App\Enums\Sales\OpportunityMessageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OpportunityMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'opportunity_id',
        'direction',
        'type',
        'content',
        'external_message_id',
        'raw_payload',
        'messaged_at',
        'status',
    ];

    protected $casts = [
        'direction' => OpportunityMessageDirection::class,
        'type' => OpportunityMessageType::class,
        'raw_payload' => 'array',
        'messaged_at' => 'datetime',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }
}
