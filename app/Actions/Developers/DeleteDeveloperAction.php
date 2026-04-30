<?php

namespace App\Actions\Developers;

use App\Models\Developer;
use Illuminate\Support\Facades\DB;

class DeleteDeveloperAction
{
    public function execute(Developer $developer): void
    {
        $developer->loadMissing('contact');

        DB::transaction(function () use ($developer): void {
            if (! $developer->trashed()) {
                $developer->delete();
            }

            if ($developer->contact && ! $developer->contact->trashed()) {
                $developer->contact->delete();
            }
        });
    }
}
