<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@nubecode.ar'],
            [
                'name' => 'Admin Nube',
                'password' => Hash::make('admin'),
                'email_verified_at' => now(),
            ]
        );
    }
}