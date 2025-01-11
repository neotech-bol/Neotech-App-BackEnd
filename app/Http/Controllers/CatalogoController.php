<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $item = Catalogo::with('productos')->get();
        return response()->json(["mensaje" => "Catalogos cargados correctamente", "datos" => $item], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "nombre"=> "required",
            "descripcion"=> "nullable",
        ]);
        $item = new Catalogo();
        $item->nombre = $request->nombre;
        $item->descripcion = $request->descripcion;
        $item->save();
        return response()->json(["mensaje"=> "Registro guardado","dato"=> $item], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Catalogo::with('productos')->find($id);
        return response()->json(['Mensaje'=> 'Registro cargado','dato'=> $item], 200);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nombre'=> 'required',
            'descripcion'=> 'nullable',
        ]);
        $item = Catalogo::find($id);
        $item->nombre = $request->nombre;
        $item->descripcion = $request->descripcion;
        $item->save();
        return response()->json(['mensaje'=> 'Registro actualizado','dato'=> $item], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Catalogo::find($id);
        $item->estado = !$item->estado;
        $item->save();
        return response()->json(['mensaje'=> 'Estado modificado del catalogo','dato'=> $item], 200);
    }
    public function indexActivos()
    {   
        $item = Catalogo::with('productos')->where('estado', true)->get();
        return response()->json(['mensaje'=> 'Catalogos activos','datos'=> $item], 200);
    }
}
