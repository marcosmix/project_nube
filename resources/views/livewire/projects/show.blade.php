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
        'sale_closed' => 'border-emerald-200 bg-emerald-50/30',
        'execution' => 'border-blue-200 bg-blue-50/30',
        'paused' => 'border-purple-200 bg-purple-50/30',
        'finished' => 'border-cyan-200 bg-cyan-50/30',
    ];
    $cardTone = $sectionTone[$status] ?? 'border-gray-200 bg-white';

    $lifecycleOrder = ['sale_closed', 'execution', 'paused', 'finished'];
    $currentIndex = array_search($status, $lifecycleOrder, true);
    if ($currentIndex === false) $currentIndex = 0;

    $client = $project->client;
    $contact = $client?->contact;
@endphp

<x-slot name="header">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('proyectos.index') }}"
               class="rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
                ← Volver
            </a>
            <div>
                <h2 class="text-xl font-semibold leading-tight text-foreground">Operación</h2>
                <p class="text-xs text-muted-foreground">Vista + edición en una sola pantalla</p>
            </div>
        </div>
    </div>
</x-slot>

<x-ui.page-container>
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="text-sm font-semibold text-slate-900">Control operativo</div>
                    <p class="mt-1 text-sm text-slate-500">Acciones concretas para avanzar la operación sin pasos intermedios innecesarios.</p>
                </div>
                <x-ui.badge :variant="$status === 'finished' ? 'warning' : 'info'">{{ $cfg['label'] }}</x-ui.badge>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-3">
                @if($status === 'sale_closed')
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-950">Comenzar ejecución</div>
                                <p class="mt-1 text-xs leading-5 text-slate-600">La operación dejará el estado inicial y pasará a trabajo activo.</p>
                            </div>
                            <x-ui.badge size="sm" variant="success">Disponible</x-ui.badge>
                        </div>

                        <x-ui.button type="button" variant="success" class="mt-4 w-full justify-center" wire:click="openStatusConfirmation('execution')">
                            Comenzar
                        </x-ui.button>
                    </div>
                @endif

                @if($status === 'execution')
                    <div class="rounded-2xl border border-orange-200 bg-orange-50/80 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-950">Frenar operación</div>
                                <p class="mt-1 text-xs leading-5 text-slate-600">Detenela temporalmente dejando contexto claro para retomarla después.</p>
                            </div>
                            <x-ui.badge size="sm" variant="accent">Acción</x-ui.badge>
                        </div>

                        <x-ui.button type="button" variant="warning" class="mt-4 w-full justify-center" wire:click="openPauseModal">
                            Frenar
                        </x-ui.button>
                    </div>

                    <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-950">Cerrar operación</div>
                                <p class="mt-1 text-xs leading-5 text-slate-600">Marcala como finalizada cuando ya no requiera más trabajo operativo.</p>
                            </div>
                            <x-ui.badge size="sm" variant="warning">Cierre</x-ui.badge>
                        </div>

                        <x-ui.button type="button" variant="warning" class="mt-4 w-full justify-center" wire:click="openStatusConfirmation('finished')">
                            Finalizar
                        </x-ui.button>
                    </div>
                @endif

                @if($status === 'paused')
                    <div class="rounded-2xl border border-sky-200 bg-sky-50/80 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-950">Reanudar operación</div>
                                <p class="mt-1 text-xs leading-5 text-slate-600">Retomá la operación y devolvela al flujo activo de ejecución.</p>
                            </div>
                            <x-ui.badge size="sm" variant="info">Acción</x-ui.badge>
                        </div>

                        <x-ui.button type="button" variant="secondary" class="mt-4 w-full justify-center" wire:click="resumeOperation">
                            Reanudar
                        </x-ui.button>
                    </div>
                @endif

                @if(in_array($status, ['execution', 'paused'], true))
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-950">Subestado de ejecución</div>
                                <p class="mt-1 text-xs leading-5 text-slate-600">Actualizá el contexto operativo actual sin alterar el estado principal.</p>
                            </div>
                            <x-ui.badge size="sm" variant="info">Seguimiento</x-ui.badge>
                        </div>

                        <div class="mt-4">
                            <select wire:model.live="form.execution_sub_status" wire:change="requestExecutionSubStatusChange($event.target.value)" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm disabled:bg-slate-50" @disabled($isFinished)>
                                <option value="">Sin subestado</option>
                                <option value="on_track">Al Día</option>
                                <option value="with_debt">Con Deuda</option>
                                <option value="delayed">Con Demora</option>
                            </select>
                            @error('form.execution_sub_status') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            @if($status === 'paused')
                                <div class="mt-2 text-xs text-slate-500">La operación está frenada, pero el subestado sigue visible y puede actualizarse con confirmación.</div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            @if($status === 'finished')
                <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <div class="font-medium">Flujo finalizado</div>
                <div class="mt-1">Esta operación ya está finalizada y quedó en solo lectura.</div>
                </div>
            @endif
        </div>

        {{-- HEADER CARD --}}
        <div class="overflow-hidden rounded-2xl border-2 {{ $cfg['border'] }} bg-white shadow-sm">
            <div class="h-3 {{ $cfg['bar'] }}"></div>

            <div class="p-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:items-start">
                    <div class="min-w-0">
                        <div class="mb-1 text-xs uppercase tracking-wide text-gray-500">Operación</div>
                        <h1 class="text-3xl leading-tight text-gray-900 md:text-4xl">
                            {{ $project->name }}
                        </h1>

                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-3 py-1 text-sm {{ $cfg['badge'] }}">
                                {{ $cfg['label'] }}
                            </span>

                            @if(in_array($status, ['execution', 'paused'], true) && $subCfg)
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

                    </div>
                </div>

                {{-- ALERTS --}}
                @if(in_array($status, ['execution', 'paused'], true) && $sub === 'with_debt')
                    <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
                        <strong>Atención:</strong> Esta operación tiene pagos pendientes.
                    </div>
                @endif

                @if(in_array($status, ['execution', 'paused'], true) && $sub === 'delayed')
                    <div class="mt-6 rounded-2xl border border-orange-200 bg-orange-50 p-4 text-orange-800">
                        <strong>Demora:</strong> Esta operación presenta retrasos.
                    </div>
                @endif

                @if($status === 'paused' && ($form['pause_reason'] ?? $project->pause_reason))
                    <div class="mt-6 rounded-2xl border border-purple-200 bg-purple-50 p-4 text-purple-800">
                        <strong>Operación frenada:</strong> {{ $form['pause_reason'] ?? $project->pause_reason }}
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
                        <div class="mb-4 flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xl text-gray-900">Equipo asignado</div>
                                <p class="mt-1 text-sm text-gray-600">Vista informativa del equipo actual. Edita los miembros desde un modal dedicado para evitar cambios accidentales.</p>
                            </div>
                            @if(!$isFinished)
                                <button type="button" wire:click="openTeamModal" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 transition hover:bg-slate-50">
                                    Editar equipo
                                </button>
                            @endif
                        </div>
                        <div class="h-px bg-gray-100 mb-4"></div>

                        @if($project->developers->isNotEmpty())
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                @foreach($project->developers as $developer)
                                    <div class="rounded-xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                                        <div class="text-sm font-medium text-slate-900">
                                            {{ $developer->contact->first_name }} {{ $developer->contact->last_name }}
                                        </div>
                                        <div class="mt-1 text-xs text-slate-500">{{ str_replace('_', ' ', $developer->level) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-xl border border-dashed border-slate-300 bg-white/70 px-4 py-5 text-sm text-slate-500">
                                Todavía no hay miembros asignados a esta operación.
                            </div>
                        @endif

                        <div class="mt-4 text-sm text-gray-600">
                            {{ $project->developers->count() }} integrante{{ $project->developers->count() === 1 ? '' : 's' }} activo{{ $project->developers->count() === 1 ? '' : 's' }}
                        </div>
                    </div>
                @endif

                {{-- FINANCIAL (desde Interested) --}}
                @if(in_array($status, ['sale_closed','execution','paused','finished'], true))
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

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="mb-4 flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-lg text-slate-900">Archivos de la operación</div>
                                    <p class="mt-1 text-sm text-slate-500">Cargá documentación interna, actas, entregables o respaldos operativos.</p>
                                </div>
                                <x-ui.badge variant="info">{{ $project->attachments->count() }} archivo{{ $project->attachments->count() === 1 ? '' : 's' }}</x-ui.badge>
                            </div>

                            <div class="grid gap-4 md:grid-cols-[minmax(0,0.8fr)_minmax(0,1fr)_auto] md:items-end">
                                <div>
                                    <x-ui.label>Etiqueta interna</x-ui.label>
                                    <x-ui.input wire:model.defer="attachmentLabel" placeholder="Acta de inicio / Soporte técnico" />
                                    @error('attachmentLabel') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <x-ui.label>Archivo</x-ui.label>
                                    <x-ui.input type="file" wire:model="attachmentUpload" />
                                    <p class="mt-1 text-xs text-slate-500">Acepta PDF, imágenes y presentaciones hasta 10 MB.</p>
                                    @error('attachmentUpload') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>
                                <x-ui.button type="button" variant="primary" wire:click="saveAttachment">
                                    Guardar archivo
                                </x-ui.button>
                            </div>

                            @if($attachmentUpload)
                                <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                                    Archivo listo para subir: <span class="font-medium">{{ $attachmentUpload->getClientOriginalName() }}</span>
                                </div>
                            @endif

                            <div class="mt-5 space-y-3">
                                @forelse($project->attachments as $attachment)
                                    @php
                                        $previewable = $attachment->isImage() || $attachment->isPdf();
                                        $sizeInKb = $attachment->size > 0 ? number_format($attachment->size / 1024, 1, ',', '.') . ' KB' : 'Tamaño desconocido';
                                    @endphp
                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 shadow-sm">
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <div class="text-sm font-semibold text-slate-900">{{ $attachment->label ?: $attachment->original_name }}</div>
                                                    @if($attachment->label)
                                                        <x-ui.badge variant="info">{{ $attachment->original_name }}</x-ui.badge>
                                                    @endif
                                                </div>
                                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                                    <span>{{ $attachment->mime_type ?: 'Archivo adjunto' }}</span>
                                                    <span>{{ $sizeInKb }}</span>
                                                    <span>{{ $attachment->uploadedBy?->name ?? 'Sistema' }} · {{ $attachment->created_at?->format('d/m/Y H:i') }}</span>
                                                </div>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                @if($previewable)
                                                    <a href="{{ route('attachments.preview', $attachment) }}" target="_blank" rel="noreferrer" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
                                                        Ver
                                                    </a>
                                                @endif
                                                <a href="{{ route('attachments.download', $attachment) }}" class="inline-flex items-center rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-medium text-sky-700 transition hover:border-sky-300 hover:bg-sky-100">
                                                    Descargar
                                                </a>
                                                <button type="button" wire:click="removeAttachment({{ $attachment->id }})" class="inline-flex items-center rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-medium text-rose-700 transition hover:border-rose-300 hover:bg-rose-100">
                                                    Quitar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="rounded-xl border border-dashed border-slate-200 bg-white px-4 py-5 text-sm text-slate-500">
                                        Todavía no hay archivos cargados para esta operación.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        @if($project->opportunity?->attachments?->isNotEmpty())
                            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50/80 p-5 shadow-sm">
                                <div class="mb-4 flex items-start justify-between gap-3">
                                    <div>
                                        <div class="text-lg text-slate-900">Archivos comerciales heredados</div>
                                        <p class="mt-1 text-sm text-slate-500">Adjuntos provenientes de la oportunidad comercial.</p>
                                    </div>
                                    <x-ui.badge variant="warning">{{ $project->opportunity->attachments->count() }} archivo{{ $project->opportunity->attachments->count() === 1 ? '' : 's' }}</x-ui.badge>
                                </div>

                                <div class="space-y-3">
                                    @foreach($project->opportunity->attachments as $attachment)
                                        @php
                                            $previewable = $attachment->isImage() || $attachment->isPdf();
                                            $sizeInKb = $attachment->size > 0 ? number_format($attachment->size / 1024, 1, ',', '.') . ' KB' : 'Tamaño desconocido';
                                        @endphp
                                        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm">
                                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <div class="text-sm font-semibold text-slate-900">{{ $attachment->label ?: $attachment->original_name }}</div>
                                                        @if($attachment->label)
                                                            <x-ui.badge variant="info">{{ $attachment->original_name }}</x-ui.badge>
                                                        @endif
                                                    </div>
                                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                                        <span>{{ $attachment->mime_type ?: 'Archivo adjunto' }}</span>
                                                        <span>{{ $sizeInKb }}</span>
                                                        <span>{{ $attachment->uploadedBy?->name ?? 'Sistema' }} · {{ $attachment->created_at?->format('d/m/Y H:i') }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    @if($previewable)
                                                        <a href="{{ route('attachments.preview', $attachment) }}" target="_blank" rel="noreferrer" class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-100">
                                                            Ver
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('attachments.download', $attachment) }}" class="inline-flex items-center rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-medium text-sky-700 transition hover:border-sky-300 hover:bg-sky-100">
                                                        Descargar
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
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
                                   placeholder="Ej: Cliente solicitó frenar por reestructuración..."></textarea>
                        @error('form.pause_reason') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>
                @endif

                {{-- NOTES OPERATIVAS --}}
                <div class="rounded-2xl border p-6 shadow-sm {{ $cardTone }}">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xl text-gray-900">Notas de la operación</div>
                            <p class="mt-1 text-sm text-gray-600">Seguimiento interno del trabajo operativo.</p>
                        </div>
                    </div>
                    <div class="h-px bg-gray-100 mb-4"></div>

                    <div class="space-y-4">
                        @forelse($project->notes as $note)
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <div class="mb-3 text-[11px] font-medium uppercase tracking-[0.16em] text-slate-500">{{ $note->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }} · {{ $note->byUser?->name ?? 'Sistema' }}</div>
                                <div class="whitespace-pre-wrap text-sm text-slate-800">{{ $note->content }}</div>
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">Todavía no hay notas cargadas.</div>
                        @endforelse
                    </div>

                    <div class="mt-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="mb-3 text-sm font-medium text-slate-900">Agregar nueva nota</div>
                        <div class="space-y-3">
                            <textarea wire:model.defer="newNote" rows="4" class="w-full rounded-lg border border-slate-200 px-4 py-2" placeholder="Registrá seguimiento, acuerdos o próximos pasos..."></textarea>
                            @error('newNote') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                            <div class="flex justify-end">
                                <button type="button" wire:click="addNote" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-700">Agregar nota</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- NOTES VENTA --}}
                <div class="rounded-2xl border p-6 shadow-sm {{ $cardTone }}">
                    <details>
                        <summary class="flex cursor-pointer items-center justify-between gap-3 text-xl text-gray-900">
                            <span>Notas de la venta</span>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700">{{ $project->opportunity?->notes?->count() ?? 0 }}</span>
                        </summary>

                        <div class="mt-4 h-px bg-gray-100"></div>

                        <div class="mt-4 space-y-4">
                            @forelse($project->opportunity?->notes ?? collect() as $note)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="mb-3 text-[11px] font-medium uppercase tracking-[0.16em] text-slate-500">{{ $note->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }} · {{ $note->byUser?->name ?? 'Sistema' }}</div>
                                    <div class="whitespace-pre-wrap text-sm text-slate-800">{{ $note->content }}</div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">Todavía no hay notas comerciales heredadas.</div>
                            @endforelse
                        </div>
                    </details>
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
                            <span class="text-gray-900">{{ $project->developers->count() }} integrantes</span>
                        </div>
                    </div>

                    @if($isFinished)
                        <div class="mt-4 rounded-xl bg-gray-50 p-3 text-xs text-gray-600">
                            Operación finalizada: campos en solo lectura.
                        </div>
                    @endif
                </div>

                <div class="rounded-2xl border p-6 shadow-sm {{ $cardTone }}">
                    <div class="mb-4 text-lg text-gray-900">Ciclo de vida</div>
                    <div class="h-px bg-gray-100 mb-4"></div>

                    <div class="space-y-3">
                        @foreach($lifecycleOrder as $i => $lifecycleStatus)
                            @php
                                $stepCfg = config("projects.status.$lifecycleStatus") ?? ['label' => ucfirst($lifecycleStatus), 'badge' => 'bg-slate-100 text-slate-700'];
                                $isCurrent = $lifecycleStatus === $status;
                                $isReached = $status === 'paused'
                                    ? in_array($lifecycleStatus, ['sale_closed', 'execution', 'paused'], true)
                                    : $i <= $currentIndex;
                            @endphp

                            <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 {{ $isCurrent ? $cardTone : 'border-slate-200 bg-white' }}">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $isReached ? $stepCfg['badge'] : 'bg-slate-100 text-slate-400' }}">
                                    {{ $isReached ? '✓' : '·' }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-slate-900">{{ $stepCfg['label'] }}</div>
                                    @if($isCurrent)
                                        <div class="text-xs text-slate-500">Estado actual</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-2xl border p-6 shadow-sm {{ $cardTone }}">
                    <div class="mb-4 text-lg text-gray-900">Historial de estados</div>
                    <div class="h-px bg-gray-100 mb-4"></div>

                    <div class="space-y-3">
                        @forelse($project->statusLogs as $log)
                            @php
                                $logCfg = config("projects.status.{$log->status}") ?? ['label' => ucfirst($log->status), 'badge' => 'bg-slate-100 text-slate-700'];
                                $showPauseReason = $log->status === 'paused' && $status === 'paused' && filled($project->pause_reason) && $loop->first;
                            @endphp
                            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium {{ $logCfg['badge'] }}">{{ $logCfg['label'] }}</span>
                                        <div class="mt-2 text-xs text-slate-500">{{ $log->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</div>
                                    </div>
                                    <div class="text-right text-xs text-slate-500">{{ $log->byUser?->name ?? 'Sistema' }}</div>
                                </div>

                                @if($showPauseReason)
                                    <div class="mt-3 rounded-xl border border-purple-200 bg-purple-50 px-3 py-2 text-xs text-purple-800">
                                        Motivo de freno: {{ $project->pause_reason }}
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">Todavía no hay cambios de estado registrados.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        @if($showTeamModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4">
                <div class="flex h-[80vh] max-h-[80vh] w-full max-w-4xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-950">Editar equipo de la operación</h3>
                            <p class="text-sm text-slate-500">Selecciona los integrantes que forman parte del equipo actual.</p>
                        </div>
                        <button type="button" wire:click="closeTeamModal" class="rounded-2xl px-3 py-2 text-slate-500 transition hover:bg-slate-100">Cerrar</button>
                    </div>

                    <div class="flex-1 overflow-y-auto px-6 py-6 [scrollbar-color:#94a3b8_transparent] [scrollbar-width:thin] [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-slate-300 [&::-webkit-scrollbar-track]:bg-transparent [&::-webkit-scrollbar]:w-2">
                        <div class="space-y-4">
                            @foreach($this->developersGroupedByJobTitle as $jobTitle => $developers)
                                <details class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 transition hover:bg-slate-50">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-950">{{ $jobTitle }}</div>
                                            <div class="mt-1 text-xs text-slate-500">{{ $developers->count() }} integrante{{ $developers->count() === 1 ? '' : 's' }}</div>
                                        </div>

                                        <div class="flex items-center gap-3">
                                            <x-ui.badge size="sm" variant="neutral">{{ $developers->count() }}</x-ui.badge>
                                            <span class="text-slate-400 transition group-open:rotate-180">⌄</span>
                                        </div>
                                    </summary>

                                    <div class="border-t border-slate-200 px-5 py-4">
                                        <div class="grid grid-cols-1 gap-3 xl:grid-cols-2">
                                            @foreach($developers as $d)
                                                @php
                                                    $checked = in_array($d->id, $selectedDevelopers, true);
                                                    $fullName = trim(($d->contact->first_name ?? '').' '.($d->contact->last_name ?? ''));
                                                @endphp
                                                <label class="flex cursor-pointer items-start gap-4 rounded-2xl border-2 p-4 transition {{ $checked ? 'border-sky-400 bg-sky-50 shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50/70' }}">
                                                    <input type="checkbox" value="{{ $d->id }}" wire:model.defer="selectedDevelopers" class="mt-1 rounded border-slate-300 text-sky-600 focus:ring-sky-500" />
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                                            <div class="min-w-0">
                                                                <div class="truncate text-sm font-semibold text-slate-950">{{ $fullName }}</div>
                                                                <div class="mt-1 text-xs text-slate-500">{{ $d->level_label }}</div>
                                                            </div>

                                                            @if($checked)
                                                                <x-ui.badge size="sm" variant="info">Seleccionado</x-ui.badge>
                                                            @endif
                                                        </div>

                                                        @if(! empty($d->skins))
                                                            <div class="mt-3 flex flex-wrap gap-2">
                                                                @foreach($d->skins as $skin)
                                                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-700 ring-1 ring-slate-200">
                                                                        {{ $skin }}
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <div class="mt-3 text-xs text-slate-400">Sin especialidades cargadas.</div>
                                                        @endif
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </details>
                            @endforeach
                        </div>

                        @error('selectedDevelopers') <div class="mt-3 text-xs text-red-600">{{ $message }}</div> @enderror
                        @error('selectedDevelopers.*') <div class="mt-3 text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50/80 px-6 py-4">
                        <div class="text-sm text-slate-600">{{ count($selectedDevelopers) }} integrante{{ count($selectedDevelopers) === 1 ? '' : 's' }} seleccionado{{ count($selectedDevelopers) === 1 ? '' : 's' }}</div>
                        <div class="flex items-center gap-3">
                            <x-ui.button type="button" variant="secondary" wire:click="closeTeamModal">Cancelar</x-ui.button>
                            <x-ui.button type="button" wire:click="saveTeamAssignments">Guardar equipo</x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if($showStatusConfirmationModal && $this->pendingStatusTransition)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4">
                <div class="w-full max-w-xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-slate-950">Confirmar cambio de estado</h3>
                        <p class="mt-1 text-sm text-slate-500">Revisá el cambio antes de actualizar el flujo operativo.</p>
                    </div>

                    <div class="space-y-4 px-6 py-6">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Estado actual</div>
                            <div class="mt-2 text-sm font-medium text-slate-900">{{ $cfg['label'] }}</div>
                        </div>

                        <div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4">
                            <div class="text-xs font-medium uppercase tracking-[0.16em] text-indigo-700">Nuevo estado</div>
                            <div class="mt-2 text-sm font-medium text-indigo-950">{{ $this->pendingStatusTransition['label'] }}</div>
                            <p class="mt-2 text-sm text-indigo-900/80">{{ $this->pendingStatusTransition['description'] }}</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50/80 px-6 py-4">
                        <x-ui.button type="button" variant="secondary" wire:click="closeStatusConfirmation">Cancelar</x-ui.button>
                        <x-ui.button type="button" :variant="$this->pendingStatusTransition['variant']" wire:click="confirmStatusChange">Confirmar</x-ui.button>
                    </div>
                </div>
            </div>
        @endif

        @if($showPauseModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4">
                <div class="w-full max-w-xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-slate-950">Frenar operación</h3>
                        <p class="mt-1 text-sm text-slate-500">Registra el motivo para dejar contexto claro antes de frenar la operación.</p>
                    </div>

                    <div class="px-6 py-6">
                        <label class="text-sm font-medium text-slate-700">Motivo de freno</label>
                        <textarea wire:model.defer="pauseReasonDraft" rows="4" class="mt-2 w-full rounded-lg border border-slate-200 px-4 py-3" placeholder="Ej: Cliente solicitó frenar por revisión interna..."></textarea>
                        @error('pauseReasonDraft') <div class="mt-2 text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50/80 px-6 py-4">
                        <x-ui.button type="button" variant="secondary" wire:click="closePauseModal">Cancelar</x-ui.button>
                        <x-ui.button type="button" variant="warning" wire:click="pauseOperation">Confirmar freno</x-ui.button>
                    </div>
                </div>
            </div>
        @endif

        @if($showExecutionSubStatusModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4">
                <div class="w-full max-w-xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70">
                    <div class="border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-slate-950">Confirmar cambio de subestado</h3>
                        <p class="mt-1 text-sm text-slate-500">Verificá el cambio antes de actualizar el contexto operativo de la operación.</p>
                    </div>

                    <div class="space-y-4 px-6 py-6">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-xs font-medium uppercase tracking-[0.16em] text-slate-500">Subestado actual</div>
                            <div class="mt-2 text-sm font-medium text-slate-900">{{ $project->execution_sub_status?->label() ?? 'Sin subestado' }}</div>
                        </div>

                        <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4">
                            <div class="text-xs font-medium uppercase tracking-[0.16em] text-sky-700">Nuevo subestado</div>
                            <div class="mt-2 text-sm font-medium text-sky-950">{{ $pendingExecutionSubStatusLabel }}</div>
                        </div>

                        @error('pendingExecutionSubStatus') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50/80 px-6 py-4">
                        <x-ui.button type="button" variant="secondary" wire:click="cancelExecutionSubStatusChange">Cancelar</x-ui.button>
                        <x-ui.button type="button" variant="primary" wire:click="confirmExecutionSubStatusChange">Confirmar cambio</x-ui.button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-ui.page-container>
