<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contact_id',
        'organization_name',
        'industry',
        'address',
        'company_logo',
        'company_size',
        'score',
        'notes',
    ];

    public const COMPANY_SIZES = [
        'small' => 'Pequeña',
        'medium' => 'Mediana',
        'large' => 'Grande',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class)->withTrashed();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function scheduledCharges(): HasMany
    {
        return $this->hasMany(ScheduledCharge::class);
    }

    public function getCompanySizeLabelAttribute(): string
    {
        return self::COMPANY_SIZES[$this->company_size] ?? $this->company_size;
    }
}
