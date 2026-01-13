<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return Renderable
     */
    public function index(): Renderable
    {
        $kpis = Cache::remember('dashboard_kpis', now()->addMinutes(10), function () {
            // KPIs generales
            $ventasHoy = Sale::whereDate('created_at', today())->sum('total_usd');
            $numVentasHoy = Sale::whereDate('created_at', today())->count();
            $ventasMes = Sale::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total_usd');
            $numClientes = Customer::count();
            $numProductos = Product::count();

            // Datos para el gráfico de ventas de los últimos 7 días
            $salesData = Sale::select(
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('SUM(total_usd) as total')
            )
                ->where('created_at', '>=', Carbon::now()->subDays(6))
                ->groupBy('sale_date')
                ->orderBy('sale_date', 'asc')
                ->get();

            $chartLabels = [];
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                $formattedDate = $date->format('Y-m-d');
                $saleOnDate = $salesData->firstWhere('sale_date', $formattedDate);
                $chartLabels[] = $date->isoFormat('ddd D');
                $chartData[] = $saleOnDate ? $saleOnDate->total : 0;
            }

            // Top 5 productos más vendidos
            $topSellingProducts = SaleItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity')
            )
                ->with('product:id,name,current_stock')
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get();

            return [
                'ventasHoy' => $ventasHoy,
                'numVentasHoy' => $numVentasHoy,
                'ventasMes' => $ventasMes,
                'numClientes' => $numClientes,
                'numProductos' => $numProductos,
                'chart' => [
                    'labels' => $chartLabels,
                    'data' => $chartData,
                ],
                'topSellingProducts' => $topSellingProducts,
            ];
        });

        return view('home', ['kpis' => $kpis]);
    }
}