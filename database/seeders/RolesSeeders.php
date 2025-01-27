<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesSeeders extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'cliente',
            'guard_name' => 'sanctum',
        ]);
        Role::create([
            'name' => 'dueño',
            'guard_name' => 'sanctum',
        ]);
    }
}

