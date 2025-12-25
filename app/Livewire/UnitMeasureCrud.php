<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\UnitMeasure;
use Livewire\WithPagination;

class UnitMeasureCrud extends Component
{
    use WithPagination;

    public $name, $symbol, $unit_measure_id;
    public $isModalOpen = false;

    public function render()
    {
        return view('livewire.unit-measure-crud', [
            'unit_measures' => UnitMeasure::paginate(10)
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
        $this->symbol = '';
        $this->unit_measure_id = null;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:50',
            'symbol' => 'required|string|max:10|unique:unit_measures,symbol',
        ]);

        UnitMeasure::create([
            'name' => $this->name,
            'symbol' => $this->symbol,
        ]);

        session()->flash('message', 'Unidad de Medida creada exitosamente.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $unit_measure = UnitMeasure::findOrFail($id);
        $this->unit_measure_id = $id;
        $this->name = $unit_measure->name;
        $this->symbol = $unit_measure->symbol;

        $this->openModal();
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:50',
            'symbol' => 'required|string|max:10|unique:unit_measures,symbol,' . $this->unit_measure_id,
        ]);

        if ($this->unit_measure_id) {
            $unit_measure = UnitMeasure::find($this->unit_measure_id);
            $unit_measure->update([
                'name' => $this->name,
                'symbol' => $this->symbol,
            ]);
            
            session()->flash('message', 'Unidad de Medida actualizada exitosamente.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function delete($id)
    {
        UnitMeasure::find($id)->delete();
        session()->flash('message', 'Unidad de Medida eliminada exitosamente.');
    }
}
