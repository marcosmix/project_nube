<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'birthdate',
        'job_title',
    ];

    protected $casts = [
        'birthdate' => 'date',
    ];

    public function client(): HasOne
    {
        return $this->hasOne(Client::class)->withTrashed();
    }

    public function developer(): HasOne
    {
        return $this->hasOne(Developer::class)->withTrashed();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
