<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Productos Más Vendidos</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .company-info { margin-bottom: 20px; }
        .company-info p { margin: 2px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .currency { text-align: right; }
        .rank { text-align: center; width: 50px; }
        .top-1 { background-color: #fffacd; }
        .top-2 { background-color: #f0f8ff; }
        .top-3 { background-color: #f5f5f5; }
        .summary { margin-top: 30px; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
        .chart-placeholder { height: 200px; background-color: #f5f5f5; border: 1px solid #ddd; 
                           margin: 20px 0; display: flex; align-items: center; justify-content: center; }
    </style>
</head>
<body>
    <div class="company-info">
        <h2>{{ $company->name ?? 'Ferretería' }}</h2>
        <p>RIF: {{ $company->rif ?? 'J-00000000-0' }}</p>
        <p>Fecha del reporte: {{ date('d/m/Y') }}</p>
    </div>

    <div class="header">
        <h1>Reporte de Productos Más Vendidos</h1>
        <p>Período: {{ request('start_date') ? date('d/m/Y', strtotime(request('start_date'))) : 'Inicio' }} 
           al {{ request('end_date') ? date('d/m/Y', strtotime(request('end_date'))) : 'Fin' }}</p>
    </div>

    <div class="summary">
        <h3>Resumen General</h3>
        <p><strong>Total de productos vendidos:</strong> {{ $products->sum('total_sold') }} unidades</p>
        <p><strong>Productos diferentes vendidos:</strong> {{ $products->count() }}</p>
        <p><strong>Período analizado:</strong> {{ $products->first() ? $products->first()->period_start : 'N/A' }} 
           al {{ $products->first() ? $products->first()->period_end : 'N/A' }}</p>
    </div>

    <div class="chart-placeholder">
        <p><em>Gráfico de productos más vendidos (se generaría dinámicamente)</em></p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th class="rank">#</th>
                <th>Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="text-right">Unidades Vendidas</th>
                <th class="text-right">Ventas Totales (BS)</th>
                <th class="text-right">Ventas Totales (USD)</th>
                <th class="text-right">Participación %</th>
                <th class="text-right">Stock Actual</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalUnits = $products->sum('total_sold');
                $totalSalesBS = $products->sum('total_sales_local');
                $totalSalesUSD = $products->sum('total_sales_usd');
            @endphp
            @foreach($products as $index => $product)
            <tr class="top-{{ $index < 3 ? $index+1 : '' }}">
                <td class="rank">{{ $index + 1 }}</td>
                <td>{{ $product->barcode ?? 'N/A' }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name ?? 'Sin categoría' }}</td>
                <td class="text-right">{{ $product->total_sold }}</td>
                <td class="currency">{{ number_format($product->total_sales_local, 2) }}</td>
                <td class="currency">{{ number_format($product->total_sales_usd, 2) }}</td>
                <td class="text-right">
                    {{ $totalUnits > 0 ? number_format(($product->total_sold / $totalUnits) * 100, 1) : 0 }}%
                </td>
                <td class="text-right">{{ $product->current_stock }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #e8f4f8; font-weight: bold;">
                <td colspan="4">TOTALES</td>
                <td class="text-right">{{ $totalUnits }}</td>
                <td class="currency">{{ number_format($totalSalesBS, 2) }}</td>
                <td class="currency">{{ number_format($totalSalesUSD, 2) }}</td>
                <td class="text-right">100%</td>
                <td class="text-right">{{ $products->sum('current_stock') }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px;">
        <h3>Análisis por Categoría</h3>
        <table style="width: 70%;">
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th class="text-right">Productos Vendidos</th>
                    <th class="text-right">Unidades Vendidas</th>
                    <th class="text-right">Participación %</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $categories = $products->groupBy('category.name');
                @endphp
                @foreach($categories as $categoryName => $categoryProducts)
                <tr>
                    <td>{{ $categoryName ?: 'Sin categoría' }}</td>
                    <td class="text-right">{{ $categoryProducts->count() }}</td>
                    <td class="text-right">{{ $categoryProducts->sum('total_sold') }}</td>
                    <td class="text-right">
                        {{ $totalUnits > 0 ? number_format(($categoryProducts->sum('total_sold') / $totalUnits) * 100, 1) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 30px; padding: 15px; background-color: #f0f8ff; border-left: 4px solid #4682b4;">
        <h4>Recomendaciones</h4>
        <p><strong>Productos para reabastecer:</strong> 
           @foreach($products->where('current_stock', '<=', 'min_stock')->take(3) as $product)
           {{ $product->name }} ({{ $product->current_stock }} unidades),
           @endforeach
           entre otros.
        </p>
        <p><strong>Top 3 productos:</strong> Representan el 
           {{ $products->take(3)->sum('total_sold') > 0 ? number_format(($products->take(3)->sum('total_sold') / $totalUnits) * 100, 1) : 0 }}% 
           de las ventas totales.
        </p>
    </div>

    <div style="margin-top: 30px; text-align: center; font-size: 10px;">
        <p>Reporte generado por: {{ Auth::user()->name ?? 'Sistema' }}</p>
        <p>Fecha: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Este reporte muestra los {{ $products->count() }} productos más vendidos del período.</p>
    </div>
</body>
</html>