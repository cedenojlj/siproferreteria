<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ExchangeRate;
use Livewire\WithPagination;

class ExchangeRateManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $rateId;
    public $rate;
    public $source;
    public $is_active;

    public $showModal = false;
    public $modalTitle = '';
    public $showDeleteModal = false;
    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected function rules()
    {
        return [
            'rate' => 'required|numeric|min:0',
            'source' => 'required|string|max:50',
            'is_active' => 'boolean',
        ];
    }

    public function mount()
    {
        $this->is_active = true;
        $this->source = 'MANUAL';
    }

    public function render()
    {
        $query = ExchangeRate::where('company_id', auth()->user()->company_id);

        if ($this->search) {
            $query->where('rate', 'like', '%' . $this->search . '%')
                  ->orWhere('source', 'like', '%' . $this->search . '%');
        }

        $rates = $query->orderBy($this->sortField, $this->sortDirection)->paginate(10);

        return view('livewire.exchange-rate-manager', [
            'rates' => $rates,
        ]);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function create()
    {
        $this->resetForm();
        $this->modalTitle = 'Crear Nueva Tasa de Cambio';
        $this->showModal = true;
        $this->dispatch('show-modal');
    }

    public function edit($id)
    {
        $rate = ExchangeRate::findOrFail($id);
        $this->rateId = $rate->id;
        $this->rate = $rate->rate;
        $this->source = $rate->source;
        $this->is_active = $rate->is_active;

        $this->modalTitle = 'Editar Tasa de Cambio';
        $this->showModal = true;
        $this->dispatch('show-modal');
    }

    public function save()
    {
        $this->validate();

        $data = [
            'company_id' => auth()->user()->company_id,
            'rate' => $this->rate,
            'source' => $this->source,
            'is_active' => $this->is_active,
        ];

        if ($this->rateId) {
            $rate = ExchangeRate::find($this->rateId);
            $rate->update($data);
            session()->flash('message', 'Tasa de cambio actualizada exitosamente.');
        } else {
            ExchangeRate::create($data);
            session()->flash('message', 'Tasa de cambio creada exitosamente.');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->rateId = $id;
        $this->showDeleteModal = true;
        $this->dispatch('show-delete-modal');
    }

    public function delete()
    {
        ExchangeRate::find($this->rateId)->delete();
        $this->showDeleteModal = false;
        session()->flash('message', 'Tasa de cambio eliminada exitosamente.');
        $this->dispatch('hide-delete-modal');
    }

    public function toggleStatus($id)
    {
        $rate = ExchangeRate::find($id);
        $rate->is_active = !$rate->is_active;
        $rate->save();
        session()->flash('message', 'Estado de la tasa de cambio actualizado.');
    }
    
    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
        $this->dispatch('hide-modal');
        $this->dispatch('hide-delete-modal');
    }

    public function resetForm()
    {
        $this->reset(['rateId', 'rate', 'source', 'is_active']);
        $this->is_active = true;
        $this->source = 'MANUAL';
    }
}
