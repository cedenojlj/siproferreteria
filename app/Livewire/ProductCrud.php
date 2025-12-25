<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Category;
use App\Models\UnitMeasure;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class ProductCrud extends Component
{
    use WithPagination;

    // Propiedades del modelo
    public $product_id, $category_id, $unit_measure_id, $barcode, $name, $description, $brand, $model;
    public $base_price, $usd_price, $cost, $min_stock, $current_stock;

    // Propiedades de control
    public $isModalOpen = false;
    public $search = '';

    // Colecciones para los select
    public $categories, $unitMeasures;

    protected $rules = [
        'name' => 'required|string|max:200',
        'category_id' => 'required|exists:categories,id',
        'unit_measure_id' => 'required|exists:unit_measures,id',
        'base_price' => 'required|numeric|min:0',
        'cost' => 'required|numeric|min:0',
        'current_stock' => 'required|integer|min:0',
        'min_stock' => 'required|integer|min:0',
        'barcode' => 'nullable|string|max:100',
        'description' => 'nullable|string',
        'brand' => 'nullable|string|max:100',
        'model' => 'nullable|string|max:100',
        'usd_price' => 'nullable|numeric|min:0',
    ];

    public function mount()
    {
        $company_id = Auth::user()->company_id;
        $this->categories = Category::where('company_id', $company_id)->get();
        // UnitMeasures es global, no necesita filtro de compañía
        $this->unitMeasures = UnitMeasure::all();
    }

    public function render()
    {
        $company_id = Auth::user()->company_id;
        $products = Product::where('company_id', $company_id)
            ->with(['category', 'unitMeasure'])
            ->where(function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('barcode', 'like', '%' . $this->search . '%');
            })
            ->paginate(10);

        return view('livewire.product-crud', [
            'products' => $products
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
        $this->reset(['product_id', 'category_id', 'unit_measure_id', 'barcode', 'name', 'description', 'brand', 'model', 'base_price', 'usd_price', 'cost', 'min_stock', 'current_stock']);
    }

    public function store()
    {
        $this->validate();
        $company_id = Auth::user()->company_id;

        Product::create([
            'company_id' => $company_id,
            'name' => $this->name,
            'category_id' => $this->category_id,
            'unit_measure_id' => $this->unit_measure_id,
            'base_price' => $this->base_price,
            'cost' => $this->cost,
            'current_stock' => $this->current_stock,
            'min_stock' => $this->min_stock,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'brand' => $this->brand,
            'model' => $this->model,
            'usd_price' => $this->usd_price,
        ]);

        session()->flash('message', 'Producto creado exitosamente.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;
        $product = Product::where('company_id', $company_id)->findOrFail($id);

        $this->product_id = $id;
        $this->name = $product->name;
        $this->category_id = $product->category_id;
        $this->unit_measure_id = $product->unit_measure_id;
        $this->base_price = $product->base_price;
        $this->cost = $product->cost;
        $this->current_stock = $product->current_stock;
        $this->min_stock = $product->min_stock;
        $this->barcode = $product->barcode;
        $this->description = $product->description;
        $this->brand = $product->brand;
        $this->model = $product->model;
        $this->usd_price = $product->usd_price;

        $this->openModal();
    }

    public function update()
    {
        // Añadir regla de validación única para el barcode, ignorando el producto actual
        $this->rules['barcode'] = 'nullable|string|max:100|unique:products,barcode,' . $this->product_id;
        $this->validate();
        $company_id = Auth::user()->company_id;

        if ($this->product_id) {
            $product = Product::where('company_id', $company_id)->findOrFail($this->product_id);
            $product->update([
                'name' => $this->name,
                'category_id' => $this->category_id,
                'unit_measure_id' => $this->unit_measure_id,
                'base_price' => $this->base_price,
                'cost' => $this->cost,
                'current_stock' => $this->current_stock,
                'min_stock' => $this->min_stock,
                'barcode' => $this->barcode,
                'description' => $this->description,
                'brand' => $this->brand,
                'model' => $this->model,
                'usd_price' => $this->usd_price,
            ]);
            session()->flash('message', 'Producto actualizado exitosamente.');
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function delete($id)
    {
        $company_id = Auth::user()->company_id;
        Product::where('company_id', $company_id)->findOrFail($id)->delete();
        session()->flash('message', 'Producto eliminado exitosamente.');
    }
}
