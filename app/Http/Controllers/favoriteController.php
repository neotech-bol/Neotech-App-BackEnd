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
        $favorites = $user->favorites()->with('producto.images')->get();
    
        // Modificar los favoritos para incluir la URL completa de las imágenes
        $formattedFavorites = $favorites->transform(function ($favorite) {
            // Obtener el producto relacionado
            $producto = $favorite->producto;
    
            // Transformar las imágenes del producto
            if ($producto && $producto->images) {
                $producto->images->transform(function ($image) {
                    $image->imagen = asset("images/productos/" . $image->imagen); // Cambia la ruta según sea necesario
                    return $image;
                });
            }
    
            // Modificar la imagen principal para incluir la URL completa
            if ($producto && $producto->imagen_principal) {
                $producto->imagen_principal = asset("images/imagenes_principales/" . $producto->imagen_principal); // Cambia la ruta según sea necesario
            }
    
            return [
                'id' => $favorite->id,
                'producto' => $producto,
            ];
        });
    
        // Retornar la respuesta en formato JSON
        return response()->json($formattedFavorites);
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
/**
 * Remove the specified resource from storage.
 */
public function destroy(string $id)
{
    // Obtener el usuario autenticado
    $user = auth()->user();

    // Buscar el favorito que se desea eliminar
    $favorite = Favorite::where('user_id', $user->id)
                        ->where('id', $id)
                        ->first();

    // Verificar si el favorito existe
    if (!$favorite) {
        return response()->json(['message' => 'Favorito no encontrado.'], 404); // 404 Not Found
    }

    // Eliminar el favorito
    $favorite->delete();

    return response()->json(['message' => 'Favorito eliminado correctamente.'], 200); // 200 OK
}
}
