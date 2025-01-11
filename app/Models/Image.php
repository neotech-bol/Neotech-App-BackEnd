<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;
    protected $fillable = ['producto_id', 'imagen'];

    public function product()
    {
        return $this->belongsTo(Producto::class, "producto_id");
    }
}
