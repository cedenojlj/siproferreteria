<?php

namespace App\Livewire;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Refund;
use App\Models\RefundItem;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class RefundCrud extends Component
{
    use WithPagination;

    // Search and selection
    public $saleSearch = '';
    public ?Sale $selectedSale = null;

    // Refund properties
    public $refundItems = [];
    public $reason = '';
    public $total_amount = 0;
    public $impuesto=0;
    public $refund_method = 'cash';
    public $status = 'pending';
    public $exchange_rate;
    public $tax_porcentage;

    // Control properties
    public $isModalOpen = false;
    public $search = ''; // For the refunds list
    public $viewModalOpen = false;
    public ?Refund $viewRefund = null;

    protected $rules = [
        'selectedSale' => 'required',
        'reason' => 'required|string|min:5',
        'refundItems' => 'required|array|min:1',
        // 'refundItems.*.quantity' => 'required|numeric|min:1',
    ];

    public function render()
    {
        $refunds = Refund::with(['customer', 'user', 'sale'])
            ->where(function ($query) {
                $query->where('id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('customer', function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%');
                    });
            })
            ->latest()
            ->paginate(10);
            
        return view('livewire.refund-crud', [
            'refunds' => $refunds
        ]);
    }

    public function searchSale()
    {
        $this->reset(['selectedSale', 'refundItems', 'reason', 'total_amount']);
        $this->selectedSale = Sale::with('saleItems.product')->find($this->saleSearch); 
        
        $venta = Sale::find($this->saleSearch);

        $this->exchange_rate = $venta ? $venta->exchange_rate : 1;

        $this->tax_porcentage = $venta ? $venta->tax_porcentaje/100 : 0;        

        if ($this->selectedSale) {
            foreach ($this->selectedSale->saleItems as $item) {
                $this->refundItems[] = [
                    'product_id' => $item->product_id,
                    'sale_item_id' => $item->id,
                    'product_name' => $item->product->name,
                    'quantity' => 0, // Default to 0, user will input the quantity to return
                    'max_quantity' => $item->quantity, // The max quantity they can return
                    'unit_price' => $item->unit_price,
                    'subtotal' => 0,
                    'item_condition' => $item->item_condition,
                ];
            }
        } else {
            session()->flash('error', 'Venta no encontrada.');
        }
    }
    
    public function updatedRefundItems()
    {
        $this->recalculateTotal();
    }

    public function recalculateTotal()
    {
        $this->total_amount = 0;
        foreach ($this->refundItems as $index => $item) {
            $quantity = is_numeric($item['quantity']) ? $item['quantity'] : 0;
            
            if ($quantity > $item['max_quantity']) {
                $this->refundItems[$index]['quantity'] = $item['max_quantity'];
                $quantity = $item['max_quantity'];
                 $this->addError('refundItems.'.$index.'.quantity', 'La cantidad a devolver no puede ser mayor a la comprada.');
            }

            $price = is_numeric($item['unit_price']) ? $item['unit_price'] : 0;
            $subtotal = $quantity * $price;
            $this->refundItems[$index]['subtotal'] = $subtotal;
            $this->total_amount += $subtotal;
        }
    }

    public function store()
    {
        $this->validate();

        // Filter out items that are not being returned
        $itemsToReturn = array_filter($this->refundItems, function($item) {
            return $item['quantity'] > 0;
        });

        if (empty($itemsToReturn)) {
            session()->flash('error', 'Debe seleccionar al menos un producto para devolver.');
            return;
        }


        DB::transaction(function () use ($itemsToReturn) {
            $refund = Refund::create([
                'company_id' => Auth::user()->company_id,
                'sale_id' => $this->selectedSale->id,
                'customer_id' => $this->selectedSale->customer_id,
                'user_id' => Auth::id(),
                'total_amount_usd' => $this->total_amount,
                'status' => $this->status,
                'refund_method' => $this->refund_method,
                'reason' => $this->reason,                 
            ]);

            //dame el tax_porcentage de la venta
            // de string a double   

            
            foreach ($itemsToReturn as $item) {
                RefundItem::create([
                    'refund_id' => $refund->id,
                    'sale_item_id' => $item['sale_item_id'],
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price_usd' => $item['unit_price'],
                    'subtotal_usd' => $item['subtotal'],
                    'tax_usd' =>  $item['subtotal']*$this->tax_porcentage,                    
                    'item_condition' => 'damaged',

                ]);

                // Update product stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->increment('current_stock', $item['quantity']);
                }

                if ($this->status=='completed') {
                    //registar movimiento de inventario de entrada por devolucion
                    InventoryMovement::create([
                        'company_id' => Auth::user()->company_id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'movement_type' => 'in',
                        'reason' => 'Devolución ID: '.$refund->id,
                        'user_id' => Auth::id(),
                        'reference_id' => $refund->id,
                        'reference_type' => 'refund',
                        'exchange_rate' => $this->exchange_rate,
                        'notes' => 'Devolución ID: '.$refund->id,
                    ]);
                }
            }
        });

        session()->flash('message', 'Devolución creada con éxito.');
        $this->resetForm();
    }

    public function showViewModal($refundId)
    {
        $this->viewRefund = Refund::with(['customer', 'user', 'sale', 'refundItems.product'])->find($refundId);
        $this->viewModalOpen = true;
    }

    public function closeViewModal()
    {
        $this->viewModalOpen = false;
        $this->viewRefund = null;
    }

    public function resetForm()
    {
        $this->reset(['saleSearch', 'selectedSale', 'refundItems', 'reason', 'total_amount', 'isModalOpen']);
    }
}