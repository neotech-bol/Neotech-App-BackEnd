<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\RolesPermisosController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


Route::group(["middleware" => "auth:sanctum"], function () {
    Route::get('email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('email/verification-notification', [VerificationController::class, 'resend'])->name('verification.send');

    Route::post("/logout", [AuthController::class, "logout"]);

    //Usuarios
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::post('/usuario-nuevo', [UserController::class, 'storeUserAdmin']);
    Route::get('/usuario/{id}', [UserController::class, 'show']);
    Route::put('/usuario/{id}', [UserController::class, 'update']);
    Route::delete('/usuario/{id}', [UserController::class,'changeEstado']);
    Route::get('/usuario-autenticado', [UserController::class, 'getAuthenticatedUser']);
    //Roles y permisos
    Route::get('/permisos', [RolesPermisosController::class, 'indexPermissions']);
    Route::get('/roles', [RolesPermisosController::class, 'indexRoles']);
    Route::post('/rol-nuevo', [RolesPermisosController::class, 'store']);
    Route::get('/roles/{id}/permisos', [RolesPermisosController::class, 'showPermissions']);
    Route::post('/roles/{id}/permisos', [RolesPermisosController::class, 'assignPermissionsToRole']);
    Route::put('/roles/{id}', [RolesPermisosController::class, 'update']);
    Route::delete('/rol/{id}', [RolesPermisosController::class, 'destroyRole']);

    //catalogos
    Route::get('/catalogos', [CatalogoController::class, 'index']);
    Route::post('/catalogo-nuevo', [CatalogoController::class, 'store']);
    Route::get('/catalogo/{id}', [CatalogoController::class, 'show']);
    Route::put('/catalogo/{id}', [CatalogoController::class, 'update']);
    Route::delete('/catalogo/{id}', [CatalogoController::class, 'destroy']);

    //categorias
    Route::get('/categorias', [CategoriaController::class, 'index']);
    Route::post('/categoria-nueva', [CategoriaController::class, 'store']);
    Route::get('/categoria/{id}', [CategoriaController::class, 'show']);
    Route::put('/categoria/{id}', [CategoriaController::class, 'update']);
    Route::delete('/categoria/{id}', [CategoriaController::class, 'destroy']);
    Route::get('/categorias-activas', [CategoriaController::class, 'indexActivos']);

    //productos
    Route::get('/productos', [ProductosController::class, 'index']);
    Route::post('/producto-nuevo', [ProductosController::class, 'store']);
    Route::get('/producto/{id}', [ProductosController::class, 'show']);
    Route::put('/producto/{id}', [ProductosController::class, 'update']);
    Route::delete('productos/{productoId}/images/{imagenId}', [ProductosController::class, 'destroyImage']);

    //Pedidos
    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::get('/pedido/{id}', [PedidoController::class, 'show']);
    Route::post('/pedido-nuevo', [PedidoController::class, 'store']);
    Route::put('/pedido/{id}', [PedidoController::class, 'update']);
    Route::get('/usuarios/{userId}/pedidos', [PedidoController::class, 'obtenerPedidosPorUsuario']);


     // Rutas para calificaciones
     Route::prefix('productos/{productoId}/calificaciones')->group(function () {
        Route::post('/calificacion-nueva', [CalificacionController::class, 'store']); // Crear una nueva calificación
        Route::get('/caalificacion', [CalificacionController::class, 'index']); // Obtener todas las calificaciones de un producto
        Route::put('/calificacion/{id}', [CalificacionController::class, 'update']); // Actualizar una calificación existente
        Route::delete('/calificacion/{id}', [CalificacionController::class, 'destroy']); // Eliminar una calificación
    });
});

//Catalogos activos
Route::get('/catalogos-activos', [CatalogoController::class, 'indexActivos']);

Route::get('/producto-ver/{id}', [ProductosController::class, 'showProductoUser']);