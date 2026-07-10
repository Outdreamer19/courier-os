<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            WarehouseAddressSeeder::class,
            ShippingRateSeeder::class,
            UsersSeeder::class,
            DemoDataSeeder::class,
            // Multi-tenant demo data, including the richer "Today Shipping &
            // Logistics" tenant used to showcase the owner dashboard.
            TodayShippingDataSeeder::class,
        ]);
    }
}
