<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos Más Vendidos</title>
    <style>
        /* Estilos consistentes con otros reportes */
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
        .header, .footer { text-align: center; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { position: fixed; bottom: 0; width: 100%; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Productos Más Vendidos</h1>
        @if(isset($company))
            <p>{{ $company->name }}</p>
            <p>{{ $company->address }}</p>
        @endif
        <p>Generado el: {{ $generationDate }}</p>
        @if($startDate && $endDate)
            <p>Periodo del: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
        @else
            <p>Periodo: Todos los registros</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Producto</th>
                <th>Código de Barras</th>
                <th class="text-center">Cantidad Vendida</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $index => $product)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->barcode }}</td>
                    <td class="text-center">{{ $product->total_quantity_sold }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">No hay datos de ventas para mostrar en el periodo seleccionado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Página <script type="text/php">if(isset($pdf)) { echo $PAGE_NUM; } else { echo '1'; }</script> de <script type="text/php">if(isset($pdf)) { echo $PAGE_COUNT; } else { echo '1'; }</script></p>
    </div>
</body>
</html>
