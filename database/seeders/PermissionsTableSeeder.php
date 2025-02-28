<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsTableSeeder extends Seeder
{
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
        ];

        // Usa firstOrCreate() para evitar duplicados
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'sanctum'], // Busca por estos campos
                ['name' => $permission, 'guard_name' => 'sanctum']  // Crea si no existe
            );
        }

        // Crea el rol solo si no existe
        $superAdminRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => 'sanctum',
        ]);

        // Sincroniza todos los permisos (asegura que tenga los últimos)
        $superAdminRole->syncPermissions(Permission::all());
    }
    //comentario para el cpanel
    //comentatio para el cpanel 2
}
