<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeders extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the 'cliente' role if it doesn't exist
        Role::firstOrCreate([
            'name' => 'cliente',
            'guard_name' => 'sanctum',
        ]);

        // Create the 'dueño' role if it doesn't exist
        Role::firstOrCreate([
            'name' => 'dueño',
            'guard_name' => 'sanctum',
        ]);
    }
}