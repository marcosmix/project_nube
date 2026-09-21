<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectAmountHistory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'old_amount' => 'decimal:2',
        'new_amount' => 'decimal:2',
    ];

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function changedBy(): BelongsTo { return $this->belongsTo(User::class, 'changed_by'); }
}
