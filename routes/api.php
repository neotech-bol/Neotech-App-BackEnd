<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CatalogoController;
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
    //Desvincular productos del catalogo
    Route::post('/catalogos/{catalogoId}/productos/{productoId}/detach', [CatalogoController::class, 'detachProduct']);

    //productos
    Route::get('/productos', [ProductosController::class,'index']);
    Route::post('/producto-nuevo', [ProductosController::class,'store']);
    Route::get('/producto/{id}', [ProductosController::class,'show']);
    Route::put('/producto/{id}', [ProductosController::class,'update']);
});
