<x-ui.page-container>
    <div class="space-y-6">
        <x-ui.section-header
            title="Proyectos"
            description="Monitorea estado, riesgo y valor del portafolio sin salir del flujo operativo."
            eyebrow="Operacion"
        >
            <x-slot:actions>
                <x-ui.button type="button" wire:click="openCreate">
                    Nuevo proyecto
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

        <x-ui.stat-card label="Proyectos totales" :value="number_format($all, 0, ',', '.')" :hint="number_format($execution, 0, ',', '.').' en ejecucion'" tone="primary" />
        <x-ui.stat-card label="Finalizados" :value="number_format($finished, 0, ',', '.')" :hint="($all > 0 ? round(($finished / $all) * 100) : 0).'% tasa de cierre'" tone="success" />
        <x-ui.stat-card label="Con problemas" :value="number_format($issues, 0, ',', '.')" hint="Proyectos con deuda o demora." tone="danger" />
        <x-ui.stat-card label="Valor promedio" :value="'$'.number_format($avg, 0, ',', '.')" :hint="'Total: $'.number_format($totalRevenue, 0, ',', '.')" tone="accent" />
    </div>

    <x-ui.card class="space-y-5 bg-slate-50/70">
        <div>
            <h2 class="text-lg font-semibold text-slate-950">Vista de portafolio</h2>
            <p class="text-sm text-slate-500">Filtra por estado y detecta rapido proyectos activos, pausados o con riesgo.</p>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <x-ui.input
                type="text"
                wire:model.live="search"
                placeholder="Buscar por nombre, cliente o empresa..."
            />

            <x-ui.select wire:model.live="statusFilter">
                <option value="all">Todos los estados</option>
                <option value="prospection">En Prospección</option>
                <option value="interested">Interesado</option>
                <option value="sale_closed">Venta Cerrada</option>
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

            <a href="{{ route('proyectos.show', $project) }}"
                class="block overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-200/70 transition hover:-translate-y-0.5 hover:shadow-md">
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

                        <span class="rounded-2xl bg-slate-100 px-3 py-2 text-sm text-slate-600">
                            Ver
                        </span>
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
                            Proyecto pausado temporalmente
                        </div>
                    @endif
                </div>
            </a>
        @empty
            <div class="lg:col-span-2">
                <x-ui.empty-state title="No se encontraron proyectos" description="Prueba con otro estado o una busqueda mas amplia para recuperar resultados." />
            </div>
        @endforelse
    </div>

        <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
            {{ $projects->links() }}
        </div>

        {{-- Modal Create (simple) --}}
        @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/45 p-4">
            <div class="w-full max-w-4xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70">
                <div class="flex items-center justify-between border-b border-slate-200 p-5">
                    <div>
                        <h3 class="text-xl font-semibold text-slate-950">Nuevo proyecto</h3>
                        <p class="text-sm text-slate-500">Completa la información base y prepara el proyecto para su ciclo operativo.</p>
                    </div>
                    <button wire:click="closeCreate" class="rounded-2xl px-3 py-2 text-slate-500 transition hover:bg-slate-100">✕</button>
                </div>

                <div class="max-h-[80vh] overflow-y-auto p-6">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <x-ui.label>Nombre del proyecto *</x-ui.label>
                            <x-ui.input wire:model.defer="form.name" />
                            @error('form.name') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Estado *</label>
                            <input type="hidden" wire:model="form.status" value="prospection" />
                            <div class="mt-1 rounded-lg border border-amber-300 bg-amber-50 px-4 py-2 text-sm text-amber-900">
                                En Prospección (estado inicial automático)
                            </div>
                            @error('form.status') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <x-ui.label>Cliente *</x-ui.label>
                            <x-ui.select wire:model.defer="form.client_id">
                                <option value="">Selecciona un cliente</option>
                                @foreach($this->clients as $c)
                                    <option value="{{ $c->id }}">
                                        {{ $c->organization_name }} - {{ $c->contact->first_name }} {{ $c->contact->last_name }}
                                    </option>
                                @endforeach
                            </x-ui.select>
                            @error('form.client_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                        </div>

                        {{-- Subestado execution --}}
                        @if(($form['status'] ?? null) === 'execution')
                            <div class="md:col-span-2">
                                <x-ui.label>Sub-estado de ejecución</x-ui.label>
                                <x-ui.select wire:model.defer="form.execution_sub_status">
                                    <option value="">—</option>
                                    <option value="on_track">Al Día</option>
                                    <option value="with_debt">Con Deuda</option>
                                    <option value="delayed">Con Demora</option>
                                </x-ui.select>
                                @error('form.execution_sub_status') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        {{-- Notas --}}
                        <div class="md:col-span-2">
                            <x-ui.label>Notas</x-ui.label>
                            <x-ui.textarea wire:model.defer="form.prospection_notes" rows="4"></x-ui.textarea>
                            @error('form.prospection_notes') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- Contratación desde interested --}}
                    @if(($form['status'] ?? 'prospection') !== 'prospection')
                        <x-ui.panel class="mt-6">
                            <div class="mb-4 text-lg font-semibold text-slate-950">Información de contratación</div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <x-ui.field-group label="Costo total">
                                    <x-ui.input type="number" wire:model.defer="form.total_cost" />
                                    @error('form.total_cost') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </x-ui.field-group>

                                <x-ui.field-group label="Inicio estimado">
                                    <x-ui.input type="date" wire:model.defer="form.estimated_start_date" />
                                    @error('form.estimated_start_date') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </x-ui.field-group>

                                <x-ui.field-group label="Fin estimado">
                                    <x-ui.input type="date" wire:model.defer="form.estimated_end_date" />
                                    @error('form.estimated_end_date') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </x-ui.field-group>

                                <x-ui.field-group class="md:col-span-2" label="Link propuesta (PDF)">
                                    <x-ui.input wire:model.defer="form.proposal_url" />
                                    @error('form.proposal_url') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </x-ui.field-group>

                                <x-ui.field-group class="md:col-span-2" label="Link Excel (anexo)">
                                    <x-ui.input wire:model.defer="form.excel_url" />
                                    @error('form.excel_url') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </x-ui.field-group>
                            </div>
                        </x-ui.panel>
                    @endif

                    {{-- Equipo desde venta cerrada --}}
                    @if(in_array(($form['status'] ?? 'prospection'), ['sale_closed','execution','paused','finished'], true))
                        <x-ui.panel class="mt-6">
                            <div class="mb-4 text-lg font-semibold text-slate-950">Asignación de equipo</div>

                            <div class="grid max-h-64 grid-cols-1 gap-3 overflow-y-auto p-1 md:grid-cols-2">
                                @foreach($this->developers as $d)
                                    @php
                                        $checked = in_array($d->id, $selectedDevelopers, true);
                                    @endphp
                                    <x-ui.radio-card :checked="$checked" class="flex items-center gap-3 border-2 p-3">
                                        <input
                                            type="checkbox"
                                            wire:model.defer="selectedDevelopers"
                                            value="{{ $d->id }}"
                                            class="rounded"
                                        />
                                        <div>
                                            <div class="text-sm text-slate-900">
                                                {{ $d->contact->first_name }} {{ $d->contact->last_name }}
                                            </div>
                                            <div class="text-xs text-slate-500">
                                                {{ $d->level }}
                                            </div>
                                        </div>
                                    </x-ui.radio-card>
                                @endforeach
                            </div>

                            @error('selectedDevelopers') <div class="mt-2 text-xs text-red-600">{{ $message }}</div> @enderror
                            @error('selectedDevelopers.*') <div class="mt-2 text-xs text-red-600">{{ $message }}</div> @enderror

                            <div class="mt-2 text-sm text-slate-600">
                                {{ count($selectedDevelopers) }} developer(s) seleccionado(s)
                            </div>
                        </x-ui.panel>
                    @endif

                    {{-- Ejecución --}}
                    @if(in_array(($form['status'] ?? 'prospection'), ['execution','paused','finished'], true))
                        <x-ui.panel class="mt-6">
                            <div class="mb-4 text-lg font-semibold text-slate-950">Información de ejecución</div>

                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <x-ui.field-group label="Día cierre sprint (1-31)">
                                    <x-ui.input type="number" min="1" max="31" wire:model.defer="form.sprint_close_day" />
                                    @error('form.sprint_close_day') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </x-ui.field-group>

                                <x-ui.field-group label="Fecha real inicio">
                                    <x-ui.input type="date" wire:model.defer="form.actual_start_date" />
                                    @error('form.actual_start_date') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                </x-ui.field-group>
                            </div>
                        </x-ui.panel>
                    @endif

                    {{-- Pausa --}}
                    @if(($form['status'] ?? null) === 'paused')
                        <x-ui.panel class="mt-6" tone="purple">
                            <div class="mb-2 text-lg font-semibold text-purple-900">Motivo de Pausa</div>
                            <x-ui.textarea wire:model.defer="form.pause_reason" rows="3" class="border-purple-200 bg-white"></x-ui.textarea>
                            @error('form.pause_reason') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                        </x-ui.panel>
                    @endif
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 p-5">
                    <x-ui.button type="button" variant="secondary" wire:click="closeCreate">Cancelar</x-ui.button>
                    <x-ui.button type="button" wire:click="create">Crear proyecto</x-ui.button>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-ui.page-container>
