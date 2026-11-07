<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Primary Admin User
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
                'phone' => '+1 (555) 019-2834',
                'address' => 'HQ Suite 100, Tech Park, Silicon Valley, CA',
                'is_active' => true,
            ]
        );

        // 2. Demo Customer User
        User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name' => 'Sarah Johnson',
                'password' => Hash::make('Customer123!'),
                'role' => 'customer',
                'phone' => '+1 (555) 382-9912',
                'address' => '742 Evergreen Terrace, Springfield, OR',
                'is_active' => true,
            ]
        );

        // 3. Second Demo Customer User
        User::firstOrCreate(
            ['email' => 'john.doe@example.com'],
            [
                'name' => 'John Doe',
                'password' => Hash::make('Customer123!'),
                'role' => 'customer',
                'phone' => '+1 (555) 441-2098',
                'address' => '102 Ocean Drive, Miami, FL',
                'is_active' => true,
            ]
        );
    }
}

