<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $item = Categoria::with('productos')->orderBy('id','desc')->paginate(10);
        return response()->json(["mensaje" => "Categorias cargadas correctamente", "datos" => $item], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "nombre" => "required",
            "descripcion" => "nullable",
        ]);
        $item = new Categoria();
        $item->nombre = $request->nombre;
        $item->descripcion = $request->descripcion;
        $item->save();
        return response()->json(["mensaje" => "Registro guardado", "dato" => $item], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Categoria::with('productos')->findOrFail($id);
        return response()->json(["mensaje" => "Dato cargado", "dato"=> $item], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            "nombre" => "required",
            "descripcion" => "nullable",
        ]);
        $item = Categoria::findOrFail($id);
        $item->nombre = $request->nombre;
        $item->descripcion = $request->descripcion;
        $item->save();
        return response()->json(["mensaje" => "Registro actualizado", "dato" => $item], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Categoria::findOrFail($id);
        $item->estado = !$item->estado;
        $item->save();
        return response()->json(["mensaje"=> "Estado modificado", "dato" => $item], 200);
    }
    public function indexActivos()
    {
        $item = Categoria::with('productos')->where('estado', true)->get();
        return response()->json(["mensaje" => "Categorias activas cargadas correctamente", "datos" => $item], 200);
    }
}
