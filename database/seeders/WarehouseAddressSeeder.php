<?php

namespace Database\Seeders;

use App\Models\WarehouseAddress;
use Illuminate\Database\Seeder;

class WarehouseAddressSeeder extends Seeder
{
    public function run(): void
    {
        if (WarehouseAddress::query()->exists()) {
            return;
        }

        WarehouseAddress::create([
            'name' => 'SHIP DJM Florida Warehouse',
            'address_line_1' => '1234 Placeholder Way',
            'address_line_2' => 'Suite #DJM-000001',
            'city' => 'Miami',
            'state' => 'FL',
            'zip' => '33101',
            'phone' => '+1 (555) 010-0000',
            'instructions' => 'Use your SHIP DJM customer reference (DJM-XXXXXX) as the suite number on every shipment. This address is a placeholder until the live Florida warehouse is finalised.',
            'is_active' => true,
        ]);
    }
}
