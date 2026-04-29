<?php

namespace App\Livewire\Settings;

use App\Models\WhatsappSetting;
use Livewire\Component;

class Whatsapp extends Component
{
    public bool $enabled = false;

    public string $api_key = '';

    public string $phone_number_id = '';

    public string $webhook_verify_token = '';

    public string $business_account_id = '';

    public function mount(): void
    {
        $settings = WhatsappSetting::current();

        $this->enabled = $settings->enabled;
        $this->api_key = (string) ($settings->api_key ?? '');
        $this->phone_number_id = (string) ($settings->phone_number_id ?? '');
        $this->webhook_verify_token = (string) ($settings->webhook_verify_token ?? '');
        $this->business_account_id = (string) ($settings->business_account_id ?? '');
    }

    public function save(): void
    {
        $data = $this->validate([
            'enabled' => ['required', 'boolean'],
            'api_key' => ['nullable', 'string', 'max:255'],
            'phone_number_id' => ['nullable', 'string', 'max:255'],
            'webhook_verify_token' => ['nullable', 'string', 'max:255'],
            'business_account_id' => ['nullable', 'string', 'max:255'],
        ]);

        $settings = WhatsappSetting::current();
        $settings->fill($data);
        $settings->save();

        $this->dispatch('toast', type: 'success', message: 'Configuración de WhatsApp guardada');
    }

    public function render()
    {
        return view('livewire.settings.whatsapp', [
            'settings' => WhatsappSetting::current(),
        ])->layout('layouts.app');
    }
}
