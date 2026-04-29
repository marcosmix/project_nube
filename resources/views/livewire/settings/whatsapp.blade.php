<x-ui.page-container>
    <div class="space-y-6">
        <x-ui.section-header
            title="Configuración de WhatsApp"
            description="Gestiona credenciales, verificación del webhook y estado operativo de la integración comercial."
            eyebrow="Configuración"
        />

        <x-ui.card class="space-y-6">
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.stat-card label="Estado" :value="$settings->enabled ? 'Activa' : 'Desactivada'" :hint="$settings->isConfigured() ? 'Credenciales completas.' : 'Faltan datos para completar la integración.'" :tone="$settings->enabled ? 'success' : 'warning'" />
                <x-ui.stat-card label="Webhook" :value="$settings->webhook_verify_token ? 'Configurado' : 'Pendiente'" hint="Usa este token para validar la suscripción del webhook." tone="info" />
            </div>

            <form wire:submit="save" class="space-y-6">
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <label class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-sm font-medium text-slate-950">Integración habilitada</p>
                                <p class="mt-1 text-sm text-slate-500">Permite recibir y procesar mensajes entrantes desde WhatsApp.</p>
                            </div>
                            <input type="checkbox" wire:model="enabled" class="h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        </label>
                    </div>

                    <x-ui.field-group label="API key de WhatsApp">
                        <x-ui.input type="password" wire:model.defer="api_key" autocomplete="off" />
                        @error('api_key') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    </x-ui.field-group>

                    <x-ui.field-group label="Phone Number ID">
                        <x-ui.input wire:model.defer="phone_number_id" autocomplete="off" />
                        @error('phone_number_id') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    </x-ui.field-group>

                    <x-ui.field-group label="Webhook verify token">
                        <x-ui.input wire:model.defer="webhook_verify_token" autocomplete="off" />
                        @error('webhook_verify_token') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    </x-ui.field-group>

                    <x-ui.field-group label="Business account ID">
                        <x-ui.input wire:model.defer="business_account_id" autocomplete="off" />
                        @error('business_account_id') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    </x-ui.field-group>
                </div>

                <x-ui.panel tone="subtle">
                    <p class="text-sm font-medium text-slate-900">Endpoint sugerido</p>
                    <p class="mt-2 break-all rounded-xl bg-white px-4 py-3 text-sm text-slate-600 ring-1 ring-slate-200/80">
                        {{ url('/webhooks/whatsapp') }}
                    </p>
                </x-ui.panel>

                <div class="flex justify-end gap-3">
                    <x-ui.button type="submit">Guardar configuración</x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-ui.page-container>
