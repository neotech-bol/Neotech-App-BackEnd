<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $fillable = [
        "catalogo_id",
        "categoria_id",
        'nombre',
        'descripcion',
        'precio',
        'estado',
        'cantidad',
        'imagen_principal'
    ];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class, "catalogo_id");
    }
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, "categoria_id");
    }
    public function images()
    {
        return $this->hasMany(Image::class);
    }
    public function caracteristicas()
    {
        return $this->hasMany(Caracteristica::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }
}
