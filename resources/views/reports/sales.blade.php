<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; }
        .text-right { text-align: right; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ventas</h1>
        <p>Fecha: {{ date('d/m/Y') }}</p>
        <p>Período: {{ request('start_date') }} - {{ request('end_date') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th># Factura</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Vendedor</th>
                <th class="text-right">Total BS</th>
                <th class="text-right">Total USD</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sales as $sale)
            <tr>
                <td>{{ $sale->invoice_number }}</td>
                <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $sale->customer->name ?? 'Consumidor Final' }}</td>
                <td>{{ $sale->seller->name }}</td>
                <td class="text-right">{{ number_format($sale->total_local, 2) }}</td>
                <td class="text-right">{{ number_format($sale->total_usd, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="4">TOTALES</td>
                <td class="text-right">{{ number_format($sales->sum('total_local'), 2) }}</td>
                <td class="text-right">{{ number_format($sales->sum('total_usd'), 2) }}</td>
            </tr>
        </tfoot>
    </table>
    
    <div style="margin-top: 30px; text-align: center;">
        <p>Total de ventas: {{ $sales->count() }}</p>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>