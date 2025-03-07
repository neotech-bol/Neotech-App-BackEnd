<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id', 
        'total_amount', 
        'total_to_pay', 
        'pending', 
        'cupon_id', 
        'payment_method', 
        'voucher'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'pedido_producto')
                    ->withPivot('cantidad', 'modelo_id', 'precio', 'color') 
                    ->withTimestamps();
    }
    
    public function cupon()
    {
        return $this->belongsTo(Cupones::class);
    }
}