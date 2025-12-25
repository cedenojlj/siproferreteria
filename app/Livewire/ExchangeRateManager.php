<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class ExchangeRateManager extends Component
{
    use WithPagination;

    // Propiedades del modelo
    public $rate_id, $rate, $source, $is_active;

    // Propiedades de control
    public $isModalOpen = false;
    public $search = '';

    protected function rules()
    {
        return [
            'rate' => 'required|numeric|min:0',
            'source' => 'required|string|max:50',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        $company_id = Auth::user()->company_id;
        $rates = ExchangeRate::where('company_id', $company_id)
            ->where(function($query) {
                $query->where('rate', 'like', '%' . $this->search . '%')
                      ->orWhere('source', 'like', '%' . $this->search . '%');
            })->orderBy('id', 'desc')->paginate(10);

        return view('livewire.exchange-rate-manager', [
            'rates' => $rates,
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
        $this->reset(['rate_id', 'rate', 'source', 'is_active']);
        $this->is_active = true; // Valor por defecto
        $this->source = 'MANUAL'; // Valor por defecto
    }

    public function store()
    {
        $this->validate();

        ExchangeRate::create([
            'company_id' => Auth::user()->company_id,
            'rate' => $this->rate,
            'source' => $this->source,
            'is_active' => $this->is_active,
        ]);

        session()->flash('message', 'Tasa de cambio creada exitosamente.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        $rate = ExchangeRate::where('company_id', $company_id)->findOrFail($id);

        $this->rate_id = $id;
        $this->rate = $rate->rate;
        $this->source = $rate->source;
        $this->is_active = $rate->is_active;

        $this->openModal();
    }

    public function update()
    {
        $this->validate();

        if ($this->rate_id) {
            $company_id = Auth::user()->company_id;
            $rate = ExchangeRate::where('company_id', $company_id)->findOrFail($this->rate_id);
            $rate->update([
                'rate' => $this->rate,
                'source' => $this->source,
                'is_active' => $this->is_active,
            ]);
            
            session()->flash('message', 'Tasa de cambio actualizada exitosamente.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function delete($id)
    {
        $company_id = Auth::user()->company_id;
        ExchangeRate::where('company_id', $company_id)->findOrFail($id)->delete();
        session()->flash('message', 'Tasa de cambio eliminada exitosamente.');
    }
    
    public function toggleStatus($id)
    {
        $company_id = Auth::user()->company_id;
        $rate = ExchangeRate::where('company_id', $company_id)->findOrFail($id);
        $rate->is_active = !$rate->is_active;
        $rate->save();
        session()->flash('message', 'Estado de la tasa de cambio actualizado.');
    }
}
