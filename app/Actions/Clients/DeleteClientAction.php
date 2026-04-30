<?php

namespace App\Actions\Clients;

use App\Models\Client;
use Illuminate\Support\Facades\DB;

class DeleteClientAction
{
    public function execute(Client $client): void
    {
        $client->loadMissing([
            'contact',
            'opportunities',
            'projects.paymentFlows',
        ]);

        DB::transaction(function () use ($client): void {
            foreach ($client->projects as $project) {
                $project->paymentFlows()
                    ->whereNull('deleted_at')
                    ->delete();

                if (! $project->trashed()) {
                    $project->delete();
                }
            }

            foreach ($client->opportunities as $opportunity) {
                if (! $opportunity->trashed()) {
                    $opportunity->delete();
                }
            }

            if (! $client->trashed()) {
                $client->delete();
            }

            if ($client->contact && ! $client->contact->trashed()) {
                $client->contact->delete();
            }
        });
    }
}
