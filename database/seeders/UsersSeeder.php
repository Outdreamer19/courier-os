<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'yashaepinnock@gmail.com'],
            [
                'name' => 'Yasha Epinnock',
                'password' => Hash::make('password'),
                'role' => User::ROLE_OWNER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'customer@shipdjm.test'],
            [
                'name' => 'Demo Customer',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CUSTOMER,
                'status' => User::STATUS_ACTIVE,
                'email_verified_at' => now(),
            ],
        );

    }
}
