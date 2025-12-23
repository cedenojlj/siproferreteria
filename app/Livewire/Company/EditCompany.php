<?php

namespace App\Livewire\Company;

use App\Models\Company;
use Livewire\Component;

class EditCompany extends Component
{
    public Company $company;

    // Propiedades públicas enlazadas al formulario
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $address = '';
    public string $tax_id = '';
    
    // Notificación de éxito
    public bool $saved = false;

    /**
     * Reglas de validación para los campos del formulario.
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:companies,email,' . $this->company->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'tax_id' => 'nullable|string|max:50',
        ];
    }

    /**
     * Se ejecuta cuando el componente es inicializado.
     * Carga los datos de la empresa en las propiedades del formulario.
     */
    public function mount(): void
    {
        //buscar company segun el company_id del usuario autenticado
        $company = Company::find(auth()->user()->company_id);
        
        $this->company = $company;
        $this->name = $company->name;
        $this->email = $company->email;
        $this->phone = $company->phone ?? '';
        $this->address = $company->address ?? '';
        $this->tax_id = $company->tax_id ?? '';
    }

    /**
     * Valida los datos y actualiza la empresa en la base de datos.
     */
    public function update(): void
    {
        $validatedData = $this->validate();

        $this->company->update($validatedData);

        $this->saved = true;
    }
    
    /**
     * Renderiza la vista del componente.
     */
    public function render()
    {
        return view('livewire.company.edit-company');
    }
}
