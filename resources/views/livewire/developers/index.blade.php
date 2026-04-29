<x-ui.page-container>
    <div class="space-y-6">
        <x-ui.section-header
            title="Developers"
            description="Gestiona capacidad, disponibilidad y skills del equipo tecnico con una interfaz consistente."
            eyebrow="Talento"
        />

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <x-ui.stat-card label="Total developers" :value="number_format($total, 0, ',', '.')" hint="Base completa de talento disponible." tone="primary" />
            <x-ui.stat-card label="Activos" :value="number_format($activos, 0, ',', '.')" hint="Perfiles habilitados para asignaciones." tone="success" />
            <x-ui.stat-card label="Tiempo completo" :value="number_format($fullTime, 0, ',', '.')" hint="Capacidad principal del equipo actual." tone="info" />
            <x-ui.stat-card label="Freelance" :value="number_format($freelance, 0, ',', '.')" hint="Soporte flexible para picos de demanda." tone="accent" />
        </div>

    <x-ui.card class="space-y-5 bg-slate-50/70">
        <div class="flex flex-col gap-2 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-950">Equipo de desarrollo</h2>
                <p class="text-sm text-slate-500">Filtra por estado, disponibilidad y seniority para balancear mejor la asignacion.</p>
            </div>

            <x-ui.button type="button" wire:click="openCreate">
                Nuevo developer
            </x-ui.button>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <x-ui.label for="developers-search">Buscar</x-ui.label>
                <x-ui.input
                    id="developers-search"
                    type="text"
                    placeholder="Buscar developers por nombre, email o github..."
                    wire:model.live.debounce.300ms="search"
                />
            </div>

            <div>
                <x-ui.label for="developers-status">Estado</x-ui.label>
                <x-ui.select id="developers-status" wire:model.live="statusFilter">
                    <option value="">Estado (todos)</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <x-ui.label for="developers-availability">Disponibilidad</x-ui.label>
                    <x-ui.select id="developers-availability" wire:model.live="availabilityFilter">
                        <option value="">Todas</option>
                        @foreach($availabilities as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                </div>

                <div>
                    <x-ui.label for="developers-level">Nivel</x-ui.label>
                    <x-ui.select id="developers-level" wire:model.live="levelFilter">
                        <option value="">Todos</option>
                        @foreach($levels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </x-ui.select>
                </div>
            </div>
        </div>
    </x-ui.card>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse($developers as $dev)
            @php
                $projectCount = (int) ($dev->projects_count ?? 0);
                $availabilityVariant = $dev->availability === 'full_time' ? 'info' : 'accent';
                $statusVariant = $dev->status === 'active' ? 'success' : 'neutral';
                $borderTone = match (true) {
                    $projectCount === 0 => 'ring-emerald-200',
                    $projectCount === 2 => 'ring-amber-200',
                    $projectCount >= 3 => 'ring-rose-200',
                    default => 'ring-slate-200/70',
                };
            @endphp

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm ring-1 {{ $borderTone }} transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="mb-4 flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex size-16 items-center justify-center overflow-hidden rounded-2xl bg-slate-100">
                            @if($dev->avatar_url)
                                <img src="{{ $dev->avatar_url }}" class="size-16 object-cover" alt="avatar">
                            @else
                                <span class="text-sm font-semibold text-slate-600">
                                    {{ strtoupper(substr($dev->contact->first_name, 0, 1) . substr($dev->contact->last_name, 0, 1)) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="relative" x-data="{open:false}">
                        <button @click="open=!open" class="rounded-2xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">⋮</button>

                        <div
                            x-cloak
                            x-show="open"
                            @click.outside="open=false"
                            class="absolute right-0 z-10 mt-2 w-44 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-lg"
                        >
                            <button wire:click="openEdit({{ $dev->id }})" @click="open=false" class="w-full rounded-xl px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">Editar</button>
                            <button
                                wire:click="delete({{ $dev->id }})"
                                @click="open=false"
                                wire:confirm="¿Seguro que querés eliminar este developer?"
                                class="w-full rounded-xl px-4 py-2 text-left text-sm text-rose-600 hover:bg-rose-50"
                            >Eliminar</button>
                        </div>
                    </div>
                </div>

                <h3 class="mb-1 text-lg font-semibold text-slate-950">{{ $dev->contact->first_name }} {{ $dev->contact->last_name }}</h3>
                <p class="text-sm text-slate-600">{{ $dev->contact->job_title ?? $dev->level_label }}</p>
                <p class="mb-4 mt-1 text-xs text-slate-500">
                    Proyectos activos: {{ $projectCount }}
                    @if($projectCount > 0)
                        · {{ $dev->projects->pluck('name')->join(', ') }}
                    @endif
                </p>

                <div class="mb-4 flex flex-wrap gap-2">
                    <x-ui.badge :variant="$statusVariant">{{ $dev->status_label }}</x-ui.badge>
                    <x-ui.badge :variant="$availabilityVariant">{{ $dev->availability_label }}</x-ui.badge>
                </div>

                <div class="mb-4 space-y-2">
                    <div class="flex items-center gap-2 text-sm text-slate-600">
                        <span class="inline-flex size-7 items-center justify-center rounded-xl bg-slate-100">✉️</span>
                        <span class="truncate">{{ $dev->contact->email }}</span>
                    </div>

                    <div class="flex gap-2">
                        @if($dev->github_url)
                            <a href="{{ $dev->github_url }}" target="_blank" class="inline-flex items-center rounded-2xl border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-50">GitHub</a>
                        @endif
                        @if($dev->linkedin_url)
                            <a href="{{ $dev->linkedin_url }}" target="_blank" class="inline-flex items-center rounded-2xl border border-slate-200 px-3 py-2 text-xs font-medium text-slate-700 transition hover:bg-slate-50">LinkedIn</a>
                        @endif
                    </div>
                </div>

                <div class="border-t border-slate-200 pt-4">
                    <div class="mb-3 flex justify-between text-sm">
                        <span class="text-slate-500">Puntuación</span>
                        <span class="font-medium text-slate-950">{{ $dev->score ?? '-' }}</span>
                    </div>

                    <div>
                        <p class="mb-2 text-xs uppercase tracking-[0.16em] text-slate-500">Skills</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(($dev->skills ?? []) as $skill)
                                <x-ui.badge size="sm">{{ $skill }}</x-ui.badge>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <x-ui.empty-state title="No hay developers cargados" description="Incorpora perfiles para empezar a asignarlos a proyectos y medir capacidad." />
            </div>
        @endforelse
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">{{ $developers->links() }}</div>

    <x-ui.card>
        <div class="mb-3 flex items-center justify-between">
            <p class="text-sm font-medium text-slate-700">Disponibles por skill</p>
            <span class="text-xs uppercase tracking-[0.16em] text-slate-500">Sin proyectos</span>
        </div>

        <div class="flex flex-wrap gap-2">
            @forelse($availableBySkill as $skill => $count)
                <x-ui.badge variant="success">{{ $skill }}: {{ $count }}</x-ui.badge>
            @empty
                <span class="text-sm text-slate-500">No hay developers disponibles sin proyectos.</span>
            @endforelse
        </div>
    </x-ui.card>

        @include('livewire.developers.partials.developer-form')
    </div>
</x-ui.page-container>
