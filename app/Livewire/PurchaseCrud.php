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
use App\Models\InventoryMovement;
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
        'items' => 'required|array|min:1',
        // 'items.*.product_id' => 'required|exists:products,id',
        // 'items.*.quantity' => 'required|numeric|min:1',
        // 'items.*.unit_price' => 'required|numeric|min:0'
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
            //quiero que tenga dos decimales
            $this->exchange_rate = number_format($latestRate->rate, 2);
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
        $latestRate = ExchangeRate::where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->first();
        if ($latestRate) {
            $this->exchange_rate = $latestRate->rate;
        }
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

    public function updatedItems($value, $key)
    {
        // This hook will trigger when any nested property of the $items array is updated.
        // For example, 'items.0.quantity' or 'items.1.price'.
        //verificar que la cantidad y el precio sean numeros y despues validos

        $parts = explode('.', $key);
        if (count($parts) == 3) {
            $index = $parts[1];
            $field = $parts[2];

            if ($field === 'quantity' || $field === 'price') {
                $value = floatval($value);
                if ($value < 0) {
                    $this->addError("items.{$index}.{$field}", "La cantidad y el precio deben ser números positivos.");
                }
            }
        }

        $this->calculateTotals();
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
                $quantity = floatval($item['quantity'] ?? 0);
                $price = floatval($item['price'] ?? 0);
                $this->subtotal += $quantity * $price;
            }
            
            // Assuming a tax rate of 16% for this example.
            // You should probably get this from a config or company setting.
            $this->tax = $this->subtotal * 0.16;
            $this->total = $this->subtotal + $this->tax;
    
            // Format for display
            $this->subtotal = number_format($this->subtotal, 2, '.', '');
            $this->tax = number_format($this->tax, 2, '.', '');
            $this->total = number_format($this->total, 2, '.', '');
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
            'payment_currency', 'subtotal', 'tax', 'total',
            'status', 'notes', 'items', 'barcode'
        ]);
        $this->subtotal = 0;
        $this->tax = 0;
        $this->total = 0;
        $this->items = [];
    }

    private function recordInventoryMovement(Purchase $purchase, array $item, string $movementType, int $quantity, string $notes)
    {
        InventoryMovement::create([
            'company_id' => $purchase->company_id,
            'product_id' => $item['product_id'],
            'user_id' => Auth::id(),
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'reference_id' => $purchase->id,
            'reference_type' => 'purchase',
            'notes' => $notes,
        ]);

        $product = Product::find($item['product_id']);
        if ($movementType === 'in') {
            $product->increment('current_stock', $quantity);
        } elseif ($movementType === 'out') {
            $product->decrement('current_stock', $quantity);
        }
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
                'subtotal' => floatval($this->subtotal),
                'tax' => floatval($this->tax),
                'total' => floatval($this->total),
                'status' => $this->status,
                'notes' => $this->notes,
            ]);

            foreach ($this->items as $item) {
                $purchase->purchaseItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => floatval($item['quantity']) * floatval($item['price']),
                ]);

                if ($this->status === 'received') {
                    $this->recordInventoryMovement($purchase, $item, 'in', $item['quantity'], 'Recepción de compra #' . $purchase->invoice_number);
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
                'price' => $item->unit_price,
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
            $purchase = Purchase::with('purchaseItems')->findOrFail($this->purchase_id);
            $originalStatus = $purchase->status;

            // Handle inventory changes
            if ($originalStatus === 'received' || $this->status === 'received') {
                $this->adjustInventoryOnUpdate($purchase);
            }

            // Update purchase details
            $purchase->update([
                'invoice_number' => $this->invoice_number,
                'supplier_id' => $this->supplier_id,
                'payment_currency' => $this->payment_currency,
                'exchange_rate' => $this->exchange_rate,
                'subtotal' => floatval($this->subtotal),
                'tax' => floatval($this->tax),
                'total' => floatval($this->total),
                'status' => $this->status,
                'notes' => $this->notes,
            ]);

            // Sync purchase items
            $purchase->purchaseItems()->delete();
            foreach ($this->items as $item) {
                $purchase->purchaseItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'subtotal' => floatval($item['quantity']) * floatval($item['price']),
                ]);
            }
        });

        session()->flash('message', 'Compra actualizada exitosamente.');
        $this->closeModal();
        $this->resetInputFields();
    }

    private function adjustInventoryOnUpdate(Purchase $purchase)
    {
        $originalItems = $purchase->purchaseItems->keyBy('product_id');
        $newItems = collect($this->items)->keyBy('product_id');
        $originalStatus = $purchase->status;
        $newStatus = $this->status;

        // Case 1: Purchase status changes FROM 'received' TO something else
        if ($originalStatus === 'received' && $newStatus !== 'received') {
            foreach ($originalItems as $item) {
                $this->recordInventoryMovement($purchase, $item->toArray(), 'out', $item->quantity, 'Cancelación/reversión de compra #' . $purchase->invoice_number);
            }
            return;
        }

        // Case 2: Purchase status changes TO 'received' FROM something else
        if ($originalStatus !== 'received' && $newStatus === 'received') {
            foreach ($newItems as $item) {
                $this->recordInventoryMovement($purchase, $item, 'in', $item['quantity'], 'Recepción de compra (actualizada) #' . $purchase->invoice_number);
            }
            return;
        }

        // Case 3: Purchase status was and remains 'received'
        if ($originalStatus === 'received' && $newStatus === 'received') {
            // Products removed from the purchase
            $removedItems = $originalItems->diffKeys($newItems);
            foreach ($removedItems as $item) {
                $this->recordInventoryMovement($purchase, $item->toArray(), 'out', $item->quantity, 'Artículo eliminado de compra #' . $purchase->invoice_number);
            }

            // Products added to the purchase
            $addedItems = $newItems->diffKeys($originalItems);
            foreach ($addedItems as $item) {
                $this->recordInventoryMovement($purchase, $item, 'in', $item['quantity'], 'Artículo agregado a compra #' . $purchase->invoice_number);
            }

            // Products with changed quantity
            $persistedItems = $originalItems->intersectByKeys($newItems);
            foreach ($persistedItems as $originalItem) {
                $newItem = $newItems[$originalItem->product_id];
                $quantityDiff = $newItem['quantity'] - $originalItem->quantity;

                if ($quantityDiff > 0) {
                    $this->recordInventoryMovement($purchase, (array)$newItem, 'in', $quantityDiff, 'Ajuste de cantidad en compra #' . $purchase->invoice_number);
                } elseif ($quantityDiff < 0) {
                    $this->recordInventoryMovement($purchase, (array)$newItem, 'out', abs($quantityDiff), 'Ajuste de cantidad en compra #' . $purchase->invoice_number);
                }
            }
        }
    }


    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $purchase = Purchase::with('purchaseItems')->findOrFail($id);

            if ($purchase->status === 'received') {
                foreach ($purchase->purchaseItems as $item) {
                    $this->recordInventoryMovement($purchase, $item->toArray(), 'out', $item->quantity, 'Eliminación de compra #' . $purchase->invoice_number);
                }
            }

            $purchase->delete();
        });

        session()->flash('message', 'Compra eliminada exitosamente.');
    }
}
