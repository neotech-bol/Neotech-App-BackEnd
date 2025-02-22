<?php

namespace App\Http\Controllers;

use App\Models\CatalogoHistoriales;
use Illuminate\Http\Request;

class CatalogoHistorialController extends Controller
{
    /**
     * Display a listing of the catalog history.
     */
    public function index()
    {
        // Obtener todos los historiales de catálogos
        $historiales = CatalogoHistoriales::with('catalogo') // Eager load para obtener la relación con el catálogo
            ->orderBy('created_at', 'desc') // Ordenar por fecha de creación
            ->get();

        return response()->json(['mensaje' => 'Historiales de catálogos cargados', 'datos' => $historiales], 200);
    }

    /**
     * Display the specified catalog history.
     */
    public function show(string $id)
    {
        // Obtener el historial específico de un catálogo
        $historial = CatalogoHistoriales::where('catalogo_id', $id)->get();

        return response()->json(['mensaje' => 'Historial cargado', 'datos' => $historial], 200);
    }
}
