<div class="space-y-8">
    <div>
        <h2 class="text-xl font-semibold text-slate-950">Paso 1. Selección de cliente y proyecto</h2>
        <p class="mt-2 text-sm text-slate-600">
            Elegí primero el cliente y después uno de sus proyectos. Los proyectos no elegibles siguen visibles con su motivo.
        </p>
    </div>

    <div class="max-w-xl">
        <label class="mb-2 block text-sm font-medium text-slate-700">Cliente</label>
        <select wire:model.live="client_id" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-4 focus:ring-amber-100">
            <option value="">Seleccioná un cliente</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}">
                    {{ $client->organization_name }} ({{ $client->projects_count }} proyectos)
                </option>
            @endforeach
        </select>
        @error('client_id')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($selectedClient)
        <div class="rounded-2xl border border-amber-200 bg-amber-50/80 p-4 shadow-sm">
            <div class="text-sm font-medium text-slate-900">{{ $selectedClient->organization_name }}</div>
            <div class="mt-1 text-sm text-slate-600">
                {{ $selectedClient->contact?->fullName ?: 'Sin contacto' }}
                @if ($selectedClient->contact?->email)
                    <span class="px-2 text-slate-300">•</span>{{ $selectedClient->contact->email }}
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-600">Proyectos del cliente</h3>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($clientProjects as $project)
                    @php
                        $disabledReason = $this->projectDisabledReason($project);
                        $isSelected = $project_id === $project->id;
                    @endphp

                    <button
                        type="button"
                        wire:click="selectProject({{ $project->id }})"
                        @disabled($disabledReason !== null)
                        class="rounded-2xl border p-5 text-left shadow-sm transition focus:outline-none focus:ring-4 focus:ring-blue-100 {{ $isSelected ? 'border-blue-500 bg-blue-50 text-blue-950 ring-2 ring-blue-100' : ($disabledReason ? 'cursor-not-allowed border-slate-300 bg-slate-100 text-slate-500 opacity-100' : 'border-slate-300 bg-white text-slate-800 hover:border-slate-400 hover:bg-slate-50') }}"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-lg font-semibold tracking-tight {{ $isSelected ? 'text-blue-950' : 'text-slate-900' }}">
                                    {{ $project->name }}
                                </div>
                                <div class="mt-1 text-sm {{ $isSelected ? 'text-blue-700' : 'text-slate-600' }}">
                                    {{ $project->status->label() }}
                                </div>
                            </div>

                            @if ($disabledReason)
                                <span class="inline-flex rounded-full border border-amber-300 bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-800">
                                    {{ $disabledReason }}
                                </span>
                            @elseif ($isSelected)
                                <span class="inline-flex rounded-full border border-blue-200 bg-white px-2.5 py-1 text-xs font-medium text-blue-700">
                                    Seleccionado
                                </span>
                            @else
                                <span class="inline-flex rounded-full border border-emerald-300 bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-800">
                                    Disponible
                                </span>
                            @endif
                        </div>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl border {{ $isSelected ? 'border-blue-200 bg-white' : ($disabledReason ? 'border-slate-300 bg-white' : 'border-slate-200 bg-slate-50') }} p-3">
                                <div class="text-[11px] font-medium uppercase tracking-[0.18em] {{ $isSelected ? 'text-blue-700' : 'text-slate-500' }}">Monto del proyecto</div>
                                <div class="mt-2 text-sm font-semibold {{ $isSelected ? 'text-blue-950' : ($disabledReason ? 'text-slate-700' : 'text-slate-900') }}">
                                    @if ($project->total_cost)
                                        ${{ number_format((float) $project->total_cost, 2, ',', '.') }}
                                    @else
                                        Sin monto definido
                                    @endif
                                </div>
                            </div>
                            <div class="rounded-xl border {{ $isSelected ? 'border-blue-200 bg-white' : ($disabledReason ? 'border-slate-300 bg-white' : 'border-slate-200 bg-slate-50') }} p-3">
                                <div class="text-[11px] font-medium uppercase tracking-[0.18em] {{ $isSelected ? 'text-blue-700' : 'text-slate-500' }}">Flujos abiertos</div>
                                <div class="mt-2 text-sm font-semibold {{ $isSelected ? 'text-blue-950' : ($disabledReason ? 'text-slate-700' : 'text-slate-900') }}">
                                    {{ $project->paymentFlows->filter(fn ($flow) => in_array($flow->status->value, ['draft', 'active'], true))->count() }}
                                </div>
                            </div>
                        </div>
                    </button>
                @empty
                    <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-slate-400 bg-slate-100 px-5 py-8 text-sm text-slate-600">
                        Este cliente no tiene proyectos asociados.
                    </div>
                @endforelse
            </div>

            @error('project_id')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif
</div>
