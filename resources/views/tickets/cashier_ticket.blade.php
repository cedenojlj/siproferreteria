<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta</title>
    <style>
        @page {
            margin: 3mm;
            size: 80mm auto; /* El alto se ajustará al contenido */
        }
        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            width: 74mm; /* Ancho un poco menor al del papel para evitar cortes */
            margin: 0;
            padding: 0;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .ticket-header, .ticket-footer {
            text-align: center;
        }
        .ticket-header h4, .ticket-header p {
            margin: 2px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5mm;
            margin-bottom: 5mm;
        }
        .items-table th, .items-table td {
            border-bottom: 1px dashed #000;
            padding: 3px 1px;
            font-size: 9pt;
        }
        .items-table th {
            text-align: left;
            border-top: 1px dashed #000;
        }
        .items-table .col-qty {
            width: 15%;
        }
        .items-table .col-desc {
            width: 55%;
        }
        .items-table .col-total {
            width: 30%;
        }
        .totals-table {
            width: 100%;
            margin-top: 5mm;
        }
        .totals-table td {
            padding: 1px;
        }
        hr.dashed {
            border: none;
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <header class="ticket-header">
            @if($company)
                <h4>{{ $company->name }}</h4>
                <p>{{ $company->address }}</p>
                <p>RIF: {{ $company->rif }}</p>
                <p>Tel: {{ $company->phone }}</p>
            @endif
            <hr class="dashed">
            <p><strong>TICKET DE VENTA</strong></p>
            <p>N°: {{ $sale->id }}</p>
            <p>Fecha: {{ $sale->created_at->format('d/m/Y H:i') }}</p>
            @if($sale->customer)
                <p>Cliente: {{ $sale->customer->name }}</p>
                <p>{{ $sale->customer->document_type }}: {{ $sale->customer->document }}</p>
            @endif
            <hr class="dashed">
        </header>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-qty">Cant</th>
                    <th class="col-desc">Descripción</th>
                    <th class="col-total text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->saleItems as $item)
                <tr>
                    <td>{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-right">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr class="dashed">

        <table class="totals-table">
            <tbody>
                <tr>
                    <td>SUBTOTAL:</td>
                    <td class="text-right">{{ number_format($sale->subtotal_usd, 2) }}</td>
                </tr>
                <tr>
                    <td>IVA:</td>
                    <td class="text-right">{{ number_format($sale->tax, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>TOTAL:</strong></td>
                    <td class="text-right"><strong>{{ number_format($sale->total_usd, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <footer class="ticket-footer">
            <hr class="dashed">
            <p>¡Gracias por su compra!</p>
        </footer>
    </div>
    <script>
        window.onload = function() {
            window.print();
             setTimeout(function() { window.close(); }, 500);
        }
    </script>
</body>
</html>
