<?php

namespace App\Models;

use App\Enums\Sales\OpportunitySource;
use App\Enums\Sales\OpportunityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'responsible_user_id',
        'name',
        'status',
        'source',
        'whatsapp_contact_id',
        'external_conversation_id',
        'first_contact_at',
        'first_customer_message_at',
        'last_customer_message_at',
        'customer_service_window_expires_at',
        'contact_name',
        'contact_phone',
        'contact_email',
        'contact_handle',
        'initial_message',
        'estimated_ticket_amount',
    ];

    protected $casts = [
        'status' => OpportunityStatus::class,
        'source' => OpportunitySource::class,
        'first_contact_at' => 'date',
        'first_customer_message_at' => 'datetime',
        'last_customer_message_at' => 'datetime',
        'customer_service_window_expires_at' => 'datetime',
        'estimated_ticket_amount' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(OpportunityNote::class)->orderBy('created_at');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OpportunityStatusLog::class)->latest('created_at');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OpportunityMessage::class)->orderBy('messaged_at');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($innerQuery) use ($term) {
            $innerQuery
                ->where('name', 'like', "%{$term}%")
                ->orWhere('contact_name', 'like', "%{$term}%")
                ->orWhere('contact_email', 'like', "%{$term}%")
                ->orWhere('contact_phone', 'like', "%{$term}%")
                ->orWhereHas('messages', fn ($messageQuery) => $messageQuery->where('content', 'like', "%{$term}%"))
                ->orWhereHas('client', function ($clientQuery) use ($term) {
                    $clientQuery
                        ->where('organization_name', 'like', "%{$term}%")
                        ->orWhereHas('contact', fn ($contactQuery) => $contactQuery
                            ->where('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%"));
                });
        });
    }

    public function getCanReplyFreelyAttribute(): bool
    {
        return $this->customer_service_window_expires_at?->isFuture() ?? false;
    }

    public function getRequiresTemplateAttribute(): bool
    {
        return ! $this->can_reply_freely && $this->last_customer_message_at !== null;
    }

    public function syncCustomerServiceWindow(?\Illuminate\Support\Carbon $receivedAt = null): void
    {
        $receivedAt ??= now();

        if (! $this->first_customer_message_at) {
            $this->first_customer_message_at = $receivedAt;
        }

        $this->last_customer_message_at = $receivedAt;
        $this->customer_service_window_expires_at = $receivedAt->copy()->addHours(24);
    }

    public function getDisplayContactAttribute(): string
    {
        if ($this->contact_name) {
            return $this->contact_name;
        }

        if ($this->client?->contact) {
            return trim($this->client->contact->first_name.' '.$this->client->contact->last_name);
        }

        return 'Sin contacto';
    }

    public function supportsCommercialProposalData(): bool
    {
        return in_array($this->status, [
            OpportunityStatus::Qualified,
            OpportunityStatus::ProposalSent,
            OpportunityStatus::Negotiation,
            OpportunityStatus::Won,
        ], true);
    }
}
