<x-ui.page-container>
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ session('status') }}
            </div>
        @endif

        @if($pendingDeleteProjectId)
            <div class="rounded-3xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900 shadow-sm">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-1">
                        <p class="font-semibold">Eliminar operación: {{ $pendingDeleteProjectName }}</p>
                        <p>
                            Tiene {{ $pendingDeleteProjectFlows }} flujo(s) de cobro asociados
                            @if($pendingDeleteProjectOpenFlows > 0)
                                y {{ $pendingDeleteProjectOpenFlows }} sigue(n) abierto(s)
                            @endif.
                            Puedes archivar solo la operación o archivar tambien sus cobros.
                        </p>
                    </div>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        <x-ui.button type="button" variant="secondary" wire:click="cancelDelete">Cancelar</x-ui.button>
                        <x-ui.button type="button" variant="secondary" wire:click="deleteKeepingFlows">Solo operación</x-ui.button>
                        <x-ui.button type="button" variant="danger" wire:click="deleteWithFlows">Operación y cobros</x-ui.button>
                    </div>
                </div>
            </div>
        @endif

        <x-ui.section-header
            title="Operaciones"
            description="Monitorea estado, riesgo y valor del portafolio operativo sin salir del flujo comercial y de entrega."
            eyebrow="Operacion"
        >
            <x-slot:actions>
                <x-ui.button type="button" wire:click="openCreate">
                    Pasar venta a Operaciones
                </x-ui.button>
            </x-slot:actions>
        </x-ui.section-header>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        @php
            $all = \App\Models\Project::count();
            $execution = \App\Models\Project::where('status', 'execution')->count();
            $finished = \App\Models\Project::where('status', 'finished')->count();
            $issues = \App\Models\Project::where('execution_sub_status', 'with_debt')->orWhere('execution_sub_status', 'delayed')->count();
            $totalRevenue = \App\Models\Project::whereNotNull('total_cost')->sum('total_cost');
            $avg = $all > 0 ? round($totalRevenue / $all) : 0;
        @endphp

        <x-ui.stat-card label="Operaciones totales" :value="number_format($all, 0, ',', '.')" :hint="number_format($execution, 0, ',', '.').' en ejecucion'" tone="primary" />
        <x-ui.stat-card label="Finalizados" :value="number_format($finished, 0, ',', '.')" :hint="($all > 0 ? round(($finished / $all) * 100) : 0).'% tasa de cierre'" tone="success" />
        <x-ui.stat-card label="Con problemas" :value="number_format($issues, 0, ',', '.')" hint="Operaciones con deuda o demora." tone="danger" />
        <x-ui.stat-card label="Valor promedio" :value="'$'.number_format($avg, 0, ',', '.')" :hint="'Total: $'.number_format($totalRevenue, 0, ',', '.')" tone="accent" />
    </div>

    <x-ui.card class="space-y-5 bg-slate-50/70">
        <div>
            <h2 class="text-lg font-semibold text-slate-950">Vista de portafolio</h2>
            <p class="text-sm text-slate-500">Filtra por estado y detecta rapido operaciones activas, frenadas o con riesgo.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-ui.input
                type="text"
                wire:model.live="search"
                placeholder="Buscar por nombre, cliente o empresa..."
            />

            <x-ui.select wire:model.live="statusFilter">
                <option value="all">Todos los estados</option>
                <option value="sale_closed">Listo para ejecutar</option>
                <option value="execution">En Ejecución</option>
                <option value="paused">Frenado</option>
                <option value="finished">Finalizado</option>
            </x-ui.select>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @forelse($projects as $project)
            @php
                $status = $project->status->value;
                $cfg = config("projects.status.$status");
                $bar = config("projects.status.$status.bar");

                if ($status === 'execution' && $project->execution_sub_status) {
                    if ($project->execution_sub_status->value === 'with_debt') $bar = 'bg-red-400';
                    if ($project->execution_sub_status->value === 'delayed') $bar = 'bg-orange-400';
                }
            @endphp

            <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-200/70 transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="h-2 {{ $bar }}"></div>

                <div class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <h3 class="mb-2 text-xl text-slate-950">{{ $project->name }}</h3>

                                    {{-- Status badge --}}
                                    <div class="flex items-center gap-2">
                                        <span class="rounded-full px-2.5 py-0.5 text-sm {{ $cfg['badge'] }}">
                                            {{ $cfg['label'] }}
                                        </span>

                                        @if($status === 'execution' && $project->execution_sub_status)
                                            @php
                                                $sub = $project->execution_sub_status->value;
                                                $scfg = config("projects.execution_sub.$sub");
                                                $semaphore = match ($sub) {
                                                    'on_track' => 'bg-emerald-500',
                                                    'with_debt' => 'bg-red-500',
                                                    'delayed' => 'bg-yellow-400',
                                                    default => 'bg-gray-400',
                                                };
                                            @endphp
                                            <span class="rounded-full px-2.5 py-0.5 text-sm {{ $scfg['badge'] }}">
                                                <span class="mr-2 inline-block size-2 rounded-full {{ $semaphore }}"></span>
                                                {{ $scfg['label'] }}
                                            </span>
                                        @endif
                                    </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('proyectos.show', $project) }}" class="rounded-2xl bg-slate-100 px-3 py-2 text-sm text-slate-600 transition hover:bg-slate-200">
                                Ver
                            </a>
                            <x-ui.button type="button" size="sm" variant="danger" wire:click="confirmDelete({{ $project->id }})">
                                Eliminar
                            </x-ui.button>
                        </div>
                    </div>

                            {{-- Client --}}
                    <div class="mt-4 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200/80">
                                @php
                                    $client = $project->client;
                                    $contact = $client?->contact;
                                    $clientName = $client?->organization_name ?? 'Cliente no asignado';
                                    $clientInitial = mb_substr($clientName, 0, 1);
                                    $contactName = $contact
                                        ? trim("{$contact->first_name} {$contact->last_name}")
                                        : 'Sin contacto registrado';
                                @endphp
                        <div class="flex items-center gap-3">
                            <div class="flex size-9 items-center justify-center rounded-2xl bg-indigo-100 text-sm font-semibold text-indigo-600">
                                {{ $clientInitial }}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-slate-950">{{ $clientName }}</div>
                                <div class="text-xs text-slate-500">
                                    {{ $contactName }}
                                </div>
                            </div>
                        </div>
                    </div>

                            {{-- Info --}}
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-slate-600">
                                <div>
                                    @if($project->total_cost)
                                        ${{ number_format($project->total_cost, 0, ',', '.') }}
                                    @else
                                        —
                                    @endif
                                </div>
                                <div>
                                    @if($project->estimated_end_date)
                                        {{ $project->estimated_end_date->format('m/Y') }}
                                    @else
                                        —
                                    @endif
                                </div>
                            </div>

                            {{-- Alerts --}}
                    @if($status === 'execution' && $project->execution_sub_status?->value === 'with_debt')
                        <div class="mt-4 rounded-2xl bg-red-50 p-3 text-xs text-red-800 ring-1 ring-red-200">
                            Este proyecto tiene pagos pendientes
                        </div>
                    @endif

                    @if($status === 'execution' && $project->execution_sub_status?->value === 'delayed')
                        <div class="mt-4 rounded-2xl bg-orange-50 p-3 text-xs text-orange-800 ring-1 ring-orange-200">
                            Este proyecto presenta demoras en la entrega
                        </div>
                    @endif

                    @if($status === 'paused')
                        <div class="mt-4 rounded-2xl bg-purple-50 p-3 text-xs text-purple-800 ring-1 ring-purple-200">
                            Operación frenada temporalmente
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="lg:col-span-2">
                <x-ui.empty-state title="No se encontraron operaciones" description="Prueba con otro estado o una busqueda mas amplia para recuperar resultados." />
            </div>
        @endforelse
    </div>

        <div class="pt-1">
            {{ $projects->links() }}
        </div>

        {{-- Modal Venta Ganada -> Operaciones --}}
        @if($showCreateModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4">
                <div class="flex w-full max-w-4xl max-h-[80vh] flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70">
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5">
                        <div>
                            <h3 class="text-xl font-semibold text-slate-950">Pasar venta a Operaciones</h3>
                            <p class="mt-1 text-sm text-slate-500">Seleccioná una venta ganada pendiente y confirmá el traspaso en el siguiente paso.</p>
                        </div>
                        <button type="button" wire:click="closeCreate" class="rounded-2xl px-3 py-2 text-slate-500 transition hover:bg-slate-100">✕</button>
                    </div>

                    <div class="border-b border-slate-200 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $createStep === 1 ? 'bg-indigo-600 text-white' : 'bg-indigo-100 text-indigo-700' }} text-sm font-semibold">1</div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">Seleccionar venta</div>
                                    <div class="text-xs text-slate-500">Elegí una venta ganada pendiente</div>
                                </div>
                            </div>
                            <div class="h-px flex-1 bg-slate-200"></div>
                            <div class="flex items-center gap-3 rounded-2xl bg-slate-50 px-4 py-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full {{ $createStep === 2 ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-600' }} text-sm font-semibold">2</div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">Confirmar</div>
                                    <div class="text-xs text-slate-500">Revisá los datos antes de pasarla</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto px-6 py-6">
                        @if($createStep === 1)
                            <div class="space-y-4">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 shadow-sm">
                                    <x-ui.label>Buscar venta ganada</x-ui.label>
                                    <x-ui.input wire:model.live.debounce.300ms="opportunitySearch" placeholder="Nombre de la venta, cliente, contacto o email" />
                                    <p class="mt-1 text-xs text-slate-500">Se muestran todas las ventas ganadas que todavía no fueron pasadas a Operaciones.</p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div class="mb-4 flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-semibold text-slate-900">Ventas ganadas pendientes</div>
                                            <div class="text-xs text-slate-500">Elegí una venta para continuar al paso de confirmación.</div>
                                        </div>
                                        <x-ui.badge variant="info">{{ $this->wonOpportunities->total() }} disponibles</x-ui.badge>
                                    </div>

                                    <div class="max-h-[38vh] space-y-3 overflow-y-auto pr-1">
                                        @forelse($this->wonOpportunities as $opportunity)
                                            @php
                                                $selected = $selectedWonOpportunityId === $opportunity->id;
                                            @endphp

                                            <button
                                                type="button"
                                                wire:key="won-opportunity-{{ $opportunity->id }}"
                                                wire:click="selectWonOpportunity({{ $opportunity->id }})"
                                                class="w-full rounded-2xl border p-4 text-left shadow-sm transition {{ $selected ? 'border-indigo-300 bg-indigo-50 ring-2 ring-indigo-200' : 'border-slate-200 bg-slate-50 hover:border-slate-300 hover:bg-white' }}"
                                            >
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-semibold text-slate-950">{{ $opportunity->name }}</div>
                                                        <div class="mt-1 text-xs text-slate-500">{{ $opportunity->client?->organization_name ?? 'Sin cliente' }} · {{ $opportunity->display_contact }}</div>
                                                    </div>
                                                    <x-ui.badge size="sm" variant="success">Ganada</x-ui.badge>
                                                </div>

                                                <div class="mt-3 flex flex-wrap gap-2 text-xs text-slate-600">
                                                    <span class="rounded-full bg-white px-2.5 py-1 ring-1 ring-slate-200">Monto: {{ $opportunity->estimated_ticket_amount ? '$'.number_format((float) $opportunity->estimated_ticket_amount, 2, ',', '.') : 'Pendiente' }}</span>
                                                    <span class="rounded-full bg-white px-2.5 py-1 ring-1 ring-slate-200">{{ $opportunity->notes_count }} nota{{ $opportunity->notes_count === 1 ? '' : 's' }}</span>
                                                    <span class="rounded-full bg-white px-2.5 py-1 ring-1 ring-slate-200">{{ $opportunity->attachments_count }} adjunto{{ $opportunity->attachments_count === 1 ? '' : 's' }}</span>
                                                </div>
                                            </button>
                                        @empty
                                            <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                                                No hay ventas ganadas pendientes para convertir.
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="mt-5">
                                        {{ $this->wonOpportunities->links() }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="text-sm font-semibold text-slate-900">Resumen previo</div>
                                <p class="mt-1 text-sm text-slate-500">Revisá los datos de la venta antes de generar la operación.</p>

                                @if($this->selectedWonOpportunity)
                                    <div class="mt-4 space-y-4">
                                        <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200">
                                            <div class="text-xs uppercase tracking-wide text-slate-500">Venta</div>
                                            <div class="mt-1 text-lg font-semibold text-slate-950">{{ $this->selectedWonOpportunity->name }}</div>
                                            <div class="mt-1 text-sm text-slate-600">{{ $this->selectedWonOpportunity->source->label() }}</div>
                                        </div>

                                        <div class="grid grid-cols-1 gap-3 text-sm text-slate-700 sm:grid-cols-2">
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <div class="text-xs uppercase tracking-wide text-slate-500">Cliente</div>
                                                <div class="mt-1 font-medium text-slate-950">{{ $this->selectedWonOpportunity->client?->organization_name ?? 'Sin cliente' }}</div>
                                            </div>
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <div class="text-xs uppercase tracking-wide text-slate-500">Contacto</div>
                                                <div class="mt-1 font-medium text-slate-950">{{ $this->selectedWonOpportunity->display_contact }}</div>
                                            </div>
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <div class="text-xs uppercase tracking-wide text-slate-500">Monto estimado</div>
                                                <div class="mt-1 font-medium text-slate-950">{{ $this->selectedWonOpportunity->estimated_ticket_amount ? '$'.number_format((float) $this->selectedWonOpportunity->estimated_ticket_amount, 2, ',', '.') : 'Pendiente' }}</div>
                                            </div>
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <div class="text-xs uppercase tracking-wide text-slate-500">Contenido</div>
                                                <div class="mt-1 font-medium text-slate-950">{{ $this->selectedWonOpportunity->notes_count }} nota{{ $this->selectedWonOpportunity->notes_count === 1 ? '' : 's' }} y {{ $this->selectedWonOpportunity->attachments_count }} adjunto{{ $this->selectedWonOpportunity->attachments_count === 1 ? '' : 's' }}</div>
                                            </div>
                                        </div>

                                        @if($this->selectedWonOpportunity->notes->isNotEmpty())
                                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                                <div class="text-xs uppercase tracking-wide text-slate-500">Última nota</div>
                                                <div class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $this->selectedWonOpportunity->notes->last()->content }}</div>
                                            </div>
                                        @endif

                                        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                                            Al confirmar, se creará la operación en Operaciones copiando el cliente, el monto y el contexto comercial de esta venta ganada.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-slate-50/80 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            @error('selectedWonOpportunityId') <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                        </div>
                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center">
                            <x-ui.button type="button" variant="secondary" wire:click="closeCreate">Cancelar</x-ui.button>

                            @if($createStep === 1)
                                <x-ui.button type="button" wire:click="goToCreateReview" :disabled="! $selectedWonOpportunityId">
                                    Ver resumen
                                </x-ui.button>
                            @else
                                <x-ui.button type="button" variant="secondary" wire:click="backToCreateSelection">
                                    Volver
                                </x-ui.button>
                                <x-ui.button type="button" variant="success" wire:click="convertSelectedWonOpportunity" :disabled="! $this->selectedWonOpportunity">
                                    Pasar venta a Operaciones
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-ui.page-container>
