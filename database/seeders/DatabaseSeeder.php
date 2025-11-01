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

        // Get first panchayat for sample wards
        $panchayat = Panchayat::first();
        
        if ($panchayat) {
            // Create sample wards
            $ward1 = Ward::firstOrCreate(
                ['ward_number' => 'WARD001'],
                [
                    'name' => 'Ward 1',
                    'panchayat_id' => $panchayat->id,
                    'description' => 'First ward area',
                ]
            );

            $ward2 = Ward::firstOrCreate(
                ['ward_number' => 'WARD002'],
                [
                    'name' => 'Ward 2',
                    'panchayat_id' => $panchayat->id,
                    'description' => 'Second ward area',
                ]
            );
        } else {
            // Fallback: Create sample wards without panchayat
            $ward1 = Ward::firstOrCreate(
                ['ward_number' => 'WARD001'],
                [
                    'name' => 'Ward 1',
                    'description' => 'First ward area',
                ]
            );

            $ward2 = Ward::firstOrCreate(
                ['ward_number' => 'WARD002'],
                [
                    'name' => 'Ward 2',
                    'description' => 'Second ward area',
                ]
            );
        }

        // Create team leads
        $teamLead1 = User::firstOrCreate(
            ['email' => 'teamlead1@voterslist.com'],
            [
                'name' => 'Team Lead 1',
                'password' => Hash::make('password123'),
                'role' => 'team_lead',
                'ward_id' => $ward1->id,
                'phone' => '1234567891',
            ]
        );
        if (!$teamLead1->hasRole('team_lead')) {
            $teamLead1->assignRole('team_lead');
        }

        $teamLead2 = User::firstOrCreate(
            ['email' => 'teamlead2@voterslist.com'],
            [
                'name' => 'Team Lead 2',
                'password' => Hash::make('password123'),
                'role' => 'team_lead',
                'ward_id' => $ward2->id,
                'phone' => '1234567892',
            ]
        );
        if (!$teamLead2->hasRole('team_lead')) {
            $teamLead2->assignRole('team_lead');
        }

        // Create booth agents
        $boothAgent1 = User::firstOrCreate(
            ['email' => 'boothagent1@voterslist.com'],
            [
                'name' => 'Booth Agent 1',
                'password' => Hash::make('password123'),
                'role' => 'booth_agent',
                'ward_id' => $ward1->id,
                'phone' => '1234567893',
            ]
        );
        if (!$boothAgent1->hasRole('booth_agent')) {
            $boothAgent1->assignRole('booth_agent');
        }

        $boothAgent2 = User::firstOrCreate(
            ['email' => 'boothagent2@voterslist.com'],
            [
                'name' => 'Booth Agent 2',
                'password' => Hash::make('password123'),
                'role' => 'booth_agent',
                'ward_id' => $ward2->id,
                'phone' => '1234567894',
            ]
        );
        if (!$boothAgent2->hasRole('booth_agent')) {
            $boothAgent2->assignRole('booth_agent');
        }

        // Create workers
        $worker1 = User::firstOrCreate(
            ['email' => 'worker1@voterslist.com'],
            [
                'name' => 'Worker 1',
                'password' => Hash::make('password123'),
                'role' => 'worker',
                'ward_id' => $ward1->id,
                'phone' => '1234567895',
            ]
        );
        if (!$worker1->hasRole('worker')) {
            $worker1->assignRole('worker');
        }

        $worker2 = User::firstOrCreate(
            ['email' => 'worker2@voterslist.com'],
            [
                'name' => 'Worker 2',
                'password' => Hash::make('password123'),
                'role' => 'worker',
                'ward_id' => $ward1->id,
                'phone' => '1234567896',
            ]
        );
        if (!$worker2->hasRole('worker')) {
            $worker2->assignRole('worker');
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Superadmin: admin@voterslist.com / password123');
    }
}
