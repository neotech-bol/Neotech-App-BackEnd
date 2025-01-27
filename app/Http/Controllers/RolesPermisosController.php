<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesPermisosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexPermissions(){
        $permissions = Permission::all();
        return response()->json(["mensaje" => "Permisos cargados correctamente", "datos" => $permissions], 200);
    }
    public function indexRoles()
    {
        $roles = Role::with('permissions')->get();
        return response()->json(["mensaje" => "Roles cargados correctamente", "datos" => $roles], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|string|unique:roles,name,NULL,id,guard_name,sanctum", // Asegúrate de que el nombre sea único para el guard_name
            'permissions' => 'array', // Permisos opcionales
            'permissions.*' => 'string|exists:permissions,name', // Asegúrate de que los permisos existan
        ]);

        // Crear el nuevo rol
        $role = new Role();
        $role->name = $request->name;
        $role->guard_name = "sanctum";
        $role->save();

        // Asignar permisos al rol si se proporcionan
        if (isset($request->permissions)) {
            $role->syncPermissions($request->permissions); // Asigna los permisos al rol
        }

        return response()->json([
            "mensaje" => "Rol creado con éxito",
            "dato" => $role->load('permissions'), // Cargar los permisos asignados
        ], 200);
    }

    /**
     * Mostrar los permisos de un rol específico.
     */
    public function showPermissions(string $roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        return response()->json([
            'mensaje' => 'Registros cargados correctamente',
            'datos' => $role,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string',
            'permissions' => 'array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::findOrFail($id);
        $role->name = $request->name;
        $role->save();

        if (isset($request->permissions)) {
            $role->syncPermissions($request->permissions); // Asigna los permisos al rol
        }

        return response()->json([
            'mensaje' => 'Rol actualizado con éxito',
            'rol' => $role->load('permissions'), // Cargar los permisos actualizados
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyRole(string $id)
    {
        $role = Role::find($id);;
        $role->delete();
        return response()->json(["mensaje" => "Rol eliminado con exito", "data" => $role], 200);
    }
}
