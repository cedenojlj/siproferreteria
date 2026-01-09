<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketController extends Controller
{
    /**
     * Generate a PDF ticket for a given sale.
     *
     * @param  \App\Models\Sale  $sale
     * @return \Illuminate\Http\Response
     */
    public function generateTicket(Sale $sale)
    {
                // Cargar relaciones para optimizar y tener todos los datos disponibles
                $sale->load('saleItems.product', 'customer', 'seller', 'company');

        // Opcional: Obtener la compañía de forma explícita si no está en la venta
        // o si es una configuración global. Asumimos que está en la venta por ahora.
        $company = $sale->company;

        // Pasamos los datos a la vista que diseñaremos
        $data = [
            'sale' => $sale,
            'company' => $company,
        ];

        // Crear el PDF
        $pdf = Pdf::loadView('tickets.thermal', $data);

        // Devolver el PDF para ser mostrado en el navegador
        return $pdf->stream('ticket_venta_' . $sale->id . '.pdf');
    }

    public function showSaleTicketForCashier(Sale $sale)
    {
        $sale->load('saleItems.product', 'customer', 'seller', 'company');
        $company = $sale->company;

        return view('tickets.cashier_ticket', compact('sale', 'company'));
    }
}
