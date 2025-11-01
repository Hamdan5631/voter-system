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
        $superadmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@voterslist.com',
            'password' => Hash::make('password123'),
            'role' => 'superadmin',
            'phone' => '1234567890',
        ]);
        $superadmin->assignRole('superadmin');

        // Get first panchayat for sample wards
        $panchayat = Panchayat::first();
        
        if ($panchayat) {
            // Create sample wards
            $ward1 = Ward::create([
                'name' => 'Ward 1',
                'ward_number' => 'WARD001',
                'panchayat_id' => $panchayat->id,
                'description' => 'First ward area',
            ]);

            $ward2 = Ward::create([
                'name' => 'Ward 2',
                'ward_number' => 'WARD002',
                'panchayat_id' => $panchayat->id,
                'description' => 'Second ward area',
            ]);
        } else {
            // Fallback: Create sample wards without panchayat
            $ward1 = Ward::create([
                'name' => 'Ward 1',
                'ward_number' => 'WARD001',
                'description' => 'First ward area',
            ]);

            $ward2 = Ward::create([
                'name' => 'Ward 2',
                'ward_number' => 'WARD002',
                'description' => 'Second ward area',
            ]);
        }

        // Create team leads
        $teamLead1 = User::create([
            'name' => 'Team Lead 1',
            'email' => 'teamlead1@voterslist.com',
            'password' => Hash::make('password123'),
            'role' => 'team_lead',
            'ward_id' => $ward1->id,
            'phone' => '1234567891',
        ]);
        $teamLead1->assignRole('team_lead');

        $teamLead2 = User::create([
            'name' => 'Team Lead 2',
            'email' => 'teamlead2@voterslist.com',
            'password' => Hash::make('password123'),
            'role' => 'team_lead',
            'ward_id' => $ward2->id,
            'phone' => '1234567892',
        ]);
        $teamLead2->assignRole('team_lead');

        // Create booth agents
        $boothAgent1 = User::create([
            'name' => 'Booth Agent 1',
            'email' => 'boothagent1@voterslist.com',
            'password' => Hash::make('password123'),
            'role' => 'booth_agent',
            'ward_id' => $ward1->id,
            'phone' => '1234567893',
        ]);
        $boothAgent1->assignRole('booth_agent');

        $boothAgent2 = User::create([
            'name' => 'Booth Agent 2',
            'email' => 'boothagent2@voterslist.com',
            'password' => Hash::make('password123'),
            'role' => 'booth_agent',
            'ward_id' => $ward2->id,
            'phone' => '1234567894',
        ]);
        $boothAgent2->assignRole('booth_agent');

        // Create workers
        $worker1 = User::create([
            'name' => 'Worker 1',
            'email' => 'worker1@voterslist.com',
            'password' => Hash::make('password123'),
            'role' => 'worker',
            'ward_id' => $ward1->id,
            'phone' => '1234567895',
        ]);
        $worker1->assignRole('worker');

        $worker2 = User::create([
            'name' => 'Worker 2',
            'email' => 'worker2@voterslist.com',
            'password' => Hash::make('password123'),
            'role' => 'worker',
            'ward_id' => $ward1->id,
            'phone' => '1234567896',
        ]);
        $worker2->assignRole('worker');

        $this->command->info('Database seeded successfully!');
        $this->command->info('Superadmin: admin@voterslist.com / password123');
    }
}
