<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Obtener los parámetros de búsqueda de la solicitud
        $query = User::with('roles');
    
        // Filtrar por el parámetro de búsqueda general
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nombre', 'like', '%' . $searchTerm . '%')
                  ->orWhere('apellido', 'like', '%' . $searchTerm . '%')
                  ->orWhere('ci', 'like', '%' . $searchTerm . '%')
                  ->orWhere('nit', 'like', '%' . $searchTerm . '%');
            });
        }
    
        // Paginación
        $item = $query->paginate(10);
    
        return response()->json(["mensaje" => "Usuarios cargados correctamente", "datos" => $item], 200);
    }
    public function storeUserAdmin(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:50',
            'apellido' => 'required|string|max:50',
            'ci' => 'required|string|max:15|unique:users',
            'nit' => 'required|string|unique:users',
            'direccion' => 'required|string',
            'telefono' => 'required|string|max:20',
            'edad' => 'required|integer',
            'genero' => 'required|in:M,F,Otro',
            'email' => 'required|string|email|max:100|unique:users',
            'role' => 'required|string|exists:roles,name', // Validar que el rol exista
        ]);
        $user = new User;
        $user->nombre = $request->nombre;
        $user->apellido = $request->apellido;
        $user->ci = $request->ci;
        $user->nit = $request->nit;
        $user->direccion = $request->direccion;
        $user->telefono = $request->telefono;
        $user->edad = $request->edad;
        $user->genero = $request->genero;
        $user->email = $request->email;
        $user->password = Hash::make($request->ci);
        $user->save();
        $user->assignRole($request->role); // Asignar el rol al usuario
        return response()->json(["mensaje" => "Usuario creado correctamente", "datos" => $user], 201);
    }
    public function show(string $id)
    {
        $item = User::with('roles')->findOrFail($id);
        return response()->json(["mensaje" => "Dato cargado", "dato" => $item], 200);
    }
    public function update(Request $request, string $id)
    {
        // Validar la solicitud
        $request->validate([
            'nombre' => 'sometimes|required|string|max:50',
            'apellido' => 'sometimes|required|string|max:50',
            'ci' => 'sometimes|required|string|max:15|unique:users,ci,' . $id,
            'nit' => 'sometimes|required|string|unique:users,nit,' . $id,
            'direccion' => 'sometimes|required|string',
            'telefono' => 'sometimes|required|string|max:20',
            'edad' => 'sometimes|required|integer',
            'genero' => 'sometimes|required|in:M,F,Otro',
            'email' => 'sometimes|required|string|email|max:100|unique:users,email,' . $id,
            'role' => 'sometimes|required|string|exists:roles,name', // Validar que el rol exista
        ]);

        // Buscar el usuario por ID
        $user = User::findOrFail($id);

        // Actualizar los campos del usuario
        if ($request->has('nombre')) {
            $user->nombre = $request->nombre;
        }
        if ($request->has('apellido')) {
            $user->apellido = $request->apellido;
        }
        if ($request->has('ci')) {
            $user->ci = $request->ci;
        }
        if ($request->has('nit')) {
            $user->nit = $request->nit;
        }
        if ($request->has('direccion')) {
            $user->direccion = $request->direccion;
        }
        if ($request->has('telefono')) {
            $user->telefono = $request->telefono;
        }
        if ($request->has('edad')) {
            $user->edad = $request->edad;
        }
        if ($request->has('genero')) {
            $user->genero = $request->genero;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        if ($request->has('ci')) {
            $user->password = Hash::make($request->ci); // Actualizar la contraseña si se proporciona el CI
        }

        // Guardar los cambios
        $user->save();

        // Asignar el nuevo rol si se proporciona
        if ($request->has('role')) {
            $user->syncRoles($request->role); // Sincronizar roles
        }

        return response()->json(["mensaje" => "Usuario actualizado correctamente", "datos" => $user], 200);
    }
}
