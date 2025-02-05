<?php

namespace App\Http\Controllers;

use App\Models\Caracteristica;
use App\Models\Image;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductosController extends Controller
{
    public function index()
    {
        $products = Producto::with('images', 'catalogo', 'categoria', 'user:id,nombre,apellido', 'caracteristicas')->get();
        return response()->json(["mensaje" => "Productos cargados correctamente", "datos" => $products], 200);
    }
    public function store(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            "nombre" => "required|string|max:255",
            "descripcion" => "nullable|string",
            "precio" => "required|numeric",
            "images" => "required|array",
            "images.*" => "image|mimes:jpeg,png,jpg,gif|max:2048", // Validar que cada imagen sea un archivo de imagen, 
            'caracteristicas' => "required|array",

        ]);
        // Iniciar una transacción
        DB::beginTransaction();
        try {
            // Crear el producto
            $item = new Producto();
            $item->nombre = $request->nombre;
            $item->descripcion = $request->descripcion;
            $item->precio = $request->precio;
            $item->catalogo_id = $request->catalogo_id;
            $item->categoria_id = $request->categoria_id;
            $item->user_id = auth()->id(); // Asignar el ID del usuario autenticado
            $item->cantidad = 0;
            if ($request->file('imagen_principal')) {
                $imagen_principal = $request->file('imagen_principal');
                $nombreImagen = md5_file($imagen_principal->getPathname()) . '.' . $imagen_principal->getClientOriginalExtension();
                $imagen_principal->move("images/imagenes_principales/", $nombreImagen);
                $item->imagen_principal = $nombreImagen;
            }
            $item->save(); // Guardar el producto primero
            // Agregar imágenes al producto
            foreach ($request->file('images') as $imagen) {
                // Crear una nueva instancia de Image
                $image = new Image();

                // Generar un nombre único para la imagen
                $nombreImagen = md5_file($imagen->getPathname()) . '.' . $imagen->getClientOriginalExtension();
                // Mover la imagen a la carpeta deseada
                $imagen->move("images/productos", $nombreImagen);

                // Guardar la imagen en la base de datos
                $image->imagen = $nombreImagen; // Asumiendo que el campo en la base de datos se llama 'imagen'
                $image->producto_id = $item->id; // Asociar la imagen con el producto
                $image->save();
            }
            $caracteristicas = $request->caracteristicas ?? [];
            foreach ($caracteristicas as $caracteristica) {
                if (!empty($caracteristica)) { // Verificar que la propiedad exista
                    Caracteristica::create([
                        "producto_id" => $item->id,
                        "caracteristica"=> $caracteristica
                    ]);
                }
            }
            // Confirmar la transacción
            DB::commit();
            return response()->json(["mensaje" => "Producto creado con éxito", "producto" => $item], 201);
        } catch (\Throwable $th) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            return response()->json(["mensaje" => "Error al crear el producto", "error" => $th->getMessage()], 500);
        }
    }
    public function show(string $id)
    {
        $item = Producto::with('images', 'catalogo', 'categoria', 'user', 'caracteristicas')->find($id);
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
    public function showProductoUser (string $id)
{
    // Obtener el producto específico con sus relaciones
    $item = Producto::with("images", "catalogo", "categoria", "caracteristicas")->findOrFail($id);

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
    $productosSimilares = Producto::with("images", "catalogo", "categoria", "caracteristicas")
        ->where('categoria_id', $item->categoria_id)
        ->where('id', '!=', $item->id) // Excluir el producto actual
        ->take(2) // Limitar a 2 productos
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

    public function update(Request $request, $id)
{
        // Validar la solicitud
        $request->validate([
            "nombre" => "required|string|max:255",
            "descripcion" => "nullable|string",
            "precio" => "required|numeric",
            "catalogo_id" => "nullable|integer", // Asegúrate de que sea un entero si se proporciona
            "categoria_id" => "nullable|integer", // Asegúrate de que sea un entero si se proporciona
            "images" => "nullable|array", // Permitir que las imágenes sean opcionales en la actualización
            "images.*" => "image|mimes:jpeg,png,jpg,gif|max:2048", // Validar que cada imagen sea un archivo de imagen
            'caracteristicas' => "nullable|array", // Permitir que las características sean opcionales en la actualización
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
        $item->catalogo_id = $request->catalogo_id; // Puede ser null
        $item->categoria_id = $request->categoria_id; // Puede ser null

        // Actualizar la imagen principal si se proporciona una nueva
        if ($request->file('imagen_principal')) {
            // Eliminar la imagen anterior si es necesario
            if ($item->imagen_principal) {
                Storage::delete("images/imagenes_principales/" . $item->imagen_principal);
            }

            $imagen_principal = $request->file('imagen_principal');
            $nombreImagen = md5_file($imagen_principal->getPathname()) . '.' . $imagen_principal->getClientOriginalExtension();
            $imagen_principal->move("images/imagenes_principales/", $nombreImagen);
            $item->imagen_principal = $nombreImagen;
        }

        $item->save(); // Guardar los cambios en el producto

        // Actualizar imágenes del producto
        if ($request->hasFile('images')) {
            // Eliminar imágenes anteriores si es necesario
            $item->images()->delete(); // Eliminar todas las imágenes asociadas (opcional)

            foreach ($request->file('images') as $imagen) {
                // Crear una nueva instancia de Image
                $image = new Image();

                // Generar un nombre único para la imagen
                $nombreImagen = md5_file($imagen->getPathname()) . '.' . $imagen->getClientOriginalExtension();
                // Mover la imagen a la carpeta deseada
                $imagen->move("images/productos", $nombreImagen);

                // Guardar la imagen en la base de datos
                $image->imagen = $nombreImagen; // Asumiendo que el campo en la base de datos se llama 'imagen'
                $image->producto_id = $item->id; // Asociar la imagen con el producto
                $image->save();
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
}
