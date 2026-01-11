<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Product; // Will be needed for products
use App\Models\Sale; // Will be needed for sales
use App\Models\SaleItem; // Will be needed for sale items
use App\Models\InventoryMovement; // Will be needed for inventory movements
use App\Models\User; // Will be needed for user association
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PointOfSale extends Component
{
    public $lastSaleId = null;
    public $customerSearch = '';
    public $customers = [];
    public $selectedCustomerId;
    public $selectedCustomer = null;

    public $productSearch = '';
    public $products = [];
    public $saleItems = []; // Array con los productos del carrito

    public $subtotal = 0;
    public $tax = 0;
    public $total = 0;
    public $impuesto=0;


    public $quantity = 1; // Default quantity for adding product

    public $abrirModalCliente = false;

    public $payment_currency='BS';
    public $payment_method='DEBITO';
    public $payment_type='EFECTIVO';
    public $exchange_rate;

    public $paymentCurrency=['BS', 'USD'];
    public $paymentMethods=['EFECTIVO', 'DEBITO','TRANSFERENCIA', 'PAGO_MOVIL', 'ZELLE', 'BANESCO_PANAMA', 'OTRO']; 
    public $paymentTypes=['EFECTIVO', 'CREDITO'];

    // New customer properties
    public $newCustomer = [
        'name' => '',
        'document' => '',
        'address' => '',
        'phone' => '',
        'email' => '',
    ];

    protected $rules = [
        'newCustomer.name' => 'required|string|max:255',
        'newCustomer.document' => 'nullable|string|max:255|unique:customers,document',
        'newCustomer.address' => 'nullable|string|max:255',
        'newCustomer.phone' => 'nullable|string|max:20',
        'newCustomer.email' => 'nullable|email|max:255',
    ];

    public function mount()
    {
        $this->customers = Customer::where('company_id', Auth::user()->company_id)->get();
        $this->calculateTotals(); // Initialize totals

        //traer el ultimo tipo de cambio segun la compañia
        $latestRate = DB::table('exchange_rates')
            ->where('company_id', Auth::user()->company_id)
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latestRate) {

            $this->exchange_rate = $latestRate->rate;

        } else {

            $this->exchange_rate = 1;
        }
        
        
    }

    public function updatedCustomerSearch()
    {
        if (empty($this->customerSearch)) {
            $this->customers = Customer::where('company_id', Auth::user()->company_id)->get();
            return;
        }

        $this->customers = Customer::where('company_id', Auth::user()->company_id)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->customerSearch . '%')
                    ->orWhere('document', 'like', '%' . $this->customerSearch . '%');
            })
            ->get();
    }

    public function selectCustomer($customerId)
    {
        $this->selectedCustomerId = $customerId;
        $this->selectedCustomer = Customer::find($customerId);
        $this->customerSearch = $this->selectedCustomer->name; // Display selected customer name
        $this->customers = []; // Clear search results
    }
   
    //guardar cliente
    public function storeCustomer()
    {
        $this->validate();

        $customer = Customer::create([
            'company_id' => Auth::user()->company_id,
            'name' => $this->newCustomer['name'],
            'document' => $this->newCustomer['document'],
            'address' => $this->newCustomer['address'],
            'phone' => $this->newCustomer['phone'],
            'email' => $this->newCustomer['email'],
        ]);

        $this->selectCustomer($customer->id);
        $this->reset('newCustomer'); // Clear the form
        $this->cerrarClienteModal(); // Close the modal
        session()->flash('message', 'Cliente creado exitosamente.');
    }

    //crear funcion de cambiar cliente colocando la propiedad a null
    public function cambiarCliente()
    {
        $this->selectedCustomerId = null;
        $this->selectedCustomer = null;
        $this->customerSearch = '';
        $this->customers = Customer::where('company_id', Auth::user()->company_id)->get();
    }

    public function updatedProductSearch()
    {
        if (empty($this->productSearch)) {
            $this->products = [];
            return;
        }

        $this->products = Product::where('company_id', Auth::user()->company_id)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->productSearch . '%')
                    ->orWhere('barcode', 'like', '%' . $this->productSearch . '%');
            })
            ->get();
    }

    public function addProduct($productId)
    {
        $product = Product::find($productId);

        if (!$product) {
            session()->flash('error', 'Producto no encontrado.');
            return;
        }

        // Check inventory
        if ($product->current_stock < 1) { // Assuming current_stock is the field
            session()->flash('error', 'Stock insuficiente para ' . $product->name);
            return;
        }

        // Check if product already in cart
        foreach ($this->saleItems as $index => $item) {
            if ($item['product_id'] == $productId) {
                if (($item['quantity'] + $this->quantity) > $product->current_stock) {
                    session()->flash('error', 'No hay suficiente stock para añadir más de ' . $product->name);
                    return;
                }
                $this->saleItems[$index]['quantity'] += $this->quantity;
                $this->calculateTotals();
                $this->reset('productSearch', 'products', 'quantity');
                $this->dispatch('focus-product-search');
                return;
            }
        }

        if ($this->payment_currency == 'BS') {

            $precio_item = $product->base_price;

        } else {

            $precio_item = $product->usd_price;            
        } 
        

        // Add new product to cart
        $this->saleItems[] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => $precio_item,
            'quantity' => $this->quantity,
        ];

        $this->calculateTotals();
        $this->reset('productSearch', 'products', 'quantity');
        $this->dispatch('focus-product-search');
    }

    public function updateQuantity($index, $quantity)
    {
       //valida que la cantidad sea un numero y que sea mayor a 0
       if (!is_numeric($quantity) || $quantity <= 0) {
            session()->flash('error', 'Cantidad inválida.');
            return;
        }
       
        $quantity = max(1, (float) $quantity); // Ensure quantity is at least 1

        $product = Product::find($this->saleItems[$index]['product_id']);

        //dd( $product);

        if ($quantity > $product->current_stock) {
            session()->flash('error', 'No hay suficiente stock. Máximo disponible: ' . $product->current_stock);
            $this->saleItems[$index]['quantity'] = $product->current_stock; // Revert to max available
        } else {
            $this->saleItems[$index]['quantity'] = $quantity;
        }

        $this->calculateTotals();
        $this->dispatch('focus-product-search');
    }

    public function removeItem($index)
    {
        array_splice($this->saleItems, $index, 1);
        $this->calculateTotals();
        $this->dispatch('focus-product-search');
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        foreach ($this->saleItems as $item) {
            $this->subtotal += $item['quantity'] * $item['price'];
        }

        // extrae la tax_rate de la compañia autenticada
        $company = Auth::user()->company;
        $taxRate = $company->tax_rate / 100; // Convert to decimal
        $this->impuesto=$taxRate;

        //dd($taxRate);
        // Assuming a fixed tax rate for now, e.g., 16%
        $this->tax = $this->subtotal * $taxRate;

        //dd($this->tax);
        $this->total = $this->subtotal + $this->tax;
    }

    public function finalizeSale()
    {
          
        
        if (!$this->selectedCustomer) {
            session()->flash('error', 'Debe seleccionar un cliente para finalizar la venta.');
            return;
        }

        if (count($this->saleItems) == 0) {
            session()->flash('error', 'No hay productos en el carrito para finalizar la venta.');
            return;
        }

        DB::transaction(function () {
            // Create the sale
            $sale = Sale::create([
                'company_id' => Auth::user()->company_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'customer_id' => $this->selectedCustomerId,
                'seller_id' => Auth::id(),
                'cashier_id' => Auth::id(),
                'payment_currency' => $this->payment_currency,
                'payment_method' => $this->payment_method,
                'payment_type' => $this->payment_type,
                'exchange_rate' => $this->exchange_rate,                
                'subtotal_usd' => $this->subtotal,
                'tax' => $this->tax,
                'tax_porcentaje' => $this->impuesto * 100,                
                'total_usd' => $this->total,
                'pending_balance' => $this->total,
                'status' => 'pending',
                'notes' => '',            
                
            ]);
               

            foreach ($this->saleItems as $item) {
                // Create sale item
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],                    
                    'subtotal_usd' => $item['quantity'] * $item['price'],
                ]);

                // Update product stock and record inventory movement
                $product = Product::find($item['product_id']);
                $this->recordInventoryMovement(
                    $product,
                    Auth::id(),
                    'out', // Movement type for sale
                    $item['quantity'],
                    $sale->id,
                    Sale::class
                );
            }

            // Asignar el ID de la venta para el botón de imprimir
            $this->lastSaleId = $sale->id;
            session()->flash('message', 'Venta finalizada exitosamente.');

        });
    }

    public function startNewSale()
    {
        $this->resetComponent();
    }

    public function cancelSale()
    {
        session()->flash('info', 'Venta cancelada.');
        $this->resetComponent();
    }

    private function resetComponent()
    {
        $this->reset([
            'lastSaleId',
            'customerSearch',
            'customers',
            'selectedCustomerId',
            'selectedCustomer',
            'productSearch',
            'products',
            'saleItems',
            'subtotal',
            'tax',
            'total',
            'quantity',
            'newCustomer',
        ]);
        $this->mount(); // Re-initialize customers and totals
    }

    private function recordInventoryMovement(Product $product, $userId, $movementType, $quantity, $referenceId, $referenceType)
    {
        DB::transaction(function () use ($product, $userId, $movementType, $quantity, $referenceId, $referenceType) {
            // Update product stock
            if ($movementType === 'in') {
                $product->increment('current_stock', $quantity);
            } elseif ($movementType === 'out') {
                $product->decrement('current_stock', $quantity);
            }

            // Create inventory movement record
            InventoryMovement::create([
                'company_id' => Auth::user()->company_id,
                'product_id' => $product->id,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'reference_id' => $referenceId,
                'reference_type' => 'sale',
                'exchange_rate' => $this->exchange_rate,
                'notes' => 'punto de venta',
                'user_id' => $userId, 
                
            ]);
        });
    }

    public function abrirClienteModal()  {
        
        $this->abrirModalCliente = true;

    }

    //cerrar modal cliente
    public function cerrarClienteModal()  {
        
        $this->abrirModalCliente = false;

    }

    //crear funcion para tener un numero de factura unico por compañia
    private function generateInvoiceNumber()
    {
        $lastSale = Sale::where('company_id', Auth::user()->company_id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastSale) {
            $lastInvoiceNumber = intval($lastSale->invoice_number);
            $newInvoiceNumber = str_pad($lastInvoiceNumber + 1, 6, '0', STR_PAD_LEFT);
        } else {
            $newInvoiceNumber = '000001';
        }

        return $newInvoiceNumber;
    }



    public function render()
    {
        return view('livewire.point-of-sale');
    }
}
