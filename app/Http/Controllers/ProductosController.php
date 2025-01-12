<?php

namespace App\Http\Controllers;

use App\Models\Image;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductosController extends Controller
{
    public function index()
    {
        $products = Producto::with('images', 'catalogo')->get();
        return response()->json(["mensaje" => "Productos cargados correctamente", "datos" => $products], 200);
    }
    public function store(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            "nombre" => "required|string|max:255",
            "descripcion" => "nullable|string",
            "precio" => "required|numeric",
            "catalogo_id" => "required",
            "images" => "required|array",
            "images.*" => "image|mimes:jpeg,png,jpg,gif|max:2048", // Validar que cada imagen sea un archivo de imagen
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
        $item = Producto::with('images', 'catalogo')->findOrFail($id);
        // Modificar las imágenes para incluir la URL completa
        $item->images->transform(function ($image) {
            $image->imagen = asset("images/productos/" . $image->imagen); // Cambia la ruta según sea necesario
            return $image;
        });
        return response()->json(["mensaje" => "Producto cargado correctamente", "dato" => $item], 200);
    }
    public function update(Request $request, string $id)
    {
        // Validar la solicitud
        $request->validate([
            "nombre" => "required|string|max:255",
            "descripcion" => "nullable|string",
            "precio" => "required|numeric",
            "catalogo_id" => "required",
            "images" => "nullable|array", // Las imágenes son opcionales en la actualización
            "images.*" => "image|mimes:jpeg,png,jpg,gif|max:2048", // Validar que cada imagen sea un archivo de imagen
        ]);

        // Iniciar una transacción
        DB::beginTransaction();
        try {
            // Buscar el producto
            $item = Producto::findOrFail($id); // Esto lanzará una excepción si no se encuentra el producto

            // Actualizar los campos del producto
            $item->nombre = $request->nombre;
            $item->descripcion = $request->descripcion;
            $item->precio = $request->precio;
            $item->catalogo_id = $request->catalogo_id;
            $item->save(); // Guardar los cambios en el producto

            // Si se proporcionan nuevas imágenes, manejarlas
            if ($request->hasFile('images')) {
                // Eliminar imágenes antiguas si es necesario
                // Aquí puedes agregar lógica para eliminar imágenes antiguas si lo deseas

                // Agregar nuevas imágenes al producto
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

            // Confirmar la transacción
            DB::commit();
            return response()->json(["mensaje" => "Producto actualizado con éxito", "producto" => $item], 200);
        } catch (\Throwable $th) {
            // Revertir la transacción en caso de error
            DB::rollBack();
            return response()->json(["mensaje" => "Error al actualizar el producto", "error" => $th->getMessage()], 500);
        }
    }
}
