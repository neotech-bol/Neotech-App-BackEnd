<?php

namespace App\Http\Controllers;

use App\Models\rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function store(Request $request)
{
    // Validar que el producto_id esté presente y exista en la tabla de productos
    $request->validate([
        'producto_id' => 'required|exists:productos,id',
        'rating' => 'required|integer|min:1|max:5', // Asegúrate de validar el rating
        'comment' => 'nullable|string|max:255', // Asegúrate de validar el comentario
    ]);

    // Verificar si el producto ya ha sido calificado por el usuario autenticado
    $existingRating = Rating::where('user_id', auth()->id())
                            ->where('producto_id', $request->producto_id)
                            ->first();

    if ($existingRating) {
        // Si ya existe, actualizar la calificación y el comentario
        $existingRating->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Calificación actualizada con éxito.'], 200); // 200 OK
    } else {
        // Si no existe, crear un nuevo rating
        $rating = new Rating();
        $rating->user_id = auth()->id(); // Asumiendo que el usuario está autenticado
        $rating->producto_id = $request->producto_id; // Asegúrate de que esto no sea null
        $rating->rating = $request->rating; // Asegúrate de que el rating también esté presente
        $rating->comment = $request->comment; // Guarda el comentario si está presente
        $rating->save();

        return response()->json(['message' => 'Calificación guardada con éxito.'], 201); // 201 Created
    }
}
public function index()
{
    // Obtener las calificaciones del usuario autenticado
    $ratings = Rating::with('producto')
        ->where('user_id', auth()->id())
        ->get();

    // Obtener la cantidad de usuarios que han calificado cada producto
    $ratingsCount = Rating::select('producto_id', \DB::raw('count(*) as total'))
        ->groupBy('producto_id')
        ->get()
        ->keyBy('producto_id');

    // Agregar la cantidad de calificaciones a cada rating
    foreach ($ratings as $rating) {
        $rating->total_users = $ratingsCount->get($rating->producto_id)->total ?? 0;
    }

    return response()->json($ratings);
}
}
