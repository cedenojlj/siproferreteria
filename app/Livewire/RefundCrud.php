<?php

namespace App\Livewire;

use App\Models\Refund;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;

class RefundCrud extends Component
{
    use WithPagination;

    // Model properties
    public $refund_id, $sale_id, $customer_id, $user_id,
           $total_amount_usd, $tax_returned_local, $status,
           $refund_method, $credit_note_number, $reason;

    // Control properties
    public $isModalOpen = false;
    public $search = '';

    // Data for dropdowns (read-only in form, but useful for display)
    public $sales = [];
    public $customers = [];
    public $users = [];

    protected $rules = [
        'status' => 'required|in:pending,approved,rejected,completed,cancelled',
        'reason' => 'nullable|string',
        // Other fields are not directly editable via this CRUD
    ];

    public function mount()
    {
        $company_id = Auth::user()->company_id;
        $this->sales = Sale::where('company_id', $company_id)->get(['id', 'invoice_number']);
        $this->customers = Customer::where('company_id', $company_id)->get(['id', 'name']);
        $this->users = User::where('company_id', $company_id)->get(['id', 'name']);
    }

    public function render()
    {
        $company_id = Auth::user()->company_id;
        $refunds = Refund::where('company_id', $company_id)
            ->with(['sale', 'customer', 'user'])
            ->where(function($query) {
                $query->where('status', 'like', '%' . $this->search . '%')
                      ->orWhere('reason', 'like', '%' . $this->search . '%')
                      ->orWhereHas('sale', function($q) {
                          $q->where('invoice_number', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('customer', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('user', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.refund-crud', [
            'refunds' => $refunds
        ]);
    }

    // Direct creation is not supported for refunds
    public function create()
    {
        session()->flash('info', 'Las devoluciones no se pueden crear directamente desde aquí. Son generadas a partir de ventas.');
        $this->closeModal();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetValidation();
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->reset([
            'refund_id', 'sale_id', 'customer_id', 'user_id',
            'total_amount_usd', 'tax_returned_local', 'status',
            'refund_method', 'credit_note_number', 'reason'
        ]);
    }

    // Store is not supported
    public function store()
    {
        session()->flash('error', 'La creación directa de devoluciones no está permitida.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        $refund = Refund::where('company_id', $company_id)
                        ->with(['sale', 'customer', 'user'])
                        ->findOrFail($id);

        $this->refund_id = $id;
        $this->sale_id = $refund->sale_id;
        $this->customer_id = $refund->customer_id;
        $this->user_id = $refund->user_id;
        $this->total_amount_usd = $refund->total_amount_usd;
        $this->tax_returned_local = $refund->tax_returned_local;
        $this->status = $refund->status;
        $this->refund_method = $refund->refund_method;
        $this->credit_note_number = $refund->credit_note_number;
        $this->reason = $refund->reason;

        $this->openModal();
    }

    public function update()
    {
        $company_id = Auth::user()->company_id;
        $this->validate(); // Only validates status and reason as per rules

        if ($this->refund_id) {
            $refund = Refund::where('company_id', $company_id)->findOrFail($this->refund_id);
            $refund->update([
                'status' => $this->status,
                'reason' => $this->reason,
            ]);
            
            session()->flash('message', 'Devolución actualizada exitosamente.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function delete($id)
    {
        session()->flash('error', 'La eliminación directa de devoluciones no está permitida para mantener la trazabilidad.');
    }
}
