<?php

namespace App\Http\Controllers;

use App\Models\ModeloProducto; // Asegúrate de importar el modelo correcto
use Illuminate\Http\Request;

class ProductoModelController extends Controller
{
    public function index()
    {
        // Obtener todos los modelos
        $modelos = ModeloProducto::all();

        // Retornar los modelos como respuesta JSON
        return response()->json($modelos);
    }
}