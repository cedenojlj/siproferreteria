<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Devolución</title>
    <style>
        body { font-family: sans-serif; margin: 40px; }
        .header { text-align: center; margin-bottom: 40px; }
        .header h1 { margin: 0; }
        .header h2 { margin: 0; font-size: 1.2em; font-weight: normal; }
        .details, .items-table { width: 100%; margin-bottom: 20px; }
        .details td { padding: 5px; }
        .items-table { border-collapse: collapse; }
        .items-table th, .items-table td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; }
        .total { text-align: right; margin-top: 20px; }
        .total strong { font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Comprobante de Devolución</h1>
        <h2>ID de Devolución: {{ $refund->id }}</h2>
    </div>

    <table class="details">
        <tr>
            <td><strong>Fecha:</strong></td>
            <td>{{ $refund->created_at->format('d/m/Y') }}</td>
            <td><strong>Cliente:</strong></td>
            <td>{{ $refund->customer->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td><strong>Venta Original:</strong></td>
            <td>#{{ $refund->sale_id }}</td>
            <td><strong>Atendido por:</strong></td>
            <td>{{ $refund->user->name ?? 'N/A' }}</td>
        </tr>
         <tr>
            <td><strong>Motivo:</strong></td>
            <td colspan="3">{{ $refund->reason }}</td>
        </tr>
    </table>

    <h3>Productos Devueltos</h3>
    <table class="items-table">
        <thead>
            <tr>
                <th>ID Producto</th>
                <th>Producto</th>
                <th>Cantidad Devuelta</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($refund->refundItems as $item)
                <tr>
                    <td>{{ $item->product_id }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->unit_price_usd, 2) }}</td>
                    <td>${{ number_format($item->subtotal_usd, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total">
        <h3>Total Devolución: <strong>${{ number_format($refund->total_amount_usd, 2) }}</strong></h3>
    </div>

</body>
</html>
