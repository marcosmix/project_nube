<?php

namespace App\Actions\Cobros;

use App\Enums\Cobros\PaymentFlowStatus;
use App\Models\PaymentFlow;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreatePaymentFlowAction
{
    public function __construct(
        protected GenerateInstallmentsAction $generateInstallmentsAction
    ) {}

    /**
     * @param  Project  $project
     * @param  array{
     *     total_amount:numeric,
     *     installments_count:int,
     *     frequency:string,
     *     start_date:string|\DateTimeInterface,
     *     grace_days?:int,
     *     notes?:string|null,
     *     status?:string,
     *     auto_send_enabled?:bool,
     *     auto_send_email?:string|null,
     *     custom_installments?:array<int, array{amount:numeric, due_date:string|\DateTimeInterface}>
     * }  $data
     */
    public function execute(Project $project, array $data, ?User $user = null): PaymentFlow
    {
        $this->ensureProjectHasNoOpenFlow($project);

        return DB::transaction(function () use ($project, $data, $user) {
            $status = $data['status'] ?? PaymentFlowStatus::Active->value;

            $flow = PaymentFlow::create([
                'project_id' => $project->id,
                'created_by' => $user?->id,
                'total_amount' => $data['total_amount'],
                'installments_count' => $data['installments_count'],
                'frequency' => $data['frequency'],
                'start_date' => $data['start_date'],
                'grace_days' => $data['grace_days'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'status' => $status,
                'auto_send_enabled' => (bool) ($data['auto_send_enabled'] ?? false),
                'auto_send_email' => $data['auto_send_email'] ?? null,
                'activated_at' => $status === PaymentFlowStatus::Active->value ? now() : null,
            ]);

            $this->generateInstallmentsAction->execute($flow, $data['custom_installments'] ?? null);

            return $flow->fresh(['installments']);
        });
    }

    protected function ensureProjectHasNoOpenFlow(Project $project): void
    {
        $exists = PaymentFlow::query()
            ->where('project_id', $project->id)
            ->whereIn('status', [
                PaymentFlowStatus::Draft->value,
                PaymentFlowStatus::Active->value,
            ])
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'project_id' => 'Este proyecto ya tiene un flujo de cobro activo o en borrador.',
            ]);
        }
    }
}
