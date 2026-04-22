<div class="space-y-4 border-t border-slate-200 pt-4">
    <div>
        <h3 class="text-sm font-semibold text-slate-800">Editar cuotas futuras</h3>
        <p class="text-xs text-slate-500">
            Solo se pueden modificar cuotas futuras sin pagos ni movimientos registrados.
        </p>
    </div>

    @error('rows')
        <div class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-700">
            {{ $message }}
        </div>
    @enderror

    <div class="space-y-3">
        @foreach ($rows as $index => $row)
            <div class="rounded-2xl border {{ $row['locked'] ? 'border-slate-200 bg-slate-50' : 'border-slate-200 bg-white' }} p-4">
                <div class="mb-3 flex items-center justify-between">
                    <div class="text-sm font-medium text-slate-900">
                        Cuota #{{ $row['number'] }}
                    </div>

                    @if ($row['locked'])
                        <span class="inline-flex rounded-full bg-slate-200 px-2.5 py-1 text-xs font-medium text-slate-700">
                            Bloqueada
                        </span>
                    @else
                        <span class="inline-flex rounded-full bg-blue-100 px-2.5 py-1 text-xs font-medium text-blue-700">
                            Editable
                        </span>
                    @endif
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Vencimiento</label>
                        <input
                            type="date"
                            wire:model="rows.{{ $index }}.due_date"
                            @disabled($row['locked'])
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none disabled:cursor-not-allowed disabled:bg-slate-100"
                        >
                        @error("rows.$index.due_date")
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Monto</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            wire:model="rows.{{ $index }}.amount"
                            @disabled($row['locked'])
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none disabled:cursor-not-allowed disabled:bg-slate-100"
                        >
                        @error("rows.$index.amount")
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-end">
        <button
            type="button"
            wire:click="save"
            wire:loading.attr="disabled"
            class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
        >
            <span wire:loading.remove wire:target="save">Guardar cambios</span>
            <span wire:loading wire:target="save">Guardando...</span>
        </button>
    </div>
</div>
