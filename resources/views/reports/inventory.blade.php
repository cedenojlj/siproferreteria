<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Inventario</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .company-info { margin-bottom: 20px; }
        .company-info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .low-stock { background-color: #ffe6e6; }
        .out-of-stock { background-color: #ffcccc; }
        .currency { text-align: right; }
        .summary { margin-top: 30px; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table th { background-color: #e6f7ff; }
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="company-info">
        <h2>{{ $company->name ?? 'Ferretería' }}</h2>
        <p>RIF: {{ $company->rif ?? 'J-00000000-0' }}</p>
        <p>Fecha del reporte: {{ date('d/m/Y') }}</p>
    </div>

    <div class="header">
        <h1>Reporte de Inventario</h1>
        <p>Generado: {{ date('d/m/Y H:i') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th>Marca/Modelo</th>
                <th class="text-right">Stock Mínimo</th>
                <th class="text-right">Stock Actual</th>
                <th class="text-right">Diferencia</th>
                <th class="text-right">Precio Base BS</th>
                <th class="text-right">Precio USD</th>
                <th class="text-right">Costo</th>
                <th class="text-right">Valor en Inventario</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalValue = 0;
                $lowStockCount = 0;
                $outOfStockCount = 0;
            @endphp
            @foreach($products as $product)
            @php
                $stockDiff = $product->current_stock - $product->min_stock;
                $inventoryValue = $product->current_stock * $product->cost;
                $totalValue += $inventoryValue;
                
                if($product->current_stock == 0) {
                    $outOfStockCount++;
                } elseif($product->current_stock <= $product->min_stock) {
                    $lowStockCount++;
                }
            @endphp
            <tr class="{{ $product->current_stock == 0 ? 'out-of-stock' : ($product->current_stock <= $product->min_stock ? 'low-stock' : '') }}">
                <td>{{ $product->barcode ?? 'N/A' }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name ?? 'Sin categoría' }}</td>
                <td>{{ $product->brand ?? '' }} {{ $product->model ?? '' }}</td>
                <td class="text-right">{{ $product->min_stock }}</td>
                <td class="text-right">{{ $product->current_stock }}</td>
                <td class="text-right {{ $stockDiff < 0 ? 'text-red-600' : '' }}">
                    {{ $stockDiff }}
                </td>
                <td class="currency">{{ number_format($product->base_price, 2) }}</td>
                <td class="currency">{{ number_format($product->usd_price, 2) }}</td>
                <td class="currency">{{ number_format($product->cost, 2) }}</td>
                <td class="currency">{{ number_format($inventoryValue, 2) }}</td>
                <td class="text-center">
                    @if($product->current_stock == 0)
                        <span style="color: red; font-weight: bold;">AGOTADO</span>
                    @elseif($product->current_stock <= $product->min_stock)
                        <span style="color: orange; font-weight: bold;">BAJO STOCK</span>
                    @else
                        <span style="color: green;">OK</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <h3>Resumen del Inventario</h3>
        <table class="summary-table">
            <tr>
                <th>Total Productos</th>
                <th>Productos Activos</th>
                <th>Con Bajo Stock</th>
                <th>Agotados</th>
                <th>Valor Total Inventario</th>
            </tr>
            <tr>
                <td class="text-center">{{ $products->count() }}</td>
                <td class="text-center">{{ $products->where('is_active', true)->count() }}</td>
                <td class="text-center">{{ $lowStockCount }}</td>
                <td class="text-center">{{ $outOfStockCount }}</td>
                <td class="text-right">{{ number_format($totalValue, 2) }} BS</td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 30px;">
        <h4>Productos con Bajo Stock (≤ Stock Mínimo)</h4>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Stock Actual</th>
                    <th>Stock Mínimo</th>
                    <th>Diferencia</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products->where('current_stock', '<=', 'min_stock') as $product)
                <tr class="low-stock">
                    <td>{{ $product->name }}</td>
                    <td class="text-right">{{ $product->current_stock }}</td>
                    <td class="text-right">{{ $product->min_stock }}</td>
                    <td class="text-right">{{ $product->current_stock - $product->min_stock }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px; text-align: center; font-size: 10px;">
        <p>Reporte generado por: {{ Auth::user()->name ?? 'Sistema' }}</p>
        <p>Fecha: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Página 1 de 1</p>
    </div>
</body>
</html>