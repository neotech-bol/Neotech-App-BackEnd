<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class rating extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'producto_id', 'rating', 'comment'];

    protected $appends = ['rating_percentages'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function getRatingPercentagesAttribute()
    {
        // Fetch distribution for this specific producto_id
        $distribution = self::select('rating', DB::raw('count(*) as count'))
            ->where('producto_id', $this->producto_id)
            ->groupBy('rating')
            ->pluck('count', 'rating')
            ->toArray();

        $total = array_sum($distribution);
        $percentages = [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0
        ];

        foreach ($distribution as $rating => $count) {
            $percentages[$rating] = $total > 0 ? round(($count / $total) * 100) : 0;
        }

        return $percentages;
    }
}