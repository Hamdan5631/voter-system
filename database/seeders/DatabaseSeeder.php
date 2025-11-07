<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Ward;
use App\Models\Panchayat;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Seed panchayats
        $this->call(PanchayatSeeder::class);

        // Create superadmin
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@voterslist.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password123'),
                'role' => 'superadmin',
                'phone' => '1234567890',
            ]
        );
        if (!$superadmin->hasRole('superadmin')) {
            $superadmin->assignRole('superadmin');
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Superadmin: admin@voterslist.com / password123');
    }
}
