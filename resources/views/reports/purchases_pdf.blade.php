<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Compras</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: normal;
        }
        .company-info {
            text-align: left;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
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
        .total-row td {
            font-weight: bold;
        }
        .footer {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
        .page-number:before {
            content: "Página " counter(page);
        }
    </style>
</head>
<body>
    <div class="footer">
        <span class="page-number"></span>
    </div>

    <div class="header">
        @if($company)
            <h1>{{ $company->name }}</h1>
            <h2>Reporte de Compras</h2>
        @else
            <h1>Reporte de Compras</h1>
        @endif
    </div>

    @if($company)
        <div class="company-info">
            <strong>Dirección:</strong> {{ $company->address }}<br>
            <strong>Teléfono:</strong> {{ $company->phone }}<br>
            <strong>Email:</strong> {{ $company->email }}
        </div>
    @endif
    
    <p><strong>Fecha del reporte:</strong> {{ date('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>N° Factura</th>
                <th>Proveedor</th>
                <th>Moneda</th>
                <th>Subtotal</th>
                <th>Impuesto</th>
                <th>Total</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $purchase)
                <tr>
                    <td>{{ $purchase->created_at->format('d/m/Y') }}</td>
                    <td>{{ $purchase->invoice_number }}</td>
                    <td>{{ $purchase->supplier->name }}</td>
                    <td>{{ $purchase->payment_currency }}</td>
                    <td>{{ number_format($purchase->subtotal, 2) }}</td>
                    <td>{{ number_format($purchase->tax, 2) }}</td>
                    <td>{{ number_format($purchase->total, 2) }}</td>
                    <td>{{ ucfirst($purchase->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">No hay compras para mostrar.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" style="text-align: right;">Total General (USD):</td>
                <td colspan="2">{{ number_format($purchases->where('payment_currency', 'USD')->sum('total'), 2) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="6" style="text-align: right;">Total General (BS):</td>
                <td colspan="2">{{ number_format($purchases->where('payment_currency', 'BS')->sum('total'), 2) }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
