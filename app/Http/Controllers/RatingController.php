<?php

namespace App\Http\Controllers;

use App\Models\rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        // Validar que el producto_id esté presente y exista en la tabla de productos
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'rating' => 'required|integer|min:1|max:5', // Asegúrate de validar el rating
            'comment' => 'nullable|string|max:255', // Asegúrate de validar el comentario
        ]);

        // Verificar si el producto ya ha sido calificado por el usuario autenticado
        $existingRating = rating::where('user_id', auth()->id())
                                ->where('producto_id', $request->producto_id)
                                ->first();

        if ($existingRating) {
            // Si ya existe, actualizar la calificación y el comentario
            $existingRating->update([
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]);

            return response()->json(['message' => 'Calificación actualizada con éxito.'], 200); // 200 OK
        } else {
            // Si no existe, crear un nuevo rating
            $rating = new rating();
            $rating->user_id = auth()->id(); // Asumiendo que el usuario está autenticado
            $rating->producto_id = $request->producto_id; // Asegúrate de que esto no sea null
            $rating->rating = $request->rating; // Asegúrate de que el rating también esté presente
            $rating->comment = $request->comment; // Guarda el comentario si está presente
            $rating->save();

            return response()->json(['message' => 'Calificación guardada con éxito.'], 201); // 201 Created
        }
    }

    public function index()
    {
        $ratings = rating::with('producto')
            ->where('user_id', auth()->id())
            ->get();
    
        $ratingsCount = rating::select('producto_id', DB::raw('count(*) as total'))
            ->groupBy('producto_id')
            ->get()
            ->keyBy('producto_id');
    
        $ratingsAvg = rating::select('producto_id', DB::raw('avg(rating) as average'))
            ->groupBy('producto_id')
            ->get()
            ->keyBy('producto_id');
    
        $ratingDistribution = rating::select('producto_id', 'rating', DB::raw('count(*) as count'))
            ->groupBy('producto_id', 'rating')
            ->get()
            ->groupBy('producto_id');
    
        foreach ($ratings as $rating) {
            $productoId = $rating->producto_id;
        
            $rating->total_users = $ratingsCount->get($productoId)->total ?? 0;
        
            $avgRating = $ratingsAvg->get($productoId)->average ?? 0;
            $rating->average_rating = round($avgRating, 1);
        
            $rating->rating_percentage = round(($avgRating / 5) * 100);
        
            if (isset($ratingDistribution[$productoId])) {
                $distribution = [
                    '1' => 0,
                    '2' => 0,
                    '3' => 0,
                    '4' => 0,
                    '5' => 0
                ];
        
                foreach ($ratingDistribution[$productoId] as $item) {
                    $distribution[$item->rating] = $item->count;
                }
        
                $rating->rating_distribution = $distribution;
            }
        }
    
        return response()->json($ratings);
    }
    /**
     * Obtener estadísticas de calificación para un producto específico
     */
    public function getProductRatingStats($productoId)
    {
        // Verificar si el producto existe
        $productExists = DB::table('productos')->where('id', $productoId)->exists();
        
        if (!$productExists) {
            return response()->json(['error' => 'El producto no existe'], 404);
        }

        // Obtener el total de calificaciones para este producto
        $totalRatings = rating::where('producto_id', $productoId)->count(); // Change 'rating' to 'Rating'
        
        if ($totalRatings === 0) {
            return response()->json([
                'producto_id' => $productoId,
                'total_ratings' => 0,
                'average_rating' => 0,
                'rating_percentage' => 0,
                'rating_distribution' => [
                    '1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0
                ],
                'rating_percentages' => [
                    '1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0
                ]
            ]);
        }

        // Obtener el promedio de calificaciones
        $avgRating = rating::where('producto_id', $productoId)->avg('rating'); // Change 'rating' to 'Rating'
        
        // Obtener la distribución de calificaciones
        $distribution = rating::select('rating', DB::raw('count(*) as count')) // Change 'rating' to 'Rating'
            ->where('producto_id', $productoId)
            ->groupBy('rating')
            ->get()
            ->pluck('count', 'rating')
            ->toArray();
        
        // Asegurar que todas las calificaciones estén representadas
        $fullDistribution = [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0
        ];
        
        foreach ($distribution as $rating => $count) {
            $fullDistribution["$rating"] = $count;
        }
        
        // Calcular porcentajes para cada nivel de calificación
        $percentages = [];
        foreach ($fullDistribution as $stars => $count) {
            $percentages["$stars"] = round(($count / $totalRatings) * 100);
        }

        return response()->json([
            'producto_id' => $productoId,
            'total_ratings' => $totalRatings,
            'average_rating' => round($avgRating, 1),
            'rating_percentage' => round(($avgRating / 5) * 100),
            'rating_distribution' => $fullDistribution,
            'rating_percentages' => $percentages
        ]);
    }

    /**
     * Obtener el total de usuarios que han calificado productos
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalRatingUsers()
    {
        // Obtener el número total de usuarios únicos que han calificado productos
        $totalUsers = rating::select('user_id')
            ->distinct()
            ->count();
        
        // Obtener el total de calificaciones
        $totalRatings = rating::count();
        
        // Obtener el promedio general de todas las calificaciones
        $avgRating = rating::avg('rating');
        
        // Calcular el porcentaje general basado en el promedio
        $overallPercentage = round(($avgRating / 5) * 100);
        
        // Obtener la distribución general de calificaciones
        $distribution = rating::select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->get()
            ->pluck('count', 'rating')
            ->toArray();
        
        // Asegurar que todas las calificaciones estén representadas
        $fullDistribution = [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0
        ];
        
        foreach ($distribution as $rating => $count) {
            $fullDistribution["$rating"] = $count;
        }
        
        // Calcular porcentajes para cada nivel de calificación
        $percentages = [];
        foreach ($fullDistribution as $stars => $count) {
            $percentages["$stars"] = $totalRatings > 0 ? round(($count / $totalRatings) * 100) : 0;
        }
        
        return response()->json([
            'total_users' => $totalUsers,
            'total_ratings' => $totalRatings,
            'average_rating' => round($avgRating, 1),
            'overall_percentage' => $overallPercentage,
            'rating_distribution' => $fullDistribution,
            'rating_percentages' => $percentages
        ]);
    }
    
    /**
     * Obtener estadísticas de calificación por categoría de producto
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRatingStatsByCategory()
    {
        // Obtener estadísticas agrupadas por categoría
        $categoryStats = DB::table('ratings')
            ->join('productos', 'ratings.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->select(
                'categorias.id as categoria_id',
                'categorias.nombre as categoria_nombre',
                DB::raw('COUNT(DISTINCT ratings.user_id) as total_users'),
                DB::raw('COUNT(ratings.id) as total_ratings'),
                DB::raw('AVG(ratings.rating) as average_rating')
            )
            ->groupBy('categorias.id', 'categorias.nombre')
            ->get();
        
        // Calcular porcentajes y añadir información adicional
        foreach ($categoryStats as $category) {
            $category->rating_percentage = round(($category->average_rating / 5) * 100);
            $category->average_rating = round($category->average_rating, 1);
            
            // Obtener la distribución de calificaciones para esta categoría
            $distribution = DB::table('ratings')
                ->join('productos', 'ratings.producto_id', '=', 'productos.id')
                ->where('productos.categoria_id', $category->categoria_id)
                ->select('ratings.rating', DB::raw('count(*) as count'))
                ->groupBy('ratings.rating')
                ->get()
                ->pluck('count', 'rating')
                ->toArray();
            
            // Asegurar que todas las calificaciones estén representadas
            $fullDistribution = [
                '1' => 0,
                '2' => 0,
                '3' => 0,
                '4' => 0,
                '5' => 0
            ];
            
            foreach ($distribution as $rating => $count) {
                $fullDistribution["$rating"] = $count;
            }
            
            $category->rating_distribution = $fullDistribution;
            
            // Calcular porcentajes para cada nivel de calificación
            $percentages = [];
            foreach ($fullDistribution as $stars => $count) {
                $percentages["$stars"] = $category->total_ratings > 0 ? 
                    round(($count / $category->total_ratings) * 100) : 0;
            }
            
            $category->rating_percentages = $percentages;
        }
        
        return response()->json([
            'total_categories' => count($categoryStats),
            'categories' => $categoryStats
        ]);
    }
}