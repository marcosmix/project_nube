<div x-data="{ open: $wire.entangle('open') }" x-on:keydown.escape.window="$wire.close()">
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/50" x-cloak></div>

    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-xl">
            <form wire:submit.prevent="save" class="max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b bg-white px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-900">{{ $developerId ? 'Editar Developer' : 'Nuevo Developer' }}</h2>
                    <button type="button" wire:click="close" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100">✕</button>
                </div>

                <div class="space-y-8 px-6 py-5">
                    <section class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Datos Personales</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Nombre *</label>
                                <input type="text" wire:model.live="first_name" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Apellido *</label>
                                <input type="text" wire:model.live="last_name" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Email *</label>
                                <input type="email" wire:model.live.debounce.400ms="email" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Celular</label>
                                <input type="text" wire:model.live="phone" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Fecha de nacimiento</label>
                                <input type="date" wire:model.live="birthdate" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('birthdate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Puesto</label>
                                <input type="text" wire:model.live="puesto" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('puesto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Skills e Información Laboral</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="relative md:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-gray-700">Skills *</label>
                                <div class="flex gap-2">
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="newSkill"
                                        wire:keydown.enter.prevent="addSkill"
                                        class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Escribí una skill"
                                    >
                                    <button type="button" wire:click="addSkill" @disabled(! $this->canAddTypedSkill) class="rounded-lg bg-indigo-600 px-3 py-2 text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-indigo-300">+</button>
                                </div>

                                @if($this->filteredSuggestions)
                                    <div class="absolute z-20 mt-1 w-full rounded-lg border bg-white shadow">
                                        @foreach($this->filteredSuggestions as $suggestion)
                                            <button
                                                type="button"
                                                wire:click="addSkill({{ \Illuminate\Support\Js::from($suggestion) }})"
                                                class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50"
                                            >
                                                {{ $suggestion }}
                                            </button>
                                        @endforeach
                                    </div>
                                @endif

                                <p class="mt-2 text-xs text-gray-500">Solo podés elegir skills permitidas desde las sugerencias.</p>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    @forelse($skills as $skill)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-xs font-medium text-indigo-700">
                                            {{ $skill }}
                                            <button type="button" wire:click="removeSkill({{ \Illuminate\Support\Js::from($skill) }})" class="font-bold">×</button>
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-500">Todavía no agregaste skills.</span>
                                    @endforelse
                                </div>
                                @error('skills') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                @error('skills.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">GitHub username</label>
                                <input type="text" wire:model.live="github_username" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="usuario">
                                @error('github_username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">GitHub URL</label>
                                <input type="url" wire:model.live.debounce.500ms="github_url" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://github.com/usuario">
                                @error('github_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">LinkedIn URL</label>
                                <input type="url" wire:model.live.debounce.500ms="linkedin_url" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="https://www.linkedin.com/in/usuario">
                                @error('linkedin_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Nivel *</label>
                                <select wire:model.live="level" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($levels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('level') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Estado *</label>
                                <select wire:model.live="status" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Disponibilidad *</label>
                                <select wire:model.live="availability" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($availabilities as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('availability') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Datos Internos (Nube)</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Alias bancario</label>
                                <input type="text" wire:model.live.debounce.400ms="alias" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('alias') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Puntuación (1-10)</label>
                                <input type="number" min="1" max="10" wire:model.live="score" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('score') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">CBU</label>
                                <input type="text" wire:model.live="cbu" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('cbu') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Frase</label>
                                <input type="text" wire:model.live="phrase" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('phrase') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-gray-700">Foto de perfil</label>
                                <input type="file" wire:model.live="profile_photo_upload" accept="image/*" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                @error('profile_photo_upload') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                                @if($profile_photo_upload)
                                    <img src="{{ $profile_photo_upload->temporaryUrl() }}" alt="Preview foto" class="mt-3 h-24 w-24 rounded-full object-cover">
                                @elseif($profile_photo)
                                    <img src="{{ asset('storage/' . $profile_photo) }}" alt="Foto actual" class="mt-3 h-24 w-24 rounded-full object-cover">
                                @endif
                            </div>
                            <div class="md:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-gray-700">Notas</label>
                                <textarea wire:model.live="notes" rows="4" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>
                </div>

                <div class="sticky bottom-0 flex items-center justify-end gap-2 border-t bg-white px-6 py-4">
                    <button type="button" wire:click="close" class="rounded-lg border border-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-50">Cancelar</button>
                    <button type="submit" @disabled(empty($skills)) class="rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:bg-indigo-300">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
