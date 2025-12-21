<?php

namespace App\Services;

use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use App\Models\Sale;
use App\Models\SaleItem;


class ThermalPrinterService
{
    protected $printer;
    
    public function __construct(string $ip = '192.168.1.100', int $port = 9100)
    {
        $connector = new NetworkPrintConnector($ip, $port);
        $this->printer = new Printer($connector);
    }
    
    public function printSaleTicket(Sale $sale): void
    {
        $this->printer->setJustification(Printer::JUSTIFY_CENTER);
        $this->printer->text("FERRETERÍA XYZ\n");
        $this->printer->text("RIF: J-12345678-9\n");
        $this->printer->text("Dirección: Av. Principal\n");
        $this->printer->text("Teléfono: 0212-1234567\n");
        $this->printer->text("================================\n");
        
        $this->printer->setJustification(Printer::JUSTIFY_LEFT);
        $this->printer->text("Factura: {$sale->invoice_number}\n");
        $this->printer->text("Fecha: {$sale->created_at->format('d/m/Y H:i')}\n");
        $this->printer->text("Cliente: {$sale->customer->name}\n");
        $this->printer->text("Vendedor: {$sale->seller->name}\n");
        $this->printer->text("Cajero: {$sale->cashier->name}\n");
        $this->printer->text("================================\n");
        
        // Items
        foreach ($sale->saleItems as $item) {
            $this->printer->text("{$item->product->name}\n");
            $this->printer->text("{$item->quantity} x {$item->unit_price} = {$item->subtotal_local}\n");
        }
        
        $this->printer->text("================================\n");
        $this->printer->text("Subtotal: {$sale->subtotal_local} BS\n");
        $this->printer->text("IVA: {$sale->tax_local} BS\n");
        $this->printer->text("TOTAL: {$sale->total_local} BS\n");
        $this->printer->text("Tasa: {$sale->exchange_rate} BS/USD\n");
        $this->printer->text("TOTAL USD: {$sale->total_usd}\n");
        
        $this->printer->text("================================\n");
        $this->printer->text("Gracias por su compra!\n");
        $this->printer->cut();
        
        $this->printer->close();
    }
}