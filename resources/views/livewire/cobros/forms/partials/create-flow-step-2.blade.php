<div class="space-y-8">
    <div>
        <h2 class="text-xl font-semibold text-slate-950">Paso 2. Configuración financiera del flujo</h2>
        <p class="mt-2 text-sm text-slate-500">
            El monto total se toma del proyecto en Venta Cerrada y las cuotas se recalculan en vivo.
        </p>
    </div>

    @if (! $selectedProject)
        <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10 text-sm text-slate-500">
            Primero seleccioná un cliente y un proyecto en el paso 1.
        </div>
    @else
        <div class="grid gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="text-sm font-medium text-slate-900">{{ $selectedProject->name }}</div>
                    <div class="mt-1 text-sm text-slate-500">{{ $selectedProject->status->label() }}</div>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Monto total del flujo</label>
                        <input
                            type="text"
                            value="${{ number_format((float) $total_amount, 2, ',', '.') }}"
                            readonly
                            class="w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-700 shadow-sm"
                        >
                        @error('total_amount')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Fecha de inicio</label>
                        <input
                            type="date"
                            wire:model.live="start_date"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                        >
                        @error('start_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Frecuencia</label>
                        <select
                            wire:model.live="frequency"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                        >
                            @foreach ($frequencies as $frequency)
                                <option value="{{ $frequency->value }}">{{ $frequency->label() }}</option>
                            @endforeach
                        </select>
                        @error('frequency')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Días de gracia</label>
                        <input
                            type="number"
                            min="0"
                            wire:model.live="grace_days"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                        >
                        @error('grace_days')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Cantidad de cuotas</label>
                        <input
                            type="number"
                            min="1"
                            max="240"
                            wire:model.live="installments_count"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                        >
                        @error('installments_count')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Observaciones</label>
                        <textarea
                            wire:model.live="notes"
                            rows="4"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                            placeholder="Notas internas del flujo..."
                        ></textarea>
                        @error('notes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="rounded-2xl border px-5 py-4 {{ $this->installmentSummaryTone }}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-[0.18em]">Control de cuotas</div>
                            <div class="mt-2 text-lg font-semibold">
                                Total proyecto: ${{ number_format((float) $total_amount, 2, ',', '.') }}
                            </div>
                            <div class="mt-1 text-sm">
                                Suma cuotas: ${{ number_format($this->installmentsSum, 2, ',', '.') }}
                            </div>
                        </div>

                        <div class="text-sm font-medium">
                            @if (abs($this->installmentsDifference) <= 0.009)
                                La suma coincide
                            @elseif ($this->installmentsDifference > 0)
                                Excede por ${{ number_format($this->installmentsDifference, 2, ',', '.') }}
                            @else
                                Faltan ${{ number_format(abs($this->installmentsDifference), 2, ',', '.') }}
                            @endif
                        </div>
                    </div>

                    @if ($this->installmentsDifference > 0)
                        <p class="mt-3 text-sm">
                            La suma de las cuotas supera el monto del proyecto. Ajustá los montos antes de guardar.
                        </p>
                    @endif
                </div>

                @error('installmentRows')
                    <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ $message }}
                    </div>
                @enderror

                <div class="space-y-3">
                    @foreach ($installmentRows as $index => $row)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="grid gap-4 md:grid-cols-[120px_minmax(0,1fr)_190px] md:items-end">
                                <div>
                                    <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400">Cuota</div>
                                    <div class="mt-2 text-lg font-semibold text-slate-900">#{{ $row['number'] }}</div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">Monto</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        wire:model.live="installmentRows.{{ $index }}.amount"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                                    >
                                    @error("installmentRows.$index.amount")
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">Vencimiento estimado</label>
                                    <input
                                        type="date"
                                        wire:model.live="installmentRows.{{ $index }}.due_date"
                                        class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                                    >
                                    @error("installmentRows.$index.due_date")
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
