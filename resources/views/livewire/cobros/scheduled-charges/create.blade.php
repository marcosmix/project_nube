<div class="min-h-[calc(100vh-10rem)] rounded-3xl border border-slate-300 bg-[#fffdfa] shadow-sm">
    <div class="border-b border-slate-300 px-6 py-6 sm:px-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="text-xs font-semibold uppercase tracking-[0.22em] text-indigo-600">Cobro programado</div>
                <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-950">Nuevo cobro</h1>
                <p class="mt-2 max-w-3xl text-sm text-slate-600">Registrá un cobro recurrente por cliente con fecha, monto, frecuencia y respaldo opcional.</p>
            </div>

            <x-ui.button href="{{ route('cobros.index', ['tab' => 'scheduled']) }}" variant="secondary">
                Volver a cobros
            </x-ui.button>
        </div>
    </div>

    <div class="grid min-h-[42rem] gap-0 xl:grid-cols-[minmax(0,1fr)_340px]">
        <div class="space-y-6 px-6 py-6 sm:px-8">
            <div class="grid gap-6 xl:grid-cols-[minmax(0,0.95fr)_minmax(0,1.05fr)]">
                <div class="space-y-4">
                    <x-ui.card class="space-y-4 bg-slate-50/70">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Seleccionar cliente</h2>
                            <p class="mt-1 text-sm text-slate-500">Este cobro puede asociarse a cualquier cliente registrado.</p>
                        </div>

                        <div>
                            <x-ui.label for="scheduled-client-search">Buscar cliente</x-ui.label>
                            <x-ui.input id="scheduled-client-search" wire:model.live.debounce.300ms="clientSearch" placeholder="Buscar por cliente, contacto o email..." />
                        </div>
                    </x-ui.card>

                    <div class="space-y-3">
                        @forelse($clients as $client)
                            @php($isSelected = $client_id === $client->id)
                            <button type="button" wire:click="selectClient({{ $client->id }})" class="w-full rounded-3xl border p-5 text-left shadow-sm transition {{ $isSelected ? 'border-indigo-300 bg-indigo-50 ring-2 ring-indigo-100' : 'border-slate-200 bg-white hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 hover:shadow-md' }}">
                                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="min-w-0">
                                        <div class="truncate text-base font-semibold text-slate-950">{{ $client->organization_name }}</div>
                                        <div class="mt-1 text-sm text-slate-500">
                                            {{ $client->contact?->fullName ?: 'Sin contacto registrado' }}
                                            @if($client->contact?->email)
                                                <span class="px-2 text-slate-300">•</span>{{ $client->contact->email }}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2 lg:justify-end">
                                        <x-ui.badge variant="neutral">{{ $client->projects_count }} operaciones</x-ui.badge>
                                        <x-ui.badge variant="info">{{ $client->scheduled_charges_count }} programados</x-ui.badge>
                                        @if($isSelected)
                                            <x-ui.badge variant="primary">Seleccionado</x-ui.badge>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        @empty
                            <div class="flex min-h-[18rem] items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                                <div>
                                    <h3 class="text-base font-semibold text-slate-950">No se encontraron clientes</h3>
                                    <p class="mt-2 max-w-sm text-sm text-slate-500">Probá con otro nombre, contacto o email.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="pt-1">
                        {{ $clients->links() }}
                    </div>

                    @error('client_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="space-y-4">
                    <x-ui.card class="space-y-5">
                        <div>
                            <h2 class="text-lg font-semibold text-slate-950">Datos del cobro</h2>
                            <p class="mt-1 text-sm text-slate-500">Completá la configuración que se repetirá según la frecuencia elegida.</p>
                        </div>

                        <div>
                            <x-ui.label>Nombre de referencia</x-ui.label>
                            <x-ui.input wire:model.defer="reference_name" placeholder="Mantenimiento mensual / Hosting anual" />
                            @error('reference_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <x-ui.label>Monto</x-ui.label>
                                <x-ui.input type="number" min="0" step="0.01" wire:model.defer="amount" placeholder="120000" />
                                @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>Fecha de cobro</x-ui.label>
                                <x-ui.input type="date" wire:model.defer="charge_date" />
                                @error('charge_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <x-ui.label>Frecuencia</x-ui.label>
                            <x-ui.select wire:model.defer="frequency">
                                @foreach($frequencies as $frequencyOption)
                                    <option value="{{ $frequencyOption->value }}">{{ $frequencyOption->label() }}</option>
                                @endforeach
                            </x-ui.select>
                            @error('frequency') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <x-ui.label>Detalle</x-ui.label>
                            <x-ui.textarea wire:model.defer="detail" rows="4" placeholder="Detalle interno del cobro, condiciones o referencia administrativa." />
                            @error('detail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <x-ui.label>Archivo asociado opcional</x-ui.label>
                            <x-ui.input type="file" wire:model="attachmentUpload" />
                            <p class="mt-1 text-xs text-slate-500">Acepta PDF, imágenes y documentos hasta 10 MB.</p>
                            @if($attachmentUpload)
                                <div class="mt-3 rounded-xl border border-dashed border-slate-300 bg-white px-3 py-2 text-sm text-slate-700">
                                    Listo para subir: <span class="font-medium">{{ $attachmentUpload->getClientOriginalName() }}</span>
                                </div>
                            @endif
                            @error('attachmentUpload') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </x-ui.card>
                </div>
            </div>
        </div>

        <aside class="border-t border-slate-300 bg-slate-100/80 px-6 py-6 xl:border-l xl:border-t-0">
            <div class="space-y-5">
                <div>
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-600">Resumen</h2>
                    <p class="mt-1 text-sm text-slate-600">Vista previa del cobro programado.</p>
                </div>

                <x-ui.panel>
                    <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Cliente</div>
                    <div class="mt-2 text-sm font-semibold text-slate-950">{{ $selectedClient?->organization_name ?? 'Sin seleccionar' }}</div>
                </x-ui.panel>

                <x-ui.panel>
                    <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Referencia</div>
                    <div class="mt-2 text-sm font-semibold text-slate-950">{{ $reference_name ?: 'Pendiente' }}</div>
                </x-ui.panel>

                <x-ui.panel>
                    <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Monto</div>
                    <div class="mt-2 text-lg font-semibold text-slate-950">${{ number_format((float) ($amount ?: 0), 2, ',', '.') }}</div>
                </x-ui.panel>

                <x-ui.panel>
                    <div class="text-xs font-medium uppercase tracking-[0.18em] text-slate-500">Programación</div>
                    <div class="mt-2 text-sm font-semibold text-slate-950">{{ $charge_date ?: 'Sin fecha' }}</div>
                    <div class="mt-1 text-xs text-slate-500">{{ collect($frequencies)->firstWhere('value', $frequency)?->label() ?? 'Mensual' }}</div>
                </x-ui.panel>
            </div>
        </aside>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-300 bg-white px-6 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-8">
        <div class="text-sm text-slate-600">El cobro queda activo al guardarlo.</div>
        <x-ui.button type="button" wire:click="save" wire:loading.attr="disabled" variant="primary">
            <span wire:loading.remove wire:target="save">Guardar cobro programado</span>
            <span wire:loading wire:target="save">Guardando...</span>
        </x-ui.button>
    </div>
</div>
