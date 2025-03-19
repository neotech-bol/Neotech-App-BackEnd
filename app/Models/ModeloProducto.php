<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModeloProducto extends Model
{
    use HasFactory;
    protected $table = 'modelo_productos'; // Asegúrate de que el nombre de la tabla sea correcto

    protected $fillable = [
        'producto_id',
        'nombre',
        'precio',
        'cantidad_minima',
        'cantidad_maxima',
        'precio_preventa',
        'cantidad_minima_preventa',
        'cantidad_maxima_preventa',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
