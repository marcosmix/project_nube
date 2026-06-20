<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientsSeeder extends Seeder
{
    public function run(): void
    {
        $existing = Client::query()->count();

        if ($existing < 40) {
            Client::factory()->count(40 - $existing)->create();
        }
    }
}
