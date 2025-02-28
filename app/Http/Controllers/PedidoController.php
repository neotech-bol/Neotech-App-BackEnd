<?php

namespace App\Http\Controllers;

use App\Exports\PedidosExport;
use App\Models\Cupones;
use App\Models\Pedido;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PedidoController extends Controller
{
    // Mostrar todos los pedidos
    public function index()
    {
        $pedidos = Pedido::with('user', 'productos', 'cupon')->get(); // Carga los pedidos con usuarios y productos
        return response()->json(["message" => "Pedidos cargados", "datos" => $pedidos]);
    }

    // Mostrar un pedido específico
    public function show($id)
    {
        $pedido = Pedido::with('user', 'productos')->findOrFail($id); // Carga el pedido con usuario y productos
        return response()->json($pedido);
    }

    // Crear un nuevo pedido
    public function store(Request $request)
    {
        $request->validate([
            'total_amount' => 'required|numeric',
            'total_to_pay' => 'nullable|numeric',
            'pending' => 'nullable|numeric',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'cupon_id' => 'nullable|exists:cupones,id', // Validar el cupon_id
            'payment_method' => 'required|string', // Validar el método de pago
            'voucher' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // Validar el comprobante (voucher)
        ]);
        // Obtener el usuario autenticado
        $user = auth()->user();
        // Crear un nuevo pedido
        $pedido = new Pedido();
        $pedido->user_id = $user->id; // Asignar el ID del usuario autenticado
        $pedido->total_amount = $request->total_amount;
        $pedido->total_to_pay = $request->total_to_pay;
        $pedido->pending = $request->pending;
        $pedido->cupon_id = $request->cupon_id;
        $pedido->payment_method = $request->payment_method; // Asignar el método de pago

        // Manejar el archivo del comprobante
        if ($request->file('voucher')) {
            // Eliminar el comprobante anterior si es necesario
            if ($pedido->voucher) {
                Storage::delete("vouchers/" . $pedido->voucher);
            }

            $voucher = $request->file('voucher');
            $nombreVoucher = md5_file($voucher->getPathname()) . '.' . $voucher->getClientOriginalExtension();
            $voucher->move("vouchers/", $nombreVoucher); // Mover el archivo a la carpeta 'vouchers'
            $pedido->voucher = $nombreVoucher; // Guardar el nombre del archivo en el modelo
        }
        $pedido->save(); // Guardar el pedido en la base de datos
        // Asociar productos al pedido
        foreach ($request->productos as $producto) {
            $pedido->productos()->attach($producto['id'], ['cantidad' => $producto['cantidad']]);
        }

        return response()->json($pedido, 201);
    }

    // Actualizar un pedido existente
    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $request->validate([
            'user_id' => 'sometimes|required|exists:users,id',
            'monto_total' => 'sometimes|required|numeric',
            'productos' => 'sometimes|array',
            'productos.*.id' => 'sometimes|required|exists:productos,id',
            'productos.*.cantidad' => 'sometimes|required|integer|min:1',
        ]);

        $pedido->update($request->only('user_id', 'monto_total'));

        // Actualizar productos del pedido
        if ($request->has('productos')) {
            $pedido->productos()->detach(); // Eliminar productos existentes
            foreach ($request->productos as $producto) {
                $pedido->productos()->attach($producto['id'], ['cantidad' => $producto['cantidad']]);
            }
        }

        return response()->json($pedido);
    }

    // Marcar un pedido como completado
    public function pedidoCompletado($id)
    {
        $pedido = Pedido::findOrFail($id);

        // Verificar si el pedido ya está completado
        if ($pedido->estado) {
            return response()->json(['message' => 'El pedido ya está completado.'], 400); // O cualquier otro código de estado que consideres apropiado
        }

        // Cambiar el estado del pedido a completado
        $pedido->estado = true; // Asumimos que 'true' significa completado
        $pedido->save(); // Guardar los cambios en la base de datos

        return response()->json(['message' => 'Pedido completado con éxito.'], 200);
    }
    // Exportar pedidos a Excel
    public function exportarPedidos()
    {
        return Excel::download(new PedidosExport, 'pedidos.xlsx');
    }
    // Descargar un PDF con los detalles del pedido
    public function descargarPedidoPDF($id)
    {
        // Obtener el pedido con sus detalles
        $pedido = Pedido::with(['user', 'productos'])->findOrFail($id);

        // Generar el PDF
        $pdf = Pdf::loadView('pedidos.pdf', compact('pedido'));

        // Descargar el PDF
        return $pdf->download('pedido_' . $pedido->id . '.pdf');
    }
    // Repetir un pedido existente
    public function repetirPedido($id)
    {
        // Obtener el pedido original con sus detalles
        $pedidoOriginal = Pedido::with(['productos'])->findOrFail($id);

        // Obtener el usuario autenticado
        $user = auth()->user();

        // Crear un nuevo pedido
        $nuevoPedido = new Pedido();
        $nuevoPedido->user_id = $user->id; // Asignar el ID del usuario autenticado
        $nuevoPedido->total_amount = $pedidoOriginal->total_amount; // Copiar el monto total
        $nuevoPedido->total_to_pay = $pedidoOriginal->total_to_pay; // Copiar el monto a pagar
        $nuevoPedido->pending = $pedidoOriginal->pending; // Copiar el monto pendiente
        $nuevoPedido->cupon_id = $pedidoOriginal->cupon_id; // Copiar el cupon_id
        $nuevoPedido->estado = 0; // Asumimos que el nuevo pedido está pendiente
        $nuevoPedido->save(); // Guardar el nuevo pedido en la base de datos

        // Asociar los productos del pedido original al nuevo pedido
        foreach ($pedidoOriginal->productos as $producto) {
            $nuevoPedido->productos()->attach($producto->id, ['cantidad' => $producto->pivot->cantidad]);
        }

        return response()->json(['message' => 'Pedido repetido con éxito.', 'nuevo_pedido' => $nuevoPedido], 201);
    }
    // Obtener el total de pedidos en proceso
    public function totalPedidosEnProceso()
    {
        $totalEnProceso = Pedido::where('estado', false)->count(); // Asumiendo que 'estado' es false para pedidos en proceso
        return response()->json(['total_pedidos_en_proceso' => $totalEnProceso], 200);
    }

    // Obtener el total de pedidos completados
    public function totalPedidosCompletados()
    {
        $totalCompletados = Pedido::where('estado', true)->count(); // Asumiendo que 'estado' es true para pedidos completados
        return response()->json(['total_pedidos_completados' => $totalCompletados], 200);
    }

    // Obtener el total general de pedidos
    public function totalPedidos()
    {
        $totalPedidos = Pedido::count(); // Contar todos los pedidos
        return response()->json(['total_pedidos' => $totalPedidos], 200);
    }
}
