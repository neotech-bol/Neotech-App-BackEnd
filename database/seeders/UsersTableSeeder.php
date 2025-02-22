<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear un usuario
        $user = User::create([
            'nombre' => 'Super Admin',
            'apellido' => 'Administrador',
            'ci' => '4254192123',
            'nit' => '4254192123',
            'direccion' => 'Calle del super Admin',
            'telefono' => '60792059',
            'email' => 'superadmin@gmail.com', // Cambia esto por el email que desees
            'departamento' => 'cochabamba',
            'password' => bcrypt('4254192123'), // Usar el CI como contraseña
            'genero' => 'M',
            "edad" => 19
        ]); 

        // Asignar el rol de super-admin al usuario
        $user->assignRole('super-admin');
    }
}
