<?php

namespace App\Http\Controllers;

use App\Models\Caracteristica;
use App\Models\Image;
use App\Models\ModeloProducto;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductosController extends Controller
{
    public function index(Request $request)
    {
        // Start with a base query
        $query = Producto::with('images', 'categoria', 'user:id,nombre,apellido', 'caracteristicas', 'modelos');
        
        // Filter by category if provided
        if ($request->has('categoria_id') && $request->categoria_id != '') {
            $query->where('categoria_id', $request->categoria_id);
        }
        
        // Search by product name if provided
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('nombre', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('descripcion', 'LIKE', "%{$searchTerm}%");
            });
        }
        
        // Paginate the results (10 items per page)
        $products = $query->paginate(10);
        
        return response()->json([
            "mensaje" => "Productos cargados correctamente", 
            "datos" => $products->items(),
            "pagination" => [
                "total" => $products->total(),
                "current_page" => $products->currentPage(),
                "per_page" => $products->perPage(),
                "last_page" => $products->lastPage(),
                "from" => $products->firstItem() ?? 0,
                "to" => $products->lastItem() ?? 0
            ]
        ], 200);
    }
    public function store(Request $request)
    {
        // Validación (mantén esta parte igual)
        $request->validate([
            "nombre" => "required|string|max:255",
            "descripcion" => "nullable|string",
            "precio" => "required|numeric",
            "modelos" => "required|array",
            "cantidad_minima" => "required|integer|min:1",
            "cantidad_maxima" => "required|integer|min:1|gt:cantidad_minima",
            "images" => "required|array",
            "images.*" => "image|mimes:jpeg,png,jpg,gif,webp|max:2048",
            'caracteristicas' => "required|array",
        ]);

        DB::beginTransaction();
        try {
            // Crear el producto (mantén esta parte igual)
            $item = new Producto();
            $item->nombre = $request->nombre;
            $item->descripcion = $request->descripcion;
            $item->precio = $request->precio;
            $item->categoria_id = $request->categoria_id;
            $item->user_id = auth()->id();
            $item->cantidad_minima = $request->cantidad_minima;
            $item->cantidad_maxima = $request->cantidad_maxima;
            $item->cantidad = 0;

            if ($request->file('imagen_principal')) {
                $imagen_principal = $request->file('imagen_principal');
                $nombreImagen = md5_file($imagen_principal->getPathname()) . '.' . $imagen_principal->getClientOriginalExtension();
                $imagen_principal->move("images/imagenes_principales/", $nombreImagen);
                $item->imagen_principal = $nombreImagen;
            }
            $item->save();

            // Agregar modelos al producto (parte modificada)
            foreach ($request->modelos as $modeloData) {
                if (is_string($modeloData)) {
                    $modeloData = json_decode($modeloData, true);
                }

                if (!is_array($modeloData)) {
                    throw new \Exception("Datos de modelo inválidos");
                }

                ModeloProducto::create([
                    'nombre' => $modeloData['nombre'],
                    'precio' => $modeloData['precio'],
                    'cantidad_minima' => $modeloData['cantidad_minima'] ?? 1,
                    'cantidad_maxima' => $modeloData['cantidad_maxima'] ?? 100,
                    'producto_id' => $item->id,
                ]);
            }

            // Agregar imágenes al producto (mantén esta parte igual)
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $imageData) {
                    $nombreImagen = md5_file($imageData->getPathname()) . '.' . $imageData->getClientOriginalExtension();
                    $imageData->move("images/productos", $nombreImagen);

                    $color = $request->input("colors.$index");

                    Image::create([
                        'imagen' => $nombreImagen,
                        'producto_id' => $item->id,
                        'color' => $color,
                    ]);
                }
            }

            // Agregar características (mantén esta parte igual)
            $caracteristicas = $request->caracteristicas ?? [];
            foreach ($caracteristicas as $caracteristica) {
                if (!empty($caracteristica)) {
                    Caracteristica::create([
                        "producto_id" => $item->id,
                        "caracteristica" => $caracteristica
                    ]);
                }
            }

            DB::commit();
            return response()->json(["mensaje" => "Producto creado con éxito", "producto" => $item], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json(["mensaje" => "Error al crear el producto", "error" => $th->getMessage()], 500);
        }
    }
    public function show(string $id)
    {
        $item = Producto::with('images', 'categoria', 'user', 'caracteristicas', 'modelos')->find($id);
        // Modificar las imágenes para incluir la URL completa
        $item->images->transform(function ($image) {
            $image->imagen = asset("images/productos/" . $image->imagen); // Cambia la ruta según sea necesario
            return $image;
        });

        // Modificar la imagen principal para incluir la URL completa
        if ($item->imagen_principal) {
            $item->imagen_principal = asset("images/imagenes_principales/" . $item->imagen_principal); // Cambia la ruta según sea necesario
        }
        return response()->json(["mensaje" => "Producto cargado correctamente", "dato" => $item], 200);
    }
    public function showProductoUser(string $id)
    {
        // Obtener el producto específico con sus relaciones
        $item = Producto::with("images", "categoria", "caracteristicas", "modelos")->findOrFail($id);

        // Modificar las imágenes para incluir la URL completa
        $item->images->transform(function ($image) {
            $image->imagen = asset("images/productos/" . $image->imagen); // Cambia la ruta según sea necesario
            return $image;
        });

        // Modificar la imagen principal para incluir la URL completa
        if ($item->imagen_principal) {
            $item->imagen_principal = asset("images/imagenes_principales/" . $item->imagen_principal); // Cambia la ruta según sea necesario
        }

        // Obtener productos similares (por ejemplo, los que tienen la misma categoría)
        $productosSimilares = Producto::with("images", "categoria", "caracteristicas")
            ->where('categoria_id', $item->categoria_id)
            ->where('id', '!=', $item->id) // Excluir el producto actual
            ->take(5) // Limitar a 2 productos
            ->get();

        // Modificar las imágenes de los productos similares para incluir la URL completa
        $productosSimilares->transform(function ($similar) {
            $similar->images->transform(function ($image) {
                $image->imagen = asset("images/productos/" . $image->imagen); // Cambia la ruta según sea necesario
                return $image;
            });

            // Modificar la imagen principal para incluir la URL completa
            if ($similar->imagen_principal) {
                $similar->imagen_principal = asset("images/imagenes_principales/" . $similar->imagen_principal); // Cambia la ruta según sea necesario
            }

            return $similar;
        });

        // Retornar la respuesta JSON con el producto y los productos similares
        return response()->json([
            "mensaje" => "Producto cargado correctamente",
            "dato" => $item,
            "productos_similares" => $productosSimilares
        ], 200);
    }
    /*   public function update(Request $request, $id)
    {
        // Validar la solicitud
        $request->validate([
            "nombre" => "required|string|max:255",
            "descripcion" => "nullable|string",
            "precio" => "required|numeric",
            "modelos" => "nullable|array",
            "categoria_id" => "nullable|integer",
            "images" => "nullable|array",
            "images.*" => "image|mimes:jpeg,png,jpg,gif,webp|max:2048",
            'caracteristicas' => "nullable|array",
            'colors' => "nullable|array", // Asegúrate de validar los colores
        ]);

        // Iniciar una transacción
        DB::beginTransaction();
        try {
            // Encontrar el producto
            $item = Producto::findOrFail($id);

            // Actualizar los campos del producto
            $item->nombre = $request->nombre;
            $item->descripcion = $request->descripcion;
            $item->precio = $request->precio;
            $item->categoria_id = $request->categoria_id;
            $item->cantidad_minima = $request->cantidad_minima;
            $item->cantidad_maxima = $request->cantidad_maxima;

            // Actualizar la imagen principal si se proporciona una nueva
            if ($request->file('imagen_principal')) {
                if ($item->imagen_principal) {
                    Storage::delete("images/imagenes_principales/" . $item->imagen_principal);
                }

                $imagen_principal = $request->file('imagen_principal');
                $nombreImagen = md5_file($imagen_principal->getPathname()) . '.' . $imagen_principal->getClientOriginalExtension();
                $imagen_principal->move("images/imagenes_principales/", $nombreImagen);
                $item->imagen_principal = $nombreImagen;
            }

            $item->save();

            // Actualizar modelos del producto
            ModeloProducto::where("producto_id", $item->id)->delete();
            foreach ($request->modelos as $modeloData) {
                try {
                    $modeloData = json_decode($modeloData, true);

                    if (is_array($modeloData)) {
                        ModeloProducto::create([
                            'nombre' => $modeloData['nombre'],
                            'precio' => $modeloData['precio'],
                            'cantidad_minima' => $modeloData['cantidad_minima'] ?? 1,
                            'cantidad_maxima' => $modeloData['cantidad_maxima'] ?? 100,
                            'producto_id' => $item->id,
                        ]);
                    } else {
                        return response()->json(["mensaje" => "Error en los datos de los modelos"], 422);
                    }
                } catch (\Exception $e) {
                    return response()->json(["mensaje" => "Error al crear el modelo: " . $e->getMessage()], 500);
                }
            }

            // Manejar imágenes existentes (actualizar color y/o archivo)
            if ($request->has('existing_images') && is_array($request->existing_images)) {
                foreach ($request->existing_images as $imageId => $color) {
                    $image = Image::where('id', $imageId)
                        ->where('producto_id', $item->id)
                        ->first();

                    if ($image) {
                        // Actualizar el color
                        $image->color = $color;

                        // Si hay un nuevo archivo para esta imagen existente
                        if ($request->hasFile("existing_images_files.{$imageId}")) {
                            // Eliminar la imagen anterior del almacenamiento
                            Storage::delete("images/productos/" . $image->imagen);

                            // Guardar la nueva imagen
                            $newImageFile = $request->file("existing_images_files.{$imageId}");
                            $newImageName = md5_file($newImageFile->getPathname()) . '.' . $newImageFile->getClientOriginalExtension();
                            $newImageFile->move("images/productos", $newImageName);

                            // Actualizar el nombre de la imagen en la base de datos
                            $image->imagen = $newImageName;
                        }

                        $image->save();
                    }
                }
            }

            // Agregar nuevas imágenes
            if ($request->hasFile('images') && is_array($request->file('images'))) {
                foreach ($request->file('images') as $index => $imageData) {
                    try {
                        // Generar un nombre único para la imagen
                        $nombreImagen = md5_file($imageData->getPathname()) . '.' . $imageData->getClientOriginalExtension();
                        // Mover la imagen a la carpeta deseada
                        $imageData->move("images/productos", $nombreImagen);

                        // Obtener el color correspondiente
                        $color = $request->input("colors.$index"); // Asegúrate de que el nombre sea correcto

                        // Crear una nueva instancia de Image y guardar en la base de datos
                        Image::create([
                            'imagen' => $nombreImagen, // Nombre de la imagen
                            'producto_id' => $item->id, // Asociar la imagen con el producto
                            'color' => $color, // Color de la imagen
                        ]);
                    } catch (\Exception $e) {
                        return response()->json(["mensaje" => "Error al cargar la imagen: " . $e->getMessage()], 500);
                    }
                }
            }
            // Actualizar características del producto
            Caracteristica::where("producto_id", $item->id)->delete();
            foreach ($request->caracteristicas ?? [] as $caracteristica) {
                Caracteristica::create([
                    "producto_id" => $item->id,
                    "caracteristica" => $caracteristica
                ]);
            }

            // Confirmar la transacción
            DB::commit();
            return response()->json(["mensaje" => "Producto actualizado con éxito", "producto" => $item], 200);
        } catch (\Throwable $th) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            return response()->json(["mensaje" => "Error al actualizar el producto", "error" => $th->getMessage()], 500);
        }
    } */
    public function update(Request $request, $id)
    {
        // Validar la solicitud
        $request->validate([
            "nombre" => "required|string|max:255",
            "descripcion" => "nullable|string",
            "precio" => "required|numeric",
            "modelos" => "nullable|array",
            "categoria_id" => "nullable|integer",
            "images" => "nullable|array",
            "images.*" => "image|mimes:jpeg,png,jpg,gif,webp|max:2048",
            'caracteristicas' => "nullable|array",
            'colors' => "nullable|array",
        ]);

        // Iniciar una transacción
        DB::beginTransaction();
        try {
            // Encontrar el producto
            $item = Producto::findOrFail($id);

            // Actualizar los campos del producto
            $item->nombre = $request->nombre;
            $item->descripcion = $request->descripcion;
            $item->precio = $request->precio;
            $item->categoria_id = $request->categoria_id;
            $item->cantidad_minima = $request->cantidad_minima;
            $item->cantidad_maxima = $request->cantidad_maxima;

            // Actualizar la imagen principal si se proporciona una nueva
            if ($request->file('imagen_principal')) {
                if ($item->imagen_principal) {
                    Storage::delete("images/imagenes_principales/" . $item->imagen_principal);
                }

                $imagen_principal = $request->file('imagen_principal');
                $nombreImagen = md5_file($imagen_principal->getPathname()) . '.' . $imagen_principal->getClientOriginalExtension();
                $imagen_principal->move("images/imagenes_principales/", $nombreImagen);
                $item->imagen_principal = $nombreImagen;
            }
            $item->save();

            // Actualizar modelos del producto
            ModeloProducto::where("producto_id", $item->id)->delete();

            foreach ($request->modelos as $modeloData) {
                try {
                    // Verificar si modeloData es un string (JSON) o ya es un array
                    if (is_string($modeloData)) {
                        $modeloData = json_decode($modeloData, true);
                    }

                    if (is_array($modeloData)) {
                        ModeloProducto::create([
                            'nombre' => $modeloData['nombre'],
                            'precio' => $modeloData['precio'],
                            'cantidad_minima' => $modeloData['cantidad_minima'] ?? 1,
                            'cantidad_maxima' => $modeloData['cantidad_maxima'] ?? 100,
                            'producto_id' => $item->id,
                        ]);
                    } else {
                        return response()->json(["mensaje" => "Error en los datos de los modelos"], 422);
                    }
                } catch (\Exception $e) {
                    return response()->json(["mensaje" => "Error al crear el modelo: " . $e->getMessage()], 500);
                }
            }

            // Modificación para el método update en ProductosController.php

            // Manejar imágenes existentes (actualizar color y/o archivo)
            if ($request->has('existing_images')) {
                foreach ($request->existing_images as $imageId => $value) {
                    $image = Image::where('id', $imageId)
                        ->where('producto_id', $item->id)
                        ->first();

                    if ($image) {
                        // Actualizar el color si está presente
                        if (isset($request->existing_colors[$imageId])) {
                            $image->color = $request->existing_colors[$imageId];
                        }

                        // Si hay un nuevo archivo para esta imagen existente
                        if ($request->hasFile("existing_images_files.{$imageId}")) {
                            // Eliminar la imagen anterior del almacenamiento
                            $oldImagePath = public_path("images/productos/" . $image->imagen);
                            if (file_exists($oldImagePath)) {
                                unlink($oldImagePath);
                            }

                            // Guardar la nueva imagen
                            $newImageFile = $request->file("existing_images_files.{$imageId}");
                            $newImageName = md5_file($newImageFile->getPathname()) . '.' . $newImageFile->getClientOriginalExtension();
                            $newImageFile->move("images/productos", $newImageName);

                            // Actualizar el nombre de la imagen en la base de datos
                            $image->imagen = $newImageName;
                        }

                        $image->save();
                    }
                }
            }

            // Agregar nuevas imágenes
            if ($request->hasFile('images') && is_array($request->file('images'))) {
                foreach ($request->file('images') as $index => $imageData) {
                    try {
                        // Generar un nombre único para la imagen
                        $nombreImagen = md5_file($imageData->getPathname()) . '.' . $imageData->getClientOriginalExtension();
                        // Mover la imagen a la carpeta deseada
                        $imageData->move("images/productos", $nombreImagen);

                        // Obtener el color correspondiente
                        $color = $request->input("colors.$index");

                        // Crear una nueva instancia de Image y guardar en la base de datos
                        Image::create([
                            'imagen' => $nombreImagen,
                            'producto_id' => $item->id,
                            'color' => $color,
                        ]);
                    } catch (\Exception $e) {
                        return response()->json(["mensaje" => "Error al cargar la imagen: " . $e->getMessage()], 500);
                    }
                }
            }

            // Actualizar características del producto
            Caracteristica::where("producto_id", $item->id)->delete();
            foreach ($request->caracteristicas ?? [] as $caracteristica) {
                Caracteristica::create([
                    "producto_id" => $item->id,
                    "caracteristica" => $caracteristica
                ]);
            }

            // Confirmar la transacción
            DB::commit();
            return response()->json(["mensaje" => "Producto actualizado con éxito", "producto" => $item], 200);
        } catch (\Throwable $th) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            return response()->json(["mensaje" => "Error al actualizar el producto", "error" => $th->getMessage()], 500);
        }
    }
    public function destroyImage(Request $request, string $productoId, string $imagenId)
    {
        // Iniciar una transacción
        DB::beginTransaction();
        try {
            // Buscar la imagen
            $image = Image::where('id', $imagenId)->where('producto_id', $productoId)->firstOrFail();

            // Eliminar la imagen del sistema de archivos
            $imagePath = public_path("images/productos/" . $image->imagen);
            if (file_exists($imagePath)) {
                unlink($imagePath); // Eliminar el archivo de imagen
            }

            // Eliminar la imagen de la base de datos
            $image->delete();

            // Confirmar la transacción
            DB::commit();
            return response()->json(["mensaje" => "Imagen eliminada con éxito"], 200);
        } catch (\Throwable $th) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            return response()->json(["mensaje" => "Error al eliminar la imagen", "error" => $th->getMessage()], 500);
        }
    }
    public function productosRecientes()
    {
        // Obtener los 4 productos más recientes
        $productos = Producto::with('images', 'categoria')
            ->orderBy('created_at', 'desc') // Ordenar por fecha de creación
            ->take(5) // Limitar a 4 productos
            ->get();

        // Modificar las imágenes para incluir la URL completa
        $productos->transform(function ($producto) {
            $producto->images->transform(function ($image) {
                $image->imagen = asset("images/productos/" . $image->imagen); // Cambia la ruta según sea necesario
                return $image;
            });

            // Modificar la imagen principal para incluir la URL completa
            if ($producto->imagen_principal) {
                $producto->imagen_principal = asset("images/imagenes_principales/" . $producto->imagen_principal); // Cambia la ruta según sea necesario
            }

            return $producto;
        });

        return response()->json(["mensaje" => "Productos recientes cargados correctamente", "datos" => $productos], 200);
    }

    public function filtrarProductos(Request $request)
    {
        // Obtener los parámetros de la solicitud
        $categoriaId = $request->input('categoria_id');
        $catalogoId = $request->input('catalogo_id');
        $search = $request->input('search');

        // Iniciar la consulta
        $query = Producto::with('images', 'categoria', 'caracteristicas', 'modelos');

        // Aplicar filtro por categoría si se proporciona
        if ($categoriaId) {
            $query->where('categoria_id', $categoriaId);
        }

        // Aplicar filtro por catálogo a través de la relación de categoría si se proporciona
        if ($catalogoId) {
            $query->whereHas('categoria', function ($q) use ($catalogoId) {
                $q->where('catalogo_id', $catalogoId); // Asegúrate de que 'catalogo_id' es el nombre correcto de la columna en la tabla de categorías
            });
        }

        // Aplicar búsqueda por nombre o descripción si se proporciona
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', '%' . $search . '%')
                    ->orWhere('descripcion', 'like', '%' . $search . '%');
            });
        }

        // Ejecutar la consulta y obtener los resultados
        $productos = $query->get();

        // Modificar las imágenes para incluir la URL completa
        $productos->transform(function ($producto) {
            $producto->images->transform(function ($image) {
                $image->imagen = asset("images/productos/" . $image->imagen); // Cambia la ruta según sea necesario
                return $image;
            });

            // Modificar la imagen principal para incluir la URL completa
            if ($producto->imagen_principal) {
                $producto->imagen_principal = asset("images/imagenes_principales/" . $producto->imagen_principal); // Cambia la ruta según sea necesario
            }

            return $producto;
        });

        // Retornar la respuesta JSON con los productos filtrados
        return response()->json(["mensaje" => "Productos filtrados correctamente", "datos" => $productos], 200);
    }
}
