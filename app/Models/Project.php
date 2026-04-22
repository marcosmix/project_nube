<?php

namespace App\Models;

use App\Enums\ExecutionSubStatus;
use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'status',
        'execution_sub_status',
        'client_id',
        'prospection_notes',
        'proposal_url',
        'excel_url',
        'total_cost',
        'installments',
        'estimated_start_date',
        'estimated_end_date',
        'sprint_close_day',
        'actual_start_date',
        'actual_end_date',
        'pause_reason',
        'paused_at',
    ];

    protected $casts = [
        'status' => ProjectStatus::class,
        'execution_sub_status' => ExecutionSubStatus::class,
        'estimated_start_date' => 'date',
        'estimated_end_date' => 'date',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'paused_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function developers()
    {
        return $this->belongsToMany(Developer::class)->withTimestamps();
    }

    public function statusLogs()
    {
        return $this->hasMany(ProjectStatusLog::class)->latest('created_at');
    }

    public function notes()
    {
        return $this->hasMany(ProjectNote::class)->latest();
    }

    // Scopes básicos
    public function scopeSearch($q, ?string $term)
    {
        if (!$term)
            return $q;

        return $q
            ->where('name', 'like', "%{$term}%")
            ->orWhereHas('client', function ($c) use ($term) {
                $c
                    ->where('organization_name', 'like', "%{$term}%")
                    ->orWhereHas('contact', fn($ct) => $ct
                        ->where('first_name', 'like', "%{$term}%")
                        ->orWhere('last_name', 'like', "%{$term}%"));
            });
    }

    public function scopeStatusFilter($q, ?string $status)
    {
        return ($status && $status !== 'all') ? $q->where('status', $status) : $q;
    }

    public function paymentFlows(): HasMany
    {
        return $this->hasMany(\App\Models\PaymentFlow::class);
    }

    public function paymentFlow(): HasOne
    {
        return $this->hasOne(\App\Models\PaymentFlow::class)->latestOfMany();
    }
}
