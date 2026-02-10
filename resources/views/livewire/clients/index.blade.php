<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl mb-2 text-gray-900">Clientes</h1>
            <p class="text-gray-600">Administra tu cartera de clientes</p>
        </div>

        <button
            type="button"
            wire:click="create"
            class="bg-blue-600 hover:bg-blue-700 text-white inline-flex items-center gap-2 rounded-lg px-4 py-2 transition"
        >
            <span class="text-lg leading-none">＋</span>
            Nuevo Cliente
        </button>
    </div>

    {{-- Search Bar --}}
    <div class="rounded-xl border bg-white p-4">
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔎</span>
            <input
                type="text"
                placeholder="Buscar clientes por nombre o contacto..."
                class="w-full rounded-lg border border-gray-200 bg-white pl-10 pr-3 py-2 outline-none focus:ring-2 focus:ring-blue-200"
                wire:model.live.debounce.300ms="search"
            />
        </div>
    </div>

    {{-- Clients Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($clients as $client)
            <div wire:key="client-{{ $client->id }}" class="bg-white rounded-xl border p-6 hover:shadow-lg transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="bg-purple-100 text-purple-700 size-12 rounded-full flex items-center justify-center text-xl font-semibold overflow-hidden">
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
                        <button
                            type="button"
                            wire:click="edit({{ $client->id }})"
                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                        >
                            Editar
                        </button>

                        <button
                            type="button"
                            wire:click="delete({{ $client->id }})"
                            class="rounded-lg border border-red-200 px-3 py-2 text-sm text-red-700 hover:bg-red-50 transition"
                        >
                            Eliminar
                        </button>
                    </div>
                </div>

                <h3 class="text-xl mb-1 text-gray-900">{{ $client->organization_name }}</h3>
                <p class="text-gray-600 mb-4">{{ $client->contact?->full_name }}</p>

                <div class="space-y-2 mb-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="inline-flex size-7 items-center justify-center rounded-md bg-gray-100">✉️</span>
                        <span class="truncate">{{ $client->contact?->email }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span class="inline-flex size-7 items-center justify-center rounded-md bg-gray-100">📞</span>
                        <span class="truncate">{{ $client->contact?->phone ?? 'Sin celular' }}</span>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Tamaño:</span>
                        <span class="text-gray-900 font-medium">{{ $client->company_size_label }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Puntuación:</span>
                        <span class="text-gray-900 font-medium">{{ $client->score ?? '—' }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-xl border bg-white p-10 text-center text-gray-600">
                No hay clientes aún. Creá el primero con “Nuevo Cliente”.
            </div>
        @endforelse
    </div>

    {{-- Paginación (NO tocar, esto es lo que estabas cuidando) --}}
    <div>
        {{ $clients->onEachSide(1)->links() }}
    </div>

    {{-- Stats Footer (si ya lo tenías) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-xl border bg-white p-4">
            <p class="text-sm text-gray-600 mb-1">Total Clientes</p>
            <p class="text-2xl text-gray-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-xl border bg-white p-4">
            <p class="text-sm text-gray-600 mb-1">Con Puntuación</p>
            <p class="text-2xl text-gray-900">{{ $stats['with_score'] }}</p>
        </div>
        <div class="rounded-xl border bg-white p-4">
            <p class="text-sm text-gray-600 mb-1">Promedio</p>
            <p class="text-2xl text-gray-900">{{ $stats['avg_score'] }}</p>
        </div>
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

        <div class="relative w-full max-w-3xl max-h-[90vh] rounded-2xl bg-white shadow-2xl border flex flex-col overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">
                        {{ $clientId ? 'Editar cliente' : 'Nuevo cliente' }}
                    </h2>
                    <p class="text-sm text-gray-600">Contacto + organización + evaluación interna.</p>
                </div>

                <button type="button" class="text-gray-500 hover:text-gray-700" wire:click="closeModal">✕</button>
            </div>

            <form wire:submit.prevent="save" class="flex flex-col flex-1 min-h-0">
                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">
                    {{-- Contacto --}}
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-gray-900">Contacto</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-700">Nombre</label>
                                <input wire:model.defer="first_name" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-200 outline-none" />
                                @error('first_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm text-gray-700">Apellido</label>
                                <input wire:model.defer="last_name" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-200 outline-none" />
                                @error('last_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm text-gray-700">Email</label>
                                <input type="email" wire:model.defer="email" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-200 outline-none" />
                                @error('email') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm text-gray-700">Celular</label>
                                <input wire:model.defer="phone" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-200 outline-none" />
                                @error('phone') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm text-gray-700">Fecha de nacimiento</label>
                                <input type="date" wire:model.defer="birthdate" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-200 outline-none" />
                                @error('birthdate') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm text-gray-700">Puesto</label>
                                <input wire:model.defer="job_title" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-200 outline-none" />
                                @error('job_title') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Organización --}}
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-gray-900">Organización</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-700">Nombre de la organización</label>
                                <input wire:model.defer="organization_name" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-200 outline-none" />
                                @error('organization_name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm text-gray-700">Rubro</label>
                                <input wire:model.defer="industry" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-200 outline-none" />
                                @error('industry') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="text-sm text-gray-700">Dirección</label>
                                <input wire:model.defer="address" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-200 outline-none" />
                                @error('address') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                        <div>
                            <label class="text-sm text-gray-700">Logo (opcional)</label>
                            <input type="file" wire:model="company_logo_upload" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 bg-white" />
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
                                <label class="text-sm text-gray-700">Tamaño</label>
                                <select wire:model.defer="company_size" class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 bg-white focus:ring-2 focus:ring-blue-200 outline-none">
                                    @foreach($sizes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('company_size') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Evaluación --}}
                    <div class="space-y-3">
                        <h3 class="text-sm font-semibold text-gray-900">Evaluación interna (Nube)</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-gray-700">Puntuación (1 a 10)</label>
                                <input type="number" min="1" max="10" wire:model.defer="score"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-200 outline-none" />
                                @error('score') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-700">Notas</label>
                                <textarea rows="3" wire:model.defer="notes"
                                    class="mt-1 w-full rounded-lg border border-gray-200 px-3 py-2 focus:ring-2 focus:ring-blue-200 outline-none"></textarea>
                                @error('notes') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row gap-3 justify-end border-t bg-white px-6 py-4 sticky bottom-0">
                    <button type="button" wire:click="closeModal" class="rounded-lg border border-gray-200 px-4 py-2 text-gray-700 hover:bg-gray-50 transition">
                        Cancelar
                    </button>

                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-white hover:bg-blue-700 transition">
                        {{ $clientId ? 'Guardar cambios' : 'Crear cliente' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
