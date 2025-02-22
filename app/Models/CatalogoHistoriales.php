<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatalogoHistoriales extends Model
{
    use HasFactory;
    protected $fillable = [
        'catalogo_id',
        'nombre',
        'descripcion',
        'banner',
        'orden',
        'estado',
    ];

    public function catalogo()
    {
        return $this->belongsTo(Catalogo::class);
    }
}
