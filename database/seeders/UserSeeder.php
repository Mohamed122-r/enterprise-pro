<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@enterprisepro.com',
            'password' => Hash::make('password'),
            'role_id' => 1,
            'department_id' => 1,
            'phone' => '+1234567890',
            'status' => true,
        ]);

        User::create([
            'name' => 'Manager User',
            'email' => 'manager@enterprisepro.com',
            'password' => Hash::make('password'),
            'role_id' => 2,
            'department_id' => 2,
            'phone' => '+1234567891',
            'status' => true,
        ]);

        User::create([
            'name' => 'Regular User',
            'email' => 'user@enterprisepro.com',
            'password' => Hash::make('password'),
            'role_id' => 3,
            'department_id' => 3,
            'phone' => '+1234567892',
            'status' => true,
        ]);
    }
}