<?php

namespace App\Livewire;

use App\Models\Purchase;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Supplier;
use App\Models\User;

class PurchaseCrud extends Component
{
    use WithPagination;

    // Model properties
    public $purchase_id, $invoice_number, $supplier_id, $user_id,
           $payment_currency, $exchange_rate, $subtotal, $tax, $total,
           $status, $notes;

    // Control properties
    public $isModalOpen = false;
    public $search = '';
    public $suppliers = [];
    public $users = []; // User who made the purchase

    protected $rules = [
        'invoice_number' => 'required|string|max:50',
        'supplier_id' => 'required|exists:suppliers,id',
        'user_id' => 'required|exists:users,id',
        'payment_currency' => 'required|in:BS,USD',
        'exchange_rate' => 'required|numeric|min:0',
        'subtotal' => 'required|numeric|min:0',
        'tax' => 'required|numeric|min:0',
        'total' => 'required|numeric|min:0',
        'status' => 'required|in:pending,received,cancelled',
        'notes' => 'nullable|string',
    ];

    public function mount()
    {
        $company_id = Auth::user()->company_id;
        $this->suppliers = Supplier::where('company_id', $company_id)->get(['id', 'name']);
        $this->users = User::where('company_id', $company_id)->get(['id', 'name']);
    }

    public function render()
    {
        $company_id = Auth::user()->company_id;
        $purchases = Purchase::where('company_id', $company_id)
            ->with(['supplier', 'user'])
            ->where(function($query) {
                $query->where('invoice_number', 'like', '%' . $this->search . '%')
                      ->orWhereHas('supplier', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('user', function($q) {
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
        // Not implementing direct creation here, as purchases typically involve PurchaseItems
        $this->resetInputFields();
        $this->isModalOpen = true;
        session()->flash('info', 'La creación de compras se realiza a través de un sistema de gestión de inventario o un módulo de compras dedicado.');
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
            'purchase_id', 'invoice_number', 'supplier_id', 'user_id',
            'payment_currency', 'exchange_rate', 'subtotal', 'tax', 'total',
            'status', 'notes'
        ]);
    }

    public function store()
    {
        // Store method intentionally left basic for same reasons as create()
        $this->validate();
        session()->flash('message', 'La creación de compras directas no está soportada en este CRUD.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        $purchase = Purchase::where('company_id', $company_id)
                            ->with(['supplier', 'user'])
                            ->findOrFail($id);

        $this->purchase_id = $id;
        $this->invoice_number = $purchase->invoice_number;
        $this->supplier_id = $purchase->supplier_id;
        $this->user_id = $purchase->user_id;
        $this->payment_currency = $purchase->payment_currency;
        $this->exchange_rate = $purchase->exchange_rate;
        $this->subtotal = $purchase->subtotal;
        $this->tax = $purchase->tax;
        $this->total = $purchase->total;
        $this->status = $purchase->status;
        $this->notes = $purchase->notes;

        $this->openModal();
    }

    public function update()
    {
        $company_id = Auth::user()->company_id;
        $this->validate([
            'invoice_number' => 'required|string|max:50|unique:purchases,invoice_number,' . $this->purchase_id . ',id,company_id,' . $company_id,
            'supplier_id' => 'required|exists:suppliers,id',
            'user_id' => 'required|exists:users,id',
            'payment_currency' => 'required|in:BS,USD',
            'exchange_rate' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'tax' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'status' => 'required|in:pending,received,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($this->purchase_id) {
            $purchase = Purchase::where('company_id', $company_id)->findOrFail($this->purchase_id);
            $purchase->update([
                'invoice_number' => $this->invoice_number,
                'supplier_id' => $this->supplier_id,
                'user_id' => $this->user_id,
                'payment_currency' => $this->payment_currency,
                'exchange_rate' => $this->exchange_rate,
                'subtotal' => $this->subtotal,
                'tax' => $this->tax,
                'total' => $this->total,
                'status' => $this->status,
                'notes' => $this->notes,
            ]);
            
            session()->flash('message', 'Compra actualizada exitosamente.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function delete($id)
    {
        $company_id = Auth::user()->company_id;
        Purchase::where('company_id', $company_id)->findOrFail($id)->delete();
        session()->flash('message', 'Compra eliminada exitosamente.');
    }
}
