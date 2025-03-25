<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Catalogo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchGlobal extends Controller
{
    /**
     * Realiza una búsqueda global en múltiples modelos
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $query = $request->input('q');
        $results = [];
        $limit = $request->input('limit', 10); // Límite por defecto de 10 resultados por categoría

        // Buscar en productos - REMOVED 'imagen' from the get() method
        $productos = Producto::where('nombre', 'LIKE', "%{$query}%")
            ->orWhere('descripcion', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get(['id', 'nombre'])
            ->map(function ($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                ];
            });
        
        if ($productos->count() > 0) {
            $results['productos'] = $productos;
        }

        // Buscar en categorías
        $categorias = Categoria::where('nombre', 'LIKE', "%{$query}%")
            ->orWhere('descripcion', 'LIKE', "%{$query}%")
            ->limit($limit)
            ->get(['id', 'nombre', 'descripcion'])
            ->map(function ($categoria) {
                return [
                    'id' => $categoria->id,
                    'nombre' => $categoria->nombre,
                ];
            });
        
        if ($categorias->count() > 0) {
            $results['categorias'] = $categorias;
        }

        // Buscar en catálogos
        $catalogos = Catalogo::where('nombre', 'LIKE', "%{$query}%")
            ->where('estado', 1) // Asumiendo que solo queremos catálogos activos
            ->limit($limit)
            ->get(['id', 'nombre'])
            ->map(function ($catalogo) {
                return [
                    'id' => $catalogo->id,
                    'nombre' => $catalogo->nombre,
                ];
            });
        
        if ($catalogos->count() > 0) {
            $results['catalogos'] = $catalogos;
        }

        // Implementación de los métodos faltantes
        return response()->json([
            'success' => true,
            'query' => $query,
            'results' => $results,
            'total' => array_sum(array_map(function ($item) {
                return count($item);
            }, $results)),
            'sugerencias' => []  // Dejamos sugerencias vacío por ahora
        ]);
    }

    /**
     * Guarda la búsqueda en el historial
     * 
     * @param string $query
     * @return void
     */
    private function saveSearchHistory($query)
    {
        // Implementa aquí la lógica para guardar el historial de búsqueda
        // Por ejemplo:
        // SearchHistory::create([
        //     'user_id' => auth()->id(),
        //     'query' => $query,
        //     'created_at' => now()
        // ]);
    }

    /**
     * Obtiene sugerencias basadas en la consulta
     * 
     * @param string $query
     * @return array
     */
    private function getSuggestions($query)
    {
        // Implementa aquí la lógica para obtener sugerencias
        // Por ejemplo, podrías devolver búsquedas populares relacionadas
        return [];
    }
}

