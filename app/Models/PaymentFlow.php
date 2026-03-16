<?php

namespace App\Models;

use App\Enums\Cobros\GenerationMode;
use App\Enums\Cobros\PaymentFlowStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PaymentFlow extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'client_id',
        'code',
        'name',
        'description',
        'status',
        'generation_mode',
        'currency',
        'total_amount',
        'installments_count',
        'payment_frequency',
        'billing_day',
        'due_day',
        'grace_days',
        'interest_daily_rate',
        'starts_at',
        'first_due_date',
        'email_automation_enabled',
        'notes',
        'created_by',
        'updated_by',
        'activated_at',
        'completed_at',
        'paused_at',
        'cancelled_at',
    ];

    protected $casts = [
        'status' => PaymentFlowStatus::class,
        'generation_mode' => GenerationMode::class,
        'total_amount' => 'decimal:2',
        'interest_daily_rate' => 'decimal:4',
        'email_automation_enabled' => 'boolean',
        'starts_at' => 'date',
        'first_due_date' => 'date',
        'activated_at' => 'datetime',
        'completed_at' => 'datetime',
        'paused_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function automation(): HasOne
    {
        return $this->hasOne(PaymentFlowAutomation::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(PaymentInstallment::class)->orderBy('number');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function emailLogs(): HasMany
    {
        return $this->hasMany(InstallmentEmailLog::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentFlowEvent::class)->latest('created_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}