<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleAndPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $roles = '', $permissions = ''): Response
    {
        if (!Auth::check()) {
            return response()->json(['mensaje' => 'No autenticado'], 401);
        }

        $user = Auth::user();
        
        // Parse roles and permissions from parameters
        $roleArray = $roles ? explode(',', $roles) : [];
        $permissionArray = $permissions ? explode(',', $permissions) : [];
        
        // Check roles if specified
        $hasRole = empty($roleArray);
        if (!empty($roleArray)) {
            foreach ($roleArray as $role) {
                if ($user->hasRole($role)) {
                    $hasRole = true;
                    break;
                }
            }
        }
        
        // Check permissions if specified
        $hasPermission = empty($permissionArray);
        if (!empty($permissionArray)) {
            foreach ($permissionArray as $permission) {
                if (!$user->hasPermissionTo($permission)) {
                    $hasPermission = false;
                    break;
                }
            }
        }
        
        // If user has required role and permission, proceed
        if ($hasRole && $hasPermission) {
            return $next($request);
        }
        
        return response()->json(['mensaje' => 'No tienes los permisos necesarios para acceder a este recurso'], 403);
    }
}

