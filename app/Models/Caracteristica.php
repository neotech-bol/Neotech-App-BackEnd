<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Caracteristica extends Model
{
    use HasFactory;
    protected $fillable = [
        "producto_id",
        "caracteristica"
    ];
    public function product()
    {
        return $this->belongsTo(Producto::class, "producto_id");
    }
}
