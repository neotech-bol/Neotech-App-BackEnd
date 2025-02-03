<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\User;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $item = Pedido::with('user', 'productos')->paginate(10);
        return response()->json(["mensaje" => "Pedidos cargados correctamente", "datos" => $item], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
{
    // Validar los datos de entrada
    $request->validate([
        "user_id" => "required|exists:users,id",
        "monto_total" => "required|numeric",
        "productos" => "required|array",
    ]);

    // Crear una nueva instancia de Pedido
    $item = new Pedido();
    $item->user_id = $request->user_id;
    $item->monto_total = $request->monto_total;
    
    // Guardar el pedido primero
    $item->save();

    // Asociar los productos al pedido
    // Asegúrate de que $request->productos contenga un array de IDs de productos
    $item->productos()->attach($request->productos);

    // Retornar la respuesta en formato JSON
    return response()->json([
        "mensaje" => "Pedido guardado",
        "dato" => $item,
    ], 201); // Cambia a 201 para indicar que se ha creado un recurso
}

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $item = Pedido::with('user', "productos")->find($id);
        return response()->json(["mensaje"=> "Registro encontrado","dato"=> $item], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            "user_id"=> "required|exists:users,id",
            "monto_total"=> "required|numeric",
            "productos"=> "required|array",
        ]);
        $item = Pedido::find($id);
        $item->user_id = $request->user_id;
        $item->monto_total = $request->monto_total;
        $item->productos()->sync($request->productos);
        $item->save();
        return response()->json(["mensaje"=> "Pedido actualizado", "dato"=> $item], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
      // Método para obtener todos los pedidos de un usuario
      public function obtenerPedidosPorUsuario($userId)
      {
          // Obtener un usuario por su ID
          $user = User::find($userId);
  
          // Verificar si el usuario existe
          if (!$user) {
              return response()->json(['mensaje' => 'Usuario no encontrado'], 404);
          }
  
          // Obtener todos los pedidos del usuario
          $pedidos = $user->pedidos;
  
          // Retornar los pedidos en formato JSON
          return response()->json([
              'usuario' => $user,
              'pedidos' => $pedidos,
          ]);
      }
}
