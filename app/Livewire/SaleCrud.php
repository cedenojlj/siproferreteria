<?php

namespace App\Livewire;

use App\Models\Sale;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Customer;
use App\Models\User;

class SaleCrud extends Component
{
    use WithPagination;

    // Model properties
    public $sale_id, $invoice_number, $customer_id, $seller_id, $cashier_id,
           $payment_currency, $payment_method, $payment_type, $exchange_rate,
           $subtotal_local, $subtotal_usd, $tax_local, $total_local, $total_usd,
           $pending_balance, $status, $notes;

    // Control properties
    public $isModalOpen = false;
    public $search = '';
    public $customers = [];
    public $users = []; // To hold sellers/cashiers

    protected $rules = [
        'invoice_number' => 'required|string|max:50',
        'customer_id' => 'nullable|exists:customers,id',
        'seller_id' => 'required|exists:users,id',
        'cashier_id' => 'required|exists:users,id',
        'payment_currency' => 'required|in:BS,USD',
        'payment_method' => 'required|in:CASH,WIRE_TRANSFER,MOBILE_PAYMENT,ZELLE,BANESCO_PANAMA,OTHER',
        'payment_type' => 'required|in:cash,credit',
        'exchange_rate' => 'required|numeric|min:0',
        'subtotal_local' => 'required|numeric|min:0',
        'subtotal_usd' => 'required|numeric|min:0',
        'tax_local' => 'required|numeric|min:0',
        'total_local' => 'required|numeric|min:0',
        'total_usd' => 'required|numeric|min:0',
        'pending_balance' => 'required|numeric|min:0',
        'status' => 'required|in:pending,completed,cancelled,credit',
        'notes' => 'nullable|string',
    ];

    public function mount()
    {
        $company_id = Auth::user()->company_id;
        $this->customers = Customer::where('company_id', $company_id)->get(['id', 'name']);
        $this->users = User::where('company_id', $company_id)->get(['id', 'name']);
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
                      })
                      ->orWhereHas('seller', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('cashier', function($q) {
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
        // We are not implementing direct creation of sales via this CRUD,
        // as sales are typically created through a POS system with SaleItems.
        // This method will remain for consistency but will reset input fields.
        $this->resetInputFields();
        $this->isModalOpen = true;
        session()->flash('info', 'La creación de ventas se realiza a través del punto de venta.');
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation(); // Clear validation errors
    }

    private function resetInputFields()
    {
        $this->reset([
            'sale_id', 'invoice_number', 'customer_id', 'seller_id', 'cashier_id',
            'payment_currency', 'payment_method', 'payment_type', 'exchange_rate',
            'subtotal_local', 'subtotal_usd', 'tax_local', 'total_local', 'total_usd',
            'pending_balance', 'status', 'notes'
        ]);
    }

    // Store method is not fully functional for creating new sales here
    public function store()
    {
        // This method is intentionally left basic as new sales should be created via POS.
        // It's here mainly to prevent errors if a 'create' button somehow calls 'store'.
        $this->validate();
        session()->flash('message', 'La creación de ventas directas no está soportada en este CRUD.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        $sale = Sale::where('company_id', $company_id)
                    ->with(['customer', 'seller', 'cashier'])
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
        $this->subtotal_local = $sale->subtotal_local;
        $this->subtotal_usd = $sale->subtotal_usd;
        $this->tax_local = $sale->tax_local;
        $this->total_local = $sale->total_local;
        $this->total_usd = $sale->total_usd;
        $this->pending_balance = $sale->pending_balance;
        $this->status = $sale->status;
        $this->notes = $sale->notes;

        $this->openModal();
    }

    public function update()
    {
        $company_id = Auth::user()->company_id;
        $this->validate([
            'invoice_number' => 'required|string|max:50|unique:sales,invoice_number,' . $this->sale_id . ',id,company_id,' . $company_id,
            'customer_id' => 'nullable|exists:customers,id',
            'seller_id' => 'required|exists:users,id',
            'cashier_id' => 'required|exists:users,id',
            'payment_currency' => 'required|in:BS,USD',
            'payment_method' => 'required|in:CASH,WIRE_TRANSFER,MOBILE_PAYMENT,ZELLE,BANESCO_PANAMA,OTHER',
            'payment_type' => 'required|in:cash,credit',
            'exchange_rate' => 'required|numeric|min:0',
            'subtotal_local' => 'required|numeric|min:0',
            'subtotal_usd' => 'required|numeric|min:0',
            'tax_local' => 'required|numeric|min:0',
            'total_local' => 'required|numeric|min:0',
            'total_usd' => 'required|numeric|min:0',
            'pending_balance' => 'required|numeric|min:0',
            'status' => 'required|in:pending,completed,cancelled,credit',
            'notes' => 'nullable|string',
        ]);

        if ($this->sale_id) {
            $sale = Sale::where('company_id', $company_id)->findOrFail($this->sale_id);
            $sale->update([
                'invoice_number' => $this->invoice_number,
                'customer_id' => $this->customer_id,
                'seller_id' => $this->seller_id,
                'cashier_id' => $this->cashier_id,
                'payment_currency' => $this->payment_currency,
                'payment_method' => $this->payment_method,
                'payment_type' => $this->payment_type,
                'exchange_rate' => $this->exchange_rate,
                'subtotal_local' => $this->subtotal_local,
                'subtotal_usd' => $this->subtotal_usd,
                'tax_local' => $this->tax_local,
                'total_local' => $this->total_local,
                'total_usd' => $this->total_usd,
                'pending_balance' => $this->pending_balance,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);
            
            session()->flash('message', 'Venta actualizada exitosamente.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function delete($id)
    {
        $company_id = Auth::user()->company_id;
        Sale::where('company_id', $company_id)->findOrFail($id)->delete();
        session()->flash('message', 'Venta eliminada exitosamente.');
    }
}
