<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $fillable = [
        "categoria_id",
        'nombre',
        'descripcion',
        'precio',
        'estado',
        'cantidad',
        'imagen_principal'
    ];

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
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pedido_producto')
                    ->withPivot('cantidad') // Si deseas acceder a la cantidad
                    ->withTimestamps();
    }
    public function modelos()
    {
        return $this->hasMany(ModeloProducto::class);
    }
}
