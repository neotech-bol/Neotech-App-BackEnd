<?php

namespace App\Http\Controllers;

use App\Models\rating;
use App\Models\User;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RatingController extends Controller
{
    /**
     * Almacenar una nueva calificación o actualizar una existente
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

            return response()->json([
                'message' => 'Calificación actualizada con éxito.',
                'rating' => $existingRating
            ], 200); // 200 OK
        } else {
            // Si no existe, crear un nuevo rating
            $rating = new rating();
            $rating->user_id = auth()->id(); // Asumiendo que el usuario está autenticado
            $rating->producto_id = $request->producto_id; // Asegúrate de que esto no sea null
            $rating->rating = $request->rating; // Asegúrate de que el rating también esté presente
            $rating->comment = $request->comment; // Guarda el comentario si está presente
            $rating->save();

            return response()->json([
                'message' => 'Calificación guardada con éxito.',
                'rating' => $rating
            ], 201); // 201 Created
        }
    }

    /**
     * Obtener todas las calificaciones del usuario autenticado
     * 
     * @return \Illuminate\Http\JsonResponse
     */
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
     * Actualizar una calificación existente
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:255',
        ]);

        $rating = rating::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$rating) {
            return response()->json([
                'message' => 'Calificación no encontrada o no tienes permiso para editarla.'
            ], 404);
        }

        $rating->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Calificación actualizada con éxito.',
            'rating' => $rating
        ]);
    }

    /**
     * Eliminar una calificación
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $rating = rating::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$rating) {
            return response()->json([
                'message' => 'Calificación no encontrada o no tienes permiso para eliminarla.'
            ], 404);
        }

        $rating->delete();

        return response()->json([
            'message' => 'Calificación eliminada con éxito.'
        ]);
    }

    /**
     * Obtener estadísticas de calificación para un producto específico
     * 
     * @param int $productoId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductRatingStats($productoId)
    {
        // Verificar si el producto existe
        $productExists = DB::table('productos')->where('id', $productoId)->exists();
        
        if (!$productExists) {
            return response()->json(['error' => 'El producto no existe'], 404);
        }

        // Obtener el total de calificaciones para este producto
        $totalRatings = rating::where('producto_id', $productoId)->count();
        
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
        $avgRating = rating::where('producto_id', $productoId)->avg('rating');
        
        // Obtener la distribución de calificaciones
        $distribution = rating::select('rating', DB::raw('count(*) as count'))
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

        // Obtener las calificaciones más recientes con datos de usuario
        $recentRatings = DB::table('ratings')
            ->join('users', 'ratings.user_id', '=', 'users.id')
            ->where('ratings.producto_id', $productoId)
            ->select(
                'ratings.id',
                'ratings.rating',
                'ratings.comment',
                'ratings.created_at',
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('ratings.created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'producto_id' => $productoId,
            'total_ratings' => $totalRatings,
            'average_rating' => round($avgRating, 1),
            'rating_percentage' => round(($avgRating / 5) * 100),
            'rating_distribution' => $fullDistribution,
            'rating_percentages' => $percentages,
            'recent_ratings' => $recentRatings
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

        // Obtener estadísticas por mes (últimos 12 meses)
        $ratingsByMonth = DB::table('ratings')
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'), DB::raw('AVG(rating) as average'))
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Formatear los datos para gráficos
        $monthlyData = [];
        foreach ($ratingsByMonth as $monthData) {
            $date = Carbon::createFromDate($monthData->year, $monthData->month, 1);
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'count' => $monthData->count,
                'average' => round($monthData->average, 1),
                'percentage' => round(($monthData->average / 5) * 100)
            ];
        }
        
        return response()->json([
            'total_users' => $totalUsers,
            'total_ratings' => $totalRatings,
            'average_rating' => round($avgRating, 1),
            'overall_percentage' => $overallPercentage,
            'rating_distribution' => $fullDistribution,
            'rating_percentages' => $percentages,
            'monthly_stats' => $monthlyData
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

            // Obtener los productos mejor calificados de esta categoría
            $topProducts = DB::table('ratings')
                ->join('productos', 'ratings.producto_id', '=', 'productos.id')
                ->where('productos.categoria_id', $category->categoria_id)
                ->select(
                    'productos.id',
                    'productos.nombre',
                    'productos.imagen_principal',
                    DB::raw('COUNT(ratings.id) as total_ratings'),
                    DB::raw('AVG(ratings.rating) as average_rating')
                )
                ->groupBy('productos.id', 'productos.nombre', 'productos.imagen_principal')
                ->having('total_ratings', '>=', 3) // Al menos 3 calificaciones
                ->orderBy('average_rating', 'desc')
                ->limit(5)
                ->get();

            foreach ($topProducts as $product) {
                $product->average_rating = round($product->average_rating, 1);
                $product->rating_percentage = round(($product->average_rating / 5) * 100);
            }

            $category->top_products = $topProducts;
        }
        
        return response()->json([
            'total_categories' => count($categoryStats),
            'categories' => $categoryStats
        ]);
    }

    /**
     * Obtener la cantidad de calificaciones por usuario y sus porcentajes
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRatingsByUser()
    {
        // Obtener el número de calificaciones por usuario
        $userRatings = DB::table('ratings')
            ->join('users', 'ratings.user_id', '=', 'users.id')
            ->select(
                'users.id as user_id',
                'users.name as user_name',
                'users.email as user_email',
                DB::raw('COUNT(ratings.id) as total_ratings'),
                DB::raw('AVG(ratings.rating) as average_rating')
            )
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_ratings', 'desc')
            ->get();
        
        // Obtener el total de calificaciones para calcular porcentajes
        $totalRatings = rating::count();
        
        // Calcular porcentajes y añadir información adicional
        foreach ($userRatings as $user) {
            // Calcular el porcentaje de calificaciones que ha hecho este usuario
            $user->percentage_of_total = $totalRatings > 0 ? 
                round(($user->total_ratings / $totalRatings) * 100, 1) : 0;
            
            // Calcular el porcentaje basado en el promedio de calificaciones
            $user->rating_percentage = round(($user->average_rating / 5) * 100);
            $user->average_rating = round($user->average_rating, 1);
            
            // Obtener la distribución de calificaciones para este usuario
            $distribution = DB::table('ratings')
                ->where('user_id', $user->user_id)
                ->select('rating', DB::raw('count(*) as count'))
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
            
            $user->rating_distribution = $fullDistribution;
            
            // Calcular porcentajes para cada nivel de calificación
            $percentages = [];
            foreach ($fullDistribution as $stars => $count) {
                $percentages["$stars"] = $user->total_ratings > 0 ? 
                    round(($count / $user->total_ratings) * 100) : 0;
            }
            
            $user->rating_percentages = $percentages;
            
            // Obtener las categorías más calificadas por este usuario
            $topCategories = DB::table('ratings')
                ->join('productos', 'ratings.producto_id', '=', 'productos.id')
                ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
                ->where('ratings.user_id', $user->user_id)
                ->select(
                    'categorias.id as categoria_id',
                    'categorias.nombre as categoria_nombre',
                    DB::raw('COUNT(ratings.id) as total_ratings')
                )
                ->groupBy('categorias.id', 'categorias.nombre')
                ->orderBy('total_ratings', 'desc')
                ->limit(3)
                ->get();
            
            $user->top_categories = $topCategories;
        }
        
        // Calcular estadísticas generales
        $stats = [
            'total_users' => count($userRatings),
            'total_ratings' => $totalRatings,
            'average_ratings_per_user' => $totalRatings > 0 ? round($totalRatings / count($userRatings), 1) : 0,
            'most_active_users' => $userRatings->take(5)
        ];
        
        return response()->json([
            'stats' => $stats,
            'users' => $userRatings
        ]);
    }

    /**
     * Obtener estadísticas detalladas de calificación para un usuario específico
     * 
     * @param int $userId ID del usuario
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserRatingStats($userId)
    {
        // Verificar si el usuario existe
        $userExists = DB::table('users')->where('id', $userId)->exists();
        
        if (!$userExists) {
            return response()->json(['error' => 'El usuario no existe'], 404);
        }
        
        // Obtener información básica del usuario
        $user = DB::table('users')
            ->where('id', $userId)
            ->select('id', 'name', 'email', 'created_at')
            ->first();
        
        // Obtener el total de calificaciones de este usuario
        $totalRatings = rating::where('user_id', $userId)->count();
        
        if ($totalRatings === 0) {
            return response()->json([
                'user' => $user,
                'total_ratings' => 0,
                'average_rating' => 0,
                'rating_percentage' => 0,
                'rating_distribution' => [
                    '1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0
                ],
                'rating_percentages' => [
                    '1' => 0, '2' => 0, '3' => 0, '4' => 0, '5' => 0
                ],
                'recent_ratings' => [],
                'top_categories' => []
            ]);
        }
        
        // Obtener el promedio de calificaciones
        $avgRating = rating::where('user_id', $userId)->avg('rating');
        
        // Obtener la distribución de calificaciones
        $distribution = rating::select('rating', DB::raw('count(*) as count'))
            ->where('user_id', $userId)
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
        
        // Obtener las calificaciones más recientes
        $recentRatings = DB::table('ratings')
            ->join('productos', 'ratings.producto_id', '=', 'productos.id')
            ->where('ratings.user_id', $userId)
            ->select(
                'ratings.id',
                'ratings.producto_id',
                'productos.nombre as producto_nombre',
                'productos.imagen_principal',
                'ratings.rating',
                'ratings.comment',
                'ratings.created_at'
            )
            ->orderBy('ratings.created_at', 'desc')
            ->limit(5)
            ->get();
        
        // Obtener las categorías más calificadas
        $topCategories = DB::table('ratings')
            ->join('productos', 'ratings.producto_id', '=', 'productos.id')
            ->join('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->where('ratings.user_id', $userId)
            ->select(
                'categorias.id as categoria_id',
                'categorias.nombre as categoria_nombre',
                DB::raw('COUNT(ratings.id) as total_ratings'),
                DB::raw('AVG(ratings.rating) as average_rating')
            )
            ->groupBy('categorias.id', 'categorias.nombre')
            ->orderBy('total_ratings', 'desc')
            ->limit(5)
            ->get();
        
        // Calcular porcentajes para las categorías
        foreach ($topCategories as $category) {
            $category->percentage_of_total = round(($category->total_ratings / $totalRatings) * 100);
            $category->rating_percentage = round(($category->average_rating / 5) * 100);
            $category->average_rating = round($category->average_rating, 1);
        }

        // Obtener estadísticas por mes (últimos 6 meses)
        $ratingsByMonth = DB::table('ratings')
            ->select(DB::raw('YEAR(created_at) as year'), DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'), DB::raw('AVG(rating) as average'))
            ->where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Formatear los datos para gráficos
        $monthlyData = [];
        foreach ($ratingsByMonth as $monthData) {
            $date = Carbon::createFromDate($monthData->year, $monthData->month, 1);
            $monthlyData[] = [
                'month' => $date->format('M Y'),
                'count' => $monthData->count,
                'average' => round($monthData->average, 1),
                'percentage' => round(($monthData->average / 5) * 100)
            ];
        }
        
        return response()->json([
            'user' => $user,
            'total_ratings' => $totalRatings,
            'average_rating' => round($avgRating, 1),
            'rating_percentage' => round(($avgRating / 5) * 100),
            'rating_distribution' => $fullDistribution,
            'rating_percentages' => $percentages,
            'recent_ratings' => $recentRatings,
            'top_categories' => $topCategories,
            'monthly_stats' => $monthlyData
        ]);
    }

    /**
     * Obtener los productos mejor calificados
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopRatedProducts(Request $request)
    {
        $limit = $request->input('limit', 10);
        $minRatings = $request->input('min_ratings', 3);
        $categoryId = $request->input('categoria_id');
        
        $query = DB::table('ratings')
            ->join('productos', 'ratings.producto_id', '=', 'productos.id')
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.descripcion',
                'productos.precio',
                'productos.imagen_principal',
                'productos.categoria_id',
                DB::raw('COUNT(ratings.id) as total_ratings'),
                DB::raw('AVG(ratings.rating) as average_rating')
            )
            ->groupBy(
                'productos.id',
                'productos.nombre',
                'productos.descripcion',
                'productos.precio',
                'productos.imagen_principal',
                'productos.categoria_id'
            )
            ->having('total_ratings', '>=', $minRatings)
            ->orderBy('average_rating', 'desc')
            ->orderBy('total_ratings', 'desc');
        
        if ($categoryId) {
            $query->where('productos.categoria_id', $categoryId);
        }
        
        $topProducts = $query->limit($limit)->get();
        
        // Calcular porcentajes y redondear promedios
        foreach ($topProducts as $product) {
            $product->average_rating = round($product->average_rating, 1);
            $product->rating_percentage = round(($product->average_rating / 5) * 100);
            
            // Obtener la distribución de calificaciones para este producto
            $distribution = DB::table('ratings')
                ->where('producto_id', $product->id)
                ->select('rating', DB::raw('count(*) as count'))
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
            
            $product->rating_distribution = $fullDistribution;
            
            // Calcular porcentajes para cada nivel de calificación
            $percentages = [];
            foreach ($fullDistribution as $stars => $count) {
                $percentages["$stars"] = round(($count / $product->total_ratings) * 100);
            }
            
            $product->rating_percentages = $percentages;
        }
        
        return response()->json([
            'total' => count($topProducts),
            'products' => $topProducts
        ]);
    }

    /**
     * Obtener las calificaciones más recientes de todos los productos
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentRatings(Request $request)
    {
        $limit = $request->input('limit', 10);
        
        $recentRatings = DB::table('ratings')
            ->join('productos', 'ratings.producto_id', '=', 'productos.id')
            ->join('users', 'ratings.user_id', '=', 'users.id')
            ->select(
                'ratings.id',
                'ratings.producto_id',
                'ratings.user_id',
                'ratings.rating',
                'ratings.comment',
                'ratings.created_at',
                'productos.nombre as producto_nombre',
                'productos.imagen_principal',
                'productos.categoria_id',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('ratings.created_at', 'desc')
            ->limit($limit)
            ->get();
        
        // Formatear fechas y añadir información adicional
        foreach ($recentRatings as $rating) {
            $rating->created_at_formatted = Carbon::parse($rating->created_at)->diffForHumans();
            $rating->rating_percentage = round(($rating->rating / 5) * 100);
        }
        
        return response()->json([
            'total' => count($recentRatings),
            'ratings' => $recentRatings
        ]);
    }

    /**
     * Obtener estadísticas de calificación por período de tiempo
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRatingStatsByPeriod(Request $request)
    {
        $period = $request->input('period', 'month'); // day, week, month, year
        $limit = $request->input('limit', 12); // Número de períodos a devolver
        
        $dateColumn = 'created_at';
        $dateFormat = '';
        $dateInterval = '';
        
        switch ($period) {
            case 'day':
                $dateFormat = '%Y-%m-%d';
                $dateInterval = 'day';
                break;
            case 'week':
                $dateFormat = '%Y-%u'; // Año-Semana
                $dateInterval = 'week';
                break;
            case 'month':
                $dateFormat = '%Y-%m';
                $dateInterval = 'month';
                break;
            case 'year':
                $dateFormat = '%Y';
                $dateInterval = 'year';
                break;
            default:
                $dateFormat = '%Y-%m';
                $dateInterval = 'month';
        }
        
        // Obtener la fecha de inicio para el límite de períodos
        $startDate = Carbon::now()->sub($dateInterval, $limit);
        
        // Obtener estadísticas por período
        $stats = DB::table('ratings')
            ->select(
                DB::raw("DATE_FORMAT($dateColumn, '$dateFormat') as period"),
                DB::raw('COUNT(*) as total_ratings'),
                DB::raw('COUNT(DISTINCT user_id) as total_users'),
                DB::raw('AVG(rating) as average_rating')
            )
            ->where($dateColumn, '>=', $startDate)
            ->groupBy('period')
            ->orderBy('period')
            ->get();
        
        // Formatear los datos y calcular porcentajes
        foreach ($stats as $stat) {
            $stat->average_rating = round($stat->average_rating, 1);
            $stat->rating_percentage = round(($stat->average_rating / 5) * 100);
            
            // Formatear el período para mejor legibilidad
            if ($period === 'month') {
                list($year, $month) = explode('-', $stat->period);
                $date = Carbon::createFromDate($year, $month, 1);
                $stat->period_formatted = $date->format('M Y');
            } elseif ($period === 'week') {
                list($year, $week) = explode('-', $stat->period);
                $stat->period_formatted = "Semana $week, $year";
            } elseif ($period === 'day') {
                $stat->period_formatted = Carbon::parse($stat->period)->format('d M Y');
            } else {
                $stat->period_formatted = $stat->period;
            }
        }
        
        return response()->json([
            'period_type' => $period,
            'total_periods' => count($stats),
            'stats' => $stats
        ]);
    }
}