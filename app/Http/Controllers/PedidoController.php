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
    public function index()
    {
        $pedidos = Pedido::with('user', 'productos', 'cupon')->get();
        return response()->json(["message" => "Pedidos cargados", "datos" => $pedidos]);
    }

    public function show($id)
    {
        $pedido = Pedido::with(['user', 'productos.modelos', 'cupon'])->findOrFail($id);

        if ($pedido->voucher) {
            $pedido->voucher = asset('vouchers/' . $pedido->voucher);
        }

        $pedido->productos = $pedido->productos->map(function ($producto) {
            $pivotData = $producto->pivot;

            return [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio_base' => $producto->precio,
                'cantidad' => $pivotData->cantidad,
                'modelo_id' => $pivotData->modelo_id,
                'precio_compra' => $pivotData->precio, // Precio al momento de la compra
                'color' => $pivotData->color,
                'subtotal' => $pivotData->precio * $pivotData->cantidad
            ];
        });

        return response()->json($pedido);
    }

    public function store(Request $request)
    {
        $request->validate([
            'total_amount' => 'required|numeric',
            'total_to_pay' => 'nullable|numeric',
            'pending' => 'nullable|numeric',
            'productos' => 'required|array',
            'productos.*.id' => 'required|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.modelo_id' => 'nullable|exists:modelo_productos,id',
            'productos.*.precio' => 'required|numeric',
            'productos.*.color' => 'nullable|string',
            'cupon_id' => 'nullable|exists:cupones,id',
            'payment_method' => 'required|string',
            'voucher' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = auth()->user();
        $pedido = new Pedido();
        $pedido->user_id = $user->id;
        $pedido->total_amount = $request->total_amount;
        $pedido->total_to_pay = $request->total_to_pay;
        $pedido->pending = $request->pending;
        $pedido->cupon_id = $request->cupon_id;
        $pedido->payment_method = $request->payment_method;

        if ($request->file('voucher')) {
            if ($pedido->voucher) {
                Storage::delete("vouchers/" . $pedido->voucher);
            }

            $voucher = $request->file('voucher');
            $nombreVoucher = md5_file($voucher->getPathname()) . '.' . $voucher->getClientOriginalExtension();
            $voucher->move("vouchers/", $nombreVoucher);
            $pedido->voucher = $nombreVoucher;
        }
        $pedido->save();

        foreach ($request->productos as $producto) {
            $pedido->productos()->attach($producto['id'], [
                'cantidad' => $producto['cantidad'],
                'modelo_id' => $producto['modelo_id'] ?? null,
                'precio' => $producto['precio'],
                'color' => $producto['color'] ?? null
            ]);
        }

        return response()->json($pedido, 201);
    }

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

        if ($request->has('productos')) {
            $pedido->productos()->detach();
            foreach ($request->productos as $producto) {
                $pedido->productos()->attach($producto['id'], ['cantidad' => $producto['cantidad']]);
            }
        }

        return response()->json($pedido);
    }

    public function pedidoCompletado($id)
    {
        $pedido = Pedido::findOrFail($id);

        if ($pedido->estado) {
            return response()->json(['message' => 'El pedido ya está completado.'], 400);
        }

        $pedido->estado = true;
        $pedido->save();

        return response()->json(['message' => 'Pedido completado con éxito.'], 200);
    }

    public function exportarPedidos()
    {
        return Excel::download(new PedidosExport, 'pedidos.xlsx');
    }

    public function descargarPedidoPDF($id, Request $request)
    {
        try {
            $pedido = Pedido::with(['user', 'productos', 'cupon'])->findOrFail($id);

            $pdf = Pdf::loadView('pedidos.pdf', compact('pedido'));

            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

            $responseFormat = $request->query('format', 'binary');

            switch ($responseFormat) {
                case 'base64':
                    $base64 = base64_encode($pdf->output());

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'pedido_id' => $pedido->id,
                            'filename' => 'pedido_' . $pedido->id . '.pdf',
                            'content_type' => 'application/pdf',
                            'base64' => $base64,
                        ],
                        'message' => 'PDF generado exitosamente'
                    ]);

                case 'url':
                    $filename = 'pedidos/pedido_' . $pedido->id . '_' . time() . '.pdf';
                    Storage::disk('public')->put($filename, $pdf->output());

                    $url = Storage::disk('public')->url($filename);

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'pedido_id' => $pedido->id,
                            'filename' => 'pedido_' . $pedido->id . '.pdf',
                            'url' => $url,
                            'expires_at' => now()->addDay()->toIso8601String(),
                        ],
                        'message' => 'URL de descarga generada exitosamente'
                    ]);

                case 'binary':
                default:
                    return $pdf->download('pedido_' . $pedido->id . '.pdf');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    public function repetirPedido($id)
    {
        $pedidoOriginal = Pedido::with(['productos'])->findOrFail($id);

        $user = auth()->user();

        $nuevoPedido = new Pedido();
        $nuevoPedido->user_id = $user->id;
        $nuevoPedido->total_amount = $pedidoOriginal->total_amount;
        $nuevoPedido->total_to_pay = $pedidoOriginal->total_to_pay;
        $nuevoPedido->pending = $pedidoOriginal->pending;
        $nuevoPedido->cupon_id = $pedidoOriginal->cupon_id;
        $nuevoPedido->estado = 0;
        $nuevoPedido->save();

        foreach ($pedidoOriginal->productos as $producto) {
            $nuevoPedido->productos()->attach($producto->id, ['cantidad' => $producto->pivot->cantidad]);
        }

        return response()->json(['message' => 'Pedido repetido con éxito.', 'nuevo_pedido' => $nuevoPedido], 201);
    }

    public function totalPedidosEnProceso()
    {
        $totalEnProceso = Pedido::where('estado', false)->count();
        return response()->json(['total_pedidos_en_proceso' => $totalEnProceso], 200);
    }

    public function totalPedidosCompletados()
    {
        $totalCompletados = Pedido::where('estado', true)->count();
        return response()->json(['total_pedidos_completados' => $totalCompletados], 200);
    }

    public function totalPedidos()
    {
        $totalPedidos = Pedido::count();
        return response()->json(['total_pedidos' => $totalPedidos], 200);
    }
}
