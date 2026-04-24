<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaymentReceipt extends Model
{
    protected $guarded = [];

    public function isImage(): bool
    {
        if ($this->mime_type && str_starts_with($this->mime_type, 'image/')) {
            return true;
        }

        return in_array($this->extension(), ['jpg', 'jpeg', 'png', 'webp'], true);
    }

    public function isPdf(): bool
    {
        if ($this->mime_type === 'application/pdf') {
            return true;
        }

        return $this->extension() === 'pdf';
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    protected function extension(): string
    {
        return Str::of($this->original_name ?: $this->path)
            ->afterLast('.')
            ->lower()
            ->toString();
    }
}
