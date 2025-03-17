<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class rating extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'producto_id', 'rating', 'comment'];

    // If you want to append the rating_percentages dynamically
    protected $appends = ['rating_percentages'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    // Accessor for rating_percentages
    public function getRatingPercentagesAttribute()
    {
        // You can return a default value or calculate it based on your logic
        return []; // Return an empty array or your calculated percentages
    }
}