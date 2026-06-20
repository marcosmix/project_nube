<div class="flex h-full min-h-0 flex-col gap-6">
    <div class="shrink-0">
        <h2 class="text-xl font-semibold text-slate-950">Paso 2. Configuración financiera del flujo</h2>
        <p class="mt-2 text-sm text-slate-600">
            Partimos del monto de la operación, sumamos un interés fijo si corresponde y calculamos las cuotas sobre el total del flujo.
        </p>
    </div>

    @if (! $selectedProject)
        <div class="flex flex-1 items-center rounded-2xl border border-dashed border-slate-400 bg-slate-100 px-5 py-10 text-sm text-slate-600">
            Primero seleccioná una operación en el paso 1.
        </div>
    @elseif ((float) $operation_amount <= 0)
        <div class="grid min-h-0 flex-1 gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
            <div class="space-y-5">
                <div class="rounded-2xl border border-amber-300 bg-amber-50 p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-700">Monto pendiente</div>
                    <h3 class="mt-3 text-lg font-semibold text-amber-950">Esta operación todavía no tiene monto cargado</h3>
                    <p class="mt-2 text-sm text-amber-900">
                        La operación queda visible en la bandeja porque no tiene flujo asociado, pero no podemos calcular cuotas hasta definir un monto base mayor a cero.
                    </p>

                    <div class="mt-5 rounded-2xl border border-amber-200 bg-white/70 p-4">
                        <div class="text-sm font-semibold text-slate-950">{{ $selectedProject->name }}</div>
                        <div class="mt-1 text-sm text-slate-600">ID #{{ $selectedProject->id }} · {{ $selectedProject->status->label() }}</div>
                    </div>

                    <div class="mt-5 flex flex-col gap-3 sm:flex-row">
                        <a
                            href="{{ route('proyectos.show', $selectedProject) }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-amber-300 bg-white px-4 py-2.5 text-sm font-semibold text-amber-900 shadow-sm transition hover:border-amber-400 hover:bg-amber-100 focus:outline-none focus:ring-4 focus:ring-amber-100"
                        >
                            Abrir operación y cargar monto
                        </a>
                        <button
                            type="button"
                            wire:click="previousStep"
                            class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100"
                        >
                            Elegir otra operación
                        </button>
                    </div>
                </div>
            </div>

            <div class="min-h-0 overflow-y-auto rounded-2xl border border-slate-300 bg-white p-5 shadow-sm">
                <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Qué falta</div>
                <div class="mt-4 space-y-3 text-sm text-slate-700">
                    <div class="flex items-start gap-3">
                        <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-amber-400 ring-4 ring-amber-100"></span>
                        <span>Definir el monto base de la operación.</span>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="mt-1 inline-flex h-2.5 w-2.5 rounded-full bg-slate-300 ring-4 ring-slate-100"></span>
                        <span>Volver a este flujo para agregar interés fijo y generar cuotas.</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="grid min-h-0 flex-1 gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
            <div class="min-h-0 overflow-y-auto rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="space-y-5">
                    <div class="rounded-2xl border border-orange-200 bg-orange-50/80 p-5 shadow-sm">
                        <div class="text-sm font-medium text-slate-900">{{ $selectedProject->name }}</div>
                        <div class="mt-1 text-sm text-slate-600">{{ $selectedProject->status->label() }}</div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Monto de la operación</label>
                        <input
                            type="text"
                            value="${{ number_format((float) $operation_amount, 2, ',', '.') }}"
                            readonly
                            class="w-full rounded-2xl border border-slate-300 bg-slate-100 px-4 py-3 text-sm font-medium text-slate-800 shadow-sm"
                        >
                        @error('operation_amount')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Interés fijo</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            wire:model.live="interest_amount"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-4 focus:ring-orange-100"
                        >
                        @error('interest_amount')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Total del flujo</label>
                        <input
                            type="text"
                            value="${{ number_format((float) $total_amount, 2, ',', '.') }}"
                            readonly
                            class="w-full rounded-2xl border border-orange-200 bg-orange-50 px-4 py-3 text-sm font-semibold text-orange-950 shadow-sm"
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
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-4 focus:ring-orange-100"
                        >
                        @error('start_date')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Frecuencia</label>
                        <select
                            wire:model.live="frequency"
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-4 focus:ring-orange-100"
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
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-4 focus:ring-orange-100"
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
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-4 focus:ring-orange-100"
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
                            class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-orange-500 focus:outline-none focus:ring-4 focus:ring-orange-100"
                            placeholder="Notas internas del flujo..."
                        ></textarea>
                        @error('notes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    </div>
                </div>
            </div>

            <div class="min-h-0 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex h-full min-h-0 flex-col">
                    <div class="shrink-0 space-y-4 border-b border-slate-200 px-5 py-5">
                        <div class="rounded-2xl border px-5 py-4 shadow-sm {{ $this->installmentSummaryTone }}">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                                <div>
                                    <div class="text-xs font-medium uppercase tracking-[0.18em]">Control de cuotas</div>
                                    <div class="mt-2 text-lg font-semibold">
                                        Total del flujo: ${{ number_format((float) $total_amount, 2, ',', '.') }}
                                    </div>
                                    <div class="mt-1 text-sm">
                                        Suma cuotas: ${{ number_format($this->installmentsSum, 2, ',', '.') }}
                                    </div>
                                    <div class="mt-1 text-xs opacity-80">
                                        Monto operación: ${{ number_format((float) $operation_amount, 2, ',', '.') }} · Interés: ${{ number_format((float) $interest_amount, 2, ',', '.') }}
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
                                    La suma de las cuotas supera el total del flujo. Ajustá los montos antes de guardar.
                                </p>
                            @endif
                        </div>

                        @error('installmentRows')
                            <div class="rounded-2xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Cuotas del flujo</div>
                                <div class="mt-1 text-xs text-slate-500">Se muestran dentro de un panel fijo para mantener visibles los controles del asistente.</div>
                            </div>
                            <div class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                                {{ min(count($installmentRows), 5) }} visibles
                            </div>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto px-5 py-4">
                        <div class="space-y-3">
                    @foreach ($installmentRows as $index => $row)
                        <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                            <div class="grid gap-4 md:grid-cols-[110px_minmax(0,1fr)_180px] md:items-end">
                                <div>
                                    <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Cuota</div>
                                    <div class="mt-2 text-lg font-semibold text-slate-900">#{{ $row['number'] }}</div>
                                </div>

                                <div>
                                    <label class="mb-2 block text-sm font-medium text-slate-700">Monto</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        wire:model.live="installmentRows.{{ $index }}.amount"
                                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-4 focus:ring-orange-100"
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
                                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-4 focus:ring-orange-100"
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
            </div>
        </div>
    @endif
</div>
