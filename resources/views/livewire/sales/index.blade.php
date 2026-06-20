@php
    $statusBadgeVariants = [
        'new' => 'info',
        'contacted' => 'primary',
        'qualified' => 'accent',
        'proposal_sent' => 'warning',
        'won' => 'success',
        'lost' => 'danger',
    ];
@endphp

<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-3">
        <x-ui.stat-card
            label="Oportunidades"
            :value="number_format($opportunities->total(), 0, ',', '.')"
            hint="Registros filtrados dentro del pipeline comercial."
            tone="primary"
        />
        <x-ui.stat-card
            label="Con responsable"
            :value="number_format($opportunities->getCollection()->whereNotNull('responsible_user_id')->count(), 0, ',', '.')"
            hint="Ayuda a detectar oportunidades sin seguimiento claro."
            tone="accent"
        />
        <x-ui.stat-card
            label="Con avances recientes"
            :value="number_format($opportunities->getCollection()->filter(fn ($item) => $item->notes->isNotEmpty() || $item->statusLogs->isNotEmpty())->count(), 0, ',', '.')"
            hint="Consultas con actividad registrada en la pagina actual."
            tone="success"
        />
    </div>

    <x-ui.card class="space-y-5 bg-slate-50/70">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-950">Vista comercial</h2>
                <p class="text-sm text-slate-500">Filtra por etapa, origen o responsable para ordenar el seguimiento diario.</p>
            </div>

            <x-ui.button type="button" variant="success" wire:click="openCreate">
                Nueva oportunidad
            </x-ui.button>
        </div>

        <div class="grid flex-1 grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <x-ui.label for="sales-search">Buscar</x-ui.label>
                <x-ui.input id="sales-search" wire:model.live.debounce.300ms="search" type="text" placeholder="Consulta, contacto o cliente..." />
            </div>

            <div>
                <x-ui.label for="sales-status">Estado</x-ui.label>
                <x-ui.select id="sales-status" wire:model.live="status">
                    <option value="">Todos</option>
                    @foreach($statusOptions as $statusOption)
                        <option value="{{ $statusOption['value'] }}">{{ $statusOption['label'] }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <x-ui.label for="sales-source">Origen</x-ui.label>
                <x-ui.select id="sales-source" wire:model.live="source">
                    <option value="">Todos</option>
                    @foreach($sourceOptions as $sourceOption)
                        <option value="{{ $sourceOption['value'] }}">{{ $sourceOption['label'] }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div>
                <x-ui.label for="sales-responsible">Responsable</x-ui.label>
                <x-ui.select id="sales-responsible" wire:model.live="responsibleUserId">
                    <option value="">Todos</option>
                    @foreach($this->users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </x-ui.select>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card padding="none" class="overflow-hidden">
        <x-ui.table>
            <x-slot:head>
                    <th class="px-5 py-4">Consulta</th>
                    <th class="px-5 py-4">Contacto</th>
                    <th class="px-5 py-4">Origen</th>
                    <th class="px-5 py-4">Responsable</th>
                    <th class="px-5 py-4">Primer contacto</th>
                    <th class="px-5 py-4">Ultimo avance</th>
                    <th class="px-5 py-4">Propuesta</th>
                    <th class="px-5 py-4">Estado</th>
                    <th class="px-5 py-4 text-right">Acciones</th>
            </x-slot:head>

                @forelse($opportunities as $opportunity)
                    @php
                        $latestNote = $opportunity->notes->first();
                        $latestStatusLog = $opportunity->statusLogs->first();
                        $statusKey = $opportunity->status->value ?? null;
                    @endphp
                    <tr class="align-top transition hover:bg-slate-50/80">
                        <td class="px-5 py-5">
                            <div class="font-medium text-slate-900">{{ $opportunity->name }}</div>
                            <div class="mt-2 text-xs text-slate-500">{{ $opportunity->client?->organization_name ?? 'Sin cliente asociado' }}</div>
                        </td>
                        <td class="px-5 py-5">
                            <div>{{ $opportunity->display_contact }}</div>
                            <div class="mt-2 text-xs text-slate-500">{{ $opportunity->contact_email ?: ($opportunity->contact_phone ?: 'Sin dato adicional') }}</div>
                        </td>
                        <td class="px-5 py-5">{{ $opportunity->source->label() }}</td>
                        <td class="px-5 py-5">{{ $opportunity->responsibleUser?->name ?? 'Sin asignar' }}</td>
                        <td class="px-5 py-5">{{ $opportunity->first_contact_at?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-5 py-5">
                            @if($latestNote)
                                <div class="max-w-xs truncate text-slate-800">{{ $latestNote->content }}</div>
                                <div class="mt-2 text-xs text-slate-500">
                                    {{ $latestNote->byUser?->name ?? 'Sistema' }} · {{ $latestNote->created_at?->format('d/m/Y H:i') }}
                                </div>
                            @elseif($latestStatusLog)
                                <div class="text-slate-800">Estado: {{ $latestStatusLog->status }}</div>
                                <div class="mt-2 text-xs text-slate-500">{{ $latestStatusLog->created_at?->format('d/m/Y H:i') }}</div>
                            @else
                                <span class="text-slate-500">Sin avances</span>
                            @endif
                        </td>
                        <td class="px-5 py-5">
                            @if($opportunity->estimated_ticket_amount || $opportunity->attachments_count > 0)
                                <div class="space-y-2 text-xs text-slate-600">
                                    <div class="rounded-xl bg-amber-50 px-3 py-2 text-amber-900">
                                        <span class="font-semibold">Monto:</span>
                                        {{ $opportunity->estimated_ticket_amount ? '$'.number_format((float) $opportunity->estimated_ticket_amount, 2, ',', '.') : 'Pendiente' }}
                                    </div>
                                    <div>
                                        <span class="font-semibold text-slate-700">Adjuntos:</span>
                                        {{ $opportunity->attachments_count }}
                                    </div>
                                </div>
                            @else
                                <span class="text-slate-400">Sin propuesta cargada</span>
                            @endif
                        </td>
                        <td class="px-5 py-5">
                            <x-ui.badge :variant="$statusBadgeVariants[$statusKey] ?? 'neutral'">
                                {{ $opportunity->status->label() }}
                            </x-ui.badge>
                        </td>
                        <td class="px-5 py-5 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" wire:click="openQuickNote({{ $opportunity->id }})" title="Agregar nota comercial" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-sky-200 bg-sky-50 text-sky-700 transition hover:border-sky-300 hover:bg-sky-100 hover:text-sky-800">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                </button>
                                <a href="{{ route('ventas.show', $opportunity) }}" title="Ver detalle de la oportunidad" class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border border-emerald-200 bg-emerald-50 text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100 hover:text-emerald-800">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-slate-500">
                            No hay oportunidades para mostrar.
                        </td>
                    </tr>
                @endforelse
        </x-ui.table>
    </x-ui.card>

    <div class="pt-1">
        {{ $opportunities->links() }}
    </div>

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/45 p-3 sm:p-4">
            <div class="flex min-h-full items-start justify-center">
            <div class="my-4 flex w-full max-w-6xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70" style="max-height: calc(100vh - 2rem);">
                <div class="shrink-0 border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
                    <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-950">Nueva oportunidad</h3>
                        <p class="text-sm text-slate-500">Primero registrá la consulta y después resolvé si se vincula o crea un cliente.</p>
                    </div>
                    <button type="button" wire:click="closeCreate" class="rounded-2xl px-3 py-2 text-slate-500 transition hover:bg-slate-100">Cerrar</button>
                    </div>
                </div>

                <div class="shrink-0 border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" wire:click="goToCreateStep(1)"
                            class="rounded-xl border px-4 py-3 text-left {{ $createStep === 1 ? 'border-sky-300 bg-sky-50 text-sky-900' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                            <div class="text-xs font-semibold uppercase tracking-wide">Paso 1</div>
                            <div class="mt-1 text-sm font-medium">Datos de consulta</div>
                        </button>
                        <button type="button" wire:click="goToCreateStep(2)"
                            class="rounded-xl border px-4 py-3 text-left {{ $createStep === 2 ? 'border-sky-300 bg-sky-50 text-sky-900' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
                            <div class="text-xs font-semibold uppercase tracking-wide">Paso 2</div>
                            <div class="mt-1 text-sm font-medium">Datos de cliente</div>
                        </button>
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto px-5 py-5 sm:px-6">
                    @if($createStep === 1)
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <x-ui.label>Referencia de consulta *</x-ui.label>
                                <x-ui.input wire:model.defer="createForm.name" />
                                @error('createForm.name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <x-ui.label>Fecha de primer contacto</x-ui.label>
                                <x-ui.input type="date" wire:model.defer="createForm.first_contact_at" />
                                @error('createForm.first_contact_at') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <x-ui.label>Origen *</x-ui.label>
                                <x-ui.select wire:model.defer="createForm.source">
                                    @foreach($sourceOptions as $sourceOption)
                                        <option value="{{ $sourceOption['value'] }}">{{ $sourceOption['label'] }}</option>
                                    @endforeach
                                </x-ui.select>
                                @error('createForm.source') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <x-ui.label>Estado inicial *</x-ui.label>
                                <x-ui.select wire:model.defer="createForm.status">
                                    @foreach($initialStatusOptions as $statusOption)
                                        <option value="{{ $statusOption['value'] }}">{{ $statusOption['label'] }}</option>
                                    @endforeach
                                </x-ui.select>
                                @error('createForm.status') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <x-ui.label>Responsable</x-ui.label>
                                <x-ui.select wire:model.defer="createForm.responsible_user_id">
                                    <option value="">Asignarme automáticamente</option>
                                    @foreach($this->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </x-ui.select>
                                @error('createForm.responsible_user_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-ui.label>Notas comerciales</x-ui.label>
                                <x-ui.textarea wire:model.defer="createForm.initial_note" rows="4"></x-ui.textarea>
                                @error('createForm.initial_note') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <details class="group overflow-hidden rounded-2xl border border-slate-200 bg-slate-50/80">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-4 text-left">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900">Chat de origen</div>
                                            <p class="mt-1 text-xs text-slate-500">Reservado para futuras integraciones de WhatsApp o chatbot. No se completa al crear una oportunidad manual.</p>
                                        </div>
                                        <span class="rounded-full bg-white px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-slate-500">Placeholder</span>
                                    </summary>
                                    <div class="border-t border-slate-200 bg-white px-4 py-4">
                                        <div class="space-y-3">
                                            <div class="flex justify-start">
                                                <div class="max-w-xl rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 shadow-sm">
                                                    Todavía no hay conversación importada desde un canal externo.
                                                </div>
                                            </div>
                                            <div class="flex justify-end">
                                                <div class="max-w-xl rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-950 shadow-sm">
                                                    Cuando se integre el bot, este espacio mostrará el contexto inicial del lead en formato chat.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </details>
                            </div>
                        </div>
                    @endif

                    @if($createStep === 2)
                        @php
                            $referencePreview = $this->referencePreview();
                            $selectedClientPreview = $this->selectedClientPreview();
                            $newClientPreview = $this->newClientPreview();
                        @endphp
                        <div class="grid grid-cols-1 gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(320px,0.8fr)]">
                            <div class="space-y-6">
                                <x-ui.panel tone="subtle">
                                    <div class="mb-4 text-base font-semibold text-slate-900">Datos de referencia</div>
                                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <x-ui.label>Nombre de referencia</x-ui.label>
                                        <x-ui.input wire:model.live.debounce.250ms="createForm.contact_name" />
                                        @error('createForm.contact_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <x-ui.label>Teléfono de referencia</x-ui.label>
                                        <x-ui.input wire:model.live.debounce.250ms="createForm.contact_phone" />
                                        @error('createForm.contact_phone') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <x-ui.label>Email de referencia</x-ui.label>
                                        <x-ui.input wire:model.live.debounce.250ms="createForm.contact_email" />
                                        @error('createForm.contact_email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <x-ui.label>Usuario / red social de referencia</x-ui.label>
                                        <x-ui.input wire:model.live.debounce.250ms="createForm.contact_handle" />
                                        @error('createForm.contact_handle') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>
                                </x-ui.panel>
                            </div>

                                <x-ui.panel>
                                    <div class="mb-4 text-base font-semibold text-slate-900">Resolución de cliente</div>
                                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                                    <x-ui.radio-card :checked="$createForm['client_mode'] === 'unlinked'">
                                        <input type="radio" wire:model.live="createForm.client_mode" value="unlinked" class="sr-only">
                                        <div class="text-sm font-medium text-slate-900">Sin vincular</div>
                                        <div class="mt-1 text-xs text-slate-500">La oportunidad queda abierta sin cliente asociado por ahora.</div>
                                    </x-ui.radio-card>

                                    <x-ui.radio-card :checked="$createForm['client_mode'] === 'link_existing'">
                                        <input type="radio" wire:model.live="createForm.client_mode" value="link_existing" class="sr-only">
                                        <div class="text-sm font-medium text-slate-900">Vincular cliente existente</div>
                                        <div class="mt-1 text-xs text-slate-500">Usá un cliente ya cargado en el sistema.</div>
                                    </x-ui.radio-card>

                                    <x-ui.radio-card :checked="$createForm['client_mode'] === 'create_new'">
                                        <input type="radio" wire:model.live="createForm.client_mode" value="create_new" class="sr-only">
                                        <div class="text-sm font-medium text-slate-900">Crear cliente nuevo</div>
                                        <div class="mt-1 text-xs text-slate-500">Aprovechá los datos de referencia y completá solo lo requerido.</div>
                                    </x-ui.radio-card>
                                    </div>

                                    @if($createForm['client_mode'] === 'link_existing')
                                        <div class="mt-5">
                                            <x-ui.label>Cliente existente</x-ui.label>
                                            <x-ui.select wire:model.live="createForm.client_id">
                                                <option value="">Seleccioná un cliente</option>
                                                @foreach($this->clients as $client)
                                                    <option value="{{ $client->id }}">
                                                        {{ $client->organization_name }}{{ $client->contact ? ' · '.$client->contact->full_name : '' }}
                                                    </option>
                                                @endforeach
                                            </x-ui.select>
                                            @error('createForm.client_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>
                                    @endif

                                    @if($createForm['client_mode'] === 'create_new')
                                        <x-ui.panel tone="success" padding="sm" class="mt-5 text-sm text-emerald-950 shadow-none">
                                            Los datos de teléfono y email se completan con la referencia si estaban vacíos. Nombre y apellido se cargan manualmente para evitar errores.
                                            <x-ui.button type="button" size="sm" variant="success" wire:click="fillNewClientFromReference" class="ml-2">
                                                Usar datos de referencia
                                            </x-ui.button>
                                        </x-ui.panel>

                                        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <x-ui.label>Nombre *</x-ui.label>
                                            <x-ui.input wire:model.live.debounce.250ms="createForm.new_client_first_name" />
                                            @error('createForm.new_client_first_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>

                                        <div>
                                            <x-ui.label>Apellido *</x-ui.label>
                                            <x-ui.input wire:model.live.debounce.250ms="createForm.new_client_last_name" />
                                            @error('createForm.new_client_last_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                            <span class="font-medium text-slate-900">Nombre de referencia:</span>
                                            {{ $createForm['contact_name'] ?: 'Sin dato cargado todavía' }}
                                        </div>

                                        <div>
                                            <x-ui.label>Email *</x-ui.label>
                                            <x-ui.input wire:model.live.debounce.250ms="createForm.new_client_email" />
                                            @error('createForm.new_client_email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>

                                        <div>
                                            <x-ui.label>Teléfono</x-ui.label>
                                            <x-ui.input wire:model.live.debounce.250ms="createForm.new_client_phone" />
                                            @error('createForm.new_client_phone') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>

                                        <div>
                                            <x-ui.label>Nombre de empresa *</x-ui.label>
                                            <x-ui.input wire:model.live.debounce.250ms="createForm.new_client_organization_name" />
                                            @error('createForm.new_client_organization_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>

                                        <div>
                                            <x-ui.label>Tamaño *</x-ui.label>
                                            <x-ui.select wire:model.live="createForm.new_client_company_size">
                                                <option value="small">Pequeña</option>
                                                <option value="medium">Mediana</option>
                                                <option value="large">Grande</option>
                                            </x-ui.select>
                                            @error('createForm.new_client_company_size') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="md:col-span-2">
                                            <x-ui.label>Industria</x-ui.label>
                                            <x-ui.input wire:model.live.debounce.250ms="createForm.new_client_industry" />
                                            @error('createForm.new_client_industry') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    @endif
                                </x-ui.panel>

                            </div>

                            <div class="space-y-6 xl:sticky xl:top-4 xl:self-start">
                                <x-ui.panel>
                                    <div class="mb-4 text-base font-semibold text-slate-900">Resumen de referencia</div>
                                    <div class="space-y-3 text-sm text-slate-700">
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500">Nombre</div>
                                            <div class="mt-1">{{ $referencePreview['name'] !== '' ? $referencePreview['name'] : 'Sin dato cargado todavía' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500">Teléfono</div>
                                            <div class="mt-1">{{ $referencePreview['phone'] !== '' ? $referencePreview['phone'] : '—' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500">Email</div>
                                            <div class="mt-1">{{ $referencePreview['email'] !== '' ? $referencePreview['email'] : '—' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500">Usuario / red social</div>
                                            <div class="mt-1">{{ $referencePreview['handle'] !== '' ? $referencePreview['handle'] : '—' }}</div>
                                        </div>
                                    </div>
                                </x-ui.panel>

                                @if($createForm['client_mode'] === 'link_existing')
                                    <x-ui.panel tone="info">
                                        <div class="mb-4 text-base font-semibold text-sky-950">Cliente vinculado</div>
                                        @if($selectedClientPreview)
                                            <div class="flex items-start gap-4">
                                                @if($selectedClientPreview->company_logo)
                                                    <img
                                                        src="{{ \Illuminate\Support\Str::startsWith($selectedClientPreview->company_logo, ['http://', 'https://', '/']) ? $selectedClientPreview->company_logo : \Illuminate\Support\Facades\Storage::url($selectedClientPreview->company_logo) }}"
                                                        alt="Logo de {{ $selectedClientPreview->organization_name }}"
                                                        class="h-14 w-14 rounded-xl border border-sky-200 object-cover bg-white"
                                                    >
                                                @else
                                                    <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-sky-200 text-lg font-semibold text-sky-900">
                                                        {{ strtoupper(substr($selectedClientPreview->organization_name, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div class="min-w-0 flex-1 space-y-3 text-sm text-sky-950">
                                                    <div>
                                                        <div class="font-semibold">{{ $selectedClientPreview->organization_name }}</div>
                                                        <div class="text-sky-700">{{ $selectedClientPreview->contact?->full_name ?? 'Sin contacto principal' }}</div>
                                                    </div>
                                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-sky-700">Email</div>
                                                            <div>{{ $selectedClientPreview->contact?->email ?? '—' }}</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-sky-700">Teléfono</div>
                                                            <div>{{ $selectedClientPreview->contact?->phone ?? '—' }}</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-sky-700">Industria</div>
                                                            <div>{{ $selectedClientPreview->industry ?? '—' }}</div>
                                                        </div>
                                                        <div>
                                                            <div class="text-xs uppercase tracking-wide text-sky-700">Tamaño</div>
                                                            <div>{{ $selectedClientPreview->company_size_label }}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="rounded-xl border border-dashed border-sky-300 bg-white/70 px-4 py-5 text-sm text-sky-800">
                                                Seleccioná un cliente para ver su ficha resumida sin editar.
                                            </div>
                                        @endif
                                    </x-ui.panel>
                                @endif

                                @if($createForm['client_mode'] === 'create_new' && $newClientPreview)
                                    <x-ui.panel tone="success">
                                        <div class="mb-4 text-base font-semibold text-emerald-950">Cliente nuevo a crear</div>
                                        <div class="flex items-start gap-4">
                                            <div class="flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-200 text-lg font-semibold text-emerald-950">
                                                {{ strtoupper(substr($newClientPreview['organization_name'] !== '' ? $newClientPreview['organization_name'] : ($newClientPreview['full_name'] !== '' ? $newClientPreview['full_name'] : 'N'), 0, 1)) }}
                                            </div>
                                            <div class="min-w-0 flex-1 space-y-3 text-sm text-emerald-950">
                                                <div>
                                                    <div class="font-semibold">{{ $newClientPreview['organization_name'] !== '' ? $newClientPreview['organization_name'] : 'Empresa pendiente' }}</div>
                                                    <div class="text-emerald-800">{{ $newClientPreview['full_name'] !== '' ? $newClientPreview['full_name'] : 'Nombre y apellido pendientes' }}</div>
                                                </div>
                                                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-emerald-800">Email</div>
                                                        <div>{{ $newClientPreview['email'] !== '' ? $newClientPreview['email'] : '—' }}</div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-emerald-800">Teléfono</div>
                                                        <div>{{ $newClientPreview['phone'] !== '' ? $newClientPreview['phone'] : '—' }}</div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-emerald-800">Industria</div>
                                                        <div>{{ $newClientPreview['industry'] !== '' ? $newClientPreview['industry'] : '—' }}</div>
                                                    </div>
                                                    <div>
                                                        <div class="text-xs uppercase tracking-wide text-emerald-800">Tamaño</div>
                                                        <div>{{ $newClientPreview['company_size_label'] }}</div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-xs uppercase tracking-wide text-emerald-800">Contexto que se guardará en notas</div>
                                                    <div class="mt-1 whitespace-pre-wrap rounded-xl bg-white/80 px-3 py-3 text-emerald-950">
                                                        {{ $newClientPreview['notes'] ?: 'Todavía no hay contexto adicional para guardar.' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </x-ui.panel>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="shrink-0 border-t border-slate-200 bg-white px-5 py-4 sm:px-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        @if($createStep === 2)
                            <x-ui.button type="button" variant="secondary" wire:click="previousCreateStep">Volver</x-ui.button>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <x-ui.button type="button" variant="secondary" wire:click="closeCreate">Cancelar</x-ui.button>
                        @if($createStep === 1)
                            <x-ui.button type="button" wire:click="nextCreateStep">Continuar con cliente</x-ui.button>
                        @else
                            <x-ui.button type="button" variant="success" wire:click="create">Guardar oportunidad</x-ui.button>
                        @endif
                    </div>
                </div>
                </div>
            </div>
            </div>
        </div>
    @endif

    @if($showNoteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4">
            <div class="w-full max-w-xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Agregar nota comercial</h3>
                </div>
                <div class="p-6">
                    <x-ui.textarea wire:model.defer="noteContent" rows="5" placeholder="Ej: Cliente pidió seguimiento el jueves..."></x-ui.textarea>
                    @error('noteContent') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4">
                    <x-ui.button type="button" variant="secondary" wire:click="closeQuickNote">Cancelar</x-ui.button>
                    <x-ui.button type="button" wire:click="saveQuickNote">Guardar nota</x-ui.button>
                </div>
            </div>
        </div>
    @endif
</div>
