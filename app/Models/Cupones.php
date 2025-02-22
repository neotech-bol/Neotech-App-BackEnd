<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cupones extends Model
{
    use HasFactory;
    protected $fillable = [
        'codigo',
        'descuento',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'activo',
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}
