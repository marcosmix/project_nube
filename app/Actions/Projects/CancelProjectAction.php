<?php

namespace App\Actions\Projects;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\ProjectStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelProjectAction
{
    public function execute(Project $project, string $reason, ?User $user = null): Project
    {
        if (in_array($project->status, [ProjectStatus::Finished, ProjectStatus::Cancelled], true)) {
            throw ValidationException::withMessages(['project' => 'La operación ya no puede cancelarse.']);
        }

        return DB::transaction(function () use ($project, $reason, $user) {
            $project->update([
                'status' => ProjectStatus::Cancelled,
                'execution_sub_status' => null,
                'pause_reason' => null,
                'cancelled_at' => now(),
                'cancelled_reason' => trim($reason),
                'cancelled_by' => $user?->id,
            ]);

            ProjectStatusLog::create(['project_id' => $project->id, 'status' => ProjectStatus::Cancelled->value, 'by_user_id' => $user?->id]);

            return $project->fresh();
        });
    }
}
