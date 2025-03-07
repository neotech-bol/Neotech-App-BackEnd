<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #{{ $pedido->id }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
            color: #333333;
            font-size: 12px;
            line-height: 1.4;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 10px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 2px solid #4f46e5;
        }
        .header-left {
            text-align: left;
        }
        .header-right {
            text-align: right;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
            margin: 0;
        }
        .document-title {
            font-size: 16px;
            color: #666;
            margin: 2px 0;
        }
        .order-number {
            font-size: 14px;
            color: #4f46e5;
            margin: 2px 0;
        }
        .two-columns {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            gap: 15px;
        }
        .column {
            flex: 1;
        }
        .section {
            margin: 10px 0;
            padding: 10px;
            background-color: #f9fafb;
            border-radius: 6px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }
        .section-title {
            font-size: 14px;
            color: #4f46e5;
            margin: 0 0 8px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-row {
            display: flex;
            margin-bottom: 4px;
        }
        .info-label {
            font-weight: bold;
            width: 35%;
            padding-right: 5px;
        }
        .info-value {
            width: 65%;
        }
        table.products {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
            font-size: 11px;
        }
        table.products th {
            background-color: #4f46e5;
            color: white;
            font-weight: bold;
            text-align: left;
            padding: 6px;
        }
        table.products td {
            padding: 6px;
            border-bottom: 1px solid #e5e7eb;
        }
        table.products tr:nth-child(even) {
            background-color: #f3f4f6;
        }
        .totals {
            margin-top: 10px;
            text-align: right;
        }
        .total-row {
            margin: 3px 0;
        }
        .total-label {
            display: inline-block;
            width: 150px;
            text-align: right;
            font-weight: bold;
            margin-right: 10px;
        }
        .total-value {
            display: inline-block;
            width: 80px;
            text-align: right;
        }
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            color: #4f46e5;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 2px solid #4f46e5;
        }
        .payment-status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 11px;
        }
        .status-completed {
            background-color: #10b981;
            color: white;
        }
        .status-pending {
            background-color: #f59e0b;
            color: white;
        }
        .voucher-container {
            margin-top: 10px;
            padding: 10px;
            background-color: #f0f9ff;
            border-radius: 6px;
            border: 1px solid #0ea5e9;
            position: relative;
        }
        .voucher-badge {
            position: absolute;
            top: -10px;
            right: 10px;
            background-color: #0ea5e9;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .voucher-title {
            color: #0ea5e9;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 13px;
        }
        .voucher-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .voucher-info {
            flex: 2;
        }
        .voucher-qr {
            flex: 1;
            text-align: center;
        }
        .voucher-qr img {
            width: 80px;
            height: 80px;
        }
        .footer {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 10px;
        }
        .thank-you {
            text-align: center;
            margin: 15px 0;
            font-size: 14px;
            color: #4f46e5;
        }
        .coupon {
            background-color: #fdf2f8;
            border: 1px dashed #ec4899;
            padding: 8px;
            border-radius: 6px;
            margin-top: 8px;
        }
        .coupon-title {
            color: #ec4899;
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 5px;
        }
        @page {
            margin: 0.5cm;
        }
    </style>
</head>
<body>
    <div class="container">
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

        <div class="two-columns">
            <div class="column">
                <div class="section">
                    <h2 class="section-title">Información del Cliente</h2>
                    <div class="info-row">
                        <div class="info-label">Nombre:</div>
                        <div class="info-value">{{ $pedido->user->nombre }} {{ $pedido->user->apellido }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">CI/NIT:</div>
                        <div class="info-value">{{ $pedido->user->ci }} / {{ $pedido->user->nit }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Teléfono:</div>
                        <div class="info-value">{{ $pedido->user->telefono }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Email:</div>
                        <div class="info-value">{{ $pedido->user->email }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Dirección:</div>
                        <div class="info-value">{{ $pedido->user->direccion }}, {{ ucfirst($pedido->user->departamento) }}</div>
                    </div>
                </div>
            </div>
            
            <div class="column">
                <div class="section">
                    <h2 class="section-title">Detalles del Pedido</h2>
                    <div class="info-row">
                        <div class="info-label">Método de Pago:</div>
                        <div class="info-value">{{ $pedido->payment_method == 'in-person' ? 'En persona' : ucfirst($pedido->payment_method) }}</div>
                    </div>
                    
                    @if($pedido->cupon)
                    <div class="coupon">
                        <div class="coupon-title">Cupón Aplicado</div>
                        <div class="info-row">
                            <div class="info-label">Código:</div>
                            <div class="info-value">{{ $pedido->cupon->codigo }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Descuento:</div>
                            <div class="info-value">
                                {{ $pedido->cupon->tipo == 'fijo' ? 'Bs '.number_format($pedido->cupon->descuento, 2) : number_format($pedido->cupon->descuento, 2).'%' }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($pedido->voucher)
        <div class="voucher-container">
            <div class="voucher-badge">COMPROBANTE DE PAGO</div>
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
                <div class="voucher-qr">
                    <img id="qrcode" alt="QR Code">
                </div>
            </div>
        </div>
        @endif

        <div class="section">
            <h2 class="section-title">Productos</h2>
            <table class="products">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Modelo</th>
                        <th>Color</th>
                        <th>Cant.</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedido->productos as $producto)
                    <tr>
                        <td>{{ $producto->nombre }}</td>
                        <td>{{ $producto->pivot->modelo_id ? $producto->modelos->firstWhere('id', $producto->pivot->modelo_id)->nombre : 'N/A' }}</td>
                        <td>{{ $producto->pivot->color ?? 'N/A' }}</td>
                        <td>{{ $producto->pivot->cantidad }}</td>
                        <td>Bs {{ number_format($producto->pivot->modelo_id ? $producto->modelos->firstWhere('id', $producto->pivot->modelo_id)->precio : $producto->precio, 2) }}</td>
                        <td>Bs {{ number_format(($producto->pivot->modelo_id ? $producto->modelos->firstWhere('id', $producto->pivot->modelo_id)->precio : $producto->precio) * $producto->pivot->cantidad, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

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

        <div class="thank-you">
            ¡Gracias por su compra!
        </div>

        <div class="footer">
            <p>Este documento es un comprobante oficial de su pedido.</p>
            <p>Para consultas: +591 XXXXXXXX | info@miempresa.com | www.miempresa.com</p>
            <p>© {{ date('Y') }} Mi Empresa. Todos los derechos reservados.</p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <script>
        window.onload = function() {
            var typeNumber = 4;
            var errorCorrectionLevel = 'L';
            var qr = qrcode(typeNumber, errorCorrectionLevel);
            qr.addData('{{ url("/pedidos/{$pedido->id}") }}');
            qr.make();
            document.getElementById('qrcode').src = qr.createDataURL();
        }
    </script>
</body>
</html>