<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario</title>
    <style>
        /* Estilos CSS para el PDF. Mantenerlos simples. */
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .header p { margin: 0; font-size: 12px; }
        .total-row td { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $company->name ?? 'Nombre de la Ferretería' }}</h1>
        <p>Reporte de Inventario General</p>
        <p>Generado el: {{ $generationDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>SKU/Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Stock Actual</th>
                <th>Precio de Costo</th>
                <th>Valor de Inventario (Costo)</th>
                <th>Precio de Venta</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalInventoryValue = 0;
            @endphp
            @forelse ($products as $product)
                @php
                    $inventoryValue = $product->current_stock * $product->cost;
                    $totalInventoryValue += $inventoryValue;
                @endphp
                <tr>
                    <td>{{ $product->barcode }}</td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                    <td>{{ $product->current_stock }}</td>
                    <td>${{ number_format($product->cost, 2) }}</td>
                    <td>${{ number_format($inventoryValue, 2) }}</td>
                    <td>${{ number_format($product->usd_price, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No hay productos en el inventario.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" style="text-align: right;">Valor Total del Inventario (al costo):</td>
                <td colspan="2">${{ number_format($totalInventoryValue, 2) }}</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
