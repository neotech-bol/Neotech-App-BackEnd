<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $fillable = [];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class, "catalogo_id");
    }
    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
