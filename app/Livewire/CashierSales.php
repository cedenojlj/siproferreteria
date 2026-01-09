<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CashierSales extends Component
{
    use WithPagination;

    public $isEditing = false;
    public $saleId;
    public $customer_id, $payment_method, $payment_type, $payment_currency, $status;
    public $saleItems = [];
    public $total_amount = 0;
    protected $listeners = ['itemAdded' => 'handleItemAdded'];


    public function render()
    {
        $sales = Sale::where('status', 'pending')
            ->with('customer', 'user')
            ->latest()
            ->paginate(10);
        
        $customers = Customer::all();

        return view('livewire.cashier-sales', [
            'sales' => $sales,
            'customers' => $customers,
        ]);
    }

    public function editSale($saleId)
    {
        $sale = Sale::with('saleItems.product')->findOrFail($saleId);
        $this->saleId = $sale->id;
        $this->customer_id = $sale->customer_id;
        $this->payment_method = $sale->payment_method;
        $this->payment_type = $sale->payment_type;
        $this->payment_currency = $sale->payment_currency;
        $this->status = $sale->status;
        $this->saleItems = [];
        foreach ($sale->saleItems as $item) {
            $this->saleItems[] = [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->unit_price,
                'subtotal' => $item->quantity * $item->unit_price,
            ];
        }
        $this->recalculateTotals();
        $this->isEditing = true;
    }

    public function cancelEdit()
    {
        $this->reset(['isEditing', 'saleId', 'customer_id', 'payment_method', 'payment_type', 'payment_currency', 'status', 'saleItems', 'total_amount']);
    }

    public function removeItem($index)
    {
        unset($this->saleItems[$index]);
        $this->saleItems = array_values($this->saleItems);
        $this->recalculateTotals();
    }
    
    public function updatedSaleItems()
    {
        $this->recalculateTotals();
    }

    public function recalculateTotals()
    {
        $this->total_amount = 0;
        foreach ($this->saleItems as $index => $item) {
            $quantity = is_numeric($item['quantity']) ? $item['quantity'] : 0;
            $price = is_numeric($item['price']) ? $item['price'] : 0;
            $subtotal = $quantity * $price;
            $this->saleItems[$index]['subtotal'] = $subtotal;
            $this->total_amount += $subtotal;
        }
    }

    public function updateSale()
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|string',
            'status' => 'required|string',
            'saleItems' => 'required|array|min:1',
        ]);

        DB::transaction(function () {
            $sale = Sale::findOrFail($this->saleId);
            $originalStatus = $sale->status;

            $sale->update([
                'customer_id' => $this->customer_id,
                'payment_method' => $this->payment_method,
                'payment_type' => $this->payment_type,
                'payment_currency' => $this->payment_currency,
                'status' => $this->status,
                'total_amount' => $this->total_amount,
            ]);

            // Sync sale items
            $sale->saleItems()->delete();
            foreach ($this->saleItems as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }

            // Inventory management
            if ($this->status === 'completed' && $originalStatus !== 'completed') {
                foreach ($this->saleItems as $item) {
                    $product = Product::find($item['product_id']);
                    $product->decrement('stock', $item['quantity']);

                    InventoryMovement::create([
                        'product_id' => $item['product_id'],
                        'type' => 'sale',
                        'quantity' => -$item['quantity'],
                        'user_id' => auth()->id(),
                        'reference_id' => $sale->id,
                    ]);
                }
            }
        });

        session()->flash('message', 'Venta actualizada con éxito.');
        $this->cancelEdit();
    }

    public function printTicket()
    {
        $this->dispatch('print-ticket', ['saleId' => $this->saleId]);
    }

    public function confirmDelete($saleId)
    {
        // En una implementación más robusta, esto mostraría un modal de confirmación.
        // Por ahora, llamaremos directamente a delete.
        $this->deleteSale($saleId);
    }

    public function deleteSale($saleId)
    {
        DB::transaction(function () use ($saleId) {
            $sale = Sale::where('status', 'pending')->findOrFail($saleId);
            
            // Eliminar items asociados
            $sale->saleItems()->delete();
            
            // Eliminar la venta
            $sale->delete();
        });

        session()->flash('message', 'Venta pendiente eliminada con éxito.');
    }
}