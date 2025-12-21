<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Sale;
use App\Services\ThermalPrinterService;

class PointOfSale extends Component
{
    
    public $saleId;
    public $printSuccess = false;
    
    public function printTicket()
    {
        $sale = Sale::with(['customer', 'seller', 'cashier', 'saleItems.product'])
            ->findOrFail($this->saleId);
            
        $printer = new ThermalPrinterService();
        $printer->printSaleTicket($sale);
        
        $this->printSuccess = true;
        session()->flash('message', 'Ticket impreso exitosamente');
    }
    
    
    public function render()
    {
        return view('livewire.point-of-sale');
    }
}
