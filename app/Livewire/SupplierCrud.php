<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Supplier;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SupplierCrud extends Component
{
    use WithPagination;

    // Propiedades del modelo
    public $supplier_id, $rif, $name, $phone, $email, $address, $contact_person;

    // Propiedades de control
    public $isModalOpen = false;
    public $search = '';

    protected $rules = [
        'rif' => 'required|string|max:20',
        'name' => 'required|string|max:200',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:100',
        'address' => 'nullable|string',
        'contact_person' => 'nullable|string|max:100',
    ];

    public function render()
    {
        $company_id = Auth::user()->company_id;
        $suppliers = Supplier::where('company_id', $company_id)
            ->where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('rif', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.supplier-crud', [
            'suppliers' => $suppliers
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
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
        $this->reset(['supplier_id', 'rif', 'name', 'phone', 'email', 'address', 'contact_person']);
    }

    public function store()
    {
        $company_id = Auth::user()->company_id;

        $this->rules['rif'] = [
            'required',
            'string',
            'max:20',
            Rule::unique('suppliers')->where(function ($query) use ($company_id) {
                return $query->where('company_id', $company_id);
            }),
        ];
        $this->validate();

        Supplier::create([
            'company_id' => $company_id,
            'rif' => $this->rif,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'contact_person' => $this->contact_person,
        ]);

        session()->flash('message', 'Proveedor creado exitosamente.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        $supplier = Supplier::where('company_id', $company_id)->findOrFail($id);

        $this->supplier_id = $id;
        $this->rif = $supplier->rif;
        $this->name = $supplier->name;
        $this->phone = $supplier->phone;
        $this->email = $supplier->email;
        $this->address = $supplier->address;
        $this->contact_person = $supplier->contact_person;

        $this->openModal();
    }

    public function update()
    {
        $company_id = Auth::user()->company_id;

        $this->rules['rif'] = [
            'required',
            'string',
            'max:20',
            Rule::unique('suppliers')->where(function ($query) use ($company_id) {
                return $query->where('company_id', $company_id);
            })->ignore($this->supplier_id),
        ];
        $this->validate();

        if ($this->supplier_id) {
            $supplier = Supplier::where('company_id', $company_id)->findOrFail($this->supplier_id);
            $supplier->update([
                'rif' => $this->rif,
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'address' => $this->address,
                'contact_person' => $this->contact_person,
            ]);
            
            session()->flash('message', 'Proveedor actualizado exitosamente.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function delete($id)
    {
        $company_id = Auth::user()->company_id;
        Supplier::where('company_id', $company_id)->findOrFail($id)->delete();
        session()->flash('message', 'Proveedor eliminado exitosamente.');
    }
}
