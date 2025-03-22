<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Obtener los parámetros de búsqueda de la solicitud
        $query = User::with('roles', 'pedidos.productos');

        // Filtrar por el parámetro de búsqueda general
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
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
            'fecha_de_nacimiento' => 'required|date',
            'genero' => 'required|in:M,F,Otro',
            'email' => 'required|string|email|max:100|unique:users',
            'role' => 'required|string|exists:roles,name', // Validar que el rol exista
            'pais' => 'required|string'
        ]);
        $user = new User;
        $user->nombre = $request->nombre;
        $user->apellido = $request->apellido;
        $user->ci = $request->ci;
        $user->nit = $request->nit;
        $user->direccion = $request->direccion;
        $user->telefono = $request->telefono;
        $user->fecha_de_nacimiento = $request->fecha_de_nacimiento;
        $user->genero = $request->genero;
        $user->email = $request->email;
        $user->pais = $request->pais;
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
    public function changeEstado($id)
    {
        // Buscar el usuario por su ID
        $user = User::find($id);

        // Verificar si el usuario existe
        if ($user) {
            // Cambiar el estado del usuario
            $user->estado = !$user->estado;

            // Guardar los cambios en la base de datos
            $user->save();

            // Retornar una respuesta exitosa
            return response()->json(['success' => true, 'estado' => $user->estado]);
        } else {
            // Retornar un error si el usuario no se encuentra
            return response()->json(['success' => false, 'message' => 'Usuario no encontrado'], 404);
        }
    }
    public function updateAuthenticatedUser(Request $request)
    {
        // Obtener el usuario autenticado
        $user = auth()->user();

        // Validar la solicitud
        $request->validate([
            'nombre' => 'sometimes|required|string|max:50',
            'apellido' => 'sometimes|required|string|max:50',
            'ci' => 'sometimes|required|string|max:15|unique:users,ci,' . $user->id,
            'nit' => 'sometimes|required|string|unique:users,nit,' . $user->id,
            'direccion' => 'sometimes|required|string',
            'telefono' => 'sometimes|required|string|max:20',
            'edad' => 'sometimes|required|integer',
            'genero' => 'sometimes|required|in:M,F,Otro',
            'email' => 'sometimes|required|string|email|max:100|unique:users,email,' . $user->id,
            // No es necesario validar el rol aquí, ya que el usuario no debería cambiar su rol
        ]);

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
        // Si se proporciona un nuevo CI, actualizar la contraseña
        if ($request->has('ci')) {
            $user->password = Hash::make($request->ci); // Actualizar la contraseña si se proporciona el CI
        }

        // Guardar los cambios
        $user->save();

        return response()->json(["mensaje" => "Usuario actualizado correctamente", "datos" => $user], 200);
    }
    public function getAuthenticatedUser()
    {
        // Obtener el usuario autenticado
        $user = auth()->user();

        // Verificar si el usuario está autenticado
        if ($user) {
            // Cargar los roles del usuario
            $user->load('roles', 'pedidos');

            // Retornar una respuesta con los datos del usuario
            return response()->json([
                "mensaje" => "Datos del usuario autenticado",
                "datos" => $user
            ], 200);
        } else {
            // Retornar un error si no hay usuario autenticado
            return response()->json([
                "mensaje" => "No hay usuario autenticado"
            ], 501);
        }
    }
    public function updateDepartment(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            'departamento' => 'required|string|max:100', // Ajusta la validación según tus necesidades
        ]);

        // Obtener el usuario autenticado
        $user = auth()->user();

        // Verificar si el usuario está autenticado
        if ($user) {
            // Mostrar el departamento actual del usuario
            $currentDepartment = $user->departamento;

            // Actualizar el departamento del usuario
            $user->departamento = $request->departamento;

            // Guardar los cambios
            $user->save();

            return response()->json([
                "mensaje" => "Departamento actualizado correctamente",
                "datos" => [
                    "departamento_anterior" => $currentDepartment,
                    "nuevo_departamento" => $user->departamento
                ]
            ], 200);
        } else {
            // Retornar el departamento por defecto si el usuario no está autenticado
            return response()->json([
                "mensaje" => "No estás autenticado para realizar esta acción.",
                "departamento_por_defecto" => "Cochabamba"
            ], 401);
        }
    }
    public function updateBasicInfo(Request $request)
    {
        // Obtener el usuario autenticado
        $user = auth()->user();

        // Verificar si el usuario está autenticado
        if (!$user) {
            return response()->json([
                "mensaje" => "No estás autenticado para realizar esta acción."
            ], 401);
        }

        // Validar la solicitud
        $request->validate([
            'nombre' => 'sometimes|required|string|max:50',
            'apellido' => 'sometimes|required|string|max:50',
            'direccion' => 'sometimes|required|string',
            'telefono' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|string|email|max:100|unique:users,email,' . $user->id,
        ]);

        // Actualizar los campos del usuario
        if ($request->has('nombre')) {
            $user->nombre = $request->nombre;
        }
        if ($request->has('apellido')) {
            $user->apellido = $request->apellido;
        }
        if ($request->has('direccion')) {
            $user->direccion = $request->direccion;
        }
        if ($request->has('telefono')) {
            $user->telefono = $request->telefono;
        }
        if ($request->has('email')) {
            $user->email = $request->email;
        }

        // Guardar los cambios
        $user->save();

        return response()->json(["mensaje" => "Información básica actualizada correctamente", "datos" => $user], 200);
    }

    public function showDepartment()
    {
        // Obtener el usuario autenticado
        $user = auth()->user();

        // Verificar si el usuario está autenticado
        if ($user) {
            // Retornar el departamento del usuario
            return response()->json([
                "mensaje" => "Usuario autenticado.",
                "departamento" => $user->departamento
            ], 200);
        } else {
            // Retornar el departamento por defecto si el usuario no está autenticado
            return response()->json([
                "mensaje" => "No estás autenticado, mostrando departamento por defecto.",
                "departamento" => "Cochabamba"
            ], 401);
        }
    }
    // Obtener usuarios activos
    public function getUsuariosActivos()
    {
        $usuariosActivos = User::where('estado', true)->with('roles')->get(); // Obtener usuarios donde estado es true
        return response()->json(["mensaje" => "Usuarios activos cargados correctamente", "datos" => $usuariosActivos], 200);
    }

    // Obtener usuarios inactivos
    public function getUsuariosInactivos()
    {
        $usuariosInactivos = User::where('estado', false)->with('roles')->get(); // Obtener usuarios donde estado es false
        return response()->json(["mensaje" => "Usuarios inactivos cargados correctamente", "datos" => $usuariosInactivos], 200);
    }
    // Obtener el total de usuarios
    public function totalUsuarios()
    {
        $totalUsuarios = User::count(); // Contar todos los usuarios
        return response()->json(['total_usuarios' => $totalUsuarios], 200);
    }

    // Obtener el total de usuarios activos
    public function totalUsuariosActivos()
    {
        $totalActivos = User::where('estado', true)->count(); // Contar usuarios donde estado es true
        return response()->json(['total_usuarios_activos' => $totalActivos], 200);
    }

    // Obtener el total de usuarios inactivos
    public function totalUsuariosInactivos()
    {
        $totalInactivos = User::where('estado', false)->count(); // Contar usuarios donde estado es false
        return response()->json(['total_usuarios_inactivos' => $totalInactivos], 200);
    }
    public function obtenerPermisos()
    {
        $usuario = User::find(Auth::id());
        $permisos = $usuario->getPermissionsViaRoles();
        $nombresPermisos = $permisos->pluck('name')->toArray();
        return response()->json(['mensaje' => 'Permisos cargados', 'datos' => $nombresPermisos], 200);
    }  //Obtener Permisos
}
