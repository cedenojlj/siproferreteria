<?php

namespace App\Livewire;

use App\Models\InventoryMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\User;

class InventoryMovementCrud extends Component
{
    use WithPagination;

    // Model properties (all read-only for display)
    public $movement_id, $product_id, $movement_type, $quantity,
           $reference_id, $reference_type, $exchange_rate, $notes, $user_id;

    // Control properties
    public $isModalOpen = false;
    public $search = '';

    // Data for dropdowns (though fields are read-only, good for context)
    public $products = [];
    public $users = [];

    protected $rules = [
        'notes' => 'nullable|string', // Only notes are potentially editable
    ];

    public function mount()
    {
        $company_id = Auth::user()->company_id;
        $this->products = Product::where('company_id', $company_id)->get(['id', 'name']);
        $this->users = User::where('company_id', $company_id)->get(['id', 'name']);
    }

    public function render()
    {
        $company_id = Auth::user()->company_id;
        $movements = InventoryMovement::where('company_id', $company_id)
            ->with(['product', 'user'])
            ->where(function($query) {
                $query->where('movement_type', 'like', '%' . $this->search . '%')
                      ->orWhere('quantity', 'like', '%' . $this->search . '%')
                      ->orWhere('notes', 'like', '%' . $this->search . '%')
                      ->orWhereHas('product', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      })
                      ->orWhereHas('user', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.inventory-movement-crud', [
            'movements' => $movements
        ]);
    }

    // Direct creation is not supported for inventory movements
    public function create()
    {
        session()->flash('info', 'Los movimientos de inventario no se pueden crear directamente desde aquí. Son generados por ventas, compras, devoluciones o ajustes específicos.');
        $this->closeModal(); // Ensure modal is closed or not opened
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
            'movement_id', 'product_id', 'movement_type', 'quantity',
            'reference_id', 'reference_type', 'exchange_rate', 'notes', 'user_id'
        ]);
    }

    // Store is not supported
    public function store()
    {
        session()->flash('error', 'La creación directa de movimientos de inventario no está permitida.');
        $this->closeModal();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        $movement = InventoryMovement::where('company_id', $company_id)
                                     ->with(['product', 'user'])
                                     ->findOrFail($id);

        $this->movement_id = $id;
        $this->product_id = $movement->product_id;
        $this->movement_type = $movement->movement_type;
        $this->quantity = $movement->quantity;
        $this->reference_id = $movement->reference_id;
        $this->reference_type = $movement->reference_type;
        $this->exchange_rate = $movement->exchange_rate;
        $this->notes = $movement->notes;
        $this->user_id = $movement->user_id;

        $this->openModal();
    }

    public function update()
    {
        // Only notes might be editable, but for safety, we'll keep all read-only
        // and only allow updating if explicitly designed for it.
        // For this task, we will consider all fields non-editable via this CRUD.
        session()->flash('error', 'La edición directa de movimientos de inventario no está permitida.');
        $this->closeModal();
    }

    public function delete($id)
    {
        // Deletion of inventory movements is also generally not allowed to maintain history.
        session()->flash('error', 'La eliminación directa de movimientos de inventario no está permitida para mantener la trazabilidad.');
    }
}
