<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'Gestionar Usuarios',
            'Gestionar Catalogos',
            'Gestionar Categorias',
            'Gestionar Productos',
            'Gestionar Roles',
            'Gestionar Pedidos',
            'Gestionar Cupones'
            // Agrega más permisos según sea necesario
        ];

        // Crear los permisos en la base de datos
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, "guard_name" => "sanctum"]);
        }

        // Crear el rol de super-admin
        $superAdminRole = Role::create([
            'name' => 'super-admin',
            'guard_name' => 'sanctum',
        ]);

        // Asignar todos los permisos al rol de super-admin
        $superAdminRole->givePermissionTo(Permission::all());
    }
}
