
<form wire:submit="save" class="space-y-5">
    <div>
        <h3 class="text-sm font-semibold text-slate-800">Registrar pago</h3>
        <p class="text-xs text-slate-500">
            El pago se registra sobre esta cuota y no puede superar el saldo pendiente.
        </p>
    </div>

    <div class="grid gap-4">
        <div>
            <x-ui.label>Fecha de pago</x-ui.label>
            <x-ui.input type="date" wire:model="paid_at" />
            @error('paid_at')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.label>Monto</x-ui.label>
            <x-ui.input type="number" step="0.01" min="0" wire:model.blur="amount" />
            <p class="mt-1 text-xs text-slate-500">
                Saldo actual: ${{ number_format((float) $installment->balance_due, 2, ',', '.') }}
            </p>
            @error('amount')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.label>Tipo de pago</x-ui.label>
            <x-ui.select wire:model="payment_method">
                <option value="">Seleccionar opcionalmente</option>
                @foreach ($this->paymentMethodOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-ui.select>
            @error('payment_method')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.label>Observación</x-ui.label>
            <x-ui.textarea wire:model="notes" rows="3" placeholder="Detalle opcional del pago..."></x-ui.textarea>
            @error('notes')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <x-ui.label>Comprobante</x-ui.label>
            <x-ui.input type="file" wire:model="receipt" class="file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200" />
            <p class="mt-1 text-xs text-slate-500">Opcional. Permitidos: JPG, PNG, WEBP y PDF. Máximo 10 MB.</p>

            <div wire:loading wire:target="receipt" class="mt-2 text-xs text-slate-500">
                Cargando comprobante...
            </div>

            @error('receipt')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @if ($receipt)
            <div class="rounded-xl bg-slate-50 p-3">
                <p class="mb-2 text-xs font-medium text-slate-700">Comprobante seleccionado</p>
                <div class="text-xs text-slate-600">{{ $receipt->getClientOriginalName() }}</div>
            </div>
        @endif

        <div class="flex items-center justify-end">
            <x-ui.button type="submit" variant="info" wire:loading.attr="disabled" class="disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Registrar pago</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </x-ui.button>
        </div>
    </div>
</form>
