<?php

namespace App\Http\Controllers;

use App\Models\CatalogoHistoriales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatalogoHistorialController extends Controller
{
    /**
     * Display a listing of the catalog history.
     */
    public function index()
    {
        // Obtener todos los historiales de catálogos
        $historiales = CatalogoHistoriales::with('catalogo.categorias', 'catalogo.categorias.productos.images', 'catalogo.categorias.productos.caracteristicas', 'catalogo.categorias.productos.modelos') // Eager load para obtener la relación con el catálogo
            ->orderBy('created_at', 'desc') // Ordenar por fecha de creación
            ->get();

        return response()->json(['mensaje' => 'Historiales de catálogos cargados', 'datos' => $historiales], 200);
    }

    /**
     * Display the specified catalog history.
     */

     public function show(string $id)
     {
         // Obtener el historial específico de un catálogo activo
         $historial = CatalogoHistoriales::with([
             'catalogo.categorias' => function ($query) {
                 $query->where('estado', true); // Filtrar solo categorías activas
             },
             'catalogo.categorias.productos.images', // Cargar imágenes de productos
             'catalogo.categorias.productos.caracteristicas',
             'catalogo.categorias.productos.modelos'
         ])
         ->where('id', $id) // Filtrar por el ID del historial
         ->where('estado', true) // Asegurarse de que el historial esté activo
         ->first();
 
         // Verificar si se encontró el historial
         if (!$historial) {
             return response()->json(['mensaje' => 'Historial no encontrado o inactivo'], 404);
         }
 
         // Modificar la estructura para incluir las URLs de las imágenes
         $historial->catalogo->banner = asset("images/catalogos/banners/" . $historial->catalogo->banner); // Asumiendo que el banner está en la ruta especificada
 
         $historial->catalogo->categorias->transform(function ($categoria) {
             $categoria->banner = asset("images/categorias/banners/" . $categoria->banner); // Asumiendo que el banner de la categoría está en la ruta especificada
 
             $categoria->productos->transform(function ($producto) {
                 $producto->imagen_principal = asset("images/imagenes_principales/" . $producto->imagen_principal); // Asumiendo que la imagen principal está en la ruta especificada
 
                 // Transformar las imágenes del producto
                 $producto->images->transform(function ($image) {
                     $image->imagen = asset("images/productos/" . $image->imagen); // Asumiendo que las imágenes están en la ruta especificada
                     return $image;
                 });
 
                 return $producto;
             });
 
             return $categoria;
         });
 
         return response()->json(['mensaje' => 'Historial cargado', 'datos' => $historial], 200);
     }
    public function changeStatus(string $id){
        $item = CatalogoHistoriales::findOrFail($id);
        $item->estado =!$item->estado;
        $item->save();
        return response()->json(["mensaje"=> "Estado modificado", "dato" => $item], 200);
    }
    public function indexActivos(Request $request) {
        // Se eliminó el caché como solicitaste
        $historiales = CatalogoHistoriales::with([
                'catalogo.categorias' => function ($query) {
                    $query->where('estado', true);
                },
                'catalogo.categorias.productos.images',
                'catalogo.categorias.productos.caracteristicas',
                'catalogo.categorias.productos.modelos'
            ])
            ->where('estado', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($historial) {
                // Verificar si el banner existe antes de asignar la ruta
                if ($historial->catalogo->banner) {
                    $historial->catalogo->banner = asset("images/catalogos/banners/" . $historial->catalogo->banner);
                } else {
                    $historial->catalogo->banner = asset("images/placeholder.jpg");
                }
    
                $historial->catalogo->categorias->each(function ($categoria) {
                    // Verificar si el banner de categoría existe
                    if ($categoria->banner) {
                        $categoria->banner = asset("images/categorias/banners/" . $categoria->banner);
                    } else {
                        $categoria->banner = asset("images/placeholder.jpg");
                    }
    
                    $categoria->productos->each(function ($producto) {
                        // CORRECCIÓN: Verificar y asignar la imagen principal desde la carpeta correcta
                        if ($producto->imagen_principal) {
                            // Verificar si la imagen principal existe en la carpeta correcta
                            $imagenPrincipalPath = public_path("images/imagenes_principales/" . $producto->imagen_principal);
                            
                            if (file_exists($imagenPrincipalPath)) {
                                $producto->imagen_principal = asset("images/imagenes_principales/" . $producto->imagen_principal);
                            } else {
                                $producto->imagen_principal = asset("images/placeholder.jpg");
                            }
                        } else {
                            $producto->imagen_principal = asset("images/placeholder.jpg");
                        }
    
                        // Verificar cada imagen adicional
                        $producto->images->each(function ($image) {
                            if ($image->imagen) {
                                $imagePath = public_path("images/productos/" . $image->imagen);
                                if (file_exists($imagePath)) {
                                    $image->imagen = asset("images/productos/" . $image->imagen);
                                } else {
                                    $image->imagen = asset("images/placeholder.jpg");
                                }
                            } else {
                                $image->imagen = asset("images/placeholder.jpg");
                            }
                        });
                    });
                });
                
                // Registrar en el log las URLs generadas para depuración
                if ($historial->catalogo->categorias->isNotEmpty() && 
                    $historial->catalogo->categorias->first()->productos->isNotEmpty()) {
                    \Log::info('URLs de imágenes principales en historiales:', [
                        'ejemplo' => $historial->catalogo->categorias->first()->productos->first()->imagen_principal ?? 'No hay productos'
                    ]);
                }
                
                return $historial;
            });
    
        return response()->json(['mensaje' => 'Historiales activos', 'datos' => $historiales], 200);
    }
    public function getActiveHistorials()
    {
        // Obtener los IDs y nombres de los historiales activos
        $activeHistorials = CatalogoHistoriales::where('estado', true) // Filtrar solo historiales activos
            ->select('id', 'nombre') // Asegúrate de que 'nombre' es el campo correcto
            ->get(); // Obtener los resultados

        return response()->json(['mensaje' => 'Historiales activos', 'datos' => $activeHistorials], 200);
    }
}
