<?php

namespace App\Http\Controllers;

use App\Models\Cupones;
use Illuminate\Http\Request;

class CuponController extends Controller
{
    // Mostrar todos los cupones
    public function index()
    {
        $cupones = Cupones::all();
        return response()->json(["message" => "Cupones cargados", "datos" => $cupones]);
    }

    // Mostrar un cupón específico
    public function show($id)
    {
        $cupon = Cupones::findOrFail($id);
        return response()->json($cupon);
    }

    // Crear un nuevo cupón
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|unique:cupones',
            'descuento' => 'required|numeric',
            'tipo' => 'required|string|in:porcentaje,fijo',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'activo' => 'boolean',
        ]);

        $cupon = Cupones::create($request->all());
        return response()->json($cupon, 201);
    }

    // Actualizar un cupón existente
    public function update(Request $request, $id)
    {
        $cupon = Cupones::findOrFail($id);

        $request->validate([
            'codigo' => 'sometimes|required|string|unique:cupones,codigo,' . $cupon->id,
            'descuento' => 'sometimes|required|numeric',
            'tipo' => 'sometimes|required|string|in:porcentaje,fijo',
            'fecha_inicio' => 'sometimes|required|date',
            'fecha_fin' => 'sometimes|required|date|after:fecha_inicio',
            'activo' => 'sometimes|boolean',
        ]);

        $cupon->update($request->all());
        return response()->json($cupon);
    }

    // Eliminar un cupón
    public function destroy($id)
    {
        $cupon = Cupones::findOrFail($id);
        $cupon->delete();
        return response()->json(null, 204);
    }
    // Agrega este método en tu CuponController
public function validateCoupon(Request $request)
{
    $request->validate([
        'codigo' => 'required|string',
    ]);

    $cupon = Cupones::where('codigo', $request->codigo)
        ->where('activo', true)
        ->where('fecha_inicio', '<=', now())
        ->where('fecha_fin', '>=', now())
        ->first();

    if ($cupon) {
        return response()->json(['success' => true, 'cupon' => $cupon]);
    }

    return response()->json(['success' => false, 'message' => 'Cupón no válido o expirado.'], 400);
}
}
