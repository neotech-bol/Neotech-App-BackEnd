<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CalificacionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth'); // Asegura que solo los usuarios autenticados puedan acceder a estos métodos
    }

    /**
     * Almacena una nueva calificación para un producto.
     */
    public function store(Request $request, $productoId)
    {
        $request->validate([
            'calificacion' => 'required|integer|min:1|max:5',
        ]);

        $calificacion = new Calificacion();
        $calificacion->producto_id = $productoId;
        $calificacion->user_id = Auth::id();
        $calificacion->calificacion = $request->calificacion;
        $calificacion->save();

        return response()->json(['message' => 'Calificación agregada con éxito', 'calificacion' => $calificacion], 201);
    }

    /**
     * Muestra todas las calificaciones de un producto específico.
     */
    public function index($productoId)
    {
        $calificaciones = Calificacion::where('producto_id', $productoId)->with('user')->get();
        return response()->json($calificaciones);
    }

    /**
     * Actualiza una calificación existente.
     */
    public function update(Request $request, $id)
    {
        $calificacion = Calificacion::findOrFail($id);

        if ($calificacion->user_id !== Auth::id()) {
            return response()->json(['message' => 'Acción prohibida'], 403);
        }

        $request->validate([
            'calificacion' => 'required|integer|min:1|max:5',
        ]);

        $calificacion->calificacion = $request->calificacion;
        $calificacion->save();

        return response()->json(['message' => 'Calificación actualizada con éxito', 'calificacion' => $calificacion]);
    }

    /**
     * Elimina una calificación.
     */
    public function destroy($id)
    {
        $calificacion = Calificacion::findOrFail($id);

        if ($calificacion->user_id !== Auth::id()) {
            return response()->json(['message' => 'Acción prohibida'], 403);
        }

        $calificacion->delete();
        return response()->json(['message' => 'Calificación eliminada con éxito'], 204);
    }
}
