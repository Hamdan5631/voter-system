<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles
        Role::firstOrCreate(['name' => 'superadmin']);
        Role::firstOrCreate(['name' => 'team_lead']);
        Role::firstOrCreate(['name' => 'booth_agent']);
        Role::firstOrCreate(['name' => 'worker']);
    }
}
