<div class="py-8">
    <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
      

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <div>
                        <h1 class="mb-2 text-3xl text-gray-900">Developers</h1>
                        <p class="text-gray-600">Gestiona tu equipo de desarrollo</p>
                    </div>

                    <button
                        wire:click="openCreate"
                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700"
                    >
                        <span class="mr-2">+</span>
                        Nuevo Developer
                    </button>
                </div>

                <div class="rounded-2xl border bg-white p-4">
                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-4">
                        <div class="relative lg:col-span-2">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔎</span>
                            <input
                                type="text"
                                placeholder="Buscar developers por nombre, email o github..."
                                wire:model.live.debounce.300ms="search"
                                class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-3 outline-none focus:ring-2 focus:ring-blue-200"
                            />
                        </div>

                        <select wire:model.live="statusFilter" class="rounded-lg border border-gray-200 px-3 py-2">
                            <option value="">Estado (todos)</option>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>

                        <div class="grid grid-cols-2 gap-3">
                            <select wire:model.live="availabilityFilter" class="rounded-lg border border-gray-200 px-3 py-2">
                                <option value="">Disponibilidad</option>
                                @foreach($availabilities as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>

                            <select wire:model.live="levelFilter" class="rounded-lg border border-gray-200 px-3 py-2">
                                <option value="">Nivel</option>
                                @foreach($levels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @forelse($developers as $dev)
                        @php
                            $projectCount = (int) ($dev->projects_count ?? 0);
                            $borderClass = match (true) {
                                $projectCount === 0 => 'border-green-400',
                                $projectCount === 2 => 'border-yellow-400',
                                $projectCount >= 3 => 'border-red-400',
                                default => 'border-gray-200',
                            };
                        @endphp
                        <div class="rounded-2xl border-2 {{ $borderClass }} bg-white p-6 transition-shadow hover:shadow-lg">
                            <div class="mb-4 flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-16 items-center justify-center overflow-hidden rounded-full bg-gray-100">
                                        @if($dev->avatar_url)
                                            <img src="{{ $dev->avatar_url }}" class="size-16 object-cover" alt="avatar">
                                        @else
                                            <span class="text-sm font-semibold text-gray-600">
                                                {{ strtoupper(substr($dev->contact->first_name, 0, 1) . substr($dev->contact->last_name, 0, 1)) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="relative" x-data="{open:false}">
                                    <button @click="open=!open" class="rounded-lg p-2 hover:bg-gray-100">⋮</button>

                                    <div
                                        x-cloak
                                        x-show="open"
                                        @click.outside="open=false"
                                        class="absolute right-0 z-10 mt-2 w-44 overflow-hidden rounded-xl border bg-white shadow-lg"
                                    >
                                        <button wire:click="openEdit({{ $dev->id }})" @click="open=false" class="w-full px-4 py-2 text-left text-sm hover:bg-gray-50">✏️ Editar</button>
                                        <button
                                            wire:click="delete({{ $dev->id }})"
                                            @click="open=false"
                                            wire:confirm="¿Seguro que querés eliminar este developer?"
                                            class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50"
                                        >🗑 Eliminar</button>
                                    </div>
                                </div>
                            </div>

                            <h3 class="mb-1 text-lg text-gray-900">{{ $dev->contact->first_name }} {{ $dev->contact->last_name }}</h3>
                            <p class="text-sm text-gray-600">{{ $dev->contact->job_title ?? $dev->level_label }}</p>
                            <p class="mb-3 mt-1 text-xs text-gray-500">
                                Proyectos activos: {{ $projectCount }}
                                @if($projectCount > 0)
                                    · {{ $dev->projects->pluck('name')->join(', ') }}
                                @endif
                            </p>

                            <div class="mb-4 flex gap-2">
                                <span class="rounded px-2 py-1 text-xs {{ $dev->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $dev->status_label }}
                                </span>

                                <span class="rounded px-2 py-1 text-xs {{ $dev->availability === 'full_time' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                    {{ $dev->availability_label }}
                                </span>
                            </div>

                            <div class="mb-4 space-y-2">
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <span>✉️</span>
                                    <span class="truncate">{{ $dev->contact->email }}</span>
                                </div>

                                <div class="flex gap-2">
                                    @if($dev->github_url)
                                        <a href="{{ $dev->github_url }}" target="_blank" class="rounded-lg border border-gray-200 p-2 hover:bg-gray-100">GH</a>
                                    @endif
                                    @if($dev->linkedin_url)
                                        <a href="{{ $dev->linkedin_url }}" target="_blank" class="rounded-lg border border-gray-200 p-2 hover:bg-gray-100">IN</a>
                                    @endif
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-4">
                                <div class="mb-3 flex justify-between text-sm">
                                    <span class="text-gray-600">Puntuación:</span>
                                    <span class="text-gray-900">{{ $dev->score ?? '-' }}</span>
                                </div>

                                <div>
                                    <p class="mb-2 text-xs text-gray-600">Skills:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(($dev->skills ?? []) as $skill)
                                            <span class="rounded bg-gray-100 px-2 py-1 text-xs text-gray-700">{{ $skill }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full rounded-2xl border bg-white p-8 text-center text-gray-500">
                            No hay developers cargados.
                        </div>
                    @endforelse
                </div>

                <div>{{ $developers->links() }}</div>

                <div class="rounded-2xl border bg-white p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <p class="text-sm text-gray-600">Disponibles por skill (sin proyectos)</p>
                        <span class="text-xs text-gray-500">General</span>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @forelse($availableBySkill as $skill => $count)
                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                                {{ $skill }}: {{ $count }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-500">No hay developers disponibles sin proyectos.</span>
                        @endforelse
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                    <div class="rounded-2xl border bg-white p-4">
                        <p class="mb-1 text-sm text-gray-600">Total Developers</p>
                        <p class="text-2xl text-gray-900">{{ $total }}</p>
                    </div>
                    <div class="rounded-2xl border bg-white p-4">
                        <p class="mb-1 text-sm text-gray-600">Activos</p>
                        <p class="text-2xl text-green-600">{{ $activos }}</p>
                    </div>
                    <div class="rounded-2xl border bg-white p-4">
                        <p class="mb-1 text-sm text-gray-600">Tiempo Completo</p>
                        <p class="text-2xl text-blue-600">{{ $fullTime }}</p>
                    </div>
                    <div class="rounded-2xl border bg-white p-4">
                        <p class="mb-1 text-sm text-gray-600">Freelance</p>
                        <p class="text-2xl text-purple-600">{{ $freelance }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    </div>

    @include('livewire.developers.partials.developer-form')
</div>

