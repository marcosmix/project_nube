<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            Developers
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Developers</h1>
                    <p class="text-sm text-gray-600">Gestioná el equipo de desarrollo con Livewire.</p>
                </div>

                <button
                    type="button"
                    wire:click="newDeveloper"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                >
                    Nuevo Developer
                </button>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <label for="search" class="mb-2 block text-sm font-medium text-gray-700">Buscar</label>
                <input
                    id="search"
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Nombre, apellido, email o alias"
                    class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Developer</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Puesto</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nivel</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Skills</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white text-sm">
                            @forelse($developers as $developer)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900">{{ $developer->contacto_nombre }} {{ $developer->contacto_apellido }}</p>
                                        <p class="text-xs text-gray-500">{{ $developer->email }}</p>
                                        <p class="text-xs text-gray-500">Alias: {{ $developer->alias }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-700">{{ $developer->puesto }}</td>
                                    <td class="px-4 py-3 text-gray-700">{{ \App\Models\Developer::NIVELES[$developer->nivel] ?? $developer->nivel }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach(array_slice($developer->skills ?? [], 0, 4) as $skill)
                                                <span class="rounded-full bg-indigo-100 px-2 py-1 text-xs text-indigo-700">{{ $skill }}</span>
                                            @endforeach
                                            @if(count($developer->skills ?? []) > 4)
                                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600">+{{ count($developer->skills) - 4 }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button
                                            type="button"
                                            wire:click="editDeveloper({{ $developer->id }})"
                                            class="rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                        >
                                            Editar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">No hay developers para mostrar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-gray-200 px-4 py-3">
                    {{ $developers->links() }}
                </div>
            </div>
        </div>
    </div>

    <livewire:developers.developer-form />
</x-app-layout>
