# Plan de Implementación: Reporte de Productos Más Vendidos

El objetivo es crear un reporte descargable en PDF que liste los productos ordenados por la cantidad total vendida. Opcionalmente, permitiremos filtrar por un rango de fechas.

---

### **Paso 1: Definir la Ruta (Route)**

Primero, necesitamos una ruta para acceder al reporte. La añadiremos en `routes/web.php`, siguiendo la estructura de las rutas de reportes existentes.

```php
// In routes/web.php

// ... (other routes)

use App\Http\Controllers\ReportController;

Route::middleware(['auth'])->group(function () {
    // ... (other routes)
    Route::get('/reports/top-selling-products', [ReportController::class, 'topSellingProductsReport'])
         ->name('reports.top_selling_products');
    // ... (other routes)
});
```

### **Paso 2: Lógica en el Controlador (Controller)**

Aquí es donde obtendremos los datos. Modificaremos el `ReportController.php` para añadir el método `topSellingProductsReport`. Este método se encargará de:
1.  Obtener los productos más vendidos.
2.  Renderizar la vista del PDF.
3.  Generar y descargar el archivo.

**Archivo:** `app/Http/Controllers/ReportController.php`

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product; // Asegúrate de importar el modelo Product
use Illuminate\Support\Facades\DB; // Importante para cálculos
use Barryvdh\DomPDF\Facade\Pdf; // Asumiendo que usas laravel-dompdf

class ReportController extends Controller
{
    // ... (otros métodos como inventoryReport)

    /**
     * Genera un reporte en PDF de los productos más vendidos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function topSellingProductsReport(Request $request)
    {
        $this->authorize('view_reports'); // Opcional: si usas Policies para seguridad

        // Fechas para el filtrado (opcional pero recomendado)
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Consulta para obtener los productos más vendidos
        $topSellingProducts = Product::select(
            'products.id',
            'products.name',
            'products.barcode',
            DB::raw('SUM(sale_items.quantity) as total_quantity_sold')
        )
        ->join('sale_items', 'products.id', '=', 'sale_items.product_id')
        ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
        ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
            // Aplica el filtro de fecha si se proporcionan las fechas
            return $query->whereBetween('sales.sale_date', [$startDate, $endDate]);
        })
        ->groupBy('products.id', 'products.name', 'products.barcode')
        ->orderBy('total_quantity_sold', 'desc')
        ->take(100) // Limita el reporte a los 100 más vendidos
        ->get();

        // Generación del PDF
        $pdf = PDF::loadView('reports.top_selling_products_pdf', [
            'products' => $topSellingProducts,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generationDate' => now()->format('d/m/Y H:i:s')
        ]);

        return $pdf->download('reporte-productos-mas-vendidos-' . now()->format('Y-m-d') . '.pdf');
    }
}
```

**Nota sobre el PDF:** Este código asume que tienes `barryvdh/laravel-dompdf` instalado. Si no es así, es un prerrequisito:
`composer require barryvdh/laravel-dompdf`

### **Paso 3: Crear la Vista del PDF (View)**

Este es el template visual del PDF. Como indicaste, nos basaremos en `inventory_pdf.blade.php`.

1.  Crea un nuevo archivo: `resources/views/reports/top_selling_products_pdf.blade.php`.
2.  Copia el contenido de `inventory_pdf.blade.php` y pégalo en el nuevo archivo.
3.  Modifica el contenido para que muestre los datos correctos.

**Archivo:** `resources/views/reports/top_selling_products_pdf.blade.php`

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos Más Vendidos</title>
    <style>
        /* Copia los mismos estilos de inventory_pdf.blade.php para consistencia */
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; }
        .header, .footer { text-align: center; }
        .header h1 { margin: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Productos Más Vendidos</h1>
        {{-- Asume que tienes los datos de la compañía disponibles --}}
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
        <p>Página <script type="text/php">echo $PAGE_NUM;</script> de <script type="text/php">echo $PAGE_COUNT;</script></p>
    </div>
</body>
</html>
```

### **Paso 4: Interfaz de Usuario para Generar el Reporte (UI)**

Finalmente, necesitas un lugar en la interfaz desde donde el usuario pueda solicitar este reporte. Una buena ubicación sería una página de "Reportes" o directamente en el dashboard.

Puedes añadir un pequeño formulario que permita seleccionar un rango de fechas.

**Ejemplo para `resources/views/reports/index.blade.php` (o similar):**

```html
{{-- ... en alguna vista de tu panel de administración ... --}}

<div class="card">
    <div class="card-header">
        <h4>Reporte de Productos Más Vendidos</h4>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.top_selling_products') }}" method="GET" target="_blank">
            <div class="row">
                <div class="col-md-5">
                    <label for="start_date">Fecha de Inicio</label>
                    <input type="date" name="start_date" class="form-control">
                </div>
                <div class="col-md-5">
                    <label for="end_date">Fecha de Fin</label>
                    <input type="date" name="end_date" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Generar PDF</button>
                </div>
            </div>
            <small class="form-text text-muted">Si no seleccionas fechas, el reporte incluirá todos los datos históricos.</small>
        </form>
    </div>
</div>
```

### **Resumen del Flujo de Trabajo:**

1.  El usuario va a la página de reportes.
2.  (Opcional) Selecciona un rango de fechas y hace clic en "Generar PDF".
3.  La petición llega a la ruta `reports.top_selling_products`.
4.  El método `topSellingProductsReport` en `ReportController` ejecuta la consulta a la base de datos, filtrando por fecha si es necesario.
5.  Los resultados se pasan a la vista `top_selling_products_pdf.blade.php`.
6.  La librería `laravel-dompdf` renderiza esa vista como un PDF.
7.  El PDF se envía al navegador del usuario para su descarga.
