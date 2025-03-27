<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!Auth::check()) {
            return response()->json(['mensaje' => 'No autenticado'], 401);
        }

        $user = Auth::user();
        
        // Si no se especifican permisos, permitir acceso
        if (empty($permissions)) {
            return $next($request);
        }
        
        // Verificar si el usuario tiene todos los permisos especificados
        foreach ($permissions as $permission) {
            if (!$user->hasPermissionTo($permission)) {
                return response()->json(['mensaje' => 'No tienes los permisos necesarios para acceder a este recurso'], 403);
            }
        }
        
        return $next($request);
    }
}