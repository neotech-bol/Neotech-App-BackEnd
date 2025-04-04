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
}