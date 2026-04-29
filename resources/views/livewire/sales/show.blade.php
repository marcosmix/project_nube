<x-slot name="header">
    <div class="flex items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold leading-tight text-foreground">Detalle de oportunidad</h2>
            <p class="text-sm text-slate-500">Seguimiento comercial con historial de estados y notas.</p>
        </div>
        <a href="{{ route('ventas.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Volver</a>
    </div>
</x-slot>

<x-ui.page-container>
    <div class="space-y-6">
        @php
            $convertBlockedReason = $this->convertBlockedReason();
        @endphp

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-5">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Consulta comercial</div>
                        <h1 class="mt-1 text-3xl font-semibold text-slate-900">{{ $opportunity->name }}</h1>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-800">{{ $opportunity->status->label() }}</span>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-sm text-amber-900">{{ $opportunity->source->label() }}</span>
                            @if($opportunity->project)
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-sm text-emerald-900">Ya vinculada a proyecto</span>
                            @endif
                        </div>
                    </div>

                    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="mb-2 text-sm font-medium text-slate-900">Actualizar estado comercial</div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <select wire:model.defer="selectedStatus" class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm">
                                @foreach($statusOptions as $statusOption)
                                    <option value="{{ $statusOption['value'] }}">{{ $statusOption['label'] }}</option>
                                @endforeach
                            </select>
                            <button type="button" wire:click="updateStatus" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-800">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 text-lg font-semibold text-slate-900">Datos iniciales</div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 text-sm text-slate-700">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Cliente</div>
                                <div class="mt-1">{{ $opportunity->client?->organization_name ?? 'Sin cliente asociado' }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Responsable</div>
                                <div class="mt-1">{{ $opportunity->responsibleUser?->name ?? 'Sin asignar' }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Primer contacto</div>
                                <div class="mt-1">{{ $opportunity->first_contact_at?->format('d/m/Y') ?? 'No definido' }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Contacto</div>
                                <div class="mt-1">{{ $opportunity->display_contact }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Telefono</div>
                                <div class="mt-1">{{ $opportunity->contact_phone ?? '—' }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Email</div>
                                <div class="mt-1">{{ $opportunity->contact_email ?? '—' }}</div>
                            </div>
                            <div class="md:col-span-2">
                                <div class="text-xs uppercase tracking-wide text-slate-500">Usuario / red social</div>
                                <div class="mt-1">{{ $opportunity->contact_handle ?? '—' }}</div>
                            </div>
                            <div class="md:col-span-2">
                                <div class="text-xs uppercase tracking-wide text-slate-500">Mensaje inicial</div>
                                <div class="mt-1 whitespace-pre-wrap rounded-xl bg-slate-50 p-4 text-slate-700">{{ $opportunity->initial_message ?: 'Sin mensaje inicial cargado.' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <div class="text-lg font-semibold text-slate-900">Canal y atención</div>
                                <p class="mt-1 text-sm text-slate-500">Ventana operativa y metadatos básicos del canal de ingreso.</p>
                            </div>
                            <x-ui.badge :variant="$opportunity->source->value === 'whatsapp' ? 'success' : 'neutral'">{{ $opportunity->source->label() }}</x-ui.badge>
                        </div>
                        <div class="grid gap-4 text-sm text-slate-700 md:grid-cols-2">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Último mensaje cliente</div>
                                <div class="mt-1">{{ $opportunity->last_customer_message_at?->format('d/m/Y H:i') ?? 'Sin registro' }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Vencimiento ventana</div>
                                <div class="mt-1">{{ $opportunity->customer_service_window_expires_at?->format('d/m/Y H:i') ?? 'Sin ventana activa' }}</div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Respuesta libre</div>
                                <div class="mt-2">
                                    <x-ui.badge :variant="$opportunity->can_reply_freely ? 'success' : 'warning'">
                                        {{ $opportunity->can_reply_freely ? 'Disponible' : 'Requiere template' }}
                                    </x-ui.badge>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">WhatsApp ID</div>
                                <div class="mt-1 break-all">{{ $opportunity->whatsapp_contact_id ?: 'Sin dato' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 text-lg font-semibold text-slate-900">Notas comerciales</div>
                        <div class="space-y-3">
                            <textarea wire:model.defer="newNote" rows="4" class="w-full rounded-lg border border-slate-200 px-4 py-2" placeholder="Registrá seguimiento, acuerdos o próximos pasos..."></textarea>
                            @error('newNote') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                            <div class="flex justify-end">
                                <button type="button" wire:click="addNote" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-700">Agregar nota</button>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            @forelse($opportunity->notes as $note)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="whitespace-pre-wrap text-sm text-slate-800">{{ $note->content }}</div>
                                    <div class="mt-2 text-xs text-slate-500">{{ $note->byUser?->name ?? 'Sistema' }} · {{ $note->created_at?->format('d/m/Y H:i') }}</div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">
                                    Todavía no hay notas cargadas.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 flex items-start justify-between gap-3">
                            <div>
                                <div class="text-lg font-semibold text-slate-900">Mensajes del canal</div>
                                <p class="mt-1 text-sm text-slate-500">Historial entrante y saliente preparado para el futuro chat unificado.</p>
                            </div>
                            <x-ui.badge variant="info">{{ $opportunity->messages->count() }} mensajes</x-ui.badge>
                        </div>

                        <div class="space-y-3">
                            @forelse($opportunity->messages as $message)
                                <div class="flex {{ $message->direction->value === 'outbound' ? 'justify-end' : 'justify-start' }}">
                                    <div class="max-w-2xl rounded-2xl border px-4 py-3 text-sm shadow-sm {{ $message->direction->value === 'outbound' ? 'border-indigo-200 bg-indigo-50 text-indigo-950' : 'border-slate-200 bg-slate-50 text-slate-800' }}">
                                        <div class="flex items-center gap-2">
                                            <x-ui.badge size="sm" :variant="$message->direction->value === 'outbound' ? 'primary' : 'neutral'">{{ $message->direction->label() }}</x-ui.badge>
                                            <x-ui.badge size="sm" variant="info">{{ $message->type->label() }}</x-ui.badge>
                                        </div>
                                        <div class="mt-3 whitespace-pre-wrap">{{ $message->content ?: 'Mensaje sin texto asociado.' }}</div>
                                        <div class="mt-3 text-xs text-slate-500">{{ $message->messaged_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">
                                    Todavía no hay mensajes almacenados para esta oportunidad.
                                </div>
                            @endforelse
                        </div>

                        <div class="mt-4 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500">
                            La respuesta desde el CRM y los templates se integrarán en la siguiente etapa sobre este mismo historial.
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 text-lg font-semibold text-slate-900">Historial de estados</div>
                        <div class="space-y-3">
                            @forelse($opportunity->statusLogs as $log)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
                                    <div class="font-medium text-slate-900">{{ \App\Enums\Sales\OpportunityStatus::from($log->status)->label() }}</div>
                                    <div class="mt-1 text-xs text-slate-500">{{ $log->byUser?->name ?? 'Sistema' }} · {{ $log->created_at?->format('d/m/Y H:i') }}</div>
                                </div>
                            @empty
                                <div class="text-sm text-slate-500">Sin historial disponible.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 text-lg font-semibold text-slate-900">Conversión a proyecto</div>
                        <p class="text-sm text-slate-600">La conversión crea un proyecto compatible con el flujo actual. Por ahora el proyecto nace en <strong>Venta Cerrada</strong> para no romper Cobros ni Proyectos legacy.</p>
                        @if($convertBlockedReason && !$opportunity->project)
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                {{ $convertBlockedReason }}
                            </div>
                        @endif
                        @if($opportunity->project)
                            <a href="{{ route('proyectos.show', $opportunity->project) }}" class="mt-4 inline-flex w-full justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                Ir al proyecto
                            </a>
                        @else
                            <button type="button" wire:click="convertToProject"
                                @disabled($convertBlockedReason !== null)
                                class="mt-4 w-full rounded-lg px-4 py-2 text-sm disabled:cursor-not-allowed {{ $convertBlockedReason !== null ? 'bg-slate-200 text-slate-600' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                                Convertir en proyecto
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-ui.page-container>
