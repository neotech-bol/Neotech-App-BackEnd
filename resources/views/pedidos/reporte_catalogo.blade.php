<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pedidos - Catálogo: {{ $catalogo->nombre }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 18px;
            margin: 0 0 5px;
        }
        h2 {
            font-size: 16px;
            margin: 15px 0 5px;
            color: #444;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
        }
        h3 {
            font-size: 14px;
            margin: 10px 0 5px;
            color: #555;
        }
        .info-box {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            width: 40%;
        }
        .info-value {
            width: 60%;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .summary {
            margin-top: 20px;
            background-color: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .price-type {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
            margin-left: 5px;
        }
        .price-regular {
            background-color: #28a745;
        }
        .price-preventa {
            background-color: #007bff;
        }
        .quantity-range {
            font-size: 10px;
            color: #666;
            display: block;
            margin-top: 3px;
        }
        .model-info {
            font-style: italic;
            color: #6c757d;
            font-size: 11px;
        }
        .color-info {
            display: inline-block;
            margin-left: 5px;
            font-size: 11px;
        }
        .color-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 3px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists(public_path('img/logo.png')))
            <img src="{{ public_path('img/logo.png') }}" alt="Logo" class="logo">
        @endif
        <h1>Reporte de Pedidos - Catálogo: {{ $catalogo->nombre }}</h1>
        <p>Fecha de generación: {{ $fechaGeneracion }}</p>
    </div>
    
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Total de pedidos:</span>
            <span class="info-value">{{ $totalPedidos }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total de productos vendidos:</span>
            <span class="info-value">{{ $totalProductos }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Monto total:</span>
            <span class="info-value">Bs. {{ number_format($montoTotal, 2) }}</span>
        </div>
    </div>
    
    <h2>Resumen por Categorías</h2>
    
    @foreach($productosPorCategoria as $categoriaId => $categoria)
        <h3>{{ $categoria['nombre'] }}</h3>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Monto Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categoria['productos'] as $productoId => $producto)
                    <tr>
                        <td>{{ $producto['nombre'] }}</td>
                        <td>{{ $producto['cantidad'] }}</td>
                        <td>Bs. {{ number_format($producto['monto'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
    
    <div class="page-break"></div>
    
    <h2>Detalle de Pedidos</h2>
    
    @foreach($pedidos as $pedido)
        <div class="info-box">
            <div class="info-row">
                <span class="info-label">Pedido ID:</span>
                <span class="info-value">{{ $pedido->id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Cliente:</span>
                <span class="info-value">{{ $pedido->user->nombre }} {{ $pedido->user->apellido }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Fecha:</span>
                <span class="info-value">{{ $pedido->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Estado:</span>
                <span class="info-value">{{ $pedido->estado ? 'Completado' : 'En proceso' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Método de pago:</span>
                <span class="info-value">{{ $pedido->payment_method }}</span>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th>Detalles</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido->productos as $producto)
                        <tr>
                            <td>
                                {{ $producto->nombre }}
                                @if($producto->pivot->modelo_id)
                                    <span class="model-info">
                                        Modelo: {{ optional($producto->modelos->where('id', $producto->pivot->modelo_id)->first())->nombre ?? 'N/A' }}
                                    </span>
                                @endif
                            </td>
                            <td>{{ $producto->categoria->nombre }}</td>
                            <td>
                                <span class="price-type {{ $producto->pivot->es_preventa ? 'price-preventa' : 'price-regular' }}">
                                    {{ $producto->pivot->es_preventa ? 'Preventa' : 'Regular' }}
                                </span>
                                
                                @if($producto->pivot->color)
                                    <span class="color-info">
                                        <span class="color-dot" style="background-color: {{ $producto->pivot->color }};"></span>
                                        {{ $producto->pivot->color }}
                                    </span>
                                @endif
                                
                                <span class="quantity-range">
                                    Rango: 
                                    @if($producto->pivot->es_preventa)
                                        {{ $producto->pivot->cantidad_minima_preventa ?? 'N/A' }} - 
                                        {{ $producto->pivot->cantidad_maxima_preventa ?? 'N/A' }}
                                    @else
                                        {{ $producto->pivot->cantidad_minima ?? 'N/A' }} - 
                                        {{ $producto->pivot->cantidad_maxima ?? 'N/A' }}
                                    @endif
                                </span>
                            </td>
                            <td>{{ $producto->pivot->cantidad }}</td>
                            <td>Bs. {{ number_format($producto->pivot->es_preventa ? $producto->pivot->precio_preventa : $producto->pivot->precio, 2) }}</td>
                            <td>Bs. {{ number_format(($producto->pivot->es_preventa ? $producto->pivot->precio_preventa : $producto->pivot->precio) * $producto->pivot->cantidad, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" style="text-align: right;">Total:</th>
                        <th>Bs. {{ number_format($pedido->total_amount, 2) }}</th>
                    </tr>
                    @if($pedido->cupon)
                        <tr>
                            <th colspan="5" style="text-align: right;">Descuento (Cupón: {{ $pedido->cupon->codigo }}):</th>
                            <th>Bs. {{ number_format($pedido->total_amount - $pedido->total_to_pay, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="5" style="text-align: right;">Total a pagar:</th>
                            <th>Bs. {{ number_format($pedido->total_to_pay, 2) }}</th>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    @endforeach
    
    <div class="footer">
        <p>Este es un documento generado automáticamente. Fecha de generación: {{ $fechaGeneracion }}</p>
    </div>
</body>
</html>

