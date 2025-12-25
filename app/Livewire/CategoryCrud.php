<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class CategoryCrud extends Component
{
    use WithPagination;

    public $name, $description, $category_id;
    public $isModalOpen = false;

    public function render()
    {
        // Solo mostrar categorías de la compañía del usuario logueado
        $company_id = Auth::user()->company_id;
        return view('livewire.category-crud', [
            'categories' => Category::where('company_id', $company_id)->paginate(10)
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
        $this->name = '';
        $this->description = '';
        $this->category_id = null;
    }

    public function store()
    {
        $company_id = Auth::user()->company_id;
        $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        Category::create([
            'company_id' => $company_id,
            'name' => $this->name,
            'description' => $this->description,
        ]);

        session()->flash('message', 'Categoría creada exitosamente.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        // Asegurarse de que la categoría pertenezca a la compañía del usuario
        $category = Category::where('company_id', $company_id)->findOrFail($id);
        
        $this->category_id = $id;
        $this->name = $category->name;
        $this->description = $category->description;

        $this->openModal();
    }

    public function update()
    {
        $company_id = Auth::user()->company_id;
        $this->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        if ($this->category_id) {
            // Asegurarse de que la categoría pertenezca a la compañía del usuario
            $category = Category::where('company_id', $company_id)->findOrFail($this->category_id);
            
            $category->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            
            session()->flash('message', 'Categoría actualizada exitosamente.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function delete($id)
    {
        $company_id = Auth::user()->company_id;
        // Asegurarse de que la categoría pertenezca a la compañía del usuario antes de borrar
        Category::where('company_id', $company_id)->findOrFail($id)->delete();
        session()->flash('message', 'Categoría eliminada exitosamente.');
    }
}
