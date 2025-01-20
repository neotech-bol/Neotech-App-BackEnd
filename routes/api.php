<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CategoriaController;
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

    Route::post("/logout", [AuthController::class,"logout"]);
    Route::get('/usuarios', [UserController::class, 'index']);

    //Roles y permisos
    Route::get('/roles', [RolesPermisosController::class,'indexRoles']);
    Route::post('/rol-nuevo', [RolesPermisosController::class, 'store']);
    Route::delete('/rol/{id}', [RolesPermisosController::class, 'destroyRole']);

    //catalogos
    Route::get('/catalogos', [CatalogoController::class, 'index']);
    Route::post('/catalogo-nuevo', [CatalogoController::class,'store']);
    Route::get('/catalogo/{id}', [CatalogoController::class,'show']);
    Route::put('/catalogo/{id}', [CatalogoController::class,'update']);
    Route::delete('/catalogo/{id}', [CatalogoController::class,'destroy']);
    Route::get('/catalogos-activos', [CatalogoController::class,'indexActivos']);

    //categorias
    Route::get('/categorias', [CategoriaController::class,'index']);
    Route::post('/categoria-nueva', [CategoriaController::class,'store']);
    Route::get('/categoria/{id}', [CategoriaController::class,'show']);
    Route::put('/categoria/{id}', [CategoriaController::class,'update']);
    Route::delete('/categoria/{id}', [CategoriaController::class,'destroy']);
    Route::get('/categorias-activas', [CategoriaController::class,'indexActivos']);



    //productos
    Route::get('/productos', [ProductosController::class,'index']);
    Route::post('/producto-nuevo', [ProductosController::class,'store']);
    Route::get('/producto/{id}', [ProductosController::class,'show']);
    Route::put('/producto/{id}', [ProductosController::class,'update']);
    Route::delete('productos/{productoId}/images/{imagenId}', [ProductosController::class, 'destroyImage']);
});
