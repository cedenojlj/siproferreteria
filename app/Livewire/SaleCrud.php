<?php

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\User;
use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class SaleCrud extends Component
{
    use WithPagination;

    // Model properties
    public $sale_id, $invoice_number, $customer_id, $seller_id, $cashier_id,
           $payment_currency, $payment_method, $payment_type, $exchange_rate,
           $subtotal_usd, $tax = 0, $total_usd,
           $pending_balance, $status, $notes;
    
    public $saleItems = [];

    // Control properties
    public $isModalOpen = false;
    public $search = '';
    public $customers = [];
    public $users = []; 
    public $allProducts = [];

    // Product search for edit modal
    public $productSearch = '';

    protected $rules = [
        'invoice_number' => 'required|string|max:50',
        'customer_id' => 'nullable|exists:customers,id',
        'seller_id' => 'required|exists:users,id',
        'cashier_id' => 'required|exists:users,id',
        'payment_currency' => 'required|in:BS,USD',
        'payment_method' => 'required|in:EFECTIVO,DEBITO,TRANSFERENCIA,PAGO_MOVIL,ZELLE,BANESCO_PANAMA,OTRO',
        'payment_type' => 'required|in:EFECTIVO,CREDITO',
        'exchange_rate' => 'required|numeric|min:0',
        'tax' => 'required|numeric|min:0',
        'status' => 'required|in:pending,completed,cancelled,credit',
        'notes' => 'nullable|string',
        'saleItems' => 'required|array|min:1',
        'saleItems.*.product_id' => 'required|exists:products,id',
        'saleItems.*.quantity' => 'required|numeric|min:1',
        'saleItems.*.unit_price' => 'required|numeric|min:0',
    ];

    #[Computed]
    public function productSearchResults()
    {
        if (strlen($this->productSearch) < 2) {
            return [];
        }

        $company_id = Auth::user()->company_id;
        return Product::where('company_id', $company_id)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->productSearch . '%')
                      ->orWhere('barcode', 'like', '%' . $this->productSearch . '%');
            })
            ->take(5)
            ->get();
    }
    
    public function mount()
    {
        $company_id = Auth::user()->company_id;
        $this->customers = Customer::where('company_id', $company_id)->orderBy('name')->get(['id', 'name']);
        $this->users = User::where('company_id', $company_id)->orderBy('name')->get(['id', 'name']);
        $this->allProducts = Product::where('company_id', $company_id)->get(['id', 'name', 'base_price']);
    }

    public function render()
    {
        $company_id = Auth::user()->company_id;
        $sales = Sale::where('company_id', $company_id)
            ->with(['customer', 'seller', 'cashier'])
            ->where(function($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('customer', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.sale-crud', [
            'sales' => $sales
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        session()->flash('info', 'La creación de ventas se realiza a través del Punto de Venta (POS). Este CRUD es solo para gestión y edición.');
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetInputFields();
        $this->resetValidation();
    }

    private function resetInputFields()
    {
        $this->reset();
        $this->saleItems = [];
        $this->productSearch = '';
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        $sale = Sale::where('company_id', $company_id)
                    ->with(['saleItems.product'])
                    ->findOrFail($id);

        $this->sale_id = $id;
        $this->invoice_number = $sale->invoice_number;
        $this->customer_id = $sale->customer_id;
        $this->seller_id = $sale->seller_id;
        $this->cashier_id = $sale->cashier_id;
        $this->payment_currency = $sale->payment_currency;
        $this->payment_method = $sale->payment_method;
        $this->payment_type = $sale->payment_type;
        $this->exchange_rate = $sale->exchange_rate;
        $this->tax = $sale->tax;
        $this->status = $sale->status;
        $this->notes = $sale->notes;

        $this->saleItems = $sale->saleItems->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_name' => $item->product->name, // For display
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'subtotal_usd' => $item->subtotal_usd,
            ];
        })->toArray();
        
        $this->recalculateTotals();
        $this->openModal();
    }

    public function update()
    {
        $this->validate();
        $company_id = Auth::user()->company_id;

        DB::transaction(function () use ($company_id) {
            $sale = Sale::where('company_id', $company_id)->findOrFail($this->sale_id);
            
            // First, update main sale attributes
            $sale->update([
                'invoice_number' => $this->invoice_number,
                'customer_id' => $this->customer_id,
                'seller_id' => $this->seller_id,
                'cashier_id' => $this->cashier_id,
                'payment_currency' => $this->payment_currency,
                'payment_method' => $this->payment_method,
                'payment_type' => $this->payment_type,
                'exchange_rate' => $this->exchange_rate,
                'tax' => $this->tax,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);

            // Delete old items and recreate them (simpler and safer)
            $sale->saleItems()->delete();

            foreach ($this->saleItems as $item) {
                $sale->saleItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal_usd' => $item['quantity'] * $item['unit_price'],
                ]);
            }

            // Recalculate final totals from the newly created items and update sale
            $subtotal = $sale->saleItems()->sum('subtotal_usd');
            $taxAmount = $subtotal * ($this->tax / 100);
            $total = $subtotal + $taxAmount;
            $pending = ($this->payment_type === 'CREDITO') ? $total : 0;

            $sale->update([
                'subtotal_usd' => $subtotal,
                'total_usd' => $total,
                'pending_balance' => $pending,
            ]);
        });

        session()->flash('message', 'Venta actualizada exitosamente.');
        $this->closeModal();
    }

    public function delete($id)
    {
        $company_id = Auth::user()->company_id;
        // Consider related inventory movements if necessary before deleting
        Sale::where('company_id', $company_id)->findOrFail($id)->delete();
        session()->flash('message', 'Venta eliminada exitosamente.');
    }
    
    public function addProduct($productId)
    {
        $product = Product::find($productId);
        if (!$product) {
            return;
        }

        // Check if product already in cart
        $existingProductIndex = -1;
        foreach ($this->saleItems as $index => $item) {
            if ($item['product_id'] === $productId) {
                $existingProductIndex = $index;
                break;
            }
        }

        if ($existingProductIndex > -1) {
            $this->saleItems[$existingProductIndex]['quantity']++;
        } else {
            $this->saleItems[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => 1,
                'unit_price' => $product->base_price,
                'subtotal_usd' => $product->base_price,
            ];
        }

        $this->productSearch = '';
        $this->recalculateTotals();
    }

    public function removeProduct($index)
    {
        unset($this->saleItems[$index]);
        $this->saleItems = array_values($this->saleItems);
        $this->recalculateTotals();
    }

    public function updatedSaleItems($value, $key)
    {
        // This will only be called for quantity and unit_price now
        $this->recalculateTotals();
    }

    public function updatedTax()
    {
        $this->recalculateTotals();
    }

    public function updatedPaymentType()
    {
        $this->recalculateTotals();
    }

    public function recalculateTotals()
    {
        $currentSubtotal = 0;
        foreach ($this->saleItems as $index => $item) {
            $quantity = !empty($item['quantity']) ? $item['quantity'] : 0;
            $price = !empty($item['unit_price']) ? $item['unit_price'] : 0;
            $itemSubtotal = $quantity * $price;
            $this->saleItems[$index]['subtotal_usd'] = $itemSubtotal;
            $currentSubtotal += $itemSubtotal;
        }

        $this->subtotal_usd = $currentSubtotal;
        $taxAmount = $this->subtotal_usd * ($this->tax / 100);
        $this->total_usd = $this->subtotal_usd + $taxAmount;

        if ($this->payment_type === 'CREDITO') {
            $this->pending_balance = $this->total_usd;
        } else {
            $this->pending_balance = 0;
        }
    }
}
