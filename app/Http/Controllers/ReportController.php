<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\InventoryMovement;
use App\Services\ThermalPrinterService;
use Barryvdh\DomPDF\Facade\Pdf;

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

    /* public function showSaleTicket(Sale $sale, ThermalPrinterService $printerService)
    {
        // Cargar las relaciones necesarias
        $sale->load('customer', 'seller', 'saleItems.product');

        // Formatear el ticket usando el servicio
        $formatted_ticket = $printerService->formatSaleTicket($sale);

        // Pasar los datos a la vista
        return view('reports.sale_ticket', [
            'sale' => $sale,
            'formatted_ticket' => $formatted_ticket,
        ]);
    } */
}
