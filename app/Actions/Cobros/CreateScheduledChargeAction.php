<?php

namespace App\Actions\Cobros;

use App\Enums\Cobros\ScheduledChargeStatus;
use App\Models\Client;
use App\Models\ScheduledCharge;
use App\Models\User;

class CreateScheduledChargeAction
{
    /**
     * @param  array{reference_name:string, detail?:string|null, amount:numeric, charge_date:string, frequency:string}  $data
     */
    public function execute(Client $client, array $data, ?User $user = null): ScheduledCharge
    {
        return ScheduledCharge::query()->create([
            'client_id' => $client->id,
            'created_by' => $user?->id,
            'reference_name' => $data['reference_name'],
            'detail' => $data['detail'] ?? null,
            'amount' => $data['amount'],
            'charge_date' => $data['charge_date'],
            'frequency' => $data['frequency'],
            'status' => ScheduledChargeStatus::Active->value,
        ]);
    }
}
