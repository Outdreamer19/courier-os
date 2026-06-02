<?php

namespace Database\Seeders;

use App\Models\CustomerProfile;
use App\Models\User;
use App\Services\CustomerReferenceGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(CustomerReferenceGenerator $references): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'shane1obdurate@gmail.com'],
            [
                'name' => 'SHIP DJM Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ],
        );

        $customer = User::query()->updateOrCreate(
            ['email' => 'customer@shipdjm.test'],
            [
                'name' => 'Demo Customer',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ],
        );

        CustomerProfile::query()->updateOrCreate(
            ['user_id' => $customer->id],
            [
                'customer_reference' => $customer->customerProfile?->customer_reference ?? $references->next(),
                'phone' => '+1 (876) 555-0100',
                'whatsapp_number' => '+1 (876) 555-0100',
                'jamaica_address' => '12 Hope Road',
                'parish' => 'Kingston',
            ],
        );

        // Silence the unused-variable warning while keeping the admin reference visible.
        unset($admin);
    }
}
