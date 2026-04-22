<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-xl font-semibold text-slate-900">Nuevo flujo de cobro</h2>
            <p class="mt-1 text-sm text-slate-500">
                Seleccioná un proyecto y configurá el flujo inicial de cuotas.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600">Buscar proyecto o cliente</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="projectSearch"
                        placeholder="Ej: Viten o Sitio corporativo..."
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                    >
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-3">
                    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Proyectos disponibles
                    </div>

                    <div class="max-h-80 space-y-2 overflow-y-auto">
                        @forelse ($projects as $project)
                            @php
                                $contact = $project->client?->contact;
                                $clientName = $contact?->organization
                                    ?? trim(($contact?->first_name ?? '') . ' ' . ($contact?->last_name ?? ''));
                                $isSelected = (int) $selectedProject?->id === (int) $project->id;
                            @endphp

                            <button
                                type="button"
                                wire:click="selectProject({{ $project->id }})"
                                class="w-full rounded-xl border px-3 py-3 text-left transition {{ $isSelected ? 'border-slate-900 bg-white shadow-sm' : 'border-slate-200 bg-white hover:border-slate-300' }}"
                            >
                                <div class="text-sm font-medium text-slate-900">{{ $project->name }}</div>
                                <div class="mt-1 text-xs text-slate-500">{{ $clientName ?: 'Sin cliente' }}</div>
                            </button>
                        @empty
                            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-4 py-6 text-sm text-slate-500">
                                No hay proyectos elegibles para crear un flujo.
                            </div>
                        @endforelse
                    </div>

                    @error('project_id')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">Proyecto seleccionado</div>

                    @if ($selectedProject)
                        @php
                            $contact = $selectedProject->client?->contact;
                            $clientName = $contact?->organization
                                ?? trim(($contact?->first_name ?? '') . ' ' . ($contact?->last_name ?? ''));
                        @endphp

                        <div class="mt-3 space-y-1">
                            <div class="text-sm font-medium text-slate-900">{{ $selectedProject->name }}</div>
                            <div class="text-sm text-slate-600">{{ $clientName ?: 'Sin cliente' }}</div>
                        </div>
                    @else
                        <div class="mt-3 text-sm text-slate-500">
                            Todavía no seleccionaste un proyecto.
                        </div>
                    @endif
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Monto total</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            wire:model="total_amount"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                        >
                        @error('total_amount')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Cantidad de cuotas</label>
                        <input
                            type="number"
                            min="1"
                            wire:model="installments_count"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                        >
                        @error('installments_count')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Frecuencia</label>
                        <select
                            wire:model="frequency"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                        >
                            @foreach ($frequencies as $frequency)
                                <option value="{{ $frequency->value }}">{{ $frequency->label() }}</option>
                            @endforeach
                        </select>
                        @error('frequency')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Fecha de inicio</label>
                        <input
                            type="date"
                            wire:model="start_date"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                        >
                        @error('start_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600">Días de gracia</label>
                        <input
                            type="number"
                            min="0"
                            wire:model="grace_days"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                        >
                        @error('grace_days')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Estado inicial</label>
                        <select
                            wire:model="status"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                        >
                            <option value="active">Activo</option>
                            <option value="draft">Borrador</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Observaciones</label>
                        <textarea
                            wire:model="notes"
                            rows="4"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-slate-400 focus:outline-none"
                            placeholder="Notas internas del flujo..."
                        ></textarea>
                        @error('notes')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="button"
                        wire:click="create"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="create">Crear flujo</span>
                        <span wire:loading wire:target="create">Creando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
