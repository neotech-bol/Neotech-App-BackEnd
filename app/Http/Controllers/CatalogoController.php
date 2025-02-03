<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\Producto;
use Illuminate\Http\Request;
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
        $catalogos = Catalogo::with(['productos.categoria', 'productos.images'])->get();

        // Iterate over each catalog to modify the banner URL
        foreach ($catalogos as $catalogo) {
            if ($catalogo->banner) {
                $catalogo->banner = asset("images/catalogos/banners/" . $catalogo->banner); // Change the path as necessary
            }
        }

        return response()->json(["mensaje" => "Catalogos cargados correctamente", "datos" => $catalogos], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "nombre" => "required",
            "descripcion" => "nullable",
            "banner" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048", // Validar que cada imagen sea un archivo de imagen
        ]);
        $item = new Catalogo();
        $item->nombre = $request->nombre;
        $item->descripcion = $request->descripcion;
        if ($request->file('banner')) {
            $banner = $request->file('banner');
            $nombreImagen = md5_file($banner->getPathname()) . '.' . $banner->getClientOriginalExtension();
            $banner->move("images/catalogos/banners/", $nombreImagen);
            $item->banner = $nombreImagen;
        }
        $item->save();
        return response()->json(["mensaje" => "Registro guardado", "dato" => $item], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Catalogo::with('productos')->find($id);
        // Modificar la imagen principal para incluir la URL completa
        if ($item->banner) {
            $item->banner = asset("images/catalogos/banners/" . $item->banner); // Cambia la ruta según sea necesario
        }
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
            "banner" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048", // Validar que cada imagen sea un archivo de imagen
        ]);
        try {
            $item = Catalogo::find($id);
            $item->nombre = $request->nombre;
            $item->descripcion = $request->descripcion;
            // Actualizar la imagen principal si se proporciona una nueva
            if ($request->file('banner')) {
                // Eliminar la imagen anterior si es necesario
                if ($item->banner) {
                    Storage::delete("images/imagenes_principales/" . $item->banner);
                }

                $banner = $request->file('banner');
                $nombreImagen = md5_file($banner->getPathname()) . '.' . $banner->getClientOriginalExtension();
                $banner->move("images/catalogos/banners/", $nombreImagen);
                $item->banner = $nombreImagen;
            }
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
    public function indexActivos()
    {
        // Obtener los catálogos activos con productos, categorías e imágenes
        $catalogos = Catalogo::with('productos.categoria', 'productos.images')
            ->where('estado', true)
            ->get();
    
        // Modificar la colección para incluir la URL de la imagen
        $catalogos->transform(function ($catalogo) {
            // Transformar los productos dentro del catálogo
            $catalogo->productos->transform(function ($producto) {
                // Generar la URL de la imagen principal
                if (isset($producto->imagen_principal)) {
                    $producto->imagen_principal = asset('images/imagenes_principales/' . $producto->imagen_principal);
                }
    
                // Generar la URL de las imágenes adicionales
                $producto->images->transform(function ($image) {
                    $image->imagen = asset('images/productos/' . $image->imagen); // Generar la URL de la imagen
                    return $image;
                });
    
                return $producto;
            });
    
            // Generar la URL del banner del catálogo si existe
            if ($catalogo->banner) {
                $catalogo->banner = asset("images/catalogos/banners/" . $catalogo->banner);
            }
    
            return $catalogo;
        });
    
        return response()->json(['mensaje' => 'Catálogos activos', 'datos' => $catalogos], 200);
    }
}
