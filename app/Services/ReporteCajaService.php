<?php

namespace App\Services;

use App\Models\CajaCierre;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReporteCajaService
{
    /**
     * Genera el reporte de cierre de caja.
     *
     * @param int $userId
     * @param int $companyId
     * @return CajaCierre
     */
    public function generarReporte(int $userId, int $companyId): CajaCierre
    {
        $fechaFin = Carbon::now();

        // Determinar la fecha de inicio
        $ultimoCierre = CajaCierre::where('company_id', $companyId)
            ->latest('fecha_cierre')
            ->first();

        $fechaInicio = $ultimoCierre ? $ultimoCierre->fecha_cierre : Sale::where('company_id', $companyId)->oldest('created_at')->first()?->created_at;

        // Si no hay ventas, no hay nada que cerrar.
        if (!$fechaInicio) {
            $fechaInicio = $fechaFin;
        }

        // Consultas
        $ventasQuery = Sale::where('company_id', $companyId)->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        $devolucionesQuery = Refund::where('company_id', $companyId)->whereBetween('created_at', [$fechaInicio, $fechaFin]);
        $pagosQuery = Payment::where('company_id', $companyId)->whereBetween('created_at', [$fechaInicio, $fechaFin]);

        // Cálculos
        $totalVentasBruto = $ventasQuery->sum('total_usd');
        $totalDevoluciones = $devolucionesQuery->sum('total_amount_usd');
        $totalVentasNeto = $totalVentasBruto - $totalDevoluciones;
        $numeroTransacciones = $ventasQuery->count();
        
        // El total de impuestos se puede calcular si tienes una columna de impuestos en la tabla de ventas.
        // Asumiendo que existe una columna 'tax_amount' en la tabla 'sales'.
        $totalImpuestos = $ventasQuery->sum('tax');

        $totalesPorMetodo = $pagosQuery
            ->groupBy('payment_method')
            ->selectRaw('payment_method, sum(amount_usd) as total')
            ->pluck('total', 'payment_method')
            ->toArray();

        // Guardar el registro del cierre
        $cierre = CajaCierre::create([
            'user_id' => $userId,
            'company_id' => $companyId,
            'fecha_cierre' => $fechaFin,
            'rango_inicio' => $fechaInicio,
            'rango_fin' => $fechaFin,
            'total_ventas_bruto' => $totalVentasBruto,
            'total_devoluciones' => $totalDevoluciones,
            'total_ventas_neto' => $totalVentasNeto,
            'total_impuestos' => $totalImpuestos,
            'totales_por_metodo' => $totalesPorMetodo,
            'numero_transacciones' => $numeroTransacciones,
        ]);

        return $cierre;
    }
}

