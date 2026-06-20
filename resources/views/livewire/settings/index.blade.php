<x-ui.page-container>
    <div class="space-y-6">
        <x-ui.section-header
            title="Configuración"
            description="Centraliza catálogos internos de la app para mantener consistencia operativa y administrativa."
            eyebrow="Configuración"
        />

        <x-ui.card class="space-y-3">
            <div>
                <div class="text-lg font-semibold text-slate-950">Equipo</div>
                <p class="mt-1 text-sm text-slate-500">Administra los catálogos que luego se usan de forma estricta en el módulo Equipo.</p>
            </div>
        </x-ui.card>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-ui.card class="space-y-5">
                <div>
                    <div class="text-lg font-semibold text-slate-950">Puestos de trabajo</div>
                    <p class="mt-1 text-sm text-slate-500">Define los puestos disponibles para asignar a integrantes del equipo.</p>
                </div>

                <form wire:submit="saveJobPosition" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <x-ui.field-group label="Nombre del puesto">
                        <x-ui.input wire:model.defer="jobPositionName" placeholder="Ej: Backend Developer" />
                        @error('jobPositionName') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    </x-ui.field-group>

                    <div class="flex items-center justify-end gap-3">
                        @if($editingJobPositionId)
                            <x-ui.button type="button" variant="secondary" wire:click="resetJobPositionForm">Cancelar</x-ui.button>
                        @endif
                        <x-ui.button type="submit">{{ $editingJobPositionId ? 'Guardar puesto' : 'Agregar puesto' }}</x-ui.button>
                    </div>
                </form>

                <div class="space-y-3">
                    @forelse($jobPositions as $position)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div>
                                <div class="text-sm font-medium text-slate-950">{{ $position->name }}</div>
                                <div class="mt-1 text-xs {{ $position->is_active ? 'text-emerald-600' : 'text-slate-500' }}">{{ $position->is_active ? 'Activo' : 'Inactivo' }}</div>
                            </div>

                            <div class="flex items-center gap-2">
                                <x-ui.button type="button" size="sm" variant="secondary" wire:click="editJobPosition({{ $position->id }})">Editar</x-ui.button>
                                <x-ui.button type="button" size="sm" :variant="$position->is_active ? 'ghost' : 'success'" wire:click="toggleJobPositionStatus({{ $position->id }})">
                                    {{ $position->is_active ? 'Desactivar' : 'Activar' }}
                                </x-ui.button>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state title="Sin puestos cargados" description="Agrega al menos un puesto para poder asignarlo luego en Equipo." />
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card class="space-y-5">
                <div>
                    <div class="text-lg font-semibold text-slate-950">Skills</div>
                    <p class="mt-1 text-sm text-slate-500">Administra las skills permitidas para selección estricta en el equipo.</p>
                </div>

                <form wire:submit="saveTeamSkill" class="space-y-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <x-ui.field-group label="Nombre de la skill">
                        <x-ui.input wire:model.defer="teamSkillName" placeholder="Ej: Laravel" />
                        @error('teamSkillName') <p class="text-xs text-rose-600">{{ $message }}</p> @enderror
                    </x-ui.field-group>

                    <div class="flex items-center justify-end gap-3">
                        @if($editingTeamSkillId)
                            <x-ui.button type="button" variant="secondary" wire:click="resetTeamSkillForm">Cancelar</x-ui.button>
                        @endif
                        <x-ui.button type="submit">{{ $editingTeamSkillId ? 'Guardar skill' : 'Agregar skill' }}</x-ui.button>
                    </div>
                </form>

                <div class="space-y-3">
                    @forelse($teamSkills as $skill)
                        <div class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div>
                                <div class="text-sm font-medium text-slate-950">{{ $skill->name }}</div>
                                <div class="mt-1 text-xs {{ $skill->is_active ? 'text-emerald-600' : 'text-slate-500' }}">{{ $skill->is_active ? 'Activo' : 'Inactivo' }}</div>
                            </div>

                            <div class="flex items-center gap-2">
                                <x-ui.button type="button" size="sm" variant="secondary" wire:click="editTeamSkill({{ $skill->id }})">Editar</x-ui.button>
                                <x-ui.button type="button" size="sm" :variant="$skill->is_active ? 'ghost' : 'success'" wire:click="toggleTeamSkillStatus({{ $skill->id }})">
                                    {{ $skill->is_active ? 'Desactivar' : 'Activar' }}
                                </x-ui.button>
                            </div>
                        </div>
                    @empty
                        <x-ui.empty-state title="Sin skills cargadas" description="Agrega skills para habilitar su selección estricta dentro del equipo." />
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>
</x-ui.page-container>
