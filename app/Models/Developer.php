<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Developer extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUSES = [
        'active' => 'Activo',
        'inactive' => 'Inactivo',
    ];

    public const AVAILABILITIES = [
        'full_time' => 'Tiempo completo',
        'freelance' => 'Freelance',
    ];

    public const LEVELS = [
        'junior' => 'Junior',
        'semi_senior' => 'Semi Senior',
        'senior' => 'Senior',
        'lead' => 'Lead',
    ];

    protected $fillable = [
        'contact_id',
        'skins',
        'skills',
        'github_username',
        'github_url',
        'linkedin_url',
        'alias',
        'cbu',
        'profile_photo',
        'phrase',
        'score',
        'level',
        'status',
        'availability',
        'notes',
    ];

    protected $casts = [
        'skins' => 'array',
        'skills' => 'array',
        'score' => 'integer',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class)->withTrashed();
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps()->withTrashed();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return self::AVAILABILITIES[$this->availability] ?? $this->availability;
    }

    public function getLevelLabelAttribute(): string
    {
        return self::LEVELS[$this->level] ?? $this->level;
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->profile_photo ? asset('storage/'.$this->profile_photo) : null;
    }
}
