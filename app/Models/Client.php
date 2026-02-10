<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'small'  => 'Pequeña',
        'medium' => 'Mediana',
        'large'  => 'Grande',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function getCompanySizeLabelAttribute(): string
    {
        return self::COMPANY_SIZES[$this->company_size] ?? $this->company_size;
    }
}
