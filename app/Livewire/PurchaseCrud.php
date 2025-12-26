<?php

namespace App\Livewire;

use App\Models\ExchangeRate;
use App\Models\Purchase;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseCrud extends Component
{
    use WithPagination;

    // Model properties
    public $purchase_id, $invoice_number, $supplier_id,
           $payment_currency, $subtotal, $tax, $total,
           $status, $notes;

    public $exchange_rate; // Default exchange rate
    
    // Items for the purchase
    public $items = [];
    public $barcode = '';

    // Control properties
    public $isModalOpen = false;
    public $search = '';
    public $suppliers = [];
    public $products = [];

    protected $rules = [
        'invoice_number' => 'required|string|max:50',
        'supplier_id' => 'required|exists:suppliers,id',
        'payment_currency' => 'required|in:BS,USD',
        'exchange_rate' => 'required|numeric|min:0',
        'status' => 'required|in:pending,received,cancelled',
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1'
    ];

    public function mount()
    {
        $company_id = Auth::user()->company_id;
        $this->suppliers = Supplier::where('company_id', $company_id)->get(['id', 'name']);
        $this->products = Product::where('company_id', $company_id)->get(['id', 'name', 'barcode', 'base_price']);
        
        //la ultima tasa de cambio activa
        $latestRate = ExchangeRate::where('company_id', $company_id)
                                  ->where('is_active', true)
                                  ->orderBy('created_at', 'desc')
                                  ->first();
        if ($latestRate) {
            $this->exchange_rate = $latestRate->rate;
        }
    }

    public function render()
    {
        $company_id = Auth::user()->company_id;
        $purchases = Purchase::where('company_id', $company_id)
            ->with('supplier')
            ->where(function($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('supplier', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.purchase-crud', [
            'purchases' => $purchases
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function updatedBarcode($barcode)
    {
        if (!empty($barcode)) {
            $this->findProductByBarcode($barcode);
        }
    }

    public function findProductByBarcode($barcode)
    {
        $company_id = Auth::user()->company_id;
        $product = Product::where('company_id', $company_id)
                          ->where('barcode', $barcode)
                          ->first();

        if ($product) {
            $this->addItem($product);
            $this->barcode = '';
        } else {
            $this->addError('barcode', 'Producto no encontrado.');
        }
    }

    public function addItem($product, $quantity = 1, $price = null)
    {
        if ($product instanceof Product) {
            $productId = $product->id;
            $productName = $product->name;
            $productPrice = $price ?? $product->price;
        } else {
            $productId = $product['id'];
            $productName = $product['name'];
            $productPrice = $price ?? $product['price'];
        }

        // Check if item already exists
        $existingItemIndex = null;
        foreach ($this->items as $index => $item) {
            if ($item['product_id'] == $productId) {
                $existingItemIndex = $index;
                break;
            }
        }

        if ($existingItemIndex !== null) {
            // Increment quantity
            $this->items[$existingItemIndex]['quantity']++;
        } else {
            // Add new item
            $this->items[] = [
                'product_id' => $productId,
                'name' => $productName,
                'quantity' => $quantity,
                'price' => $productPrice,
            ];
        }
        $this->calculateTotals();
    }

    public function updateItemQuantity($index, $quantity)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['quantity'] = max(1, $quantity); // Ensure quantity is at least 1
            $this->calculateTotals();
        }
    }
    
    public function updateItemPrice($index, $price)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['price'] = max(0, $price); // Ensure price is non-negative
            $this->calculateTotals();
        }
    }

    public function removeItem($index)
    {
        if (isset($this->items[$index])) {
            unset($this->items[$index]);
            $this->items = array_values($this->items); // Re-index array
            $this->calculateTotals();
        }
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        foreach ($this->items as $item) {
            $this->subtotal += $item['price'] * $item['quantity'];
        }
        
        // Assuming a tax rate of 16% for this example.
        // You should probably get this from a config or company setting.
        $this->tax = $this->subtotal * 0.16;
        $this->total = $this->subtotal + $this->tax;
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
    }

    private function resetInputFields()
    {
        $this->reset([
            'purchase_id', 'invoice_number', 'supplier_id',
            'payment_currency', 'exchange_rate', 'subtotal', 'tax', 'total',
            'status', 'notes', 'items', 'barcode'
        ]);
        $this->subtotal = 0;
        $this->tax = 0;
        $this->total = 0;
        $this->items = [];
    }

    public function store()
    {
        $this->validate();

        DB::transaction(function () {
            $purchase = Purchase::create([
                'company_id' => Auth::user()->company_id,
                'user_id' => Auth::id(),
                'invoice_number' => $this->invoice_number,
                'supplier_id' => $this->supplier_id,
                'payment_currency' => $this->payment_currency,
                'exchange_rate' => $this->exchange_rate,
                'subtotal' => $this->subtotal,
                'tax' => $this->tax,
                'total' => $this->total,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);

            foreach ($this->items as $item) {
                $purchase->purchaseItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Update stock if purchase is received
                if ($this->status === 'received') {
                    $product = Product::find($item['product_id']);
                    $product->increment('stock', $item['quantity']);
                }
            }
        });

        session()->flash('message', 'Compra creada exitosamente.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        $purchase = Purchase::where('company_id', $company_id)
                            ->with('purchaseItems.product')
                            ->findOrFail($id);

        $this->purchase_id = $id;
        $this->invoice_number = $purchase->invoice_number;
        $this->supplier_id = $purchase->supplier_id;
        $this->payment_currency = $purchase->payment_currency;
        $this->exchange_rate = $purchase->exchange_rate;
        $this->status = $purchase->status;
        $this->notes = $purchase->notes;

        $this->items = [];
        foreach ($purchase->purchaseItems as $item) {
            $this->items[] = [
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
            ];
        }

        $this->calculateTotals();
        $this->openModal();
    }

    public function update()
    {
        $this->validate();

        if (!$this->purchase_id) {
            return;
        }

        DB::transaction(function () {
            $company_id = Auth::user()->company_id;
            $purchase = Purchase::where('company_id', $company_id)
                                ->with('purchaseItems.product')
                                ->findOrFail($this->purchase_id);

            // Revert stock if purchase was received
            if ($purchase->status === 'received') {
                foreach ($purchase->purchaseItems as $item) {
                    Product::find($item->product_id)->decrement('stock', $item->quantity);
                }
            }

            $purchase->update([
                'invoice_number' => $this->invoice_number,
                'supplier_id' => $this->supplier_id,
                'payment_currency' => $this->payment_currency,
                'exchange_rate' => $this->exchange_rate,
                'subtotal' => $this->subtotal,
                'tax' => $this->tax,
                'total' => $this->total,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);

            // Delete old items and create new ones
            $purchase->purchaseItems()->delete();
            foreach ($this->items as $item) {
                $purchase->purchaseItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

                // Update stock if new status is received
                if ($this->status === 'received') {
                    Product::find($item['product_id'])->increment('stock', $item['quantity']);
                }
            }
        });

        session()->flash('message', 'Compra actualizada exitosamente.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $company_id = Auth::user()->company_id;
            $purchase = Purchase::where('company_id', $company_id)
                                ->with('purchaseItems')
                                ->findOrFail($id);

            // Revert stock if purchase was received
            if ($purchase->status === 'received') {
                foreach ($purchase->purchaseItems as $item) {
                    Product::find($item->product_id)->decrement('stock', $item->quantity);
                }
            }

            $purchase->delete();
        });

        session()->flash('message', 'Compra eliminada exitosamente.');
    }
}
