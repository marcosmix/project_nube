<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $existing = User::query()->count();

        if ($existing < 40) {
            User::factory()->count(40 - $existing)->create();
        }
    }
}
