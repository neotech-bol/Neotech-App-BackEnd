<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\CatalogoHistoriales;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CatalogoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Eager load catalogs with their products, categories, and images
        $catalogos = Catalogo::with(['categorias.productos'])
            ->orderBy('orden', 'desc') // Ordenar por el campo 'orden'
            ->orderBy('id', 'desc') // Ordenar por el campo 'id' en orden descendente
            ->get();
        return response()->json(["mensaje" => "Catalogos cargados correctamente", "datos" => $catalogos], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            "nombre" => "required",
            "descripcion" => "nullable",
            "banner" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048", // Validar que cada imagen sea un archivo de imagen
            "orden" => "required|integer", // Validar el campo 'orden'
        ]);

        // Crear el nuevo catálogo
        $item = new Catalogo();
        $item->nombre = $request->nombre;
        $item->descripcion = $request->descripcion;
        $item->orden = $request->orden; // Asignar el orden
        $item->estado = true;

        // Guardar el catálogo primero
        $item->save();

        // Guardar el historial después de que el catálogo ha sido guardado
        CatalogoHistoriales::create([
            'catalogo_id' => $item->id, // Ahora $item->id tiene un valor válido
            'nombre' => $item->nombre,
            'descripcion' => $item->descripcion,
            'orden' => $item->orden,
            'estado' => false, // Asegúrate de que 'estado' esté definido en el modelo Catalogo
        ]);

        return response()->json(["mensaje" => "Registro guardado", "dato" => $item], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Catalogo::with('productos')->find($id);
        return response()->json(['Mensaje' => 'Registro cargado', 'dato' => $item], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'nombre' => 'required',
            'descripcion' => 'nullable',
            "orden" => "nullable|integer", // Validar el campo 'orden'
            "banner" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048", // Validar que cada imagen sea un archivo de imagen
        ]);
        try {
            $item = Catalogo::find($id);
            $item->nombre = $request->nombre;
            $item->descripcion = $request->descripcion;
            $item->orden = $request->orden; // Asignar el orden
            $item->save();
            return response()->json(['mensaje' => 'Registro actualizado', 'dato' => $item], 200);
        } catch (\Throwable $th) {
            return response()->json(['mensaje' => 'Error al actualizar el registro', 'error' => $th->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $item = Catalogo::find($id);
        $item->estado = !$item->estado;
        $item->save();
        return response()->json(['mensaje' => 'Estado modificado del catalogo', 'dato' => $item], 200);
    }
    public function indexActivos(Request $request)
    {
        // Eliminamos el caché como solicitaste
        $catalogos = Catalogo::with(['categorias' => function ($query) {
                $query->where('estado', true); // Filtrar solo categorías activas
            }, 'categorias.productos.images']) // Cargar imágenes de productos
            ->where('estado', true)
            ->orderBy('orden', 'desc')
            ->orderBy('id', 'desc') // Ordenar por el campo 'id' en orden descendente
            ->get();
    
        // Modificar la estructura para incluir las URLs de las imágenes
        $catalogos->transform(function ($catalogo) {
            $catalogo->categorias->transform(function ($categoria) {
                // Verificar si existe el banner antes de asignar la ruta
                if ($categoria->banner) {
                    $categoria->banner = asset("images/categorias/banners/" . $categoria->banner);
                }
    
                $categoria->productos->transform(function ($producto) {
                    // CORRECCIÓN: La imagen principal está en una carpeta diferente
                    if ($producto->imagen_principal) {
                        $producto->imagen_principal = asset("images/imagenes_principales/" . $producto->imagen_principal);
                    }
    
                    // Transformar las imágenes del producto
                    $producto->images->transform(function ($image) {
                        if ($image->imagen) {
                            $image->imagen = asset("images/productos/" . $image->imagen);
                        }
                        return $image;
                    });
    
                    return $producto;
                });
    
                return $categoria;
            });
    
            return $catalogo;
        });
    
        // Registrar en el log las URLs generadas para depuración
        \Log::info('URLs de imágenes principales:', [
            'ejemplo' => $catalogos->first()->categorias->first()->productos->first()->imagen_principal ?? 'No hay productos'
        ]);
    
        return response()->json(['mensaje' => 'Catálogos activos', 'datos' => $catalogos], 200);
    }
    /**
     * Display a listing of catalogos with only id, name and category id.
     */
    public function indexCatalogosConCategorias()
    {
        // Obtener los catálogos con sus categorías
        $catalogos = Catalogo::with(['categorias' => function ($query) {
            $query->select('id', 'nombre', 'catalogo_id'); // Seleccionar solo los campos necesarios
        }])
            ->select('id', 'nombre') // Seleccionar solo los campos necesarios del catálogo
            ->where('estado', true) // Filtrar solo catálogos activos
            ->get();

        // Transformar la estructura para incluir solo el nombre y el id del catálogo y el id de la categoría
        $resultados = $catalogos->map(function ($catalogo) {
            return [
                'id' => $catalogo->id,
                'nombre' => $catalogo->nombre,
                'categorias' => $catalogo->categorias->map(function ($categoria) {
                    return [
                        'id' => $categoria->id,
                        'nombre' => $categoria->nombre,
                    ];
                }),
            ];
        });

        return response()->json(['mensaje' => 'Catálogos con categorías', 'datos' => $resultados], 200);
    }
    public function getActiveCatalogos()
    {
        // Obtener los IDs y nombres de los historiales activos
        $activeHistorials = Catalogo::where('estado', true) // Filtrar solo historiales activos
            ->select('id', 'nombre') // Asegúrate de que 'nombre' es el campo correcto
            ->get(); // Obtener los resultados

        return response()->json(['mensaje' => 'Catalogos activos', 'datos' => $activeHistorials], 200);
    }
    public function showCatalogoActive(string $id)
    {
        // Obtener el historial específico de un catálogo activo
        $catalogo = Catalogo::with([
            'categorias' => function ($query) {
                $query->where('estado', true); // Filtrar solo categorías activas
            },
            'categorias.productos.images', // Cargar imágenes de productos
            'categorias.productos.caracteristicas',
            'categorias.productos.modelos'
        ])
            ->where('id', $id) // Filtrar por el ID del catálogo
            ->where('estado', true) // Asegurarse de que el catálogo esté activo
            ->first();

        // Verificar si se encontró el catálogo
        if (!$catalogo) {
            return response()->json(['mensaje' => 'Catálogo no encontrado o inactivo'], 404);
        }

        // Modificar la estructura para incluir las URLs de las imágenes
        $catalogo->banner = asset("images/catalogos/banners/" . $catalogo->banner); // Asumiendo que el banner está en la ruta especificada

        $catalogo->categorias->transform(function ($categoria) {
            $categoria->banner = asset("images/categorias/banners/" . $categoria->banner); // Asumiendo que el banner de la categoría está en la ruta especificada

            $categoria->productos->transform(function ($producto) {
                $producto->imagen_principal = asset("images/productos/" . $producto->imagen_principal); // Asumiendo que la imagen principal está en la ruta especificada

                // Transformar las imágenes del producto
                $producto->images->transform(function ($image) {
                    $image->imagen = asset("images/productos/" . $image->imagen); // Asumiendo que las imágenes están en la ruta especificada
                    return $image;
                });

                return $producto;
            });

            return $categoria;
        });

        return response()->json(['mensaje' => 'Catálogo cargado', 'datos' => $catalogo], 200);
    }
}
