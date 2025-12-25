<?php

namespace App\Livewire;

use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Sale;
use App\Models\Customer;
use App\Models\User;

class PaymentCrud extends Component
{
    use WithPagination;

    // Model properties
    public $payment_id, $sale_id, $customer_id, $amount_local, $amount_usd,
           $payment_method, $reference, $notes, $user_id;

    // Control properties
    public $isModalOpen = false;
    public $search = '';

    // Data for dropdowns (for display in modal)
    public $sales = [];
    public $customers = [];
    public $users = [];

    protected $rules = [
        'reference' => 'nullable|string|max:100',
        'notes' => 'nullable|string',
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
        $payments = Payment::where('company_id', $company_id)
            ->with(['sale', 'customer', 'user'])
            ->where(function($query) {
                $query->where('reference', 'like', '%' . $this->search . '%')
                      ->orWhere('notes', 'like', '%' . $this->search . '%')
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

        return view('livewire.payment-crud', [
            'payments' => $payments
        ]);
    }

    // Direct creation is not supported for payments
    public function create()
    {
        session()->flash('info', 'Los pagos no se pueden crear directamente desde aquí. Son generados por ventas u otros procesos financieros.');
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
            'payment_id', 'sale_id', 'customer_id', 'amount_local', 'amount_usd',
            'payment_method', 'reference', 'notes', 'user_id'
        ]);
    }

    // Store is not supported
    public function store()
    {
        session()->flash('error', 'La creación directa de pagos no está permitida.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        $payment = Payment::where('company_id', $company_id)
                        ->with(['sale', 'customer', 'user'])
                        ->findOrFail($id);

        $this->payment_id = $id;
        $this->sale_id = $payment->sale_id;
        $this->customer_id = $payment->customer_id;
        $this->amount_local = $payment->amount_local;
        $this->amount_usd = $payment->amount_usd;
        $this->payment_method = $payment->payment_method;
        $this->reference = $payment->reference;
        $this->notes = $payment->notes;
        $this->user_id = $payment->user_id;

        $this->openModal();
    }

    public function update()
    {
        $company_id = Auth::user()->company_id;
        $this->validate(); // Only validates reference and notes as per rules

        if ($this->payment_id) {
            $payment = Payment::where('company_id', $company_id)->findOrFail($this->payment_id);
            $payment->update([
                'reference' => $this->reference,
                'notes' => $this->notes,
            ]);
            
            session()->flash('message', 'Pago actualizado exitosamente.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function delete($id)
    {
        session()->flash('error', 'La eliminación directa de pagos no está permitida para mantener la integridad financiera.');
    }
}
