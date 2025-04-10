<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;
    protected $fillable = [
        "nombre",
        "titulo",
        "subtitulo",
        "descripcion",
        "estado",
        'catalogo_id', // Relación con el catálogo
    ];
    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class);
    }

    public function productos()
    {
        return $this->hasMany(Producto::class);
    }
}
