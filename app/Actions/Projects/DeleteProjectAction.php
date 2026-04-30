<?php

namespace App\Actions\Projects;

use App\Models\Project;
use Illuminate\Support\Facades\DB;

class DeleteProjectAction
{
    public function execute(Project $project, bool $deletePaymentFlows = false): void
    {
        $project->loadMissing('paymentFlows');

        DB::transaction(function () use ($project, $deletePaymentFlows): void {
            if ($deletePaymentFlows) {
                $project->paymentFlows()
                    ->whereNull('deleted_at')
                    ->delete();
            }

            if (! $project->trashed()) {
                $project->delete();
            }
        });
    }
}
