<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// Creates the administrator account
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@aralynk.local'],
            ['name' => 'Administrator', 'password' => Hash::make('Admin@12345'), 'role' => 'admin', 'status' => 'active']
        );
    }
}
