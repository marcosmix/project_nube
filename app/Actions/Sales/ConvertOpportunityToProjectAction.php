<?php

namespace App\Actions\Sales;

use App\Enums\ProjectStatus;
use App\Enums\Sales\OpportunityStatus;
use App\Models\Opportunity;
use App\Models\Project;
use App\Models\ProjectNote;
use App\Models\ProjectStatusLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConvertOpportunityToProjectAction
{
    /**
     * Convierte una oportunidad comercial en proyecto legacy sin romper Proyectos/Cobros.
     * Por compatibilidad, el proyecto nuevo nace en `sale_closed` hasta migrar el flujo operativo.
     */
    public function execute(Opportunity $opportunity, ?User $user = null): Project
    {
        $opportunity->loadMissing(['client.contact', 'project']);

        $this->ensureCanConvert($opportunity);

        return DB::transaction(function () use ($opportunity, $user) {
            $project = Project::create([
                'name' => $opportunity->name,
                'status' => ProjectStatus::SaleClosed->value,
                'execution_sub_status' => null,
                'client_id' => $opportunity->client_id,
                'opportunity_id' => $opportunity->id,
                'prospection_notes' => $this->buildInitialProjectNotes($opportunity),
                'proposal_url' => null,
                'excel_url' => null,
                'total_cost' => null,
                'estimated_start_date' => null,
                'estimated_end_date' => null,
                'sprint_close_day' => null,
                'actual_start_date' => null,
                'actual_end_date' => null,
                'pause_reason' => null,
                'paused_at' => null,
            ]);

            ProjectStatusLog::create([
                'project_id' => $project->id,
                'status' => ProjectStatus::SaleClosed->value,
                'by_user_id' => $user?->id,
                'created_at' => now(),
            ]);

            ProjectNote::create([
                'project_id' => $project->id,
                'content' => 'Proyecto creado a partir de la oportunidad comercial #'.$opportunity->id.'.',
                'status' => ProjectStatus::SaleClosed->value,
                'by_user_id' => $user?->id,
            ]);

            return $project->fresh(['client.contact', 'statusLogs.byUser', 'notes.byUser']);
        });
    }

    protected function ensureCanConvert(Opportunity $opportunity): void
    {
        if ($opportunity->project) {
            throw ValidationException::withMessages([
                'opportunity' => 'La oportunidad ya fue convertida en proyecto.',
            ]);
        }

        if ($opportunity->status !== OpportunityStatus::Won) {
            throw ValidationException::withMessages([
                'status' => 'Solo las oportunidades ganadas pueden convertirse en proyecto.',
            ]);
        }

        if (! $opportunity->client_id) {
            throw ValidationException::withMessages([
                'client_id' => 'Antes de convertir la oportunidad debes asociarla a un cliente.',
            ]);
        }
    }

    protected function buildInitialProjectNotes(Opportunity $opportunity): ?string
    {
        $parts = array_filter([
            $opportunity->initial_message ? 'Mensaje inicial: '.$opportunity->initial_message : null,
            $opportunity->contact_name ? 'Contacto: '.$opportunity->contact_name : null,
            $opportunity->contact_phone ? 'Telefono: '.$opportunity->contact_phone : null,
            $opportunity->contact_email ? 'Email: '.$opportunity->contact_email : null,
            $opportunity->contact_handle ? 'Red/usuario: '.$opportunity->contact_handle : null,
        ]);

        return empty($parts) ? null : implode(PHP_EOL, $parts);
    }
}
