<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900">Oportunidades</h1>
        <p class="text-sm text-slate-500">Centralizá el trabajo comercial sin mezclarlo con la ejecución operativa.</p>
    </div>

    <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div class="grid flex-1 grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Buscar</label>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Consulta, contacto o cliente..."
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none">
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Estado</label>
                <select wire:model.live="status"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none">
                    <option value="">Todos</option>
                    @foreach($statusOptions as $statusOption)
                        <option value="{{ $statusOption['value'] }}">{{ $statusOption['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Origen</label>
                <select wire:model.live="source"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none">
                    <option value="">Todos</option>
                    @foreach($sourceOptions as $sourceOption)
                        <option value="{{ $sourceOption['value'] }}">{{ $sourceOption['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Responsable</label>
                <select wire:model.live="responsibleUserId"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none">
                    <option value="">Todos</option>
                    @foreach($this->users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <button type="button" wire:click="openCreate"
                class="inline-flex rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-800 transition hover:border-emerald-300 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-200">
                Nueva oportunidad
            </button>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <th class="px-4 py-3">Consulta</th>
                    <th class="px-4 py-3">Contacto</th>
                    <th class="px-4 py-3">Origen</th>
                    <th class="px-4 py-3">Responsable</th>
                    <th class="px-4 py-3">Primer contacto</th>
                    <th class="px-4 py-3">Ultimo avance</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white text-sm text-slate-700">
                @forelse($opportunities as $opportunity)
                    @php
                        $latestNote = $opportunity->notes->first();
                        $latestStatusLog = $opportunity->statusLogs->first();
                    @endphp
                    <tr class="hover:bg-slate-50/70">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $opportunity->name }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $opportunity->client?->organization_name ?? 'Sin cliente asociado' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ $opportunity->display_contact }}</div>
                            <div class="mt-1 text-xs text-slate-500">{{ $opportunity->contact_email ?: ($opportunity->contact_phone ?: 'Sin dato adicional') }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $opportunity->source->label() }}</td>
                        <td class="px-4 py-3">{{ $opportunity->responsibleUser?->name ?? 'Sin asignar' }}</td>
                        <td class="px-4 py-3">{{ $opportunity->first_contact_at?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($latestNote)
                                <div class="max-w-xs truncate text-slate-800">{{ $latestNote->content }}</div>
                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $latestNote->byUser?->name ?? 'Sistema' }} · {{ $latestNote->created_at?->format('d/m/Y H:i') }}
                                </div>
                            @elseif($latestStatusLog)
                                <div class="text-slate-800">Estado: {{ $latestStatusLog->status }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $latestStatusLog->created_at?->format('d/m/Y H:i') }}</div>
                            @else
                                <span class="text-slate-500">Sin avances</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700">
                                {{ $opportunity->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <button type="button" wire:click="openQuickNote({{ $opportunity->id }})"
                                    class="inline-flex rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    Agregar nota
                                </button>
                                <a href="{{ route('ventas.show', $opportunity) }}"
                                    class="inline-flex rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                    Ver detalle
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">
                            No hay oportunidades para mostrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $opportunities->links() }}
    </div>

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4">
            <div class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">Nueva oportunidad</h3>
                        <p class="text-sm text-slate-500">Primero registrá la consulta y después resolvé si se vincula o crea un cliente.</p>
                    </div>
                    <button type="button" wire:click="closeCreate" class="rounded-lg px-3 py-2 text-slate-500 hover:bg-slate-100">Cerrar</button>
                </div>

                <div class="border-b border-slate-200 px-6 py-4">
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

                <div class="max-h-[80vh] overflow-y-auto p-6">
                    @if($createStep === 1)
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="text-sm text-slate-600">Referencia de consulta *</label>
                                <input wire:model.defer="createForm.name" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2" />
                                @error('createForm.name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="text-sm text-slate-600">Fecha de primer contacto</label>
                                <input type="date" wire:model.defer="createForm.first_contact_at" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2" />
                                @error('createForm.first_contact_at') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="text-sm text-slate-600">Origen *</label>
                                <select wire:model.defer="createForm.source" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2">
                                    @foreach($sourceOptions as $sourceOption)
                                        <option value="{{ $sourceOption['value'] }}">{{ $sourceOption['label'] }}</option>
                                    @endforeach
                                </select>
                                @error('createForm.source') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="text-sm text-slate-600">Estado inicial *</label>
                                <select wire:model.defer="createForm.status" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2">
                                    @foreach($initialStatusOptions as $statusOption)
                                        <option value="{{ $statusOption['value'] }}">{{ $statusOption['label'] }}</option>
                                    @endforeach
                                </select>
                                @error('createForm.status') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="text-sm text-slate-600">Responsable</label>
                                <select wire:model.defer="createForm.responsible_user_id" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2">
                                    <option value="">Asignarme automáticamente</option>
                                    @foreach($this->users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('createForm.responsible_user_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="text-sm text-slate-600">Mensaje inicial</label>
                                <textarea wire:model.defer="createForm.initial_message" rows="4" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2"></textarea>
                                @error('createForm.initial_message') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="text-sm text-slate-600">Notas comerciales</label>
                                <textarea wire:model.defer="createForm.initial_note" rows="4" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2"></textarea>
                                @error('createForm.initial_note') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
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
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                                <div class="mb-4 text-base font-semibold text-slate-900">Datos de referencia</div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <label class="text-sm text-slate-600">Nombre de referencia</label>
                                        <input wire:model.live.debounce.250ms="createForm.contact_name" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-slate-900" />
                                        @error('createForm.contact_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm text-slate-600">Teléfono de referencia</label>
                                        <input wire:model.live.debounce.250ms="createForm.contact_phone" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-slate-900" />
                                        @error('createForm.contact_phone') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm text-slate-600">Email de referencia</label>
                                        <input wire:model.live.debounce.250ms="createForm.contact_email" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-slate-900" />
                                        @error('createForm.contact_email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>

                                    <div>
                                        <label class="text-sm text-slate-600">Usuario / red social de referencia</label>
                                        <input wire:model.live.debounce.250ms="createForm.contact_handle" class="mt-1 w-full rounded-lg border border-slate-200 bg-white px-4 py-2 text-slate-900" />
                                        @error('createForm.contact_handle') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                    <div class="mb-4 text-base font-semibold text-slate-900">Resolución de cliente</div>
                                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                                    <label class="rounded-xl border px-4 py-4 {{ $createForm['client_mode'] === 'unlinked' ? 'border-sky-300 bg-sky-50' : 'border-slate-200 bg-white' }}">
                                        <input type="radio" wire:model.live="createForm.client_mode" value="unlinked" class="sr-only">
                                        <div class="text-sm font-medium text-slate-900">Sin vincular</div>
                                        <div class="mt-1 text-xs text-slate-500">La oportunidad queda abierta sin cliente asociado por ahora.</div>
                                    </label>

                                    <label class="rounded-xl border px-4 py-4 {{ $createForm['client_mode'] === 'link_existing' ? 'border-sky-300 bg-sky-50' : 'border-slate-200 bg-white' }}">
                                        <input type="radio" wire:model.live="createForm.client_mode" value="link_existing" class="sr-only">
                                        <div class="text-sm font-medium text-slate-900">Vincular cliente existente</div>
                                        <div class="mt-1 text-xs text-slate-500">Usá un cliente ya cargado en el sistema.</div>
                                    </label>

                                    <label class="rounded-xl border px-4 py-4 {{ $createForm['client_mode'] === 'create_new' ? 'border-sky-300 bg-sky-50' : 'border-slate-200 bg-white' }}">
                                        <input type="radio" wire:model.live="createForm.client_mode" value="create_new" class="sr-only">
                                        <div class="text-sm font-medium text-slate-900">Crear cliente nuevo</div>
                                        <div class="mt-1 text-xs text-slate-500">Aprovechá los datos de referencia y completá solo lo requerido.</div>
                                    </label>
                                    </div>

                                    @if($createForm['client_mode'] === 'link_existing')
                                        <div class="mt-5">
                                            <label class="text-sm text-slate-600">Cliente existente</label>
                                            <select wire:model.live="createForm.client_id" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 text-slate-900">
                                                <option value="">Seleccioná un cliente</option>
                                                @foreach($this->clients as $client)
                                                    <option value="{{ $client->id }}">
                                                        {{ $client->organization_name }}{{ $client->contact ? ' · '.$client->contact->full_name : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('createForm.client_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>
                                    @endif

                                    @if($createForm['client_mode'] === 'create_new')
                                        <div class="mt-5 rounded-2xl border border-emerald-300 bg-emerald-50 p-4 text-sm text-emerald-950">
                                            Los datos de teléfono y email se completan con la referencia si estaban vacíos. Nombre y apellido se cargan manualmente para evitar errores.
                                            <button type="button" wire:click="fillNewClientFromReference" class="ml-2 inline-flex rounded-lg border border-emerald-700 bg-emerald-700 px-3 py-1 text-xs font-medium text-white hover:bg-emerald-800">
                                                Usar datos de referencia
                                            </button>
                                        </div>

                                        <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                                        <div>
                                            <label class="text-sm text-slate-600">Nombre *</label>
                                            <input wire:model.live.debounce.250ms="createForm.new_client_first_name" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 text-slate-900" />
                                            @error('createForm.new_client_first_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>

                                        <div>
                                            <label class="text-sm text-slate-600">Apellido *</label>
                                            <input wire:model.live.debounce.250ms="createForm.new_client_last_name" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 text-slate-900" />
                                            @error('createForm.new_client_last_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="md:col-span-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
                                            <span class="font-medium text-slate-900">Nombre de referencia:</span>
                                            {{ $createForm['contact_name'] ?: 'Sin dato cargado todavía' }}
                                        </div>

                                        <div>
                                            <label class="text-sm text-slate-600">Email *</label>
                                            <input wire:model.live.debounce.250ms="createForm.new_client_email" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 text-slate-900" />
                                            @error('createForm.new_client_email') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>

                                        <div>
                                            <label class="text-sm text-slate-600">Teléfono</label>
                                            <input wire:model.live.debounce.250ms="createForm.new_client_phone" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 text-slate-900" />
                                            @error('createForm.new_client_phone') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>

                                        <div>
                                            <label class="text-sm text-slate-600">Nombre de empresa *</label>
                                            <input wire:model.live.debounce.250ms="createForm.new_client_organization_name" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 text-slate-900" />
                                            @error('createForm.new_client_organization_name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>

                                        <div>
                                            <label class="text-sm text-slate-600">Tamaño *</label>
                                            <select wire:model.live="createForm.new_client_company_size" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 text-slate-900">
                                                <option value="small">Pequeña</option>
                                                <option value="medium">Mediana</option>
                                                <option value="large">Grande</option>
                                            </select>
                                            @error('createForm.new_client_company_size') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="md:col-span-2">
                                            <label class="text-sm text-slate-600">Industria</label>
                                            <input wire:model.live.debounce.250ms="createForm.new_client_industry" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2 text-slate-900" />
                                            @error('createForm.new_client_industry') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    @endif
                                </div>

                            </div>

                            <div class="space-y-6 xl:sticky xl:top-4 xl:self-start">
                                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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
                                </div>

                                @if($createForm['client_mode'] === 'link_existing')
                                    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 shadow-sm">
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
                                    </div>
                                @endif

                                @if($createForm['client_mode'] === 'create_new' && $newClientPreview)
                                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
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
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-3 border-t border-slate-200 px-6 py-4">
                    <div>
                        @if($createStep === 2)
                            <button type="button" wire:click="previousCreateStep" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Volver</button>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="closeCreate" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancelar</button>
                        @if($createStep === 1)
                            <button type="button" wire:click="nextCreateStep" class="rounded-lg border border-slate-950 bg-slate-950 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Continuar con cliente</button>
                        @else
                            <button type="button" wire:click="create" class="rounded-lg border border-emerald-700 bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-800">Guardar oportunidad</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showNoteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4">
            <div class="w-full max-w-xl overflow-hidden rounded-2xl bg-white shadow-xl">
                <div class="border-b border-slate-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-slate-900">Agregar nota comercial</h3>
                </div>
                <div class="p-6">
                    <textarea wire:model.defer="noteContent" rows="5" class="w-full rounded-lg border border-slate-200 px-4 py-2" placeholder="Ej: Cliente pidió seguimiento el jueves..."></textarea>
                    @error('noteContent') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>
                <div class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4">
                    <button type="button" wire:click="closeQuickNote" class="rounded-lg border border-slate-200 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="button" wire:click="saveQuickNote" class="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white hover:bg-slate-800">Guardar nota</button>
                </div>
            </div>
        </div>
    @endif
</div>
