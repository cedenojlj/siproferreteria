<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Services\ThermalPrinterService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class ReportController extends Controller
{
     // Reporte de Ventas
    public function salesReport(Request $request)
    {
        $query = Sale::with(['customer', 'seller', 'cashier', 'saleItems.product'])
            ->where('status', 'completed');
            
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $sales = $query->orderBy('created_at', 'desc')->get();
        
        $pdf = Pdf::loadView('reports.sales', compact('sales'));
        
        return $pdf->download('reporte-ventas-' . date('Y-m-d') . '.pdf');
    }
    
    // Reporte de Compras
    public function purchasesReport(Request $request)
    {
        $query = Purchase::with(['supplier', 'user', 'purchaseItems.product'])
            ->where('status', 'received');
            
        if ($request->has('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        
        if ($request->has('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }
        
        $purchases = $query->orderBy('created_at', 'desc')->get();
        
        $pdf = Pdf::loadView('reports.purchases', compact('purchases'));
        
        return $pdf->download('reporte-compras-' . date('Y-m-d') . '.pdf');
    }
    
    // Reporte de Inventario
    public function inventoryReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        // 1. Obtener los productos de la compañía del usuario
        $products = Product::with('category', 'unitMeasure') // Se eliminó 'supplier'
                            ->where('company_id', $user->company_id) // Filtrado por compañía
                            ->where('is_active', true)
                            ->orderBy('name', 'asc')
                            ->get();

        // 2. Preparar datos adicionales para la vista
        $data = [
            'products' => $products,
            'generationDate' => Carbon::now()->format('d/m/Y H:i:s'),
            'company' => $company, // Se pasa la compañía del usuario
        ];

        // 3. Cargar la vista Blade que diseñará nuestro PDF
        $pdf = PDF::loadView('reports.inventory_pdf', $data);

        // 4. Configurar el papel y la orientación
        $pdf->setPaper('A4', 'landscape');

        // 5. Descargar el PDF con un nombre de archivo dinámico
        $fileName = 'reporte-inventario-' . Carbon::now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }
    
    // Reporte de Productos Más Vendidos
    public function topProductsReport(Request $request)
    {
        $query = Product::with(['category'])
            ->whereHas('saleItems')
            ->withCount(['saleItems as total_sold' => function($query) use ($request) {
                $query->selectRaw('SUM(quantity)');
                if ($request->has('start_date')) {
                    $query->whereDate('created_at', '>=', $request->start_date);
                }
                if ($request->has('end_date')) {
                    $query->whereDate('created_at', '<=', $request->end_date);
                }
            }])
            ->orderBy('total_sold', 'desc')
            ->limit(20);
            
        $products = $query->get();
        
        $pdf = Pdf::loadView('reports.top-products', compact('products'));
        
        return $pdf->download('productos-mas-vendidos-' . date('Y-m-d') . '.pdf');
    }

    public function showSaleTicket(Sale $sale, ThermalPrinterService $printerService)
    {
        try {
            // Cargar las relaciones necesarias si no se han cargado automáticamente
            $sale->loadMissing('customer', 'seller', 'cashier', 'saleItems.product');

            // El servicio se encarga de la impresión directamente.
            $printerService->printSaleTicket($sale);

            // Retornar una respuesta JSON de éxito
            return response()->json(['message' => 'Ticket enviado a la impresora correctamente.']);

        } catch (\Exception $e) {
            // Manejar cualquier excepción que ocurra durante la impresión
            // Es buena idea loggear el error para depuración
            \Log::error("Error al imprimir ticket de venta #{$sale->id}: " . $e->getMessage());

            // Retornar una respuesta JSON de error
            return response()->json(['error' => 'No se pudo conectar con la impresora.'], 500);
        }
    }

    /**
     * Genera un reporte en PDF de los productos más vendidos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function topSellingProductsReport(Request $request)
    {
        // $this->authorize('view_reports'); // Opcional: si usas Policies para seguridad
        $user = auth()->user();
        $company = $user->company;

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
        ->where('products.company_id', $user->company_id) // Filtrar por la compañía del usuario
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
            'company' => $company,
            'generationDate' => now()->format('d/m/Y H:i:s')
        ]);

        return $pdf->download('reporte-productos-mas-vendidos-' . now()->format('Y-m-d') . '.pdf');
    }
}
