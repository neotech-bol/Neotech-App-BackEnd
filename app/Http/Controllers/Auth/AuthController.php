<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Buscar el usuario por email y cargar sus roles
        $user = User::where('email', $request->email)->with('roles')->first();
        // Verificar si el usuario existe y si la contraseña es correcta
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }
    
        // Generar un token de acceso
        $token = $user->createToken('auth_token')->plainTextToken;
    
        // Retornar respuesta
        return response()->json(['message' => 'Inicio de sesión exitoso', 'user' => $user, 'access_token' => $token, 'token_type' => 'Bearer'], 200);
    }
    public function register(Request $request)
    {
        // Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
            'apellido' => 'required|string|max:50',
            'ci' => 'required|string|max:15|unique:users',
            'nit' => 'required|string|unique:users',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'edad' => 'nullable|integer',
            'genero' => 'nullable|in:M,F,Otro',
            'email' => 'required|string|email|max:100|unique:users',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Crear el usuario
        $user = User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'ci' => $request->ci,
            'nit' => $request->nit,
            'direccion' => $request->direccion,
            'telefono' => $request->telefono,
            'edad' => $request->edad,
            'genero' => $request->genero,
            'email' => $request->email,
            'password' => Hash::make($request->ci), // Usar CI como contraseña
        ]);

        // Asignar rol de cliente
        $user->assignRole('cliente');

        $token = $user->createToken('auth_token')->plainTextToken;
        // Retornar respuesta
        return response()->json(['message' => 'Usuario registrado con éxito', 'user' => $user, 'access_token' => $token, 'token_type' => 'Bearer'], 201);
    }
    public function logout() {
        Auth::user()->tokens()->delete();
        return response()->json(['message'=> 'Sesion finalizada'], 200);
    }
}
