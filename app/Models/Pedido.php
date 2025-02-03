<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'monto_total'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function productos()
    {
        return $this->belongsToMany(Producto::class)->withPivot('cantidad');
    }
}
