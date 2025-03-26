<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 10px;
            color: #333;
            font-size: 10px;
            line-height: 1.4;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #4a6cf7;
            padding-bottom: 10px;
        }
        .header-left {
            text-align: left;
        }
        .header-right {
            text-align: right;
            font-size: 9px;
            color: #666;
        }
        .header h1 {
            color: #4a6cf7;
            margin: 0;
            font-size: 18px;
        }
        .summary {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .summary-item {
            text-align: center;
            padding: 0 10px;
            border-right: 1px solid #e0e0e0;
            flex: 1;
        }
        .summary-item:last-child {
            border-right: none;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            color: #4a6cf7;
            margin: 0;
        }
        .summary-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }
        .pedidos-list {
            margin-bottom: 15px;
        }
        .pedido-container {
            margin-bottom: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
            overflow: hidden;
        }
        .pedido-header {
            background-color: #f8f9fa;
            padding: 6px 8px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pedido-id {
            font-weight: bold;
            color: #4a6cf7;
            font-size: 11px;
        }
        .pedido-fecha {
            color: #666;
            font-size: 9px;
        }
        .pedido-info {
            display: flex;
            padding: 6px 8px;
            background-color: #fcfcfc;
            border-bottom: 1px solid #eee;
            font-size: 9px;
        }
        .pedido-cliente {
            flex: 1;
        }
        .pedido-cliente-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
        .pedido-cliente-nombre {
            font-weight: bold;
        }
        .pedido-total {
            text-align: right;
            min-width: 100px;
        }
        .pedido-total-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
        }
        .pedido-total-valor {
            font-weight: bold;
            font-size: 12px;
            color: #4a6cf7;
        }
        .productos-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        .productos-table th {
            background-color: #f2f2f2;
            padding: 5px 6px;
            text-align: left;
            font-size: 8px;
            color: #555;
            text-transform: uppercase;
        }
        .productos-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #eee;
            vertical-align: top;
        }
        .producto-nombre {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .producto-categoria {
            font-size: 8px;
            color: #666;
        }
        .producto-descripcion {
            font-size: 8px;
            color: #777;
            margin-top: 2px;
        }
        .producto-detalles {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
        }
        .producto-modelo {
            font-size: 8px;
            background-color: #f0f4ff;
            padding: 2px 4px;
            border-radius: 2px;
            margin-right: 4px;
        }
        .producto-color {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 3px;
            vertical-align: middle;
            border: 1px solid #ddd;
        }
        .producto-color-text {
            font-size: 8px;
        }
        .producto-precio {
            font-weight: bold;
            text-align: right;
            white-space: nowrap;
        }
        .producto-cantidad {
            text-align: center;
            font-weight: bold;
        }
        .producto-subtotal {
            text-align: right;
            font-weight: bold;
            color: #4a6cf7;
            white-space: nowrap;
        }
        .pedido-resumen {
            padding: 6px 8px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #eee;
            font-size: 9px;
        }
        .pedido-metodo-label, .pedido-descuento-label {
            color: #666;
            margin-right: 3px;
            font-size: 8px;
        }
        .pedido-metodo-valor {
            font-weight: bold;
        }
        .pedido-descuento-valor {
            font-weight: bold;
            color: #28a745;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .page-break {
            page-break-after: always;
        }
        .no-pedidos {
            text-align: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin: 10px 0;
            color: #666;
            font-size: 10px;
        }
        .no-pedidos-icon {
            font-size: 16px;
            margin-bottom: 5px;
            color: #ccc;
        }
        /* Estilos para tablas compactas */
        .compact-table {
            font-size: 8px;
        }
        .compact-table th {
            padding: 4px;
        }
        .compact-table td {
            padding: 4px;
        }
        /* Estilos para el comprobante/voucher */
        .voucher-container {
            margin-top: 10px;
            padding: 8px;
            background-color: #f9f9f9;
            border: 1px dashed #ddd;
            border-radius: 4px;
        }
        .voucher-title {
            font-size: 10px;
            font-weight: bold;
            color: #4a6cf7;
            margin-bottom: 5px;
        }
        .voucher-info {
            font-size: 9px;
        }
        .voucher-image {
            max-width: 100%;
            max-height: 150px;
            margin-top: 5px;
            border: 1px solid #eee;
        }
        /* Nuevos estilos para tipos de precio */
        .price-type-badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
            color: white;
            margin-left: 3px;
            vertical-align: middle;
        }
        .price-type-regular {
            background-color: #3b82f6;
        }
        .price-type-preventa {
            background-color: #f59e0b;
        }
        .quantity-range {
            font-size: 7px;
            color: #666;
            margin-top: 2px;
            display: block;
        }
        /* Estilos para impresión */
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <h1>{{ $titulo }}</h1>
        </div>
        <div class="header-right">
            Reporte generado: {{ $fechaGeneracion }}
        </div>
    </div>
    
    <div class="summary">
        <div class="summary-item">
            <div class="summary-value">{{ $totalPedidos }}</div>
            <div class="summary-label">PEDIDOS {{ isset($estado) ? strtoupper($estado) : '' }}</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">Bs {{ number_format($montoTotal, 2) }}</div>
            <div class="summary-label">MONTO TOTAL</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $totalProductos }}</div>
            <div class="summary-label">PRODUCTOS</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">
                {{ $totalPedidos > 0 ? 'Bs ' . number_format($montoTotal / $totalPedidos, 2) : 'Bs 0.00' }}
            </div>
            <div class="summary-label">PROMEDIO</div>
        </div>
    </div>
    
    <div class="pedidos-list">
        @if(count($pedidos) > 0)
            @foreach($pedidos as $index => $pedido)
                <div class="pedido-container">
                    <div class="pedido-header">
                        <div class="pedido-id">Pedido #{{ $pedido->id }}</div>
                        <div class="pedido-fecha">{{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y H:i') }}</div>
                    </div>
                    
                    <div class="pedido-info">
                        <div class="pedido-cliente">
                            <div class="pedido-cliente-label">Cliente</div>
                            <div class="pedido-cliente-nombre">
                                {{ $pedido->user->name ?? $pedido->user->nombre . ' ' . $pedido->user->apellido }}
                            </div>
                            <div>{{ $pedido->user->email ?? 'No disponible' }}</div>
                        </div>
                        <div class="pedido-total">
                            <div class="pedido-total-label">Total</div>
                            <div class="pedido-total-valor">Bs {{ number_format($pedido->total_amount, 2) }}</div>
                            @if($pedido->total_to_pay && $pedido->total_to_pay != $pedido->total_amount)
                                <div class="pedido-total-label">A Pagar</div>
                                <div class="pedido-total-valor">Bs {{ number_format($pedido->total_to_pay, 2) }}</div>
                            @endif
                        </div>
                    </div>
                    
                    <table class="productos-table compact-table">
                        <thead>
                            <tr>
                                <th width="35%">Producto</th>
                                <th width="25%">Detalles</th>
                                <th width="13%">Precio</th>
                                <th width="7%">Cant</th>
                                <th width="20%">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pedido->productos as $producto)
                                @php
                                    // Determinar el precio correcto según el tipo (preventa o regular)
                                    $precioAplicado = $producto->pivot->es_preventa ? $producto->pivot->precio_preventa : $producto->pivot->precio;
                                    $subtotal = $precioAplicado * $producto->pivot->cantidad;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="producto-nombre">
                                            {{ $producto->nombre }}
                                            @if($producto->pivot->es_preventa)
                                                <span class="price-type-badge price-type-preventa">PREVENTA</span>
                                            @else
                                                <span class="price-type-badge price-type-regular">REGULAR</span>
                                            @endif
                                        </div>
                                        <div class="producto-categoria">
                                            {{ $producto->categoria->nombre ?? 'Sin categoría' }}
                                        </div>
                                        <div class="producto-descripcion">
                                            {{ \Illuminate\Support\Str::limit($producto->descripcion, 60) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="producto-detalles">
                                            @if($producto->pivot->modelo_id)
                                                @php
                                                    $modelo = $producto->modelos->where('id', $producto->pivot->modelo_id)->first();
                                                @endphp
                                                @if($modelo)
                                                    <div class="producto-modelo">{{ $modelo->nombre }}</div>
                                                @endif
                                            @endif
                                            
                                            @if($producto->pivot->color)
                                                <div>
                                                    <span class="producto-color" style="background-color: {{ $producto->pivot->color }};"></span>
                                                    <span class="producto-color-text">{{ $producto->pivot->color }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Rangos de cantidad -->
                                        @if($producto->pivot->es_preventa)
                                            <span class="quantity-range">
                                                Rango preventa: {{ $producto->pivot->cantidad_minima_preventa ?? 'N/A' }} - {{ $producto->pivot->cantidad_maxima_preventa ?? 'N/A' }}
                                            </span>
                                        @else
                                            <span class="quantity-range">
                                                Rango regular: {{ $producto->pivot->cantidad_minima ?? 'N/A' }} - {{ $producto->pivot->cantidad_maxima ?? 'N/A' }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="producto-precio">
                                        Bs {{ number_format($precioAplicado, 2) }}
                                        @if($producto->pivot->es_preventa && $producto->pivot->precio)
                                            <div style="font-size: 7px; color: #888; text-decoration: line-through;">
                                                Reg: Bs {{ number_format($producto->pivot->precio, 2) }}
                                            </div>
                                        @elseif(!$producto->pivot->es_preventa && $producto->pivot->precio_preventa)
                                            <div style="font-size: 7px; color: #888; text-decoration: line-through;">
                                                Pre: Bs {{ number_format($producto->pivot->precio_preventa, 2) }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="producto-cantidad">
                                        {{ $producto->pivot->cantidad }}
                                    </td>
                                    <td class="producto-subtotal">
                                        Bs {{ number_format($subtotal, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <div class="pedido-resumen">
                        <div class="pedido-metodo">
                            <span class="pedido-metodo-label">Método:</span>
                            <span class="pedido-metodo-valor">
                                {{ $pedido->payment_method == 'in-person' ? 'En persona' : ($pedido->payment_method == 'qr' ? 'Pago QR' : ucfirst($pedido->payment_method)) }}
                            </span>
                        </div>
                        
                        @if($pedido->cupon)
                            <div class="pedido-descuento">
                                <span class="pedido-descuento-label">Cupón:</span>
                                <span class="pedido-descuento-valor">{{ $pedido->cupon->codigo }} ({{ $pedido->cupon->descuento }}%)</span>
                            </div>
                        @endif
                    </div>
                    
                    @if($pedido->voucher)
                        <div class="voucher-container">
                            <div class="voucher-title">Comprobante de Pago</div>
                            <div class="voucher-info">
                                El cliente ha proporcionado un comprobante para este pedido.
                            </div>
                            @php
                                $voucherPath = 'vouchers/' . $pedido->voucher;
                                $voucherUrl = asset($voucherPath);
                            @endphp
                            <img src="{{ $voucherUrl }}" alt="Comprobante de pago" class="voucher-image">
                        </div>
                    @endif
                </div>
                
                @if($index < count($pedidos) - 1 && ($index + 1) % 2 == 0)
                    <div class="page-break"></div>
                @endif
            @endforeach
        @else
            <div class="no-pedidos">
                <div class="no-pedidos-icon">📭</div>
                <p>No hay pedidos {{ isset($estado) ? $estado : '' }} para mostrar.</p>
            </div>
        @endif
    </div>
    
    <div class="footer">
        <p>© {{ date('Y') }} Mi Empresa - Todos los derechos reservados | Reporte oficial de pedidos {{ isset($estado) ? $estado : '' }}</p>
    </div>
</body>
</html>

