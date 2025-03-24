<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $item = Categoria::with('catalogo', 'productos')->orderBy('id', 'desc')->paginate(10);
        // Iterate over each catalog to modify the banner URL
        foreach ($item as $categoria) {
            if ($categoria->banner) {
                $categoria->banner = asset("images/categorias/banners/" . $categoria->banner); // Change the path as necessary
            }
        }
        return response()->json(["mensaje" => "Categorias cargadas correctamente", "datos" => $item], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            "nombre" => "required",
            "titulo" => "required",
            "subtitulo" => "required",
            "descripcion" => "nullable",
            'catalogo_id' => 'nullable|exists:catalogos,id', // Asegúrate de que catalogo_id sea requerido y exista

        ]);
        $item = new Categoria();
        $item->nombre = $request->nombre;
        $item->titulo = $request->titulo;
        $item->subtitulo = $request->subtitulo;
        $item->catalogo_id = $request->catalogo_id;
        $item->descripcion = $request->descripcion;
        // Manejar la carga del banner
        if ($request->file('banner')) {
            $banner = $request->file('banner');
            $nombreImagen = md5_file($banner->getPathname()) . '.' . $banner->getClientOriginalExtension();
            $banner->move("images/categorias/banners/", $nombreImagen);
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
        $item = Categoria::with('productos')->findOrFail($id);
        // Modificar la imagen principal para incluir la URL completa
        if ($item->banner) {
            $item->banner = asset("images/categorias/banners/" . $item->banner); // Cambia la ruta según sea necesario
        }
        return response()->json(["mensaje" => "Dato cargado", "dato" => $item], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            "nombre" => "required|string|max:255", // Cambiar a 'required' para asegurar que siempre se envíe
            "descripcion" => "nullable|string",
            'catalogo_id' => 'nullable|exists:catalogos,id', // Asegúrate de que catalogo_id sea válido si se proporciona
            "titulo" => "nullable",
            "subtitulo" => "nullable",
            "banner" => "nullable|image|mimes:jpeg,png,jpg,gif|max:2048" // Asegúrate de validar la imagen principal
        ]);

        $item = Categoria::findOrFail($id);

        // Asignar valores solo si están presentes
        $item->nombre = $request->filled('nombre') ? $request->nombre : $item->nombre;
        $item->titulo = $request->filled('titulo') ? $request->titulo : $item->titulo;
        $item->subtitulo = $request->filled('subtitulo') ? $request->subtitulo : $item->subtitulo;
        $item->descripcion = $request->filled('descripcion') ? $request->descripcion : $item->descripcion;
        $item->catalogo_id = $request->filled('catalogo_id') ? $request->catalogo_id : $item->catalogo_id;

        // Actualizar la imagen principal si se proporciona una nueva
        if ($request->file('banner')) {
            // Eliminar la imagen anterior si es necesario
            if ($item->banner) {
                Storage::delete("images/categorias/banners/" . $item->banner);
            }

            $banner = $request->file('banner');
            $nombreImagen = md5_file($banner->getPathname()) . '.' . $banner->getClientOriginalExtension();
            $banner->move("images/categorias/banners/", $nombreImagen);
            $item->banner = $nombreImagen;
        }

        $item->save();

        return response()->json(["mensaje" => "Registro actualizado", "dato" => $item], 200);
    }
    public function destroy(string $id)
    {
        $item = Categoria::findOrFail($id);
        $item->estado = !$item->estado;
        $item->save();
        return response()->json(["mensaje"=> "Estado modificado", "dato" => $item], 200);
    }
    public function indexActivos()
    {
        $item = Categoria::with('productos')->where('estado', true)->get();
        return response()->json(["mensaje" => "Categorias activas cargadas correctamente", "datos" => $item], 200);
    }
    public function search(Request $request)
    {
        // Validar el parámetro de búsqueda
        $request->validate([
            'query' => 'nullable|string|max:255',
        ]);

        // Obtener el término de búsqueda
        $searchQuery = $request->input('query');

        // Realizar la consulta
        $item = Categoria::with('productos.images') // Cargar también las imágenes de los productos
            ->where('estado', true) // Solo categorías activas
            ->when($searchQuery, function ($queryBuilder) use ($searchQuery) {
                return $queryBuilder->where('nombre', 'like', '%' . $searchQuery . '%')
                    ->orWhere('descripcion', 'like', '%' . $searchQuery . '%')
                    ->orWhereHas('productos', function ($productQuery) use ($searchQuery) {
                        $productQuery->where('nombre', 'like', '%' . $searchQuery . '%');
                    });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Iterar sobre cada categoría para modificar la URL de la imagen
        foreach ($item as $categoria) {
            if ($categoria->banner) {
                $categoria->banner = asset("images/categorias/banners/" . $categoria->banner);
            }

            // Iterar sobre cada producto para modificar la URL de la imagen principal y las imágenes
            foreach ($categoria->productos as $producto) {
                if ($producto->imagen_principal) { // Asegúrate de que 'imagen_principal' sea el nombre correcto del campo
                    $producto->imagen_principal = asset("images/imagenes_principales/" . $producto->imagen_principal);
                }

                // Iterar sobre las imágenes del producto
                foreach ($producto->images as $image) {
                    if ($image->imagen) { // Asegúrate de que 'imagen' sea el nombre correcto del campo
                        $image->imagen = asset("images/productos/" . $image->imagen); // Cambia la ruta según sea necesario
                    }
                }
            }
        }

        return response()->json(["mensaje" => "Categorias cargadas correctamente", "datos" => $item], 200);
    }
    public function getActiveCategorias()
    {
        // Obtener los IDs y nombres de los historiales activos
        $categoriasActives = Categoria::where('estado', true) // Filtrar solo historiales activos
            ->select('id', 'nombre') // Asegúrate de que 'nombre' es el campo correcto
            ->get(); // Obtener los resultados

        return response()->json(['mensaje' => 'Categorias activas', 'datos' => $categoriasActives], 200);
    }
}
