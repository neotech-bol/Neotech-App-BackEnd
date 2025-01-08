<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolesPermisosController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
            "name"=> "required|string",
        ]);
        $role = new Role();
        $role->name = $request->name;
        $role->guard_name = "sanctum";
        $role->save();
        return response()->json(["mensaje"=> "Rol creado con exito", "dato" => $role], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyRole(string $id)
    {
        $role = Role::find($id);;
        $role->delete();
        return response()->json(["mensaje"=> "Rol eliminado con exito", "data"=> $role], 200);
    }
}
