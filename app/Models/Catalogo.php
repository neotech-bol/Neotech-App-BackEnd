<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    use HasFactory;
    protected $fillable = [
        "nombre",
        "descripcion",
        'banner',
        "estado",
    ];
    public function categorias()
    {
        return $this->hasMany(Categoria::class);
    }
    public function productos()
    {
        return $this->hasManyThrough(Producto::class, Categoria::class);
    }
}
