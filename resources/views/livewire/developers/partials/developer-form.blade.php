<div x-data="{ open: $wire.entangle('open') }" x-on:keydown.escape.window="$wire.close()">
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/50" x-cloak></div>

    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="w-full max-w-5xl overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl ring-1 ring-slate-200/70">
            <form wire:submit.prevent="save" class="max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-6 py-5">
                    <div>
                        <h2 class="text-xl font-semibold text-slate-950">{{ $developerId ? 'Editar developer' : 'Nuevo developer' }}</h2>
                        <p class="mt-1 text-sm text-slate-500">Perfil tecnico, skills, disponibilidad e informacion interna.</p>
                    </div>

                    <button type="button" wire:click="close" class="rounded-2xl p-2 text-slate-500 transition hover:bg-slate-100">✕</button>
                </div>

                <div class="space-y-8 px-6 py-5">
                    <section class="space-y-4">
                        <h3 class="text-lg font-medium text-slate-950">Datos personales</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <x-ui.label>Nombre *</x-ui.label>
                                <x-ui.input type="text" wire:model.live="first_name" />
                                @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>Apellido *</x-ui.label>
                                <x-ui.input type="text" wire:model.live="last_name" />
                                @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>Email *</x-ui.label>
                                <x-ui.input type="email" wire:model.live.debounce.400ms="email" />
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>Celular</x-ui.label>
                                <x-ui.input type="text" wire:model.live="phone" />
                                @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>Fecha de nacimiento</x-ui.label>
                                <x-ui.input type="date" wire:model.live="birthdate" />
                                @error('birthdate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>Puesto</x-ui.label>
                                <x-ui.input type="text" wire:model.live="puesto" />
                                @error('puesto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-lg font-medium text-slate-950">Skills e información laboral</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="relative md:col-span-2">
                                <x-ui.label>Skills *</x-ui.label>
                                <div class="flex gap-2">
                                    <x-ui.input
                                        type="text"
                                        wire:model.live.debounce.300ms="newSkill"
                                        wire:keydown.enter.prevent="addSkill"
                                        placeholder="Escribí una skill"
                                    />
                                    <x-ui.button type="button" wire:click="addSkill" :disabled="! $this->canAddTypedSkill" class="px-3 disabled:bg-indigo-300">+</x-ui.button>
                                </div>

                                @if($this->filteredSuggestions)
                                    <div class="absolute z-20 mt-1 w-full rounded-lg border bg-white shadow">
                                        @foreach($this->filteredSuggestions as $suggestion)
                                            <button type="button" wire:click="addSkill({{ \Illuminate\Support\Js::from($suggestion) }})" class="block w-full rounded-xl px-3 py-2 text-left text-sm hover:bg-slate-50">
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
                                <x-ui.label>GitHub username</x-ui.label>
                                <x-ui.input type="text" wire:model.live="github_username" placeholder="usuario" />
                                @error('github_username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>GitHub URL</x-ui.label>
                                <x-ui.input type="url" wire:model.live.debounce.500ms="github_url" placeholder="https://github.com/usuario" />
                                @error('github_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>LinkedIn URL</x-ui.label>
                                <x-ui.input type="url" wire:model.live.debounce.500ms="linkedin_url" placeholder="https://www.linkedin.com/in/usuario" />
                                @error('linkedin_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>Nivel *</x-ui.label>
                                <x-ui.select wire:model.live="level">
                                    @foreach($levels as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                                @error('level') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>Estado *</x-ui.label>
                                <x-ui.select wire:model.live="status">
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                                @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>Disponibilidad *</x-ui.label>
                                <x-ui.select wire:model.live="availability">
                                    @foreach($availabilities as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </x-ui.select>
                                @error('availability') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h3 class="text-lg font-medium text-slate-950">Datos internos (Nube)</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <x-ui.label>Alias bancario</x-ui.label>
                                <x-ui.input type="text" wire:model.live.debounce.400ms="alias" />
                                @error('alias') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>Puntuación (1-10)</x-ui.label>
                                <x-ui.input type="number" min="1" max="10" wire:model.live="score" />
                                @error('score') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>CBU</x-ui.label>
                                <x-ui.input type="text" wire:model.live="cbu" />
                                @error('cbu') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <x-ui.label>Frase</x-ui.label>
                                <x-ui.input type="text" wire:model.live="phrase" />
                                @error('phrase') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <x-ui.label>Foto de perfil</x-ui.label>
                                <x-ui.input type="file" wire:model.live="profile_photo_upload" accept="image/*" class="file:mr-4 file:rounded-xl file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-slate-700" />
                                @error('profile_photo_upload') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                                @if($profile_photo_upload)
                                    <img src="{{ $profile_photo_upload->temporaryUrl() }}" alt="Preview foto" class="mt-3 h-24 w-24 rounded-full object-cover">
                                @elseif($profile_photo)
                                    <img src="{{ asset('storage/' . $profile_photo) }}" alt="Foto actual" class="mt-3 h-24 w-24 rounded-full object-cover">
                                @endif
                            </div>
                            <div class="md:col-span-2">
                                <x-ui.label>Notas</x-ui.label>
                                <x-ui.textarea wire:model.live="notes" rows="4"></x-ui.textarea>
                                @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>
                </div>

                <div class="sticky bottom-0 flex items-center justify-end gap-2 border-t border-slate-200 bg-white px-6 py-4">
                    <x-ui.button type="button" variant="secondary" wire:click="close">Cancelar</x-ui.button>
                    <x-ui.button type="submit" :disabled="empty($skills)" class="disabled:bg-indigo-300">Guardar</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
