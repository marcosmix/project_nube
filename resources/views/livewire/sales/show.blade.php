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

                <div wire:key="opportunity-status-panel-{{ $opportunity->id }}-{{ $opportunity->status->value }}" class="mt-5 w-full rounded-3xl border border-slate-200 bg-slate-50/80 p-5 shadow-sm">
                    <div class="flex flex-col gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-slate-900">Siguiente paso comercial</div>
                            <p class="mt-1 text-sm text-slate-500">Elegí una acción disponible para avanzar la oportunidad desde el estado actual.</p>
                        </div>
                        <x-ui.badge :variant="$allowedStatusTransitions->isNotEmpty() ? 'info' : 'warning'">{{ $opportunity->status->label() }}</x-ui.badge>
                    </div>

                    @if($allowedStatusTransitions->isNotEmpty())
                        <div class="mt-4 grid items-stretch gap-3 md:grid-cols-2 xl:grid-cols-3">
                            @foreach($allowedStatusTransitions as $statusOption)
                                @php
                                    $surfaceClasses = match ($statusOption['variant']) {
                                        'success' => 'border-emerald-200 bg-emerald-50/80 hover:border-emerald-300 hover:bg-emerald-50',
                                        'danger' => 'border-rose-200 bg-rose-50/80 hover:border-rose-300 hover:bg-rose-50',
                                        'warning' => 'border-amber-200 bg-amber-50/80 hover:border-amber-300 hover:bg-amber-50',
                                        'accent' => 'border-orange-200 bg-orange-50/80 hover:border-orange-300 hover:bg-orange-50',
                                        default => 'border-indigo-200 bg-indigo-50/80 hover:border-indigo-300 hover:bg-indigo-50',
                                    };
                                @endphp

                                <div wire:key="opportunity-status-option-{{ $opportunity->id }}-{{ $statusOption['value'] }}" class="flex h-full flex-col rounded-2xl border p-4 shadow-sm transition {{ $surfaceClasses }}">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-950">{{ $statusOption['label'] }}</div>
                                            <p class="mt-1 text-xs leading-5 text-slate-600">{{ $statusOption['description'] }}</p>
                                        </div>
                                        <x-ui.badge size="sm" :variant="$statusOption['variant']">Disponible</x-ui.badge>
                                    </div>

                                    <x-ui.button
                                        type="button"
                                        :variant="$statusOption['variant']"
                                        class="mt-4 w-full justify-center"
                                        wire:click="openStatusConfirmation('{{ $statusOption['value'] }}')"
                                    >
                                        {{ $statusOption['buttonLabel'] }}
                                    </x-ui.button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            <div class="font-medium">Flujo finalizado</div>
                            <div class="mt-1">Esta oportunidad está en estado <strong>{{ $opportunity->status->label() }}</strong> y no permite más cambios de estado.</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 p-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 text-lg font-semibold text-slate-900">Datos iniciales</div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 text-sm text-slate-700">
                            <div>
                                <div class="text-xs uppercase tracking-wide text-slate-500">Cliente</div>
                                <div class="mt-1 flex items-center gap-3">
                                    <span>{{ $opportunity->client?->organization_name ?? 'Sin cliente asociado' }}</span>
                                    @if($this->canEditClient)
                                        <button type="button" wire:click="openClientModal" class="ml-2 inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-50">
                                            <svg class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                            Editar
                                        </button>
                                    @endif
                                </div>
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

                    @if($this->canShowCommercialSection())
                        @php
                            $formattedAmount = $this->formattedEstimatedTicketAmount();
                        @endphp
                        <div class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 via-white to-orange-50 p-6 shadow-sm">
                            <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <div class="text-lg font-semibold text-slate-900">Propuesta comercial</div>
                                    <p class="mt-1 text-sm text-slate-600">Desde Calificado podés cargar el monto estimado y respaldar la propuesta con archivos adjuntos.</p>
                                </div>
                                <div class="rounded-2xl border border-amber-200 bg-white/90 px-4 py-3 text-right shadow-sm">
                                    <div class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500">Monto estimado</div>
                                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ $formattedAmount ?? 'Pendiente' }}</div>
                                </div>
                            </div>

                            <div class="space-y-5">
                                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                    <x-ui.label>Monto estimado comercial</x-ui.label>
                                    <x-ui.input type="number" min="0" step="0.01" wire:model.defer="commercialForm.estimated_ticket_amount" placeholder="450000" />
                                    <p class="mt-1 text-xs text-slate-500">Se toma como referencia comercial. Las diferencias por intereses se resolverán después en Cobros.</p>
                                    @error('commercialForm.estimated_ticket_amount') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>

                                    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                        <div class="mb-4 flex items-start justify-between gap-3">
                                            <div>
                                                <div class="text-base font-semibold text-slate-900">Adjuntos comerciales</div>
                                                <p class="mt-1 text-sm text-slate-500">Subí propuestas, versiones iteradas o presentaciones enviadas al lead.</p>
                                            </div>
                                            <x-ui.badge variant="warning">{{ $opportunity->attachments->count() }} archivo{{ $opportunity->attachments->count() === 1 ? '' : 's' }}</x-ui.badge>
                                        </div>

                                        <div class="grid gap-4 md:grid-cols-[minmax(0,0.8fr)_minmax(0,1fr)_auto] md:items-end">
                                            <div>
                                                <x-ui.label>Etiqueta interna</x-ui.label>
                                                <x-ui.input wire:model.defer="commercialForm.attachment_label" placeholder="Propuesta v1 / Presentación inicial" />
                                                @error('commercialForm.attachment_label') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                            </div>
                                            <div>
                                                <x-ui.label>Archivo</x-ui.label>
                                                <x-ui.input type="file" wire:model="attachmentUpload" />
                                                <p class="mt-1 text-xs text-slate-500">Acepta PDF, imágenes y presentaciones hasta 10 MB.</p>
                                                @error('attachmentUpload') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                                @error('attachments') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                            </div>
                                            <x-ui.button type="button" variant="warning" wire:click="saveCommercialData">
                                                Guardar propuesta
                                            </x-ui.button>
                                        </div>

                                        @if($attachmentUpload)
                                            <div class="mt-4 rounded-xl border border-dashed border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                                Archivo listo para subir: <span class="font-medium">{{ $attachmentUpload->getClientOriginalName() }}</span>
                                            </div>
                                        @endif

                                        <div class="mt-5 space-y-3">
                                            @forelse($opportunity->attachments as $attachment)
                                                @php
                                                    $sizeInKb = $attachment->size > 0 ? number_format($attachment->size / 1024, 1, ',', '.') . ' KB' : 'Tamaño desconocido';
                                                    $previewable = $attachment->isImage() || $attachment->isPdf();
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
                                                    Todavía no hay adjuntos comerciales cargados para esta oportunidad.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                            </div>
                        </div>
                    @endif

                    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="mb-4 text-lg font-semibold text-slate-900">Notas comerciales</div>
                        <div class="space-y-4">
                            @forelse($opportunity->notes as $note)
                                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                                    <div class="mb-3 text-[11px] font-medium uppercase tracking-[0.16em] text-slate-500">{{ $note->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }} · {{ $note->byUser?->name ?? 'Sistema' }}</div>
                                    <div class="whitespace-pre-wrap text-sm text-slate-800">{{ $note->content }}</div>
                                </div>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-500">
                                    Todavía no hay notas cargadas.
                                </div>
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
                    @if($this->canShowCommercialSection())
                        <div class="rounded-2xl border border-amber-200 bg-white/90 p-5 shadow-sm">
                            <div class="text-sm font-semibold text-slate-900">Condición para Propuesta enviada</div>
                            <div class="mt-3 space-y-3 text-sm text-slate-600">
                                <div class="flex items-start justify-between gap-3 rounded-xl bg-slate-50 px-3 py-3">
                                    <span>Monto estimado cargado</span>
                                    <span class="font-medium {{ $opportunity->estimated_ticket_amount ? 'text-emerald-700' : 'text-rose-700' }}">{{ $opportunity->estimated_ticket_amount ? 'Listo' : 'Falta' }}</span>
                                </div>
                                <div class="flex items-start justify-between gap-3 rounded-xl bg-slate-50 px-3 py-3">
                                    <span>Adjunto comercial disponible</span>
                                    <span class="font-medium {{ $opportunity->attachments->isNotEmpty() ? 'text-emerald-700' : 'text-rose-700' }}">{{ $opportunity->attachments->isNotEmpty() ? 'Listo' : 'Falta' }}</span>
                                </div>
                            </div>
                            @if($pendingStatus === 'proposal_sent' && ($opportunity->estimated_ticket_amount === null || $opportunity->attachments->isEmpty()))
                                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                    Antes de avanzar, guardá esta sección para dejar trazabilidad comercial de la propuesta enviada.
                                </div>
                            @endif
                            @error('estimated_ticket_amount') <div class="mt-3 text-xs text-red-600">{{ $message }}</div> @enderror
                        </div>
                    @endif

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
                        <div class="mb-4 text-lg font-semibold text-slate-900">Pasar venta a Operaciones</div>
                        <p class="text-sm text-slate-600">La venta se enviará al módulo de Operaciones. Por ahora nace en <strong>Venta Cerrada</strong> para no romper Cobros ni el módulo legacy.</p>
                        @if($convertBlockedReason && !$opportunity->project)
                            <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                {{ $convertBlockedReason }}
                            </div>
                        @endif
                        @if($opportunity->project)
                            <a href="{{ route('proyectos.show', $opportunity->project) }}" class="mt-4 inline-flex w-full justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                Ir a la operación
                            </a>
                        @else
                            <button type="button" wire:click="convertToProject"
                                @disabled($convertBlockedReason !== null)
                                class="mt-4 w-full rounded-lg px-4 py-2 text-sm disabled:cursor-not-allowed {{ $convertBlockedReason !== null ? 'bg-slate-200 text-slate-600' : 'bg-emerald-600 text-white hover:bg-emerald-700' }}">
                                Pasar venta a Operaciones
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($showClientModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4">
            <div class="w-full max-w-3xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">Editar cliente de la oportunidad</h3>
                        <p class="text-sm text-slate-500">Seleccioná cómo asociar el cliente a esta oportunidad.</p>
                    </div>
                    <button type="button" wire:click="closeClientModal" class="rounded-2xl px-3 py-2 text-slate-500 transition hover:bg-slate-100">Cerrar</button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto px-6 py-6">
                    <div class="space-y-6">
                        <x-ui.panel tone="subtle">
                            <div class="mb-4 text-base font-semibold text-slate-900">Resolución de cliente</div>
                            <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                                <x-ui.radio-card :checked="$clientForm['mode'] === 'unlinked'">
                                    <input type="radio" wire:model.live="clientForm.mode" value="unlinked" class="sr-only">
                                    <div class="text-sm font-medium text-slate-900">Sin vincular</div>
                                    <div class="mt-1 text-xs text-slate-500">La oportunidad queda sin cliente asociado por ahora.</div>
                                </x-ui.radio-card>

                                <x-ui.radio-card :checked="$clientForm['mode'] === 'link_existing'">
                                    <input type="radio" wire:model.live="clientForm.mode" value="link_existing" class="sr-only">
                                    <div class="text-sm font-medium text-slate-900">Vincular cliente existente</div>
                                    <div class="mt-1 text-xs text-slate-500">Usá un cliente ya cargado en el sistema.</div>
                                </x-ui.radio-card>

                                <x-ui.radio-card :checked="$clientForm['mode'] === 'create_new'">
                                    <input type="radio" wire:model.live="clientForm.mode" value="create_new" class="sr-only">
                                    <div class="text-sm font-medium text-slate-900">Crear cliente nuevo</div>
                                    <div class="mt-1 text-xs text-slate-500">Aprovechá los datos de referencia y completá solo lo requerido.</div>
                                </x-ui.radio-card>
                            </div>

                            @if($clientForm['mode'] === 'link_existing')
                                <div class="mt-5">
                                    <x-ui.label>Cliente existente</x-ui.label>
                                    <x-ui.select wire:model.live="clientForm.client_id">
                                        <option value="">Seleccioná un cliente</option>
                                        @foreach($this->clients as $client)
                                            <option value="{{ $client->id }}">
                                                {{ $client->organization_name }}{{ $client->contact ? ' · '.$client->contact->fullName : '' }}
                                            </option>
                                        @endforeach
                                    </x-ui.select>
                                    @error('clientForm.client_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            @if($clientForm['mode'] === 'create_new')
                                <x-ui.panel tone="success" padding="sm" class="mt-5 text-sm text-emerald-950 shadow-none">
                                    Los datos de teléfono y email se completan con la referencia si estaban vacíos. Nombre y apellido se cargan manualmente para evitar errores.
                                    <x-ui.button type="button" size="sm" variant="success" wire:click="fillNewClientFromReference" class="ml-2">
                                        Usar datos de referencia
                                    </x-ui.button>
                                </x-ui.panel>

                                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <x-ui.label>Nombre *</x-ui.label>
                                        <x-ui.input wire:model.live.debounce.250ms="clientForm.new_client_first_name" />
                                        @error('clientForm.new_client_first_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <x-ui.label>Apellido *</x-ui.label>
                                        <x-ui.input wire:model.live.debounce.250ms="clientForm.new_client_last_name" />
                                        @error('clientForm.new_client_last_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                        <span class="font-medium text-slate-900">Nombre de referencia:</span>
                                        {{ $opportunity->contact_name ?: 'Sin dato cargado' }}
                                    </div>

                                    <div>
                                        <x-ui.label>Email *</x-ui.label>
                                        <x-ui.input wire:model.live.debounce.250ms="clientForm.new_client_email" />
                                        @error('clientForm.new_client_email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <x-ui.label>Teléfono</x-ui.label>
                                        <x-ui.input wire:model.live.debounce.250ms="clientForm.new_client_phone" />
                                        @error('clientForm.new_client_phone') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="md:col-span-2">
                                        <x-ui.label>Organización</x-ui.label>
                                        <x-ui.input wire:model.live.debounce.250ms="clientForm.new_client_organization_name" />
                                        @error('clientForm.new_client_organization_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <x-ui.label>Industria</x-ui.label>
                                        <x-ui.input wire:model.live.debounce.250ms="clientForm.new_client_industry" />
                                        @error('clientForm.new_client_industry') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <x-ui.label>Tamaño de empresa</x-ui.label>
                                        <x-ui.select wire:model.live="clientForm.new_client_company_size">
                                            <option value="small">Pequeña</option>
                                            <option value="medium">Mediana</option>
                                            <option value="large">Grande</option>
                                        </x-ui.select>
                                        @error('clientForm.new_client_company_size') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            @endif
                        </x-ui.panel>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50/80 px-6 py-4">
                    <x-ui.button type="button" variant="secondary" wire:click="closeClientModal">Cancelar</x-ui.button>
                    <x-ui.button type="button" wire:click="saveClient">Guardar cliente</x-ui.button>
                </div>
            </div>
        </div>
    @endif

    @if($showStatusConfirmationModal && $pendingStatusTransition)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4">
            <div class="w-full max-w-xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70">
                <div class="border-b border-slate-200 px-6 py-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Confirmar transición</div>
                            <h3 class="mt-1 text-lg font-semibold text-slate-950">Cambiar a {{ $pendingStatusTransition['label'] }}</h3>
                            <p class="mt-2 text-sm text-slate-500">{{ $pendingStatusTransition['description'] }}</p>
                        </div>
                        <x-ui.badge :variant="$pendingStatusTransition['variant']">{{ $pendingStatusTransition['label'] }}</x-ui.badge>
                    </div>
                </div>

                <div class="space-y-4 px-6 py-6">
                    @if($pendingStatusTransition['requiresCommercialData'])
                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            Si hay monto o adjuntos pendientes, se guardarán antes de cambiar el estado. Después de confirmar, el sistema validará cliente, importe y al menos un archivo comercial.
                        </div>
                    @else
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                            {{ $pendingStatusTransition['confirmationHint'] }}
                        </div>
                    @endif

                    @if($errors->has('status') || $errors->has('client_id') || $errors->has('estimated_ticket_amount') || $errors->has('attachments') || $errors->has('commercialForm.estimated_ticket_amount') || $errors->has('commercialForm.attachment_label') || $errors->has('attachmentUpload'))
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                            <div class="font-medium">No se pudo completar el cambio</div>
                            <ul class="mt-2 list-disc space-y-1 pl-5">
                                @foreach(['status', 'client_id', 'estimated_ticket_amount', 'attachments', 'commercialForm.estimated_ticket_amount', 'commercialForm.attachment_label', 'attachmentUpload'] as $errorKey)
                                    @error($errorKey)
                                        <li>{{ $message }}</li>
                                    @enderror
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-slate-50/80 px-6 py-4">
                    <x-ui.button type="button" variant="secondary" wire:click="closeStatusConfirmation">Cancelar</x-ui.button>
                    <x-ui.button type="button" :variant="$pendingStatusTransition['variant']" wire:click="confirmStatusChange">Confirmar cambio</x-ui.button>
                </div>
            </div>
        </div>
    @endif
</x-ui.page-container>
