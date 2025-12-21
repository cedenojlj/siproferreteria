<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Compras</title>
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
        .currency { text-align: right; }
        .page-break { page-break-after: always; }
        .company-info { margin-bottom: 20px; }
        .company-info p { margin: 2px 0; }
    </style>
</head>
<body>
    <div class="company-info">
        <h2>{{ $company->name ?? 'Ferretería' }}</h2>
        <p>RIF: {{ $company->rif ?? 'J-00000000-0' }}</p>
        <p>Dirección: {{ $company->address ?? '' }}</p>
        <p>Teléfono: {{ $company->phone ?? '' }}</p>
    </div>

    <div class="header">
        <h1>Reporte de Compras</h1>
        <p>Fecha de generación: {{ date('d/m/Y H:i') }}</p>
        @if(request('start_date') || request('end_date'))
        <p>Período: {{ request('start_date') ? date('d/m/Y', strtotime(request('start_date'))) : 'Inicio' }} 
           al {{ request('end_date') ? date('d/m/Y', strtotime(request('end_date'))) : 'Fin' }}</p>
        @endif
    </div>
    
    <table>
        <thead>
            <tr>
                <th># Factura</th>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>RIF Proveedor</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">IVA</th>
                <th class="text-right">Total</th>
                <th>Moneda</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $purchase)
            <tr>
                <td>{{ $purchase->invoice_number }}</td>
                <td>{{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $purchase->supplier->name }}</td>
                <td>{{ $purchase->supplier->rif }}</td>
                <td class="currency">{{ number_format($purchase->subtotal, 2) }}</td>
                <td class="currency">{{ number_format($purchase->tax, 2) }}</td>
                <td class="currency">{{ number_format($purchase->total, 2) }}</td>
                <td>{{ $purchase->payment_currency }}</td>
                <td>
                    @if($purchase->status == 'received')
                        <span style="color: green;">Recibido</span>
                    @elseif($purchase->status == 'pending')
                        <span style="color: orange;">Pendiente</span>
                    @else
                        <span style="color: red;">Cancelado</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td colspan="4">TOTALES</td>
                <td class="currency">{{ number_format($purchases->sum('subtotal'), 2) }}</td>
                <td class="currency">{{ number_format($purchases->sum('tax'), 2) }}</td>
                <td class="currency">{{ number_format($purchases->sum('total'), 2) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 40px;">
        <h3>Resumen por Moneda</h3>
        <table style="width: 50%;">
            <tr>
                <th>Moneda</th>
                <th class="text-right">Total Compras</th>
                <th class="text-right">Cantidad</th>
            </tr>
            @php
                $totalsByCurrency = $purchases->groupBy('payment_currency')->map(function($group) {
                    return [
                        'total' => $group->sum('total'),
                        'count' => $group->count()
                    ];
                });
            @endphp
            @foreach($totalsByCurrency as $currency => $data)
            <tr>
                <td>{{ $currency }}</td>
                <td class="currency">{{ number_format($data['total'], 2) }}</td>
                <td class="text-right">{{ $data['count'] }}</td>
            </tr>
            @endforeach
        </table>
    </div>

    <div style="margin-top: 30px; text-align: center; font-size: 10px;">
        <p>Total de compras: {{ $purchases->count() }}</p>
        <p>Generado por: {{ Auth::user()->name ?? 'Sistema' }}</p>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>