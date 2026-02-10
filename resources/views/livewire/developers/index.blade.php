<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl mb-2 text-gray-900">Developers</h1>
            <p class="text-gray-600">Gestiona tu equipo de desarrollo</p>
        </div>

        <button wire:click="openCreate"
            class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 transition">
            <span class="mr-2">+</span>
            Nuevo Developer
        </button>
    </div>

    {{-- Search --}}
    <div class="rounded-2xl border bg-white p-4">
        <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">🔎</span>
            <input
                type="text"
                placeholder="Buscar developers por nombre, email o puesto..."
                wire:model.live.debounce.300ms="search"
                class="w-full rounded-lg border border-gray-200 py-2 pl-10 pr-3 outline-none focus:ring-2 focus:ring-blue-200"
            />
        </div>
    </div>

    {{-- Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($developers as $dev)
            <div class="rounded-2xl border bg-white p-6 hover:shadow-lg transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    {{-- Avatar --}}
                    <div class="flex items-center gap-3">
                        <div class="size-16 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center">
                            @if($dev->avatar_url)
                                <img src="{{ $dev->avatar_url }}" class="size-16 object-cover" alt="avatar">
                            @else
                                <span class="text-sm font-semibold text-gray-600">
                                    {{ strtoupper(substr($dev->contact->first_name,0,1).substr($dev->contact->last_name,0,1)) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Dropdown simple (sin shadcn) --}}
                    <div class="relative" x-data="{open:false}">
                        <button @click="open=!open" class="rounded-lg p-2 hover:bg-gray-100">
                            ⋮
                        </button>

                        <div x-cloak x-show="open" @click.outside="open=false"
                             class="absolute right-0 mt-2 w-40 rounded-xl border bg-white shadow-lg overflow-hidden z-10">
                            <button wire:click="edit({{ $dev->id }})" @click="open=false"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50">
                                ✏️ Editar
                            </button>
                            <button wire:click="delete({{ $dev->id }})" @click="open=false"
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                🗑 Eliminar
                            </button>
                        </div>
                    </div>
                </div>

                <h3 class="text-lg mb-1 text-gray-900">
                    {{ $dev->contact->first_name }} {{ $dev->contact->last_name }}
                </h3>
                <p class="text-sm text-gray-600 mb-3">
                    {{ $dev->contact->job_title ?? $dev->level_label }}
                </p>

                {{-- Badges --}}
                <div class="flex gap-2 mb-4">
                    <span class="text-xs px-2 py-1 rounded
                        {{ $dev->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                        {{ $dev->status_label }}
                    </span>

                    <span class="text-xs px-2 py-1 rounded
                        {{ $dev->availability === 'full_time' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                        {{ $dev->availability_label }}
                    </span>
                </div>

                {{-- Contact --}}
                <div class="space-y-2 mb-4">
                    <div class="flex items-center gap-2 text-sm text-gray-600">
                        <span>✉️</span>
                        <span class="truncate">{{ $dev->contact->email }}</span>
                    </div>

                    <div class="flex gap-2">
                        @if($dev->github_url)
                            <a href="{{ $dev->github_url }}" target="_blank"
                               class="rounded-lg p-2 hover:bg-gray-100 border border-gray-200">
                                GH
                            </a>
                        @endif
                        @if($dev->linkedin_url)
                            <a href="{{ $dev->linkedin_url }}" target="_blank"
                               class="rounded-lg p-2 hover:bg-gray-100 border border-gray-200">
                                IN
                            </a>
                        @endif
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    <div class="flex justify-between text-sm mb-3">
                        <span class="text-gray-600">Puntuación:</span>
                        <span class="text-gray-900">{{ $dev->score ?? '-' }}</span>
                    </div>

                    <div>
                        <p class="text-xs text-gray-600 mb-2">Skills:</p>
                        <div class="flex flex-wrap gap-1">
                            @foreach(($dev->skills ?? []) as $skill)
                                <span class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div>
        {{ $developers->links() }}
    </div>

    {{-- Stats Footer --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-2xl border bg-white p-4">
            <p class="text-sm text-gray-600 mb-1">Total Developers</p>
            <p class="text-2xl text-gray-900">{{ $total }}</p>
        </div>
        <div class="rounded-2xl border bg-white p-4">
            <p class="text-sm text-gray-600 mb-1">Activos</p>
            <p class="text-2xl text-green-600">{{ $activos }}</p>
        </div>
        <div class="rounded-2xl border bg-white p-4">
            <p class="text-sm text-gray-600 mb-1">Tiempo Completo</p>
            <p class="text-2xl text-blue-600">{{ $fullTime }}</p>
        </div>
        <div class="rounded-2xl border bg-white p-4">
            <p class="text-sm text-gray-600 mb-1">Freelance</p>
            <p class="text-2xl text-purple-600">{{ $freelance }}</p>
        </div>
    </div>

    {{-- Modal Create/Edit (podés reutilizar tu modal responsive que ya arreglamos) --}}
    {{-- Si querés, te lo paso ya con fields de Developer y sticky actions --}}
</div>