<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use Illuminate\Http\Request;

class favoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener el usuario autenticado
        $user = auth()->user();
    
        // Obtener los favoritos del usuario, incluyendo los productos relacionados
        $favorites = $user->favorites()->with('producto')->get();
    
        // Retornar la respuesta en formato JSON
        return response()->json($favorites);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar que el producto_id esté presente y exista en la tabla de productos
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
        ]);
    
        // Verificar si el producto ya está en favoritos para el usuario autenticado
        $existingFavorite = Favorite::where('user_id', auth()->id())
                                    ->where('producto_id', $request->producto_id)
                                    ->first();
    
        // Si ya existe, devolver un mensaje indicando que no se puede agregar nuevamente
        if ($existingFavorite) {
            return response()->json(['message' => 'Este producto ya está en favoritos.'], 409); // 409 Conflict
        }
    
        // Si no existe, crear un nuevo favorito
        $favorite = new Favorite();
        $favorite->user_id = auth()->id(); // Asumiendo que el usuario está autenticado
        $favorite->producto_id = $request->producto_id;
        $favorite->save();
    
        return response()->json(['message' => 'Producto agregado a favoritos.'], 201); // 201 Created
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
    public function destroy(string $id)
    {
        //
    }
}
