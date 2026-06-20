<div class="space-y-6">
    @if (session('status'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <x-ui.stat-card label="Total clientes" :value="number_format($stats['total'], 0, ',', '.')" hint="Organizaciones registradas en la cartera." tone="primary" />
        <x-ui.stat-card label="Con puntuacion" :value="number_format($stats['with_score'], 0, ',', '.')" hint="Clientes con evaluacion interna cargada." tone="accent" />
        <x-ui.stat-card label="Promedio" :value="number_format((float) $stats['avg_score'], 1, ',', '.')" hint="Promedio de score para seguimiento comercial." tone="success" />
    </div>

    <x-ui.card class="space-y-5 bg-slate-50/70">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-950">Cartera activa</h2>
                <p class="text-sm text-slate-500">Busca rapido, revisa scoring y abre el formulario sin salir del contexto.</p>
            </div>

            <x-ui.button type="button" wire:click="create">
                Nuevo cliente
            </x-ui.button>
        </div>

        <div>
            <x-ui.label for="clients-search">Buscar</x-ui.label>
            <x-ui.input
                id="clients-search"
                type="text"
                placeholder="Buscar clientes por nombre o contacto..."
                wire:model.live.debounce.300ms="search"
            />
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
        @forelse($clients as $client)
            @php
                $score = $client->score;
                $scoreVariant = $score >= 8 ? 'success' : ($score >= 5 ? 'warning' : 'neutral');
            @endphp

            <x-ui.card wire:key="client-{{ $client->id }}" class="gap-0 p-0 transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="p-6">
                    <div class="mb-4 flex items-start justify-between">
                        <div class="flex size-12 items-center justify-center overflow-hidden rounded-2xl bg-indigo-100 text-xl font-semibold text-indigo-700 ring-1 ring-indigo-200">
                            @if($client->company_logo)
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::url($client->company_logo) }}"
                                    alt="Logo de {{ $client->organization_name }}"
                                    class="size-14 -m-1 object-cover"
                                />
                            @else
                                {{ mb_substr($client->organization_name, 0, 1) }}
                            @endif
                        </div>

                        <div class="flex gap-2">
                            <x-ui.button type="button" size="sm" variant="secondary" wire:click="edit({{ $client->id }})">
                                Editar
                            </x-ui.button>
                            <x-ui.button
                                type="button"
                                size="sm"
                                variant="danger"
                                wire:click="delete({{ $client->id }})"
                                wire:confirm="Se eliminara este cliente de forma logica y tambien se archivaran sus proyectos, cobros y oportunidades."
                            >
                                Eliminar
                            </x-ui.button>
                        </div>
                    </div>

                    <h3 class="mb-1 text-xl text-slate-950">{{ $client->organization_name }}</h3>
                    <p class="mb-4 text-slate-600">{{ $client->contact?->full_name }}</p>

                    <div class="mb-4 space-y-2">
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <span class="inline-flex size-7 items-center justify-center rounded-xl bg-slate-100">✉️</span>
                            <span class="truncate">{{ $client->contact?->email }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <span class="inline-flex size-7 items-center justify-center rounded-xl bg-slate-100">📞</span>
                            <span class="truncate">{{ $client->contact?->phone ?? 'Sin celular' }}</span>
                        </div>
                    </div>

                    <div class="space-y-3 border-t border-slate-200 pt-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Tamaño</span>
                            <span class="font-medium text-slate-950">{{ $client->company_size_label }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Puntuación</span>
                            <span class="font-medium text-slate-950">{{ $score ?? '—' }}</span>
                        </div>
                        <x-ui.badge :variant="$scoreVariant">
                            {{ $score ? 'Score '.$score : 'Sin score' }}
                        </x-ui.badge>
                    </div>
                </div>
            </x-ui.card>
        @empty
            <div class="col-span-full">
                <x-ui.empty-state title="No hay clientes aun" description="Crea el primero para empezar a construir la cartera comercial del ERP." />
            </div>
        @endforelse
    </div>

    <div class="pt-1">
        {{ $clients->onEachSide(1)->links() }}
    </div>

    {{-- MODAL Create/Edit --}}
    <div
        x-data
        x-cloak
        x-show="$wire.modalOpen"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @keydown.escape.window="$wire.closeModal()"
    >
        <div class="absolute inset-0 bg-black/40" @click="$wire.closeModal()"></div>

        <div class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <h2 class="text-xl font-semibold text-slate-950">
                        {{ $clientId ? 'Editar cliente' : 'Nuevo cliente' }}
                    </h2>
                    <p class="text-sm text-slate-500">Contacto, organización y evaluación interna en una sola ficha.</p>
                </div>

                <button type="button" class="rounded-2xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700" wire:click="closeModal">✕</button>
            </div>

            <form wire:submit.prevent="save" class="flex flex-col flex-1 min-h-0">
                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                    {{-- Contacto --}}
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-slate-950">Contacto</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-ui.label>Nombre</x-ui.label>
                                <x-ui.input wire:model.defer="first_name" />
                                @error('first_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <x-ui.label>Apellido</x-ui.label>
                                <x-ui.input wire:model.defer="last_name" />
                                @error('last_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <x-ui.label>Email</x-ui.label>
                                <x-ui.input type="email" wire:model.defer="email" />
                                @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <x-ui.label>Celular</x-ui.label>
                                <x-ui.input wire:model.defer="phone" />
                                @error('phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <x-ui.label>Fecha de nacimiento</x-ui.label>
                                <x-ui.input type="date" wire:model.defer="birthdate" />
                                @error('birthdate') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <x-ui.label>Puesto</x-ui.label>
                                <x-ui.input wire:model.defer="job_title" />
                                @error('job_title') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Organización --}}
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-slate-950">Organización</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <x-ui.label>Nombre de la organización</x-ui.label>
                                <x-ui.input wire:model.defer="organization_name" />
                                @error('organization_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <x-ui.label>Rubro</x-ui.label>
                                <x-ui.input wire:model.defer="industry" />
                                @error('industry') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <x-ui.label>Dirección</x-ui.label>
                                <x-ui.input wire:model.defer="address" />
                                @error('address') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                        <div>
                            <x-ui.label>Logo (opcional)</x-ui.label>
                            <x-ui.input type="file" wire:model="company_logo_upload" class="file:mr-4 file:rounded-xl file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700" />
                            @error('company_logo_upload') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            @if($company_logo_upload)
                                <img
                                    src="{{ $company_logo_upload->temporaryUrl() }}"
                                    alt="Vista previa del logo"
                                    class="mt-3 size-20 rounded-lg object-cover border border-gray-200"
                                />
                            @elseif($company_logo_path)
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::url($company_logo_path) }}"
                                    alt="Logo actual"
                                    class="mt-3 size-20 rounded-lg object-cover border border-gray-200"
                                />
                            @endif
                            </div>

                            <div>
                                <x-ui.label>Tamaño</x-ui.label>
                                <x-ui.select wire:model.defer="company_size">
                                    @foreach($sizes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                                @error('company_size') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Evaluación --}}
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-slate-950">Evaluación interna (Nube)</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-ui.label>Puntuación (1 a 10)</x-ui.label>
                                <x-ui.input type="number" min="1" max="10" wire:model.defer="score" />
                                @error('score') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <x-ui.label>Notas</x-ui.label>
                                <x-ui.textarea rows="3" wire:model.defer="notes"></x-ui.textarea>
                                @error('notes') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="sticky bottom-0 flex flex-col justify-end gap-3 border-t border-slate-200 bg-white px-6 py-4 sm:flex-row">
                    <x-ui.button type="button" variant="secondary" wire:click="closeModal">
                        Cancelar
                    </x-ui.button>

                    <x-ui.button type="submit">
                        {{ $clientId ? 'Guardar cambios' : 'Crear cliente' }}
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
