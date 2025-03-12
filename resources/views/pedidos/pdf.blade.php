<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #{{ $pedido->id }}</title>
    <style>
        /* Estilos base */
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #333333;
            font-size: 9px;
            line-height: 1.3;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 5px;
        }
        
        /* Encabezado */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            border-bottom: 1px solid #4f46e5;
            margin-bottom: 8px;
        }
        .header-left {
            text-align: left;
        }
        .header-right {
            text-align: right;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #4f46e5;
            margin: 0;
        }
        .document-title {
            font-size: 10px;
            color: #666;
            margin: 1px 0;
        }
        .order-number {
            font-size: 10px;
            color: #4f46e5;
            margin: 1px 0;
        }
        
        /* Layout de columnas */
        .three-columns {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
            gap: 8px;
        }
        .column {
            flex: 1;
        }
        
        /* Secciones */
        .section {
            margin: 5px 0;
            padding: 5px;
            background-color: #f9fafb;
            border-radius: 4px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .section-title {
            font-size: 10px;
            color: #4f46e5;
            margin: 0 0 4px 0;
            padding-bottom: 2px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        /* Filas de información */
        .info-row {
            display: flex;
            margin-bottom: 2px;
        }
        .info-label {
            font-weight: bold;
            width: 35%;
            padding-right: 3px;
        }
        .info-value {
            width: 65%;
        }
        
        /* Tabla de productos */
        table.products {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0;
            font-size: 8px;
        }
        table.products th {
            background-color: #4f46e5;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 3px;
        }
        table.products td {
            padding: 3px;
            border-bottom: 1px solid #e5e7eb;
        }
        table.products tr:nth-child(even) {
            background-color: #f3f4f6;
        }
        
        /* Totales */
        .totals {
            margin-top: 5px;
            text-align: right;
        }
        .total-row {
            margin: 2px 0;
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
            width: 60px;
            text-align: right;
        }
        .grand-total {
            font-size: 10px;
            font-weight: bold;
            color: #4f46e5;
            margin-top: 3px;
            padding-top: 3px;
            border-top: 1px solid #4f46e5;
        }
        
        /* Estados y badges */
        .payment-status {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 8px;
        }
        .status-completed {
            background-color: #10b981;
            color: white;
        }
        .status-pending {
            background-color: #f59e0b;
            color: white;
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
        
        /* Cupón */
        .coupon {
            background-color: #fdf2f8;
            border: 1px dashed #ec4899;
            padding: 4px;
            border-radius: 4px;
            margin-top: 4px;
        }
        .coupon-title {
            color: #ec4899;
            font-weight: bold;
            font-size: 9px;
            margin-bottom: 2px;
        }
        
        /* Pie de página */
        .footer {
            margin-top: 8px;
            padding-top: 5px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 7px;
        }
        .thank-you {
            text-align: center;
            margin: 8px 0;
            font-size: 10px;
            color: #4f46e5;
        }
        
        /* Ajustes de página */
        @page {
            margin: 0.3cm;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">Mi Empresa</div>
                <div class="document-title">Comprobante de Pedido</div>
            </div>
            <div class="header-right">
                <div class="order-number">Pedido #{{ $pedido->id }}</div>
                <div>{{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y H:i') }}</div>
                <div>
                    <span class="payment-status {{ $pedido->estado ? 'status-completed' : 'status-pending' }}">
                        {{ $pedido->estado ? 'Completado' : 'Pendiente' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Información principal en 3 columnas -->
        <div class="three-columns">
            <!-- Columna 1: Cliente -->
            <div class="column">
                <div class="section">
                    <h2 class="section-title">Cliente</h2>
                    <div class="info-row">
                        <div class="info-label">Nombre:</div>
                        <div class="info-value">{{ $pedido->user->nombre }} {{ $pedido->user->apellido }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">CI/NIT:</div>
                        <div class="info-value">{{ $pedido->user->ci ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Teléfono:</div>
                        <div class="info-value">{{ $pedido->user->telefono ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email:</div>
                        <div class="info-value">{{ $pedido->user->email }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Columna 2: Detalles del Pedido -->
            <div class="column">
                <div class="section">
                    <h2 class="section-title">Detalles del Pedido</h2>
                    <div class="info-row">
                        <div class="info-label">Método:</div>
                        <div class="info-value">{{ $pedido->payment_method == 'in-person' ? 'En persona' : ($pedido->payment_method == 'qr' ? 'Pago QR' : ucfirst($pedido->payment_method)) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Dirección:</div>
                        <div class="info-value">{{ $pedido->user->direccion ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Ciudad:</div>
                        <div class="info-value">{{ ucfirst($pedido->user->departamento ?? 'N/A') }}</div>
                    </div>
                </div>
            </div>
            
            <!-- Columna 3: Resumen Financiero -->
            <div class="column">
                <div class="section">
                    <h2 class="section-title">Resumen Financiero</h2>
                    <div class="info-row">
                        <div class="info-label">Subtotal:</div>
                        <div class="info-value">Bs {{ number_format($pedido->total_amount, 2) }}</div>
                    </div>
                    
                    @if($pedido->cupon)
                    <div class="info-row">
                        <div class="info-label">Descuento:</div>
                        <div class="info-value">Bs {{ number_format($pedido->total_amount - $pedido->total_to_pay, 2) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Cupón:</div>
                        <div class="info-value">{{ $pedido->cupon->codigo }} ({{ $pedido->cupon->descuento }}%)</div>
                    </div>
                    @endif
                    
                    <div class="info-row" style="font-weight: bold; color: #4f46e5;">
                        <div class="info-label">Total:</div>
                        <div class="info-value">Bs {{ number_format($pedido->total_to_pay, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comprobante de pago (si existe) -->
        @if($pedido->voucher)
        <div class="voucher-container">
            <div class="voucher-badge">COMPROBANTE</div>
            <div class="voucher-details">
                <div class="voucher-info">
                    <div class="voucher-title">Información del Pago</div>
                    <div class="info-row">
                        <div class="info-label">Referencia:</div>
                        <div class="info-value">{{ $pedido->voucher }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Fecha:</div>
                        <div class="info-value">{{ \Carbon\Carbon::parse($pedido->updated_at)->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Monto:</div>
                        <div class="info-value">Bs {{ number_format($pedido->total_to_pay, 2) }}</div>
                    </div>
                </div>
                <div class="voucher-image">
                    <img src="{{ asset('storage/vouchers/'.$pedido->voucher) }}" alt="Comprobante de pago">
                </div>
            </div>
        </div>
        @endif

        <!-- Tabla de productos -->
        <div class="section">
            <h2 class="section-title">Productos</h2>
            <table class="products">
                <thead>
                    <tr>
                        <th width="30%">Producto</th>
                        <th width="20%">Modelo</th>
                        <th width="15%">Color</th>
                        <th width="5%">Cant.</th>
                        <th width="15%">Precio</th>
                        <th width="15%">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedido->productos as $producto)
                    <tr>
                        <td>{{ $producto->nombre }}</td>
                        <td>
                            @if($producto->pivot->modelo_id)
                                @php
                                    $modelo = $producto->modelos->firstWhere('id', $producto->pivot->modelo_id);
                                @endphp
                                {{ $modelo ? $modelo->nombre : 'N/A' }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $producto->pivot->color ?? 'N/A' }}</td>
                        <td align="center">{{ $producto->pivot->cantidad }}</td>
                        <td align="right">Bs {{ number_format($producto->pivot->precio, 2) }}</td>
                        <td align="right">Bs {{ number_format($producto->pivot->precio * $producto->pivot->cantidad, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Totales finales -->
            <div class="totals">
                <div class="total-row">
                    <span class="total-label">Subtotal:</span>
                    <span class="total-value">Bs {{ number_format($pedido->total_amount, 2) }}</span>
                </div>
                
                @if($pedido->cupon)
                <div class="total-row">
                    <span class="total-label">Descuento:</span>
                    <span class="total-value">Bs {{ number_format($pedido->total_amount - $pedido->total_to_pay, 2) }}</span>
                </div>
                @endif
                
                <div class="total-row grand-total">
                    <span class="total-label">Total a Pagar:</span>
                    <span class="total-value">Bs {{ number_format($pedido->total_to_pay, 2) }}</span>
                </div>
                
                @if($pedido->pending > 0)
                <div class="total-row">
                    <span class="total-label">Pendiente:</span>
                    <span class="total-value">Bs {{ number_format($pedido->pending, 2) }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Mensaje de agradecimiento -->
        <div class="thank-you">
            ¡Gracias por su compra!
        </div>

        <!-- Pie de página -->
        <div class="footer">
            <p>Este documento es un comprobante oficial de su pedido.</p>
            <p>Para consultas: +591 XXXXXXXX | info@miempresa.com | www.miempresa.com</p>
            <p>© {{ date('Y') }} Mi Empresa. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>