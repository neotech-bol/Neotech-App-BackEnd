<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\VerificarCorreoElectronico;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'apellido',
        'ci',
        'nit',
        'direccion',
        'telefono',
        'fecha_de_nacimiento',
        'genero',
        'email',
        'password',
        'email_verified_at',
        'departamento',
        'pais',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
        /**
     * Enviar la notificación de verificación de correo electrónico en español.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerificarCorreoElectronico);
    }
}
