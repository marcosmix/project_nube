<?php

namespace App\Livewire\Settings;

use App\Models\JobPosition;
use App\Models\TeamSkill;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public ?int $editingJobPositionId = null;

    public string $jobPositionName = '';

    public ?int $editingTeamSkillId = null;

    public string $teamSkillName = '';

    public function editJobPosition(int $id): void
    {
        $position = JobPosition::query()->findOrFail($id);

        $this->editingJobPositionId = $position->id;
        $this->jobPositionName = $position->name;
        $this->resetValidation('jobPositionName');
    }

    public function saveJobPosition(): void
    {
        $validated = $this->validate([
            'jobPositionName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('job_positions', 'name')->ignore($this->editingJobPositionId),
            ],
        ], [], [
            'jobPositionName' => 'puesto de trabajo',
        ]);

        JobPosition::query()->updateOrCreate(
            ['id' => $this->editingJobPositionId],
            ['name' => $this->normalizeName($validated['jobPositionName'])]
        );

        $this->resetJobPositionForm();
        $this->dispatch('toast', type: 'success', message: 'Puesto guardado');
    }

    public function toggleJobPositionStatus(int $id): void
    {
        $position = JobPosition::query()->findOrFail($id);
        $position->update(['is_active' => ! $position->is_active]);

        $this->dispatch('toast', type: 'success', message: $position->is_active ? 'Puesto activado' : 'Puesto desactivado');
    }

    public function resetJobPositionForm(): void
    {
        $this->editingJobPositionId = null;
        $this->jobPositionName = '';
        $this->resetValidation('jobPositionName');
    }

    public function editTeamSkill(int $id): void
    {
        $skill = TeamSkill::query()->findOrFail($id);

        $this->editingTeamSkillId = $skill->id;
        $this->teamSkillName = $skill->name;
        $this->resetValidation('teamSkillName');
    }

    public function saveTeamSkill(): void
    {
        $validated = $this->validate([
            'teamSkillName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('team_skills', 'name')->ignore($this->editingTeamSkillId),
            ],
        ], [], [
            'teamSkillName' => 'skill',
        ]);

        TeamSkill::query()->updateOrCreate(
            ['id' => $this->editingTeamSkillId],
            ['name' => $this->normalizeName($validated['teamSkillName'])]
        );

        $this->resetTeamSkillForm();
        $this->dispatch('toast', type: 'success', message: 'Skill guardada');
    }

    public function toggleTeamSkillStatus(int $id): void
    {
        $skill = TeamSkill::query()->findOrFail($id);
        $skill->update(['is_active' => ! $skill->is_active]);

        $this->dispatch('toast', type: 'success', message: $skill->is_active ? 'Skill activada' : 'Skill desactivada');
    }

    public function resetTeamSkillForm(): void
    {
        $this->editingTeamSkillId = null;
        $this->teamSkillName = '';
        $this->resetValidation('teamSkillName');
    }

    public function render()
    {
        return view('livewire.settings.index', [
            'jobPositions' => JobPosition::query()->orderByDesc('is_active')->orderBy('name')->get(),
            'teamSkills' => TeamSkill::query()->orderByDesc('is_active')->orderBy('name')->get(),
        ]);
    }

    private function normalizeName(string $value): string
    {
        return preg_replace('/\s+/', ' ', trim($value)) ?? '';
    }
}
