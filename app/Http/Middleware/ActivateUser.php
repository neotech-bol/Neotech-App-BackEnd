<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ActivateUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Verificar si el usuario está autenticado
        if (Auth::check()) {
            $user = Auth::user();
            // Si el usuario está inactivo, activarlo
            if (!$user->estado) {
                $user->estado = true; // Cambiar el estado a activo
                $user->save(); // Guardar los cambios
            }
        }

        return $next($request);
    }
}
