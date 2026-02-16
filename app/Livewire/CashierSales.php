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
    public $subtotal = 0;
    public $tax = 0;
    public $total = 0;
    public $search = '';
    public $searchPending = ''; // Para buscar en ventas pendientes
    public $searchResults = [];
    protected $listeners = ['itemAdded' => 'handleItemAdded'];
    public $tasaBsDolar; // Variable para la tasa de cambio bs/dólar


    public function render()
    {
        $query = Sale::where('status', 'pending')
            ->with('customer', 'user');

        if (!empty($this->searchPending)) {
            $query->where(function($q) {
                $q->whereHas('customer', function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->searchPending . '%')
                             ->orWhere('document', 'like', '%' . $this->searchPending . '%');
                })->orWhereHas('user', function ($subQuery) {
                    $subQuery->where('name', 'like', '%' . $this->searchPending . '%');
                });
            });
        }

        $sales = $query->latest()->paginate(10);
        
        $customers = Customer::all();

        return view('livewire.cashier-sales', [
            'sales' => $sales,
            'customers' => $customers,
        ]);
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->searchResults = Product::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('barcode', 'like', '%' . $this->search . '%')
                ->take(5)
                ->get();
        } else {
            $this->searchResults = [];
        }
    }

    public function handleItemAdded($productId)
    {
        $this->addProductToSale($productId);
    }
    
    public function addProductToSale($productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return;
        }

        $existingItemIndex = -1;
        foreach ($this->saleItems as $index => $item) {
            if ($item['product_id'] == $productId) {
                $existingItemIndex = $index;
                break;
            }
        }

        if ($existingItemIndex != -1) {
            $this->saleItems[$existingItemIndex]['quantity']++;
        } else {
            $this->saleItems[] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'quantity'   => 1,
                'price'      => $product->base_price,
                'subtotal'   => $product->base_price,
            ];
        }

        $this->recalculateTotals();
        $this->search = '';
        $this->searchResults = [];
    }

    public function editSale($saleId)
    {
        $sale = Sale::with('saleItems.product')->findOrFail($saleId);
        $this->tasaBsDolar = $sale->exchange_rate; // Asignar la tasa de cambio de la venta
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
        $this->reset(['isEditing', 'saleId', 'customer_id', 'payment_method', 'payment_type', 'payment_currency', 'status', 'saleItems', 'subtotal', 'tax', 'total', 'search', 'searchResults']);
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
        $this->subtotal = 0;
        foreach ($this->saleItems as $index => $item) {
            $quantity = is_numeric($item['quantity']) ? $item['quantity'] : 0;
            $price = is_numeric($item['price']) ? $item['price'] : 0;
            $itemSubtotal = $quantity * $price;
            $this->saleItems[$index]['subtotal'] = $itemSubtotal;
            $this->subtotal += $itemSubtotal;
        }

        $company = auth()->user()->company;
        $taxRate = $company && $company->tax_rate ? ($company->tax_rate / 100) : 0;
        $this->tax = $this->subtotal * $taxRate;
        $this->total = $this->subtotal + $this->tax;
    }

    public function updateSale()
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_method' => 'required|string',
            'status' => 'required|string',
            'saleItems' => 'required|array|min:1',
        ]);

        try {
            DB::transaction(function () {
                $sale = Sale::with('saleItems')->findOrFail($this->saleId);
                $originalStatus = $sale->status;
                $originalItems = $sale->saleItems;

                // 1. Revert stock if sale was previously completed
                if ($originalStatus === 'completed') {
                    foreach ($originalItems as $item) {
                        $product = Product::find($item->product_id);
                        if ($product) {
                            $product->increment('current_stock', $item->quantity);
                             InventoryMovement::where('reference_id', $sale->id)->where('product_id', $item->product_id)->delete();
                        }
                    }
                }

                // Recalculate totals before updating
                $this->recalculateTotals();

                $sale->update([
                    'customer_id' => $this->customer_id,
                    'payment_method' => $this->payment_method,
                    'payment_type' => $this->payment_type,
                    'payment_currency' => $this->payment_currency,
                    'status' => $this->status,
                    'subtotal_usd' => $this->subtotal,
                    'tax' => $this->tax,
                    'total_usd' => $this->total,
                ]);

                // Sync sale items
                $sale->saleItems()->delete();
                foreach ($this->saleItems as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'subtotal_usd' => $item['subtotal'],
                    ]);
                }

                // 2. Apply new stock changes if sale is now completed
                if ($this->status === 'completed') {
                    foreach ($this->saleItems as $item) {
                        $product = Product::find($item['product_id']);
                        if ($product) {
                            if ($product->current_stock < $item['quantity']) {
                                throw new \Exception('Stock insuficiente para el producto: ' . $product->name);
                            }
                            $product->decrement('current_stock', $item['quantity']);

                            InventoryMovement::create([
                                'product_id' => $item['product_id'],
                                'type' => 'sale',
                                'quantity' => -$item['quantity'],
                                'user_id' => auth()->id(),
                                'reference_id' => $sale->id,
                                'company_id' => auth()->user()->company_id,
                                'exchange_rate' => $sale->exchange_rate,
                            ]);
                        } else {
                            throw new \Exception('Producto no encontrado con ID: ' . $item['product_id']);
                        }
                    }
                }
            });

            session()->flash('message', 'Venta actualizada con éxito.');
            $this->cancelEdit();

        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar la venta: ' . $e->getMessage());
            // Optionally, re-read data from DB to revert optimistic UI updates
            $this->editSale($this->saleId);
        }
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


    //crear funcion para actualizar el precio del producto del carrito segun la moneda seleccionada
    public function updatedPaymentCurrency()
    {
       // dd($this->payment_currency);
    
         foreach ($this->saleItems as &$item) {
            $product = Product::find($item['product_id']);
            if ($this->payment_currency == 'BS') {
                $item['price'] = $product->base_price;
            } else {
                $item['price'] = $product->usd_price;
            }
        }
        $this->calculateTotals();

        // $this->recalculateTotals();
    }

     private function calculateTotals()
    {
        $this->subtotal = 0;
        foreach ($this->saleItems as $index => $item) {
            $quantity = is_numeric($item['quantity']) ? $item['quantity'] : 0;
            $price = is_numeric($item['price']) ? $item['price'] : 0;
            $itemSubtotal = $quantity * $price;
            $this->saleItems[$index]['subtotal'] = $itemSubtotal;
            $this->subtotal += $itemSubtotal;
        }

        $company = auth()->user()->company;
        $taxRate = $company && $company->tax_rate ? ($company->tax_rate / 100) : 0;
        $this->tax = $this->subtotal * $taxRate;
        $this->total = $this->subtotal + $this->tax;
    }
}