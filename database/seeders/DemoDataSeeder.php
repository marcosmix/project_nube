<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            UsersSeeder::class,
            ClientsSeeder::class,
            TeamSeeder::class,
            SalesDemoSeeder::class,
            OperationsDemoSeeder::class,
            CobrosDemoSeeder::class,
        ]);
    }
}
