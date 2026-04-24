
<form wire:submit="save" class="space-y-5">
    <div>
        <h3 class="text-sm font-semibold text-slate-800">Registrar pago</h3>
        <p class="text-xs text-slate-500">
            El pago se registra sobre esta cuota y no puede superar el saldo pendiente.
        </p>
    </div>

    <div class="grid gap-4">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Fecha de pago</label>
            <input
                type="date"
                wire:model="paid_at"
                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
            >
            @error('paid_at')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Monto</label>
            <input
                type="number"
                step="0.01"
                min="0"
                wire:model.blur="amount"
                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
            >
            <p class="mt-1 text-xs text-slate-500">
                Saldo actual: ${{ number_format((float) $installment->balance_due, 2, ',', '.') }}
            </p>
            @error('amount')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Tipo de pago</label>
            <select
                wire:model="payment_method"
                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
            >
                <option value="">Seleccionar opcionalmente</option>
                @foreach ($this->paymentMethodOptions() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('payment_method')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Observación</label>
            <textarea
                wire:model="notes"
                rows="3"
                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                placeholder="Detalle opcional del pago..."
            ></textarea>
            @error('notes')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1 block text-xs font-medium text-slate-600">Comprobante</label>
            <input
                type="file"
                wire:model="receipt"
                class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700 hover:file:bg-slate-200"
            >
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
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="inline-flex items-center rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700 transition hover:border-blue-300 hover:bg-blue-100 disabled:cursor-not-allowed disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="save">Registrar pago</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </button>
        </div>
    </div>
</form>
