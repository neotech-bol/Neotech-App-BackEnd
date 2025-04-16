<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pedidos {{ isset($estado) ? ucfirst($estado) : '' }} - Catálogo: {{ $catalogo->nombre }}</title>
    <style>
        /* Estilos base */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 10px;
            color: #333;
            font-size: 10px;
            line-height: 1.4;
        }
        
        /* Encabezado principal */
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
        .logo {
            max-width: 120px;
            margin-bottom: 5px;
        }
        
        /* Resumen estadístico */
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
        
        /* Secciones y títulos */
        .section-title {
            color: #4a6cf7;
            font-size: 14px;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        /* Categorías */
        .category-container {
            margin-bottom: 20px;
            background-color: #fff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .category-header {
            background-color: #f3f4f6;
            padding: 8px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
        }
        .category-name {
            font-weight: bold;
            color: #4a6cf7;
            font-size: 12px;
        }
        .product-count {
            background-color: #e0e7ff;
            color: #4338ca;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: bold;
        }
        
        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        th {
            background-color: #f8f9fa;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .totals-row {
            background-color: #f3f4f6;
            font-weight: bold;
        }
        .totals-row td {
            padding: 8px;
        }
        
        /* Pedidos */
        .pedido-container {
            margin-bottom: 15px;
            background-color: #fff;
            border-radius: 6px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            border: 1px solid #e5e7eb;
        }
        .pedido-header {
            background-color: #f3f4f6;
            padding: 8px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
        }
        .pedido-id {
            font-weight: bold;
            color: #4a6cf7;
            font-size: 12px;
        }
        .pedido-fecha {
            color: #666;
            font-size: 9px;
        }
        .pedido-info {
            display: flex;
            padding: 8px 10px;
            background-color: #fcfcfc;
            border-bottom: 1px solid #e5e7eb;
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
        .pedido-estado {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: bold;
            margin-left: 8px;
        }
        .estado-completado {
            background-color: #10b981;
            color: white;
        }
        .estado-proceso {
            background-color: #f59e0b;
            color: white;
        }
        
        /* Productos */
        .producto-nombre {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .producto-categoria {
            font-size: 8px;
            color: #666;
        }
        .producto-detalles {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
            margin-top: 2px;
        }
        .producto-modelo {
            font-size: 8px;
            background-color: #f0f4ff;
            padding: 2px 4px;
            border-radius: 2px;
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
            text-align: right;
            white-space: nowrap;
            font-weight: bold;
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
        
        /* Badges y etiquetas */
        .estandar-badge {
            display: inline-block;
            background-color: #f59e0b;
            color: white;
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
            margin-left: 3px;
            vertical-align: middle;
        }
        
        .especial-badge {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 1px 3px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
            margin-left: 3px;
            vertical-align: middle;
        }
        
        .estandar-info {
            background-color: #fffbeb;
            border: 1px dashed #f59e0b;
            border-radius: 3px;
            padding: 2px 4px;
            margin-top: 2px;
            font-size: 7px;
        }
        
        .especial-info {
            background-color: #eff6ff;
            border: 1px dashed #3b82f6;
            border-radius: 3px;
            padding: 2px 4px;
            margin-top: 2px;
            font-size: 7px;
        }
        
        .price-comparison {
            display: flex;
            justify-content: space-between;
            margin-top: 2px;
            font-size: 7px;
            border-top: 1px dotted #e5e7eb;
            padding-top: 2px;
        }
        
        .price-tag {
            display: inline-block;
            padding: 1px 3px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 7px;
        }
        
        .price-tag.selected {
            background-color: #10b981;
            color: white;
        }
        
        .price-tag.not-selected {
            background-color: #e5e7eb;
            color: #6b7280;
            text-decoration: line-through;
        }
        
        .quantity-range {
            margin-top: 2px;
            padding: 1px 3px;
            background-color: #f3f4f6;
            border-radius: 2px;
            font-size: 6.5px;
            color: #4b5563;
        }
        
        .quantity-range-label {
            font-weight: bold;
            color: #4b5563;
        }
        
        /* Resumen de pedido */
        .pedido-resumen {
            padding: 8px 10px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            border-top: 1px solid #e5e7eb;
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
            color: #10b981;
        }
        
        /* Totales */
        .totals {
            margin-top: 5px;
            text-align: right;
            padding: 5px 10px;
            background-color: #f8f9fa;
            border-top: 1px solid #e5e7eb;
        }
        .total-row {
            margin: 2px 0;
            font-size: 9px;
        }
        .total-label {
            display: inline-block;
            width: 100px;
            text-align: right;
            font-weight: bold;
            margin-right: 5px;
        }
        .total-value {
            display: inline-block;
            width: 80px;
            text-align: right;
        }
        .grand-total {
            font-size: 11px;
            font-weight: bold;
            color: #4a6cf7;
            margin-top: 3px;
            padding-top: 3px;
            border-top: 1px solid #4a6cf7;
        }
        
        /* Cupón */
        .cupon-info {
            background-color: #fdf2f8;
            border: 1px dashed #ec4899;
            padding: 4px 8px;
            border-radius: 4px;
            margin-top: 4px;
            font-size: 8px;
        }
        .cupon-title {
            color: #ec4899;
            font-weight: bold;
        }
        
        /* Comprobante */
        .voucher-container {
            margin: 5px 0;
            padding: 5px;
            background-color: #f0f9ff;
            border-radius: 4px;
            border: 1px solid #0ea5e9;
            position: relative;
        }
        .voucher-badge {
            position: absolute;
            top: -8px;
            right: 8px;
            background-color: #0ea5e9;
            color: white;
            padding: 2px 5px;
            border-radius: 2px;
            font-size: 7px;
            font-weight: bold;
        }
        .voucher-title {
            color: #0ea5e9;
            font-weight: bold;
            margin-bottom: 3px;
            font-size: 9px;
        }
        .voucher-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .voucher-info {
            flex: 2;
        }
        .voucher-image {
            flex: 1;
            text-align: center;
            max-width: 100px;
            max-height: 100px;
            margin-left: 10px;
        }
        .voucher-image img {
            max-width: 100%;
            max-height: 100%;
            border: 1px solid #ddd;
        }
        
        /* Pie de página */
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        
        /* Saltos de página */
        .page-break {
            page-break-after: always;
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
    <!-- Encabezado principal -->
    <div class="header">
        <div class="header-left">
            @if(file_exists(public_path('img/logo.png')))
                <img src="{{ public_path('img/logo.png') }}" alt="Logo" class="logo">
            @endif
            <h1>
                Reporte de Pedidos 
                @if(isset($estado))
                    <span class="pedido-estado {{ $estado == 'completado' ? 'estado-completado' : 'estado-proceso' }}">
                        {{ ucfirst($estado) }}
                    </span>
                @endif
                - Catálogo: {{ $catalogo->nombre }}
            </h1>
        </div>
        <div class="header-right">
            <div>Fecha de generación: {{ $fechaGeneracion }}</div>
            <div>Catálogo ID: {{ $catalogo->id }}</div>
        </div>
    </div>
    
    <!-- Resumen estadístico -->
    <div class="summary">
        <div class="summary-item">
            <div class="summary-value">{{ $totalPedidos }}</div>
            <div class="summary-label">TOTAL PEDIDOS</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">{{ $totalProductos }}</div>
            <div class="summary-label">PRODUCTOS VENDIDOS</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">Bs. {{ number_format($montoTotal, 2) }}</div>
            <div class="summary-label">MONTO TOTAL</div>
        </div>
        <div class="summary-item">
            <div class="summary-value">
                {{ $totalPedidos > 0 ? 'Bs. ' . number_format($montoTotal / $totalPedidos, 2) : 'Bs. 0.00' }}
            </div>
            <div class="summary-label">PROMEDIO POR PEDIDO</div>
        </div>
    </div>
    
    <!-- Resumen por categorías -->
    <h2 class="section-title">Resumen por Categorías</h2>

    @foreach($productosPorCategoria as $categoriaId => $categoria)
        <div class="category-container">
            <div class="category-header">
                <div class="category-name">{{ $categoria['nombre'] }}</div>
                <div class="product-count">{{ count($categoria['productos']) }} productos</div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th width="25%">Producto</th>
                        <th width="10%">Tipo</th>
                        <th width="15%">Precio Unit.</th>
                        <th width="10%">Cantidad</th>
                        <th width="15%">Monto Total</th>
                        <th width="15%">A Pagar</th>
                        <th width="10%">Pendiente</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalCategoria = 0; $totalAPagar = 0; $totalPendiente = 0; @endphp
                    @foreach($categoria['productos'] as $productoId => $producto)
                        @php 
                            $totalCategoria += $producto['monto']; 
                            
                            // Asegurarnos de que estos valores existan, si no, usar valores predeterminados
                            $montoPagar = isset($producto['monto_a_pagar']) ? $producto['monto_a_pagar'] : $producto['monto'];
                            $montoPendiente = isset($producto['pendiente']) ? $producto['pendiente'] : 0;
                            $totalAPagar += $montoPagar;
                            $totalPendiente += $montoPendiente;
                            
                            // Determinar si es precio estándar o especial
                            $esEstandar = isset($producto['es_estandar']) ? $producto['es_estandar'] : false;
                            
                            // Calcular el precio unitario si no está disponible
                            $precioUnitario = isset($producto['precio_unitario']) 
                                ? $producto['precio_unitario'] 
                                : ($producto['cantidad'] > 0 ? $producto['monto'] / $producto['cantidad'] : 0);
                        @endphp
                        <tr>
                            <td>
                                <div class="producto-nombre">
                                    {{ $producto['nombre'] }}
                                </div>
                            </td>
                            <td>
                                @if($esEstandar)
                                    <span class="estandar-badge">Estándar</span>
                                @else
                                    <span class="especial-badge">Especial</span>
                                @endif
                            </td>
                            <td align="right">
                                Bs. {{ number_format($precioUnitario, 2) }}
                            </td>
                            <td align="center">
                                {{ $producto['cantidad'] }}
                            </td>
                            <td align="right">
                                Bs. {{ number_format($producto['monto'], 2) }}
                            </td>
                            <td align="right">
                                <strong>Bs. {{ number_format($montoPagar, 2) }}</strong>
                            </td>
                            <td align="right">
                                @if($montoPendiente > 0)
                                    <span style="color: #f59e0b; font-weight: bold;">
                                        Bs. {{ number_format($montoPendiente, 2) }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr class="totals-row">
                        <td colspan="4" align="right"><strong>Totales:</strong></td>
                        <td align="right"><strong>Bs. {{ number_format($totalCategoria, 2) }}</strong></td>
                        <td align="right"><strong>Bs. {{ number_format($totalAPagar, 2) }}</strong></td>
                        <td align="right">
                            @if($totalPendiente > 0)
                                <strong style="color: #f59e0b;">
                                    Bs. {{ number_format($totalPendiente, 2) }}
                                </strong>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach
    
    <div class="page-break"></div>
    
    <!-- Detalle de pedidos -->
    <h2 class="section-title">Detalle de Pedidos</h2>
    
    @foreach($pedidos as $pedido)
        <div class="pedido-container">
            <div class="pedido-header">
                <div>
                    <span class="pedido-id">Pedido #{{ $pedido->id }}</span>
                    <span class="pedido-estado {{ $pedido->estado ? 'estado-completado' : 'estado-proceso' }}">
                        {{ $pedido->estado ? 'Completado' : 'En proceso' }}
                    </span>
                </div>
                <div class="pedido-fecha">{{ $pedido->created_at->format('d/m/Y H:i') }}</div>
            </div>
            
            <div class="pedido-info">
                <div class="pedido-cliente">
                    <div class="pedido-cliente-label">Cliente</div>
                    <div class="pedido-cliente-nombre">
                        {{ $pedido->user->nombre }} {{ $pedido->user->apellido }}
                    </div>
                    <div>{{ $pedido->user->email }}</div>
                    @if(isset($pedido->user->telefono))
                        <div>Tel: {{ $pedido->user->telefono }}</div>
                    @endif
                </div>
                <div class="pedido-total">
                    <div class="pedido-total-label">Subtotal</div>
                    <div class="pedido-total-valor">Bs. {{ number_format($pedido->total_amount, 2) }}</div>
                    @if($pedido->total_to_pay && $pedido->total_to_pay != $pedido->total_amount)
                        <div class="pedido-total-label">A Pagar</div>
                        <div class="pedido-total-valor">Bs. {{ number_format($pedido->total_to_pay, 2) }}</div>
                    @endif
                    @if($pedido->pending > 0)
                        <div class="pedido-total-label" style="color: #f59e0b;">Pendiente</div>
                        <div class="pedido-total-valor" style="color: #f59e0b;">Bs. {{ number_format($pedido->pending, 2) }}</div>
                    @endif
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th width="25%">Producto</th>
                        <th width="15%">Modelo/Color</th>
                        <th width="20%">Precios</th>
                        <th width="10%">Cant.</th>
                        <th width="15%">Subtotal</th>
                        <th width="15%">Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pedido->productos as $producto)
                        @php
                            // Determinar el precio correcto según el tipo (preventa o regular)
                            $precioAplicado = $producto->pivot->es_preventa ? $producto->pivot->precio_preventa : $producto->pivot->precio;
                            $subtotal = $precioAplicado * $producto->pivot->cantidad;
                            
                            // Obtener los valores de cantidades mínimas y máximas
                            $cantidadMinima = $producto->cantidad_minima ?? $producto->pivot->cantidad_minima ?? 'N/A';
                            $cantidadMaxima = $producto->cantidad_maxima ?? $producto->pivot->cantidad_maxima ?? 'N/A';
                            $cantidadMinimaPreventa = $producto->cantidad_minima_preventa ?? $producto->pivot->cantidad_minima_preventa ?? 'N/A';
                            $cantidadMaximaPreventa = $producto->cantidad_maxima_preventa ?? $producto->pivot->cantidad_maxima_preventa ?? 'N/A';
                        @endphp
                        <tr>
                            <td>
                                <div class="producto-nombre">
                                    {{ $producto->nombre }}
                                    @if($producto->pivot->es_preventa)
                                        <span class="estandar-badge">Estándar</span>
                                    @else
                                        <span class="especial-badge">Especial</span>
                                    @endif
                                </div>
                                <div class="producto-categoria">
                                    {{ $producto->categoria->nombre }}
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
                            </td>
                            <td>
                                <div style="font-weight: bold; text-align: right;">
                                    Bs. {{ number_format($precioAplicado, 2) }}
                                </div>
                                <div style="font-size: 7px; color: #888; text-align: right;">
                                    @if($producto->pivot->es_preventa)
                                        <span style="text-decoration: line-through;">Especial: Bs. {{ number_format($producto->pivot->precio, 2) }}</span>
                                    @else
                                        <span style="text-decoration: line-through;">Estándar: Bs. {{ number_format($producto->pivot->precio_preventa, 2) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td align="center">
                                {{ $producto->pivot->cantidad }}
                            </td>
                            <td align="right">
                                <strong>Bs. {{ number_format($subtotal, 2) }}</strong>
                            </td>
                            <td>
                                @if($producto->pivot->es_preventa)
                                    <!-- Información de Estándar -->
                                    <div class="estandar-info">
                                        <div style="font-weight: bold; color: #9a3412;">Precio Estándar</div>
                                        
                                        <!-- Rango de cantidades -->
                                        <div class="quantity-range">
                                            <span class="quantity-range-label">Rango Estándar:</span> 
                                            {{ $cantidadMinimaPreventa }} - {{ $cantidadMaximaPreventa }}
                                        </div>
                                    </div>
                                @else
                                    <!-- Información de Especial -->
                                    <div class="especial-info">
                                        <div style="font-weight: bold; color: #1e40af;">Precio Especial</div>
                                        
                                        <!-- Rango de cantidades -->
                                        <div class="quantity-range">
                                            <span class="quantity-range-label">Rango Especial:</span> 
                                            {{ $cantidadMinima }} - {{ $cantidadMaxima }}
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <!-- Totales finales -->
            <div class="totals">
                <div class="total-row">
                    <span class="total-label">Subtotal:</span>
                    <span class="total-value">Bs. {{ number_format($pedido->total_amount, 2) }}</span>
                </div>
                
                @if($pedido->cupon)
                <div class="total-row">
                    <span class="total-label">Descuento:</span>
                    <span class="total-value" style="color: #10b981;">- Bs. {{ number_format($pedido->total_amount - $pedido->total_to_pay, 2) }}</span>
                </div>
                <div class="total-row">
                    <span class="total-label">Cupón:</span>
                    <span class="total-value">{{ $pedido->cupon->codigo }} ({{ $pedido->cupon->descuento }}%)</span>
                </div>
                @endif
                
                <div class="total-row grand-total">
                    <span class="total-label">Total a Pagar:</span>
                    <span class="total-value">Bs. {{ number_format($pedido->total_to_pay, 2) }}</span>
                </div>
                
                @if($pedido->pending > 0)
                <div class="total-row" style="color: #f59e0b;">
                    <span class="total-label">Pendiente:</span>
                    <span class="total-value">Bs. {{ number_format($pedido->pending, 2) }}</span>
                </div>
                @endif
            </div>
            
            <div class="pedido-resumen">
                <div class="pedido-metodo">
                    <span class="pedido-metodo-label">Método de pago:</span>
                    <span class="pedido-metodo-valor">
                        {{ $pedido->payment_method == 'in-person' ? 'En persona' : ($pedido->payment_method == 'qr' ? 'Pago QR' : ucfirst($pedido->payment_method)) }}
                    </span>
                </div>
                
                @if($pedido->voucher)
                    <div class="pedido-descuento">
                        <span class="pedido-descuento-label">Comprobante:</span>
                        <span class="pedido-descuento-valor">Disponible</span>
                    </div>
                @endif
            </div>
            
            @if($pedido->voucher)
                <div class="voucher-container">
                    <div class="voucher-badge">COMPROBANTE</div>
                    <div class="voucher-details">
                        <div class="voucher-info">
                            <div class="voucher-title">Información del Pago</div>
                            <div style="font-size: 8px;">
                                <div>Referencia: {{ $pedido->voucher }}</div>
                                <div>Fecha: {{ \Carbon\Carbon::parse($pedido->updated_at)->format('d/m/Y H:i') }}</div>
                                <div>Monto: Bs. {{ number_format($pedido->total_to_pay, 2) }}</div>
                            </div>
                        </div>
                        <div class="voucher-image">
                            <img src="{{ asset('vouchers/'.$pedido->voucher) }}" alt="Comprobante de pago">
                        </div>
                    </div>
                </div>
            @endif
        </div>
        
        @if(!$loop->last)
            <div style="margin-bottom: 20px;"></div>
            @if($loop->iteration % 2 == 0)
                <div class="page-break"></div>
            @endif
        @endif
    @endforeach
    
    <div class="footer">
        <p>© {{ date('Y') }} Neo Tech Bol - Todos los derechos reservados</p>
        <p>Este es un documento generado automáticamente. Fecha de generación: {{ $fechaGeneracion }}</p>
    </div>
</body>
</html>