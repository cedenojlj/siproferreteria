<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerCrud extends Component
{
    use WithPagination;

    // Propiedades del modelo
    public $customer_id, $document_type, $document, $name, $phone, $email, $address;
    public $credit_limit, $pending_balance; // No se editan directamente, solo se muestran

    // Propiedades de control
    public $isModalOpen = false;
    public $search = '';

    protected $rules = [
        'document_type' => 'required|in:V,J,G,P',
        'document' => 'required|string|max:20',
        'name' => 'required|string|max:200',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:100',
        'address' => 'nullable|string',
    ];

    public function render()
    {
        $company_id = Auth::user()->company_id;
        $customers = Customer::where('company_id', $company_id)
            ->where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('document', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.customer-crud', [
            'customers' => $customers
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->document_type = 'V'; // Default value
        $this->openModal();
    }

    public function openModal()
    {
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
    }

    private function resetInputFields()
    {
        $this->reset(['customer_id', 'document_type', 'document', 'name', 'phone', 'email', 'address']);
    }

    public function store()
    {
        $company_id = Auth::user()->company_id;

        $this->rules['document'] = [
            'required',
            'string',
            'max:20',
            Rule::unique('customers')->where(function ($query) use ($company_id) {
                return $query->where('company_id', $company_id);
            }),
        ];
        $this->validate();

        Customer::create([
            'company_id' => $company_id,
            'document_type' => $this->document_type,
            'document' => $this->document,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            // credit_limit and pending_balance default to 0.00
        ]);

        session()->flash('message', 'Cliente creado exitosamente.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        $customer = Customer::where('company_id', $company_id)->findOrFail($id);

        $this->customer_id = $id;
        $this->document_type = $customer->document_type;
        $this->document = $customer->document;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->email = $customer->email;
        $this->address = $customer->address;

        $this->openModal();
    }

    public function update()
    {
        $company_id = Auth::user()->company_id;

        $this->rules['document'] = [
            'required',
            'string',
            'max:20',
            Rule::unique('customers')->where(function ($query) use ($company_id) {
                return $query->where('company_id', $company_id);
            })->ignore($this->customer_id),
        ];
        $this->validate();

        if ($this->customer_id) {
            $customer = Customer::where('company_id', $company_id)->findOrFail($this->customer_id);
            $customer->update([
                'document_type' => $this->document_type,
                'document' => $this->document,
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
            ]);
            
            session()->flash('message', 'Cliente actualizado exitosamente.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function delete($id)
    {
        $company_id = Auth::user()->company_id;
        Customer::where('company_id', $company_id)->findOrFail($id)->delete();
        session()->flash('message', 'Cliente eliminado exitosamente.');
    }
}
