<div class="min-h-[calc(100vh-10rem)] rounded-3xl border border-slate-300 bg-[#fffdfa] shadow-sm">
    <div class="border-b border-slate-300 px-6 py-6 sm:px-8">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-3xl font-semibold tracking-tight text-slate-950">Nuevo flujo de cobro</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Configurá el flujo en 3 pasos: proyecto, cuotas y automatización.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-3">
                @foreach ([
                    1 => ['title' => 'Cliente y proyecto', 'subtitle' => 'Seleccioná el origen del flujo'],
                    2 => ['title' => 'Configuración del flujo', 'subtitle' => 'Definí cuotas y fechas'],
                    3 => ['title' => 'Envío automático', 'subtitle' => 'Ajustá la automatización'],
                ] as $step => $meta)
                    @php
                        $stepPalette = match ($step) {
                            1 => [
                                'base' => 'border-amber-300',
                                'idle' => 'bg-white text-slate-800 hover:border-amber-500 hover:bg-amber-50/60',
                                'active' => 'border-amber-600 bg-amber-100 text-amber-950 ring-4 ring-amber-100 shadow-sm',
                                'done' => 'border-amber-500 bg-amber-50 text-amber-900',
                                'subtitle' => 'text-amber-700',
                            ],
                            2 => [
                                'base' => 'border-orange-300',
                                'idle' => 'bg-white text-slate-800 hover:border-orange-500 hover:bg-orange-50/60',
                                'active' => 'border-orange-600 bg-orange-100 text-orange-950 ring-4 ring-orange-100 shadow-sm',
                                'done' => 'border-orange-500 bg-orange-50 text-orange-900',
                                'subtitle' => 'text-orange-700',
                            ],
                            default => [
                                'base' => 'border-blue-300',
                                'idle' => 'bg-white text-slate-800 hover:border-blue-500 hover:bg-blue-50/60',
                                'active' => 'border-blue-600 bg-blue-100 text-blue-950 ring-4 ring-blue-100 shadow-sm',
                                'done' => 'border-blue-500 bg-blue-50 text-blue-900',
                                'subtitle' => 'text-blue-700',
                            ],
                        };

                        $stateClasses = $currentStep === $step
                            ? $stepPalette['active']
                            : ($currentStep > $step ? $stepPalette['done'] : $stepPalette['idle']);
                    @endphp
                    <button
                        type="button"
                        wire:click="goToStep({{ $step }})"
                        class="rounded-2xl border-2 px-4 py-3 text-left shadow-sm transition focus:outline-none focus:ring-4 focus:ring-slate-100 {{ $stepPalette['base'] }} {{ $stateClasses }}"
                    >
                        <div class="text-xs font-semibold uppercase tracking-[0.2em]">{{ str_pad((string) $step, 2, '0', STR_PAD_LEFT) }}</div>
                        <div class="mt-2 text-sm font-semibold">{{ $meta['title'] }}</div>
                        <div class="mt-1 text-xs {{ $currentStep === $step || $currentStep > $step ? $stepPalette['subtitle'] : 'text-slate-500' }}">
                            {{ $meta['subtitle'] }}
                        </div>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid min-h-[42rem] gap-0 xl:grid-cols-[minmax(0,1fr)_320px]">
        <div class="px-6 py-6 sm:px-8">
            @if ($currentStep === 1)
                @include('livewire.cobros.forms.partials.create-flow-step-1')
            @elseif ($currentStep === 2)
                @include('livewire.cobros.forms.partials.create-flow-step-2')
            @else
                @include('livewire.cobros.forms.partials.create-flow-step-3')
            @endif
        </div>

        <aside class="border-t border-slate-300 bg-slate-100/80 px-6 py-6 xl:border-l xl:border-t-0">
            <div class="space-y-5">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-600">Resumen</h2>
                    <p class="mt-1 text-sm text-slate-600">Estado actual del flujo en creación.</p>
                </div>

                <div class="space-y-3">
                    <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-600">Cliente</div>
                        <div class="mt-2 text-sm font-medium text-slate-900">
                            {{ $selectedClient?->organization_name ?? 'Sin seleccionar' }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-600">Proyecto</div>
                        <div class="mt-2 text-sm font-medium text-slate-900">
                            {{ $selectedProject?->name ?? 'Sin seleccionar' }}
                        </div>
                        @if ($selectedProject)
                            <div class="mt-1 text-xs text-slate-600">{{ $selectedProject->status->label() }}</div>
                        @endif
                    </div>

                    <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-600">Monto total</div>
                        <div class="mt-2 text-lg font-semibold text-slate-900">
                            ${{ number_format((float) $total_amount, 2, ',', '.') }}
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-600">Cuotas</div>
                        <div class="mt-2 text-lg font-semibold text-slate-900">{{ $installments_count }}</div>
                        <div class="mt-1 text-xs text-slate-600">{{ collect($installmentRows)->filter()->count() }} configuradas</div>
                    </div>

                    <div class="rounded-2xl border px-4 py-4 shadow-sm {{ $this->installmentSummaryTone }}">
                        <div class="text-xs font-medium uppercase tracking-[0.18em]">Control de suma</div>
                        <div class="mt-2 text-sm font-semibold">
                            ${{ number_format($this->installmentsSum, 2, ',', '.') }}
                            @if (abs($this->installmentsDifference) > 0.009)
                                <span class="font-normal">
                                    (dif. ${{ number_format(abs($this->installmentsDifference), 2, ',', '.') }})
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-300 bg-white p-4 shadow-sm">
                        <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-600">Automatización</div>
                        <div class="mt-2 text-sm font-medium text-slate-900">
                            {{ $auto_send_enabled ? 'Envío automático habilitado' : 'Envío automático desactivado' }}
                        </div>
                        <div class="mt-1 text-xs text-slate-600">
                            {{ $this->autoSendEmail ?: 'Sin email detectado' }}
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-300 bg-white px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
        <div class="text-sm text-slate-600">
            Paso {{ $currentStep }} de 3
        </div>

        <div class="flex flex-col gap-3 sm:flex-row">
            <button
                type="button"
                wire:click="previousStep"
                @disabled($currentStep === 1)
                class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-100 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 disabled:opacity-100"
            >
                Volver
            </button>

            @if ($currentStep < 3)
                <button
                    type="button"
                    wire:click="nextStep"
                    class="inline-flex items-center justify-center rounded-2xl border border-orange-300 bg-orange-50 px-4 py-2.5 text-sm font-medium text-orange-950 shadow-sm transition hover:border-orange-400 hover:bg-orange-100 focus:outline-none focus:ring-4 focus:ring-orange-100"
                >
                    Siguiente
                </button>
            @else
                <button
                    type="button"
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center justify-center rounded-2xl border border-orange-300 bg-orange-50 px-5 py-2.5 text-sm font-medium text-orange-950 shadow-sm transition hover:border-orange-400 hover:bg-orange-100 focus:outline-none focus:ring-4 focus:ring-orange-100 disabled:cursor-not-allowed disabled:border-orange-200 disabled:bg-orange-50 disabled:text-orange-500 disabled:opacity-100"
                >
                    <span wire:loading.remove wire:target="save">Guardar flujo</span>
                    <span wire:loading wire:target="save">Guardando...</span>
                </button>
            @endif
        </div>
    </div>
</div>
