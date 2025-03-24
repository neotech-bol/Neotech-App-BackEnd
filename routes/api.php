<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\CatalogoHistorialController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ContactanosController;
use App\Http\Controllers\CuponController;
use App\Http\Controllers\favoriteController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoModelController;
use App\Http\Controllers\ProductosController;
use App\Http\Controllers\RatingController;
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
// Ruta de verificación de correo (debe ser accesible sin autenticación)
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verificarEmail'])
    ->middleware(['signed'])  // Solo necesita ser firmada, no autenticada
    ->name('verification.verify');

// Ruta para solicitar un nuevo enlace sin estar autenticado
Route::post('/email/generate-verification', [AuthController::class, 'generarEnlaceVerificacion'])
    ->middleware(['throttle:6,1'])
    ->name('verification.generate');

Route::group(["middleware" => "auth:sanctum"], function () {
    // Ruta para reenviar el correo de verificación cuando ya está autenticado
    Route::post('/email/verification-notification', [AuthController::class, 'enviarVerificacionEmail'])
        ->middleware(['throttle:6,1'])
        ->name('verification.send');
    Route::post("/logout", [AuthController::class, "logout"]);

    //Usuarios
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::post('/usuario-nuevo', [UserController::class, 'storeUserAdmin']);
    Route::get('/usuario/{id}', [UserController::class, 'show']);
    Route::put('/usuario/{id}', [UserController::class, 'update']);
    Route::delete('/usuario/{id}', [UserController::class, 'changeEstado']);
    Route::get('/usuarios/activos', [UserController::class, 'getUsuariosActivos']);
    Route::get('/usuarios/inactivos', [UserController::class, 'getUsuariosInactivos']);
    Route::put('/user/update-basic-info', [UserController::class, 'updateBasicInfo']);
    //
    Route::get('/usuarios/total', [UserController::class, 'totalUsuarios']); // Ruta para obtener el total de usuarios
    Route::get('/usuarios/total/activos', [UserController::class, 'totalUsuariosActivos']); // Ruta para obtener el total de usuarios activos
    Route::get('/usuarios/total/inactivos', [UserController::class, 'totalUsuariosInactivos']); // Ruta para obtener el total de usuarios inactivos

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

    //Historial de catalogos
    Route::get('catalogos/historiales', [CatalogoHistorialController::class, 'index']);
    Route::delete('/historiales/{id}', [CatalogoHistorialController::class, 'changeStatus']);
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
    Route::delete('/producto/{id}', [ProductosController::class, 'cambiarEstado']);
    //favorites
    Route::get('/favorites', [favoriteController::class, 'index']);
    Route::post('/favorite-nuevo', [favoriteController::class, 'store']);

    //Pedidos
    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::get('/pedido/{id}', [PedidoController::class, 'show']);
    Route::post('/pedido-nuevo', [PedidoController::class, 'store']);
    Route::put('/pedido/{id}', [PedidoController::class, 'update']);
    Route::get('/usuarios/{userId}/pedidos', [PedidoController::class, 'obtenerPedidosPorUsuario']);
    Route::post('/pedido-complementado/{id}', [PedidoController::class, 'pedidoCompletado']);
    //report pedidos
    Route::get('/pedidos/exportar', [PedidoController::class, 'exportarPedidos']);
    Route::get('/pedidos/{id}/pdf', [PedidoController::class, 'descargarPedidoPDF']);
    Route::post('/pedidos/{id}/repetir', [PedidoController::class, 'repetirPedido']);
    //PEDIDOS COUNT
    Route::get('/pedidos/total/en-proceso', [PedidoController::class, 'totalPedidosEnProceso']);
    Route::get('/pedidos/total/completados', [PedidoController::class, 'totalPedidosCompletados']);
    Route::get('/pedidos/total', [PedidoController::class, 'totalPedidos']);
    //Cupones
    Route::get('/cupones', [CuponController::class, 'index']); // Mostrar todos los cupones
    Route::get('/cupon/{id}', [CuponController::class, 'show']); // Mostrar un cupón específico
    Route::post('/cupon-nuevo', [CuponController::class, 'store']); // Crear un nuevo cupón
    Route::put('/cupon/{id}', [CuponController::class, 'update']); // Actualizar un cupón existente
    Route::delete('/cupon/{id}', [CuponController::class, 'destroy']); // Eliminar un cupón
    Route::post('/cupon-validar', [CuponController::class, 'validateCoupon']); // Validar un cupón
    // Rutas para calificaciones
    Route::prefix('productos/{productoId}/calificaciones')->group(function () {
        Route::post('/calificacion-nueva', [CalificacionController::class, 'store']); // Crear una nueva calificación
        Route::get('/caalificacion', [CalificacionController::class, 'index']); // Obtener todas las calificaciones de un producto
        Route::put('/calificacion/{id}', [CalificacionController::class, 'update']); // Actualizar una calificación existente
        Route::delete('/calificacion/{id}', [CalificacionController::class, 'destroy']); // Eliminar una calificación
    });
    //
    Route::post('/usuario/departamento', [UserController::class, 'updateDepartment']);

    //Contactanos
    Route::get('/contacto', [ContactanosController::class, 'index']);
    Route::delete('/contacto/{id}', [ContactanosController::class, 'destroy']);
    Route::get('/contactos-total', [ContactanosController::class, 'countContactanos']);

    //
    Route::put('/usuario-edit', [UserController::class, 'updateAuthenticatedUser']); // Actualizar una calificación existente
    //permisos user
    Route::get('/usuario-permiso', [UserController::class, 'obtenerPermisos']);

    //Modelos Productos all
    Route::get('/modelos-productos', [ProductoModelController::class, 'index']);

    //pedidos reportes
// Rutas para generación de PDFs
Route::get('/pedidos/pdf/completados', [PedidoController::class, 'generarPdfPedidosCompletados'])->name('pedidos.pdf.completados');
Route::get('/pedidos/pdf/en-proceso', [PedidoController::class, 'generarPdfPedidosEnProceso'])->name('pedidos.pdf.en-proceso');
});

//Catalogos activos
Route::get('/catalogos-activos', [CatalogoController::class, 'indexActivos']);
Route::get('/catalogos-con-categorias', [CatalogoController::class, 'indexCatalogosConCategorias']);
Route::get('/categorias-acticas-users', [CategoriaController::class, 'search']);
Route::get('/producto-ver/{id}', [ProductosController::class, 'showProductoUser']);
Route::get('/productos-recientes', [ProductosController::class, 'productosRecientes']);
Route::get('/categorias-activas-home', [CategoriaController::class, 'indexActivos']);
Route::get('/categorias-activas-ids', [CategoriaController::class, 'getActiveCategorias']);
Route::get('/categoria/{id}', [CategoriaController::class, 'getCategoriaActiveById']);
Route::get('/productos/filtrar', [ProductosController::class, 'filtrarProductos']);
Route::get('/historiales-activos', [CatalogoHistorialController::class, 'indexActivos']);
Route::get('/historiales-activos-ids', [CatalogoHistorialController::class, 'getActiveHistorials']);
Route::get('/historiales/{id}', [CatalogoHistorialController::class, 'show']);
//contactanos
Route::post('/contacto-nuevo', [ContactanosController::class, 'store']);
Route::get('/catalogos-activos-ids', [CatalogoController::class, 'getActiveCatalogos']);
Route::get('/catalogo-activo/{id}', [CatalogoController::class, 'showCatalogoActive']);







//user autenticado 
Route::get('/usuario-autenticado', [UserController::class, 'getAuthenticatedUser']);


// Rutas existentes
Route::post('/ratings', [RatingController::class, 'store']);
Route::put('/ratings/{id}', [RatingController::class, 'update']);
Route::get('/ratings', [RatingController::class, 'index']);

// Nueva ruta para estadísticas de calificación por producto
Route::get('/products/{productoId}/ratings/stats', [RatingController::class, 'getProductRatingStats']);

// Ruta alternativa si prefieres mantener todo bajo el prefijo 'ratings'
Route::get('/ratings/product/{productoId}/stats', [RatingController::class, 'getProductRatingStats']);