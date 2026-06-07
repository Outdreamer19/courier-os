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
            'name' => "Ship'd JM Florida Warehouse",
            'address_line_1' => '1234 Placeholder Way',
            'address_line_2' => 'Suite #SJM-000001',
            'city' => 'Miami',
            'state' => 'FL',
            'zip' => '33101',
            'phone' => '+1 (555) 010-0000',
            'instructions' => "Use your Ship'd JM customer reference (SJM-XXXXXX) as the suite number on every shipment. This address is a placeholder until the live Florida warehouse is finalised.",
            'is_active' => true,
        ]);
    }
}
