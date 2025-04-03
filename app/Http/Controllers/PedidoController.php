<?php

namespace App\Http\Controllers;

use App\Exports\PedidosExport;
use App\Exports\PedidosPorCatalogoExport;
use App\Models\Catalogo;
use App\Models\Cupones;
use App\Models\Pedido;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Str;
class PedidoController extends Controller
{
    public function index(Request $request)
    {
        // Start with a base query
        $query = Pedido::with('user', 'productos', 'cupon');

        // Filter by search term if provided
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->whereHas('user', function ($q) use ($searchTerm) {
                $q->where('nombre', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('apellido', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('email', 'LIKE', "%{$searchTerm}%");
            })->orWhere('id', 'LIKE', "%{$searchTerm}%");
        }

        // Filter by status if provided
        if ($request->has('status') && $request->status != 'all') {
            $isPending = $request->status === 'pending';
            $query->where('estado', !$isPending);
        }

        // Sort results if sort parameters are provided
        if ($request->has('sort_field') && $request->has('sort_direction')) {
            $sortField = $request->sort_field;
            $sortDirection = $request->sort_direction;

            // Handle different sort fields
            switch ($sortField) {
                case 'id':
                    $query->orderBy('id', $sortDirection);
                    break;
                case 'amount':
                    $query->orderBy('total_amount', $sortDirection);
                    break;
                case 'created_at':
                default:
                    $query->orderBy('created_at', $sortDirection);
                    break;
            }
        } else {
            // Default sorting by most recent
            $query->orderBy('created_at', 'desc');
        }

        // Paginate the results (10 items per page)
        $pedidos = $query->paginate(10);

        return response()->json([
            "message" => "Pedidos cargados",
            "datos" => $pedidos->items(),
            "pagination" => [
                "total" => $pedidos->total(),
                "current_page" => $pedidos->currentPage(),
                "per_page" => $pedidos->perPage(),
                "last_page" => $pedidos->lastPage(),
                "from" => $pedidos->firstItem() ?? 0,
                "to" => $pedidos->lastItem() ?? 0
            ]
        ]);
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
                'precio_preventa_base' => $producto->precio_preventa,
                'cantidad' => $pivotData->cantidad,
                'modelo_id' => $pivotData->modelo_id,
                'precio_compra' => $pivotData->precio, // Precio regular al momento de la compra
                'precio_preventa_compra' => $pivotData->precio_preventa, // Precio de preventa al momento de la compra
                'es_preventa' => $pivotData->es_preventa, // Indica si se compró con precio de preventa
                'color' => $pivotData->color,
                'cantidad_minima' => $pivotData->cantidad_minima,
                'cantidad_maxima' => $pivotData->cantidad_maxima,
                'cantidad_minima_preventa' => $pivotData->cantidad_minima_preventa,
                'cantidad_maxima_preventa' => $pivotData->cantidad_maxima_preventa,
                'subtotal' => $pivotData->es_preventa
                    ? $pivotData->precio_preventa * $pivotData->cantidad
                    : $pivotData->precio * $pivotData->cantidad,
                'imagen_principal' => $producto->imagen_principal
                    ? asset('images/imagenes_principales/' . $producto->imagen_principal)
                    : null // Agregar la imagen principal
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
            'productos.*.precio_preventa' => 'nullable|numeric',
            'productos.*.es_preventa' => 'nullable|boolean',
            'productos.*.color' => 'nullable|string',
            'productos.*.cantidad_minima' => 'nullable|string',
            'productos.*.cantidad_maxima' => 'nullable|string',
            'productos.*.cantidad_minima_preventa' => 'nullable|string',
            'productos.*.cantidad_maxima_preventa' => 'nullable|string',
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
                'precio_preventa' => $producto['precio_preventa'] ?? null,
                'es_preventa' => $producto['es_preventa'] ?? false,
                'color' => $producto['color'] ?? null,
                'cantidad_minima' => $producto['cantidad_minima'] ?? null,
                'cantidad_maxima' => $producto['cantidad_maxima'] ?? null,
                'cantidad_minima_preventa' => $producto['cantidad_minima_preventa'] ?? null,
                'cantidad_maxima_preventa' => $producto['cantidad_maxima_preventa'] ?? null
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
            'productos.*.precio' => 'sometimes|required|numeric',
            'productos.*.precio_preventa' => 'nullable|numeric',
            'productos.*.es_preventa' => 'nullable|boolean',
            'productos.*.modelo_id' => 'nullable|exists:modelo_productos,id',
            'productos.*.color' => 'nullable|string',
            'productos.*.cantidad_minima' => 'nullable|string',
            'productos.*.cantidad_maxima' => 'nullable|string',
            'productos.*.cantidad_minima_preventa' => 'nullable|string',
            'productos.*.cantidad_maxima_preventa' => 'nullable|string',
        ]);

        $pedido->update($request->only('user_id', 'monto_total'));

        if ($request->has('productos')) {
            $pedido->productos()->detach();
            foreach ($request->productos as $producto) {
                $pedido->productos()->attach($producto['id'], [
                    'cantidad' => $producto['cantidad'],
                    'precio' => $producto['precio'] ?? null,
                    'precio_preventa' => $producto['precio_preventa'] ?? null,
                    'es_preventa' => $producto['es_preventa'] ?? false,
                    'modelo_id' => $producto['modelo_id'] ?? null,
                    'color' => $producto['color'] ?? null,
                    'cantidad_minima' => $producto['cantidad_minima'] ?? null,
                    'cantidad_maxima' => $producto['cantidad_maxima'] ?? null,
                    'cantidad_minima_preventa' => $producto['cantidad_minima_preventa'] ?? null,
                    'cantidad_maxima_preventa' => $producto['cantidad_maxima_preventa'] ?? null
                ]);
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
    /**
     * Exporta los pedidos filtrados por catálogo a un archivo Excel.
     *
     * @param int $catalogoId ID del catálogo para filtrar
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportarPedidosPorCatalogo($catalogoId)
    {
        // Obtener el nombre del catálogo para el nombre del archivo
        $catalogo = Catalogo::findOrFail($catalogoId);
        $nombreArchivo = 'pedidos-catalogo-' . Str::slug($catalogo->nombre) . '.xlsx';

        return Excel::download(new PedidosPorCatalogoExport($catalogoId), $nombreArchivo);
    }
    public function descargarPedidoPDF($id, Request $request)
    {
        try {
            // Carga el pedido con sus relaciones necesarias
            $pedido = Pedido::with([
                'user',
                'productos.modelos',
                'productos.categoria',
                'cupon'
            ])->findOrFail($id);

            // Asegurarse de que los datos de la tabla pivot estén disponibles en la vista
            // Esto es crucial para mostrar las cantidades mínimas y máximas
            foreach ($pedido->productos as $producto) {
                // Asignar explícitamente los valores de la tabla pivot al objeto producto
                $producto->cantidad_minima = $producto->pivot->cantidad_minima;
                $producto->cantidad_maxima = $producto->pivot->cantidad_maxima;
                $producto->cantidad_minima_preventa = $producto->pivot->cantidad_minima_preventa;
                $producto->cantidad_maxima_preventa = $producto->pivot->cantidad_maxima_preventa;

                // También asegurarse de que otros campos importantes estén disponibles
                $producto->es_preventa = $producto->pivot->es_preventa;
                $producto->precio_aplicado = $producto->pivot->es_preventa ?
                    $producto->pivot->precio_preventa : $producto->pivot->precio;
                $producto->precio_regular = $producto->pivot->precio;
                $producto->precio_preventa = $producto->pivot->precio_preventa;
                $producto->color = $producto->pivot->color;
                $producto->modelo_id = $producto->pivot->modelo_id;
                $producto->cantidad = $producto->pivot->cantidad;
            }

            // Configuración del PDF
            $pdf = Pdf::loadView('pedidos.pdf', compact('pedido'));
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
                'dpi' => 96, // Reducir DPI para disminuir tamaño
                'fontHeightRatio' => 1.1, // Ajustar ratio de altura de fuente
                'chroot' => [
                    public_path('storage'), // Permitir acceso a archivos en storage/app/public
                    public_path(), // Permitir acceso a archivos en public
                ]
            ]);

            // Determinar formato de respuesta
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
                    // Generar nombre único para el archivo
                    $filename = 'pedidos/pedido_' . $pedido->id . '_' . time() . '.pdf';

                    // Guardar el PDF en el almacenamiento
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
                    // Configurar nombre del archivo para descarga
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
            $nuevoPedido->productos()->attach($producto->id, [
                'cantidad' => $producto->pivot->cantidad,
                'modelo_id' => $producto->pivot->modelo_id,
                'precio' => $producto->pivot->precio,
                'precio_preventa' => $producto->pivot->precio_preventa,
                'es_preventa' => $producto->pivot->es_preventa,
                'color' => $producto->pivot->color,
                'cantidad_minima' => $producto->pivot->cantidad_minima,
                'cantidad_maxima' => $producto->pivot->cantidad_maxima,
                'cantidad_minima_preventa' => $producto->pivot->cantidad_minima_preventa,
                'cantidad_maxima_preventa' => $producto->pivot->cantidad_maxima_preventa
            ]);
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

    /**
     * Genera un PDF con todos los pedidos completados (estado = 1)
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarPdfPedidosCompletados(Request $request)
    {
        try {
            // Cargar pedidos con relaciones necesarias
            $pedidos = Pedido::with([
                'user',
                'productos.modelos',
                'productos.categoria',
                'cupon'
            ])
                ->where('estado', true)
                ->orderBy('created_at', 'desc')
                ->get();

            // Asegurarse de que los datos de la tabla pivot estén disponibles para cada pedido
            foreach ($pedidos as $pedido) {
                $pedido->productos->each(function ($producto) {
                    $producto->cantidad_minima = $producto->pivot->cantidad_minima;
                    $producto->cantidad_maxima = $producto->pivot->cantidad_maxima;
                    $producto->cantidad_minima_preventa = $producto->pivot->cantidad_minima_preventa;
                    $producto->cantidad_maxima_preventa = $producto->pivot->cantidad_maxima_preventa;
                    $producto->es_preventa = $producto->pivot->es_preventa;
                    $producto->precio_aplicado = $producto->pivot->es_preventa ?
                        $producto->pivot->precio_preventa : $producto->pivot->precio;
                    $producto->precio_regular = $producto->pivot->precio;
                    $producto->precio_preventa = $producto->pivot->precio_preventa;
                    $producto->color = $producto->pivot->color;
                    $producto->modelo_id = $producto->pivot->modelo_id;
                    $producto->cantidad = $producto->pivot->cantidad;
                });
            }

            // Procesar los datos para el reporte
            $fechaActual = Carbon::now()->format('d/m/Y H:i');
            $totalPedidos = $pedidos->count();
            $montoTotal = $pedidos->sum('total_amount');
            $totalProductos = 0;

            // Calcular el total de productos vendidos
            foreach ($pedidos as $pedido) {
                foreach ($pedido->productos as $producto) {
                    $totalProductos += $producto->pivot->cantidad;
                }
            }

            // Preparar datos para la vista
            $data = [
                'pedidos' => $pedidos,
                'fechaGeneracion' => $fechaActual,
                'totalPedidos' => $totalPedidos,
                'montoTotal' => $montoTotal,
                'totalProductos' => $totalProductos,
                'titulo' => 'Pedidos Completados',
                'estado' => 'completado',
                'totalCompletados' => $totalPedidos,
                'totalEnProceso' => Pedido::where('estado', false)->count()
            ];

            // Generar el PDF
            $pdf = PDF::loadView('pedidos.reporte', $data);
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

            // Determinar el formato de respuesta
            $responseFormat = $request->query('format', 'binary');
            $filename = 'pedidos_completados_' . Carbon::now()->format('dmY_His') . '.pdf';

            switch ($responseFormat) {
                case 'base64':
                    $base64 = base64_encode($pdf->output());
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'filename' => $filename,
                            'content_type' => 'application/pdf',
                            'base64' => $base64,
                            'total_pedidos' => $totalPedidos,
                            'monto_total' => $montoTotal,
                            'total_productos' => $totalProductos
                        ],
                        'message' => 'PDF de pedidos completados generado exitosamente'
                    ]);

                case 'url':
                    $path = 'pedidos/' . $filename;
                    Storage::disk('public')->put($path, $pdf->output());
                    $url = Storage::disk('public')->url($path);

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'filename' => $filename,
                            'url' => $url,
                            'expires_at' => now()->addDay()->toIso8601String(),
                            'total_pedidos' => $totalPedidos,
                            'monto_total' => $montoTotal,
                            'total_productos' => $totalProductos
                        ],
                        'message' => 'URL de descarga generada exitosamente'
                    ]);

                case 'binary':
                default:
                    return $pdf->download($filename);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Genera un PDF con todos los pedidos en proceso (estado = 0)
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarPdfPedidosEnProceso(Request $request)
    {
        try {
            // Cargar pedidos con relaciones necesarias
            $pedidos = Pedido::with([
                'user',
                'productos.modelos',
                'productos.categoria',
                'cupon'
            ])
                ->where('estado', false)
                ->orderBy('created_at', 'desc')
                ->get();

            // Asegurarse de que los datos de la tabla pivot estén disponibles para cada pedido
            foreach ($pedidos as $pedido) {
                $pedido->productos->each(function ($producto) {
                    $producto->cantidad_minima = $producto->pivot->cantidad_minima;
                    $producto->cantidad_maxima = $producto->pivot->cantidad_maxima;
                    $producto->cantidad_minima_preventa = $producto->pivot->cantidad_minima_preventa;
                    $producto->cantidad_maxima_preventa = $producto->pivot->cantidad_maxima_preventa;
                    $producto->es_preventa = $producto->pivot->es_preventa;
                    $producto->precio_aplicado = $producto->pivot->es_preventa ?
                        $producto->pivot->precio_preventa : $producto->pivot->precio;
                    $producto->precio_regular = $producto->pivot->precio;
                    $producto->precio_preventa = $producto->pivot->precio_preventa;
                    $producto->color = $producto->pivot->color;
                    $producto->modelo_id = $producto->pivot->modelo_id;
                    $producto->cantidad = $producto->pivot->cantidad;
                });
            }

            // Procesar los datos para el reporte
            $fechaActual = Carbon::now()->format('d/m/Y H:i');
            $totalPedidos = $pedidos->count();
            $montoTotal = $pedidos->sum('total_amount');
            $totalProductos = 0;

            // Calcular el total de productos en proceso
            foreach ($pedidos as $pedido) {
                foreach ($pedido->productos as $producto) {
                    $totalProductos += $producto->pivot->cantidad;
                }
            }

            // Preparar datos para la vista
            $data = [
                'pedidos' => $pedidos,
                'fechaGeneracion' => $fechaActual,
                'totalPedidos' => $totalPedidos,
                'montoTotal' => $montoTotal,
                'totalProductos' => $totalProductos,
                'titulo' => 'Pedidos en Proceso',
                'estado' => 'en proceso',
                'totalCompletados' => Pedido::where('estado', true)->count(),
                'totalEnProceso' => $totalPedidos
            ];

            // Generar el PDF
            $pdf = PDF::loadView('pedidos.reporte', $data);
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

            // Determinar el formato de respuesta
            $responseFormat = $request->query('format', 'binary');
            $filename = 'pedidos_en_proceso_' . Carbon::now()->format('dmY_His') . '.pdf';

            switch ($responseFormat) {
                case 'base64':
                    $base64 = base64_encode($pdf->output());
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'filename' => $filename,
                            'content_type' => 'application/pdf',
                            'base64' => $base64,
                            'total_pedidos' => $totalPedidos,
                            'monto_total' => $montoTotal,
                            'total_productos' => $totalProductos
                        ],
                        'message' => 'PDF de pedidos en proceso generado exitosamente'
                    ]);

                case 'url':
                    $path = 'pedidos/' . $filename;
                    Storage::disk('public')->put($path, $pdf->output());
                    $url = Storage::disk('public')->url($path);

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'filename' => $filename,
                            'url' => $url,
                            'expires_at' => now()->addDay()->toIso8601String(),
                            'total_pedidos' => $totalPedidos,
                            'monto_total' => $montoTotal,
                            'total_productos' => $totalProductos
                        ],
                        'message' => 'URL de descarga generada exitosamente'
                    ]);

                case 'binary':
                default:
                    return $pdf->download($filename);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
    /**
     * Genera un PDF con todos los pedidos de un catálogo específico
     * 
     * @param int $catalogoId ID del catálogo
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generarPdfPedidosPorCatalogo($catalogoId, Request $request)
    {
        try {
            // Verificar que el catálogo existe
            $catalogo = \App\Models\Catalogo::findOrFail($catalogoId);

            // Obtener todas las categorías del catálogo
            $categorias = \App\Models\Categoria::where('catalogo_id', $catalogoId)->pluck('id');

            if ($categorias->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El catálogo no tiene categorías asociadas'
                ], 404);
            }

            // Obtener todos los productos de las categorías del catálogo
            $productosIds = \App\Models\Producto::whereIn('categoria_id', $categorias)->pluck('id');

            if ($productosIds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay productos asociados a las categorías de este catálogo'
                ], 404);
            }

            // Cargar pedidos que contengan productos del catálogo
            $pedidos = Pedido::with([
                'user',
                'productos' => function ($query) use ($productosIds) {
                    $query->whereIn('productos.id', $productosIds);
                },
                'productos.modelos',
                'productos.categoria',
                'productos.categoria.catalogo',
                'cupon'
            ])
                ->whereHas('productos', function ($query) use ($productosIds) {
                    $query->whereIn('productos.id', $productosIds);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // Asegurarse de que los datos de la tabla pivot estén disponibles para cada pedido
            foreach ($pedidos as $pedido) {
                $pedido->productos->each(function ($producto) {
                    $producto->cantidad_minima = $producto->pivot->cantidad_minima;
                    $producto->cantidad_maxima = $producto->pivot->cantidad_maxima;
                    $producto->cantidad_minima_preventa = $producto->pivot->cantidad_minima_preventa;
                    $producto->cantidad_maxima_preventa = $producto->pivot->cantidad_maxima_preventa;
                    $producto->es_preventa = $producto->pivot->es_preventa;
                    $producto->precio_aplicado = $producto->pivot->es_preventa ?
                        $producto->pivot->precio_preventa : $producto->pivot->precio;
                    $producto->precio_regular = $producto->pivot->precio;
                    $producto->precio_preventa = $producto->pivot->precio_preventa;
                    $producto->color = $producto->pivot->color;
                    $producto->modelo_id = $producto->pivot->modelo_id;
                    $producto->cantidad = $producto->pivot->cantidad;
                });
            }

            // Filtrar pedidos que no tengan productos después del whereIn
            $pedidos = $pedidos->filter(function ($pedido) {
                return $pedido->productos->isNotEmpty();
            });

            if ($pedidos->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay pedidos con productos de este catálogo'
                ], 404);
            }

            // Procesar los datos para el reporte
            $fechaActual = Carbon::now()->format('d/m/Y H:i');
            $totalPedidos = $pedidos->count();

            // Calcular el monto total y productos vendidos solo para los productos del catálogo
            $montoTotal = 0;
            $totalProductos = 0;

            foreach ($pedidos as $pedido) {
                foreach ($pedido->productos as $producto) {
                    $precioUnitario = $producto->pivot->es_preventa ? $producto->pivot->precio_preventa : $producto->pivot->precio;
                    $subtotal = $precioUnitario * $producto->pivot->cantidad;
                    $montoTotal += $subtotal;
                    $totalProductos += $producto->pivot->cantidad;
                }
            }

            // Agrupar productos por categoría para el reporte
            $productosPorCategoria = [];
            foreach ($pedidos as $pedido) {
                foreach ($pedido->productos as $producto) {
                    $categoriaId = $producto->categoria_id;
                    $categoriaNombre = $producto->categoria->nombre;

                    if (!isset($productosPorCategoria[$categoriaId])) {
                        $productosPorCategoria[$categoriaId] = [
                            'nombre' => $categoriaNombre,
                            'productos' => []
                        ];
                    }

                    if (!isset($productosPorCategoria[$categoriaId]['productos'][$producto->id])) {
                        $productosPorCategoria[$categoriaId]['productos'][$producto->id] = [
                            'nombre' => $producto->nombre,
                            'cantidad' => 0,
                            'monto' => 0
                        ];
                    }

                    $precioUnitario = $producto->pivot->es_preventa ? $producto->pivot->precio_preventa : $producto->pivot->precio;
                    $subtotal = $precioUnitario * $producto->pivot->cantidad;

                    $productosPorCategoria[$categoriaId]['productos'][$producto->id]['cantidad'] += $producto->pivot->cantidad;
                    $productosPorCategoria[$categoriaId]['productos'][$producto->id]['monto'] += $subtotal;
                }
            }

            // Preparar datos para la vista
            $data = [
                'catalogo' => $catalogo,
                'pedidos' => $pedidos,
                'fechaGeneracion' => $fechaActual,
                'totalPedidos' => $totalPedidos,
                'montoTotal' => $montoTotal,
                'totalProductos' => $totalProductos,
                'fechaGeneracion' => $fechaActual,
                'totalPedidos' => $totalPedidos,
                'montoTotal' => $montoTotal,
                'totalProductos' => $totalProductos,
                'productosPorCategoria' => $productosPorCategoria,
                'titulo' => 'Pedidos del Catálogo: ' . $catalogo->nombre,
            ];

            // Generar el PDF
            $pdf = PDF::loadView('pedidos.reporte_catalogo', $data);
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

            // Determinar el formato de respuesta
            $responseFormat = $request->query('format', 'binary');
            $filename = 'pedidos_catalogo_' . $catalogo->id . '_' . Carbon::now()->format('dmY_His') . '.pdf';

            switch ($responseFormat) {
                case 'base64':
                    $base64 = base64_encode($pdf->output());
                    return response()->json([
                        'success' => true,
                        'data' => [
                            'catalogo_id' => $catalogo->id,
                            'catalogo_nombre' => $catalogo->nombre,
                            'filename' => $filename,
                            'content_type' => 'application/pdf',
                            'base64' => $base64,
                            'total_pedidos' => $totalPedidos,
                            'monto_total' => $montoTotal,
                            'total_productos' => $totalProductos
                        ],
                        'message' => 'PDF de pedidos del catálogo generado exitosamente'
                    ]);

                case 'url':
                    $path = 'pedidos/' . $filename;
                    Storage::disk('public')->put($path, $pdf->output());
                    $url = Storage::disk('public')->url($path);

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'catalogo_id' => $catalogo->id,
                            'catalogo_nombre' => $catalogo->nombre,
                            'filename' => $filename,
                            'url' => $url,
                            'expires_at' => now()->addDay()->toIso8601String(),
                            'total_pedidos' => $totalPedidos,
                            'monto_total' => $montoTotal,
                            'total_productos' => $totalProductos
                        ],
                        'message' => 'URL de descarga generada exitosamente'
                    ]);

                case 'binary':
                default:
                    return $pdf->download($filename);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el PDF: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
