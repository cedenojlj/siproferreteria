<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Compra - {{ $purchase->invoice_number }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #333;
            font-size: 12px;
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
        .company-details, .purchase-details, .supplier-details {
            margin-bottom: 20px;
        }
        .company-details {
            text-align: right;
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
        .totals {
            width: 40%;
            float: right;
            margin-top: 20px;
        }
        .totals table {
            width: 100%;
        }
        .totals th, .totals td {
            border: none;
            text-align: right;
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
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="footer">
        <span class="page-number"></span>
    </div>

    <table style="border: none; width: 100%;">
        <tr>
            <td style="border: none; width: 50%; vertical-align: top;">
                @if($company)
                    <h1 style="margin:0;">{{ $company->name }}</h1>
                    <p style="margin:0;">{{ $company->address }}</p>
                    <p style="margin:0;">{{ $company->phone }}</p>
                    <p style="margin:0;">{{ $company->email }}</p>
                @endif
            </td>
            <td style="border: none; width: 50%; text-align: right; vertical-align: top;">
                <h2 style="margin:0;">COMPRA</h2>
                <p style="margin:0;"><strong>N° Factura:</strong> {{ $purchase->invoice_number }}</p>
                <p style="margin:0;"><strong>Fecha:</strong> {{ $purchase->created_at->format('d/m/Y') }}</p>
            </td>
        </tr>
    </table>

    <hr>
    
    <div class="supplier-details">
        <strong>Proveedor:</strong><br>
        <strong>Nombre:</strong> {{ $purchase->supplier->name }}<br>
        <strong>Dirección:</strong> {{ $purchase->supplier->address ?? 'N/A' }}<br>
        <strong>Teléfono:</strong> {{ $purchase->supplier->phone ?? 'N/A' }}<br>
    </div>

    <div class="purchase-details">
        <strong>Detalles de la Compra:</strong><br>
        <strong>Estado:</strong> {{ ucfirst($purchase->status) }}<br>
        <strong>Moneda de Pago:</strong> {{ $purchase->payment_currency }}<br>
        @if($purchase->payment_currency == 'BS')
        <strong>Tasa de Cambio:</strong> {{ number_format($purchase->exchange_rate, 2) }}<br>
        @endif
        <strong>Registrado por:</strong> {{ $purchase->user->name }}
    </div>

    <h3>Items de la Compra</h3>
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchase->purchaseItems as $item)
                <tr>
                    <td>{{ $item->product->barcode ?? 'N/A' }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td style="text-align: center;">{{ $item->quantity }}</td>
                    <td style="text-align: right;">{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($item->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="clearfix"></div>

    <div class="totals">
        <table>
            <tr>
                <th>Subtotal:</th>
                <td>{{ number_format($purchase->subtotal, 2) }} {{ $purchase->payment_currency }}</td>
            </tr>
            <tr>
                <th>Impuesto (16%):</th>
                <td>{{ number_format($purchase->tax, 2) }} {{ $purchase->payment_currency }}</td>
            </tr>
            <tr>
                <th style="font-size: 14px;"><strong>Total:</strong></th>
                <td style="font-size: 14px;"><strong>{{ number_format($purchase->total, 2) }} {{ $purchase->payment_currency }}</strong></td>
            </tr>
        </table>
    </div>

    @if($purchase->notes)
        <div style="margin-top: 50px;">
            <strong>Notas:</strong>
            <p>{{ $purchase->notes }}</p>
        </div>
    @endif
</body>
</html>
