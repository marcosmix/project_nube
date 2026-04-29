<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappSetting extends Model
{
    protected $fillable = [
        'enabled',
        'api_key',
        'phone_number_id',
        'webhook_verify_token',
        'business_account_id',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'api_key' => 'encrypted',
        'phone_number_id' => 'encrypted',
        'webhook_verify_token' => 'encrypted',
        'business_account_id' => 'encrypted',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'enabled' => (bool) config('services.whatsapp.enabled', false),
                'api_key' => config('services.whatsapp.api_key'),
                'phone_number_id' => config('services.whatsapp.phone_number_id'),
                'webhook_verify_token' => config('services.whatsapp.webhook_verify_token'),
                'business_account_id' => config('services.whatsapp.business_account_id'),
            ],
        );
    }

    public function isConfigured(): bool
    {
        return filled($this->api_key) && filled($this->phone_number_id) && filled($this->webhook_verify_token);
    }
}
