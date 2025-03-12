<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

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
            return response()->json(['message' => 'Credenciales incorrectas'], 404);
        }

        // Verificar si el correo está verificado
   /*      if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Por favor, verifica tu correo electrónico antes de iniciar sesión',
                'email_verified' => false
            ], 403);
        } */

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
            'direccion' => 'required|string',
            'telefono' => 'required|string|max:20',
            'fecha_de_nacimiento' => 'required|date',
            'genero' => 'required|in:M,F,Otro',
            'email' => 'required|string|email|max:100|unique:users',
            'pais' => 'required|string'
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
            'fecha_de_nacimiento' => $request->fecha_de_nacimiento,
            'genero' => $request->genero,
            'email' => $request->email,
            'pais' => $request->pais,
            'password' => Hash::make($request->ci), // Usar CI como contraseña
        ]);

        // Asignar rol de cliente
        $user->assignRole('cliente');

        // Cargar los roles del usuario
        $user->load('roles');

        // Generar un token de acceso
        $token = $user->createToken('auth_token')->plainTextToken;

        // Enviar correo de verificación
       /*  event(new Registered($user)); */

        // Retornar respuesta
        return response()->json([
            'message' => 'Usuario registrado con éxito. Por favor, verifica tu correo electrónico.',
            'user' => $user,
            'roles' => $user->roles, // Incluir los roles en la respuesta
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();
        return response()->json(['message' => 'Sesión finalizada'], 200);
    }

    /**
     * Enviar correo de verificación
     */
    public function enviarVerificacionEmail(Request $request)
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo electrónico ya ha sido verificado'], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Enlace de verificación enviado a tu correo electrónico'], 200);
    }
    /**
     * Verificar correo electrónico
     */
    public function verificarEmail(Request $request)
    {
        $userId = $request->route('id');
        $user = User::findOrFail($userId);

        if (!hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Enlace de verificación inválido'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo electrónico ya ha sido verificado'], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Correo electrónico verificado con éxito'], 200);
    }
    /**
     * Generar enlace de verificación para un usuario no autenticado
     */
    public function generarEnlaceVerificacion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'El correo electrónico ya ha sido verificado'], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Enlace de verificación enviado a tu correo electrónico'], 200);
    }
}
