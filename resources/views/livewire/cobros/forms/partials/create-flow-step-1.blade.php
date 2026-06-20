<div class="flex h-full min-h-0 flex-col gap-6">
    <div class="shrink-0 flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-950">Operaciones pendientes de flujo</h2>
            <p class="mt-2 max-w-3xl text-sm text-slate-600">
                Mostramos todas las operaciones que todavía no tienen un flujo de cobro asociado, sin importar estado o monto cargado.
            </p>
        </div>

        <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900 shadow-sm">
            Si falta monto, la operación queda visible pero bloqueada para calcular cuotas.
        </div>
    </div>

    <div class="shrink-0 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-200/70">
        <x-ui.label for="operation-flow-search">Buscar operación</x-ui.label>
        <x-ui.input
            id="operation-flow-search"
            type="text"
            wire:model.live.debounce.300ms="operationSearch"
            placeholder="Buscar por operación, ID, cliente, contacto, email o teléfono..."
        />
        <p class="mt-2 text-xs text-slate-500">La condición principal es simple: operación sin flujo de cobro asociado.</p>
    </div>

    <div class="flex min-h-0 flex-1 flex-col gap-4">
        <div class="shrink-0 flex flex-col gap-2 rounded-3xl border border-slate-200 bg-slate-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-600">Operaciones pendientes</h3>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $operations->total() }} operación{{ $operations->total() === 1 ? '' : 'es' }} sin flujo asociado.
                </p>
                <p class="mt-1 text-xs text-slate-400">Panel optimizado para 5 operaciones visibles y navegación interna.</p>
            </div>

            @if ($selectedProject)
                <x-ui.badge variant="info">Operación seleccionada #{{ $selectedProject->id }}</x-ui.badge>
            @endif
        </div>

        <div class="grid min-h-0 flex-1 gap-6 xl:grid-cols-[minmax(0,1.45fr)_minmax(280px,0.55fr)] xl:items-start">
            <div class="min-h-0 overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
                <div class="flex h-full min-h-0 flex-col">
                    <div class="shrink-0 border-b border-slate-200 px-5 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-900">Bandeja de operaciones</div>
                                <div class="mt-1 text-xs text-slate-500">Desplazate dentro de este panel para ver el resto sin perder los botones del flujo.</div>
                            </div>
                            <div class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">
                                {{ min($operations->count(), 5) }} visibles
                            </div>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4">
                        <div class="space-y-3">
                @forelse ($operations as $project)
                    @php
                        $isSelected = $project_id === $project->id;
                        $hasAmount = (float) ($project->total_cost ?? 0) > 0;
                        $statusTone = match ($project->status) {
                            \App\Enums\ProjectStatus::Execution => 'border-blue-200 bg-blue-50 text-blue-700',
                            \App\Enums\ProjectStatus::SaleClosed => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            \App\Enums\ProjectStatus::Paused => 'border-purple-200 bg-purple-50 text-purple-700',
                            \App\Enums\ProjectStatus::Finished => 'border-slate-300 bg-slate-100 text-slate-700',
                            default => 'border-slate-200 bg-slate-50 text-slate-700',
                        };
                    @endphp

                    <button
                        type="button"
                        wire:click="selectProject({{ $project->id }})"
                        class="w-full rounded-3xl border p-4 text-left shadow-sm transition {{ $isSelected ? 'border-blue-400 bg-blue-50 ring-2 ring-blue-100' : 'border-slate-200 bg-white hover:-translate-y-0.5 hover:border-slate-300 hover:bg-slate-50 hover:shadow-md' }}"
                    >
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex h-2.5 w-2.5 rounded-full bg-amber-400 ring-4 ring-amber-100"></span>
                                    <span class="text-[11px] font-semibold uppercase tracking-[0.18em] text-amber-700">Requiere flujo</span>
                                    <span class="inline-flex rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600">ID #{{ $project->id }}</span>
                                </div>

                                <div class="mt-3 truncate text-base font-semibold text-slate-950">{{ $project->name }}</div>
                                <div class="mt-1 text-sm text-slate-500">
                                    Cliente: <span class="font-medium text-slate-700">{{ $project->client?->organization_name ?? 'Sin cliente' }}</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 lg:justify-end">
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-semibold {{ $statusTone }}">
                                    {{ $project->status->label() }}
                                </span>
                                @unless ($hasAmount)
                                    <span class="inline-flex rounded-full border border-amber-300 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">
                                        Falta monto
                                    </span>
                                @endunless
                                @if ($isSelected)
                                    <x-ui.badge variant="info">Seleccionada</x-ui.badge>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">Fecha de ganado</div>
                                <div class="mt-2 text-sm font-semibold text-slate-950">{{ $project->won_date_label }}</div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">Monto</div>
                                <div class="mt-2 text-sm font-semibold {{ $hasAmount ? 'text-slate-950' : 'text-amber-800' }}">
                                    @if ($hasAmount)
                                        ${{ number_format((float) $project->total_cost, 2, ',', '.') }}
                                    @else
                                        Sin monto definido
                                    @endif
                                </div>
                            </div>
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">Acción</div>
                                <div class="mt-2 text-sm font-semibold {{ $hasAmount ? 'text-slate-950' : 'text-amber-800' }}">
                                    {{ $hasAmount ? 'Crear flujo' : 'Completar monto' }}
                                </div>
                            </div>
                        </div>
                    </button>
                @empty
                    <x-ui.empty-state
                        title="No hay operaciones pendientes"
                        description="Todas las operaciones ya tienen un flujo asociado o no coinciden con la búsqueda."
                    />
                @endforelse
                        </div>
                    </div>

                    <div class="shrink-0 border-t border-slate-200 px-5 py-4">
                        {{ $operations->links() }}
                    </div>

                    @error('project_id')
                        <div class="shrink-0 px-5 pb-4">
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        </div>
                    @enderror
                </div>
            </div>

            <div class="min-h-0 overflow-y-auto rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                @if ($selectedProject)
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-blue-700">Operación seleccionada</div>
                    <div class="mt-3 text-lg font-semibold text-slate-950">{{ $selectedProject->name }}</div>
                    <div class="mt-2 text-sm text-slate-600">
                        ID #{{ $selectedProject->id }} · {{ $selectedProject->status->label() }}
                    </div>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">Cliente</div>
                            <div class="mt-2 text-sm font-semibold text-slate-950">{{ $selectedClient?->organization_name ?? 'Sin cliente' }}</div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="text-[11px] font-medium uppercase tracking-[0.18em] text-slate-500">Monto base</div>
                            <div class="mt-2 text-sm font-semibold {{ (float) $operation_amount > 0 ? 'text-slate-950' : 'text-amber-800' }}">
                                @if ((float) $operation_amount > 0)
                                    ${{ number_format((float) $operation_amount, 2, ',', '.') }}
                                @else
                                    Sin monto definido
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ((float) $operation_amount <= 0)
                        <div class="mt-4 rounded-2xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            Cargá el monto de la operación antes de configurar cuotas.
                        </div>
                    @endif
                @else
                    <div class="flex min-h-[18rem] items-center justify-center text-center">
                        <div>
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-50 text-slate-500 shadow-sm ring-1 ring-slate-200">$</div>
                            <h3 class="mt-4 text-base font-semibold text-slate-950">Seleccioná una operación</h3>
                            <p class="mt-2 text-sm text-slate-500">Al elegir una operación vas a configurar el total, cuotas y automatizaciones del flujo.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
