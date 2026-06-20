<div class="flex h-full min-h-0 flex-col gap-6">
    <div class="shrink-0">
        <h2 class="text-xl font-semibold text-slate-950">Paso 3. Automatizaciones</h2>
        <p class="mt-2 text-sm text-slate-600">
            Definí si el flujo debe quedar listo para automatizar el envío de comunicaciones de cobro.
        </p>
    </div>

    <div class="min-h-0 flex-1 overflow-y-auto">
        <div class="max-w-3xl space-y-5 pr-1">
        <label class="flex cursor-pointer items-start gap-4 rounded-2xl border p-5 shadow-sm transition {{ $auto_send_enabled ? 'border-blue-300 bg-blue-50/80' : 'border-slate-300 bg-white hover:border-blue-300 hover:bg-blue-50/40' }}">
            <input
                type="checkbox"
                wire:model.live="auto_send_enabled"
                class="mt-1 h-5 w-5 rounded border-slate-400 bg-white text-blue-600 focus:ring-4 focus:ring-blue-100"
            >
            <div>
                <div class="text-base font-semibold text-slate-900">Habilitar envío automático</div>
                <p class="mt-1 text-sm text-slate-600">
                    Si el cliente tiene email, el flujo guardará esa dirección para futuros envíos automáticos.
                </p>
            </div>
        </label>

        <div class="rounded-2xl border {{ $auto_send_enabled ? 'border-blue-200 bg-blue-50/70' : 'border-slate-300 bg-slate-100' }} p-5 shadow-sm">
            <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-600">Email detectado</div>
            <div class="mt-3 text-lg font-semibold text-slate-900">
                {{ $this->autoSendEmail ?: 'No hay correo registrado' }}
            </div>
            <p class="mt-2 text-sm text-slate-600">
                El email se toma desde el contacto principal del cliente asociado a la operación.
            </p>
        </div>

        @if ($auto_send_enabled && ! $this->autoSendEmail)
            <div class="rounded-2xl border border-amber-300 bg-amber-50 px-5 py-4 text-sm text-amber-900 shadow-sm">
                Este cliente no tiene correo registrado. El flujo puede guardarse, pero no se enviarán cobros automáticos hasta que se agregue un email.
            </div>
        @endif

        @if ($auto_send_enabled && $this->autoSendEmail)
            <div class="rounded-2xl border border-emerald-300 bg-emerald-50 px-5 py-4 text-sm text-emerald-900 shadow-sm">
                El flujo quedará listo para usar el email {{ $this->autoSendEmail }} cuando se implemente el proceso de envío automático.
            </div>
        @endif
        </div>
    </div>
</div>
