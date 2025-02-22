<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido #{{ $pedido->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        h2 {
            color: #555;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background-color: #fff;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            font-weight: bold;
            font-size: 1.2em;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h1>Detalles del Pedido #{{ $pedido->id }}</h1>

    <div class="info">
        <h2>Información del Cliente</h2>
        <p><strong>Nombre:</strong> {{ $pedido->user->nombre }} {{ $pedido->user->apellido }}</p>
        <p><strong>CI:</strong> {{ $pedido->user->ci }}</p>
        <p><strong>NIT:</strong> {{ $pedido->user->nit }}</p>
        <p><strong>Dirección:</strong> {{ $pedido->user->direccion }}</p>
        <p><strong>Teléfono:</strong> {{ $pedido->user->telefono }}</p>
        <p><strong>Email:</strong> {{ $pedido->user->email }}</p>
        <p><strong>Fecha de Pedido:</strong> {{ \Carbon\Carbon::parse($pedido->created_at)->format('d/m/Y H:i') }}</p>
    </div>

    <h2>Resumen del Pedido</h2>
    <p><strong>Total a Pagar:</strong> ${{ number_format($pedido->total_to_pay, 2) }}</p>
    <p><strong>Estado:</strong> {{ $pedido->estado ? 'Completado' : 'Pendiente' }}</p>

    <h2>Productos</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pedido->productos as $producto)
                <tr>
                    <td>{{ $producto->id }}</td>
                    <td>{{ $producto->nombre }}</td>
                    <td>{{ $producto->descripcion }}</td>
                    <td>{{ $producto->pivot->cantidad }}</td>
                    <td>{{ number_format($producto->precio, 2) }}Bs</td>
                    <td>{{ number_format($producto->precio * $producto->pivot->cantidad, 2) }}Bs</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <p>Total del Pedido: {{ number_format($pedido->total_amount, 2) }}Bs</p>
    </div>
</body>
</html>