<?php

namespace App\Actions\Projects;

use App\Models\Project;
use App\Models\ProjectAmountHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateProjectTotalCostAction
{
    public function execute(Project $project, float|int|string $amount, ?string $reason, ?User $user = null): Project
    {
        $amount = round((float) $amount, 2);
        if ($amount <= 0 || in_array($project->status?->value, ['finished', 'cancelled'], true)) {
            throw ValidationException::withMessages(['form.total_cost' => 'El monto no es válido para esta operación.']);
        }

        if ((float) $project->total_cost === $amount) return $project;
        if (blank(trim((string) $reason))) {
            throw ValidationException::withMessages(['amountChangeReason' => 'El motivo del cambio es obligatorio.']);
        }

        return DB::transaction(function () use ($project, $amount, $reason, $user) {
            ProjectAmountHistory::create([
                'project_id' => $project->id,
                'old_amount' => $project->total_cost,
                'new_amount' => $amount,
                'reason' => trim($reason),
                'changed_by' => $user?->id,
            ]);
            $project->update(['total_cost' => $amount]);
            return $project->fresh();
        });
    }
}
