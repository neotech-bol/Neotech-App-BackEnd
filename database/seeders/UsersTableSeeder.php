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
            'ci' => '123123123',
            'nit' => '123123123',
            'direccion' => 'Calle del super Admin',
            'telefono' => '60792059',
            'email' => 'superadmin@gmail.com', // Cambia esto por el email que desees
            'departamento' => 'cochabamba',
            'password' => bcrypt('123123123'), // Usar el CI como contraseña
            'genero' => 'M',
            "fecha_de_nacimiento" => '2005-05-25',
            "pais" => "Bolivia"
        ]); 

        // Asignar el rol de super-admin al usuario
        $user->assignRole('super-admin');
    }
}
