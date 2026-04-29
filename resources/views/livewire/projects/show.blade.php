{{-- resources/views/livewire/projects/show.blade.php --}}

@php
    $status = $form['status'] ?? $project->status->value;

    $cfg = config("projects.status.$status") ?? [
        'label' => ucfirst($status),
        'bar' => 'bg-gray-200',
        'border' => 'border-gray-200',
        'badge' => 'bg-gray-100 text-gray-800',
    ];

    $sub = $form['execution_sub_status'] ?? $project->execution_sub_status?->value;
    $subCfg = $sub ? (config("projects.execution_sub.$sub") ?? null) : null;

    $isFinished = $status === 'finished';
    $isFinancialLocked = in_array($status, ['sale_closed','execution','paused','finished'], true);
    $sectionTone = [
        'prospection' => 'border-amber-200 bg-amber-50/30',
        'interested' => 'border-orange-200 bg-orange-50/30',
        'sale_closed' => 'border-emerald-200 bg-emerald-50/30',
        'execution' => 'border-blue-200 bg-blue-50/30',
        'paused' => 'border-purple-200 bg-purple-50/30',
        'finished' => 'border-cyan-200 bg-cyan-50/30',
    ];
    $cardTone = $sectionTone[$status] ?? 'border-gray-200 bg-white';

    $order = ['prospection','interested','sale_closed','execution','paused','finished'];
    $currentIndex = array_search($status, $order, true);
    if ($currentIndex === false) $currentIndex = -1;

    $prevStatus = $this->previousStatus();
    $nextStatus = $this->nextStatus();
    $prevLabel = $this->previousStatusLabel();
    $nextLabel = $this->nextStatusLabel();

    $prevBlocked = $prevStatus ? $this->transitionBlockReason($prevStatus) : null;
    $nextBlocked = $nextStatus ? $this->transitionBlockReason($nextStatus) : null;
    $canGoPrev = $prevStatus && $prevBlocked === null;
    $canGoNext = $nextStatus && $nextBlocked === null;

    $client = $project->client;
    $contact = $client?->contact;
    
    $alertMessage = null;
    if ($nextStatus && !$canGoNext) {
        $alertMessage = $nextBlocked;
    }
@endphp

<x-slot name="header">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('proyectos.index') }}"
               class="rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
                ← Volver
            </a>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-foreground">Proyecto</h2>
                <p class="text-xs text-muted-foreground">Vista + edición en una sola pantalla</p>
            </div>
        </div>
    </div>
</x-slot>

<x-ui.page-container>
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-2">
                @if($prevStatus)
                    <button
                        wire:click="changeStatus('{{ $prevStatus }}')"
                        @disabled($isFinished || !$canGoPrev)
                        class="rounded-lg border px-4 py-2 text-sm disabled:cursor-not-allowed {{ $isFinished || !$canGoPrev ? 'border-gray-200 bg-gray-100 text-gray-500' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}"
                        title="{{ $prevBlocked ?? 'Ir al estado anterior' }}"
                    >
                        ← {{ $prevLabel ?? 'Anterior' }}
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-2">
                @if($nextStatus)
                    <button
                        wire:click="changeStatus('{{ $nextStatus }}')"
                        @disabled($isFinished || !$canGoNext)
                        class="rounded-lg px-4 py-2 text-sm disabled:cursor-not-allowed {{ $isFinished || !$canGoNext ? 'bg-gray-200 text-gray-600' : 'bg-green-600 text-white hover:bg-green-700' }}"
                        title="{{ $nextBlocked ?? 'Avanzar al siguiente estado' }}"
                    >
                        {{ $nextLabel ?? 'Siguiente' }} →
                    </button>
                @endif
            </div>
        </div>

        @if($alertMessage)
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <div class="font-medium">No puedes avanzar</div>
                <div class="mt-1">{{ $alertMessage }}</div>
            </div>
        @endif

        {{-- HEADER CARD --}}
        <div class="overflow-hidden rounded-2xl border-2 {{ $cfg['border'] }} bg-white shadow-sm">
            <div class="h-3 {{ $cfg['bar'] }}"></div>

            <div class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:items-start">
                    <div class="min-w-0">
                        <div class="mb-1 text-xs uppercase tracking-wide text-gray-500">Proyecto</div>
                        <h1 class="text-3xl leading-tight text-gray-900 md:text-4xl">
                            {{ $project->name }}
                        </h1>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-sm {{ $cfg['badge'] }}">
                                {{ $cfg['label'] }}
                            </span>

                            @if($status === 'execution' && $subCfg)
                                <span class="rounded-full px-3 py-1 text-sm {{ $subCfg['badge'] }}">
                                    {{ $subCfg['label'] }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="min-w-0">
                        <div class="mb-2 text-sm text-gray-600">Cliente</div>

                        {{-- READONLY: cliente --}}
                        <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-900">
                            <div class="font-medium">{{ $client?->organization_name ?? '—' }}</div>
                            <div class="text-xs text-gray-600">
                                {{ $contact?->first_name }} {{ $contact?->last_name }}
                            </div>
                        </div>

                        {{-- Subestado ejecución (solo si execution) --}}
                        @if($status === 'execution')
                            <div class="mt-4">
                                <div class="mb-2 text-sm text-gray-600">Sub-estado de ejecución</div>
                                <select wire:model.defer="form.execution_sub_status"
                                        @disabled($isFinished)
                                        class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm disabled:bg-gray-50">
                                    <option value="">—</option>
                                    <option value="on_track">Al Día</option>
                                    <option value="with_debt">Con Deuda</option>
                                    <option value="delayed">Con Demora</option>
                                </select>
                                @error('form.execution_sub_status') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>
                        @endif
                    </div>
                </div>

                {{-- TIMELINE HORIZONTAL --}}
                <div class="mt-6">
                    <div class="mb-3 text-sm text-gray-600">Ciclo de Vida del Proyecto</div>

                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($order as $i => $st)
                            @php
                                $isCompleted = $i <= $currentIndex;
                                $isCurrent = $st === $status;
                                $stCfg = config("projects.status.$st") ?? ['label' => ucfirst($st)];
                            @endphp

                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm {{ $isCompleted ? $stCfg['badge'] : 'bg-gray-100 text-gray-500' }}">
                                <span>{{ $isCompleted ? '✓' : '⏳' }}</span>
                                <span>{{ $stCfg['label'] }}</span>
                                @if($isCurrent)
                                    <span class="text-xs">(actual)</span>
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>

                {{-- ALERTS --}}
                @if($status === 'execution' && $sub === 'with_debt')
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
                        <strong>Atención:</strong> Este proyecto tiene pagos pendientes.
                    </div>
                @endif

                @if($status === 'execution' && $sub === 'delayed')
                    <div class="mt-6 rounded-2xl border border-orange-200 bg-orange-50 p-4 text-orange-800">
                        <strong>Demora:</strong> Este proyecto presenta retrasos.
                    </div>
                @endif

                @if($status === 'paused' && ($form['pause_reason'] ?? $project->pause_reason))
                    <div class="mt-6 rounded-2xl border border-purple-200 bg-purple-50 p-4 text-purple-800">
                        <strong>Proyecto Frenado:</strong> {{ $form['pause_reason'] ?? $project->pause_reason }}
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- MAIN --}}
            <div class="space-y-6 lg:col-span-2">

                {{-- TEAM (desde Venta Cerrada) --}}
                @if(in_array($status, ['sale_closed','execution','paused','finished'], true))
                    <div class="rounded-2xl border p-6 shadow-sm {{ $cardTone }}">
                        <div class="mb-4 text-xl text-gray-900">Equipo Asignado</div>
                        <div class="h-px bg-gray-100 mb-4"></div>

                        <div class="grid max-h-64 grid-cols-1 gap-3 overflow-y-auto p-1 md:grid-cols-2">
                            @foreach($this->developers as $d)
                                @php $checked = in_array($d->id, $selectedDevelopers, true); @endphp
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border-2 p-3 transition {{ $checked ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="checkbox"
                                           value="{{ $d->id }}"
                                           wire:model.defer="selectedDevelopers"
                                           @disabled($isFinished)
                                           class="rounded" />
                                    <div>
                                        <div class="text-sm text-gray-900">
                                            {{ $d->contact->first_name }} {{ $d->contact->last_name }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ str_replace('_', ' ', $d->level) }}
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="mt-3 text-sm text-gray-600">
                            {{ count($selectedDevelopers) }} dev{{ count($selectedDevelopers) === 1 ? '' : 's' }} seleccionado{{ count($selectedDevelopers) === 1 ? '' : 's' }}
                        </div>
                    </div>
                @endif

                {{-- FINANCIAL (desde Interested) --}}
                @if($status !== 'prospection')
                    <div class="rounded-2xl border p-6 shadow-sm {{ $cardTone }}">
                        <div class="mb-4 text-xl text-gray-900">Información de Contratación</div>
                        <div class="h-px bg-gray-100 mb-4"></div>
                        @if($isFinancialLocked)
                            <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600">
                                Esta información queda bloqueada desde "Venta Cerrada" y el monto se reutiliza al crear un flujo de cobro.
                            </div>
                        @else
                            <div class="mb-4 rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs text-sky-700">
                                Cargá el costo total antes de pasar a "Venta Cerrada".
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <div class="text-sm text-gray-600">Costo Total</div>
                                <input type="number"
                                       wire:model.defer="form.total_cost"
                                       @disabled($isFinished || $isFinancialLocked)
                                       class="mt-1 w-full rounded-lg border border-gray-200 px-4 py-2 disabled:bg-gray-50"
                                       placeholder="450000" />
                                @error('form.total_cost') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <div class="text-sm text-gray-600">Fecha Posible de Inicio</div>
                                <input type="date"
                                       wire:model.defer="form.estimated_start_date"
                                       @disabled($isFinished || $isFinancialLocked)
                                       class="mt-1 w-full rounded-lg border border-gray-200 px-4 py-2 disabled:bg-gray-50" />
                                @error('form.estimated_start_date') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <div class="text-sm text-gray-600">Fecha Posible de Finalización</div>
                                <input type="date"
                                       wire:model.defer="form.estimated_end_date"
                                       @disabled($isFinished || $isFinancialLocked)
                                       class="mt-1 w-full rounded-lg border border-gray-200 px-4 py-2 disabled:bg-gray-50" />
                                @error('form.estimated_end_date') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <div class="text-sm text-gray-600">Link a Propuesta (PDF)</div>
                                <input wire:model.defer="form.proposal_url"
                                       @disabled($isFinished || $isFinancialLocked)
                                       class="mt-1 w-full rounded-lg border border-gray-200 px-4 py-2 disabled:bg-gray-50"
                                       placeholder="https://drive.google.com/..." />
                                @error('form.proposal_url') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <div class="text-sm text-gray-600">Link a Excel (Anexo Financiero)</div>
                                <input wire:model.defer="form.excel_url"
                                       @disabled($isFinished || $isFinancialLocked)
                                       class="mt-1 w-full rounded-lg border border-gray-200 px-4 py-2 disabled:bg-gray-50"
                                       placeholder="https://docs.google.com/spreadsheets/..." />
                                @error('form.excel_url') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                @endif

                {{-- EXECUTION (desde execution) --}}
                @if(in_array($status, ['execution','paused','finished'], true))
                    <div class="rounded-2xl border p-6 shadow-sm {{ $cardTone }}">
                        <div class="mb-4 text-xl text-gray-900">Información de Ejecución</div>
                        <div class="h-px bg-gray-100 mb-4"></div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <div class="text-sm text-gray-600">Día del Mes para Cierre de Sprint (1-31)</div>
                                <input type="number" min="1" max="31"
                                       wire:model.defer="form.sprint_close_day"
                                       @disabled($isFinished)
                                       class="mt-1 w-full rounded-lg border border-gray-200 px-4 py-2 disabled:bg-gray-50"
                                       placeholder="15" />
                            </div>

                            <div>
                                <div class="text-sm text-gray-600">Fecha Real de Inicio</div>
                                <input type="date"
                                       wire:model.defer="form.actual_start_date"
                                       @disabled($isFinished)
                                       class="mt-1 w-full rounded-lg border border-gray-200 px-4 py-2 disabled:bg-gray-50" />
                            </div>
                        </div>
                    </div>
                @endif

                {{-- PAUSE (solo paused) --}}
                @if($status === 'paused')
                    <div class="rounded-2xl border border-purple-200 bg-purple-50 p-6 shadow-sm">
                        <div class="mb-2 text-xl font-semibold text-purple-900">Motivo de Pausa</div>

                        <textarea wire:model.defer="form.pause_reason"
                                  rows="3"
                                  @disabled($isFinished)
                                  class="mt-3 w-full rounded-lg border border-purple-200 bg-white px-4 py-2 disabled:bg-gray-50"
                                  placeholder="Ej: Cliente solicitó pausa por reestructuración..."></textarea>
                        @error('form.pause_reason') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>
                @endif

                {{-- NOTES --}}
                <div class="rounded-2xl border p-6 shadow-sm {{ $cardTone }}">
                    <div class="mb-4 text-xl text-gray-900">
                        Notas del Proyecto {{ !$isFinished ? '(Editable)' : '' }}
                    </div>
                    <div class="h-px bg-gray-100 mb-4"></div>

                    <textarea wire:model.defer="form.prospection_notes"
                              rows="6"
                              @disabled($isFinished)
                              class="w-full rounded-lg border border-gray-200 px-4 py-2 {{ $isFinished ? 'bg-gray-50' : 'bg-white' }}"
                              placeholder="Agrega notas sobre el proyecto..."></textarea>
                </div>
            </div>

            {{-- SIDEBAR --}}
            <div class="space-y-6">
                <div class="rounded-2xl border p-6 shadow-sm {{ $cardTone }}">
                    <div class="mb-4 text-lg text-gray-900">Datos Rápidos</div>
                    <div class="h-px bg-gray-100 mb-4"></div>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Creado:</span>
                            <span class="text-gray-900">{{ $project->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Última Actualización:</span>
                            <span class="text-gray-900">{{ $project->updated_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Equipo:</span>
                            <span class="text-gray-900">{{ count($selectedDevelopers) }} devs</span>
                        </div>
                    </div>

                    @if($isFinished)
                        <div class="mt-4 rounded-xl bg-gray-50 p-3 text-xs text-gray-600">
                            Proyecto finalizado: campos en solo lectura.
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-ui.page-container>
