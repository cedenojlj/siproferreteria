<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Product;
use App\Models\Category;
use App\Models\UnitMeasure;
use Illuminate\Support\Facades\Storage;
use Milon\Barcode\Facades\DNS1DFacade as DNS1D;

class ProductManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'bootstrap';

    // Propiedades del formulario
    public $productId;
    public $barcode;
    public $name;
    public $description;
    public $brand;
    public $model;
    public $base_price = 0;
    public $usd_price = 0;
    public $cost = 0;
    public $category_id;
    public $unit_measure_id;
    public $min_stock = 10;
    public $current_stock = 0;
    public $is_active = true;
    public $photo;
    public $generateBarcode = false;
    public $margin = 0;
    
    // Propiedades de búsqueda y filtro
    public $search = '';
    public $categoryFilter = '';
    public $statusFilter = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    
    // Modal
    public $showModal = false;
    public $modalTitle = '';
    public $showDeleteModal = false;
    
    // Propiedades para el código de barras
    public $barcodeType = 'C128';
    public $barcodeWidth = 2;
    public $barcodeHeight = 30;

    protected $rules = [
        'barcode' => 'nullable|string|max:100|unique:products,barcode',
        'name' => 'required|string|max:200',
        'description' => 'nullable|string',
        'brand' => 'nullable|string|max:100',
        'model' => 'nullable|string|max:100',
        'base_price' => 'required|numeric|min:0',
        'usd_price' => 'required|numeric|min:0',
        'cost' => 'required|numeric|min:0',
        'category_id' => 'required|exists:categories,id',
        'unit_measure_id' => 'required|exists:unit_measures,id',
        'min_stock' => 'required|integer|min:0',
        'current_stock' => 'required|integer|min:0',
        'is_active' => 'boolean',
        'photo' => 'nullable|image|max:2048',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset([
            'productId', 'barcode', 'name', 'description', 'brand', 'model',
            'base_price', 'usd_price', 'cost', 'category_id', 'unit_measure_id',
            'min_stock', 'current_stock', 'is_active', 'photo', 'generateBarcode'
        ]);
        
        // Set default values
        $this->is_active = true;
        $this->min_stock = 10;
        $this->current_stock = 0;
        
        // Set first category and unit if available
        $firstCategory = Category::first();
        $firstUnit = UnitMeasure::first();
        
        if ($firstCategory) $this->category_id = $firstCategory->id;
        if ($firstUnit) $this->unit_measure_id = $firstUnit->id;
    }

    public function generateRandomBarcode()
    {
        $this->barcode = 'PROD' . date('YmdHis') . rand(100, 999);
    }

    public function render()
    {
        $query = Product::with(['category', 'unitMeasure'])
            ->where('company_id', auth()->user()->company_id);

        // Aplicar filtros
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categoryFilter) {
            $query->where('category_id', $this->categoryFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('is_active', $this->statusFilter);
        }

        // Aplicar ordenación
        $query->orderBy($this->sortField, $this->sortDirection);

        $products = $query->paginate(15);

        $categories = Category::where('company_id', auth()->user()->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $unitMeasures = UnitMeasure::where('company_id', auth()->user()->company_id)
            ->orderBy('name')
            ->get();

        return view('livewire.product-manager', [
            'products' => $products,
            'categories' => $categories,
            'unitMeasures' => $unitMeasures,
            'totalProducts' => Product::where('company_id', auth()->user()->company_id)->count(),
            'activeProducts' => Product::where('company_id', auth()->user()->company_id)
                ->where('is_active', true)
                ->count(),
            'lowStockProducts' => Product::where('company_id', auth()->user()->company_id)
                ->where('current_stock', '<=', 'min_stock')
                ->where('current_stock', '>', 0)
                ->count(),
            'outOfStockProducts' => Product::where('company_id', auth()->user()->company_id)
                ->where('current_stock', '=', 0)
                ->count(),
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
        $this->modalTitle = 'Crear Nuevo Producto';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        
        $this->productId = $product->id;
        $this->barcode = $product->barcode;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->brand = $product->brand;
        $this->model = $product->model;
        $this->base_price = $product->base_price;
        $this->usd_price = $product->usd_price;
        $this->cost = $product->cost;
        $this->category_id = $product->category_id;
        $this->unit_measure_id = $product->unit_measure_id;
        $this->min_stock = $product->min_stock;
        $this->current_stock = $product->current_stock;
        $this->is_active = $product->is_active;

        $this->modalTitle = 'Editar Producto';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        // Generar código de barras si está marcado
        if ($this->generateBarcode && empty($this->barcode)) {
            $this->generateRandomBarcode();
        }

        $data = [
            'company_id' => auth()->user()->company_id,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'description' => $this->description,
            'brand' => $this->brand,
            'model' => $this->model,
            'base_price' => $this->base_price,
            'usd_price' => $this->usd_price,
            'cost' => $this->cost,
            'category_id' => $this->category_id,
            'unit_measure_id' => $this->unit_measure_id,
            'min_stock' => $this->min_stock,
            'current_stock' => $this->current_stock,
            'is_active' => $this->is_active,
        ];

        // Guardar imagen si existe
        if ($this->photo) {
            $path = $this->photo->store('products', 'public');
            $data['image_path'] = $path;
        }

        if ($this->productId) {
            $product = Product::find($this->productId);
            $product->update($data);
            session()->flash('message', 'Producto actualizado exitosamente.');
        } else {
            Product::create($data);
            session()->flash('message', 'Producto creado exitosamente.');
        }

        $this->closeModal();
        $this->resetForm();
    }

    public function confirmDelete($id)
    {
        $this->productId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        $product = Product::find($this->productId);
        
        // Verificar si el producto tiene ventas o compras asociadas
        if ($product->saleItems()->exists() || $product->purchaseItems()->exists()) {
            session()->flash('error', 'No se puede eliminar el producto porque tiene transacciones asociadas.');
        } else {
            $product->delete();
            session()->flash('message', 'Producto eliminado exitosamente.');
        }
        
        $this->showDeleteModal = false;
    }

    public function toggleStatus($id)
    {
        $product = Product::find($id);
        $product->is_active = !$product->is_active;
        $product->save();
        
        session()->flash('message', 'Estado del producto actualizado.');
    }

    public function generateBarcodeImage($barcode)
    {
        if (empty($barcode)) return null;
        
        try {
            return DNS1D::getBarcodePNG($barcode, $this->barcodeType, $this->barcodeWidth, $this->barcodeHeight);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function exportToCSV()
    {
        $products = Product::with(['category', 'unitMeasure'])
            ->where('company_id', auth()->user()->company_id)
            ->get();

        $fileName = 'productos-' . date('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function() use ($products) {
            $file = fopen('php://output', 'w');
            
            // Encabezados
            fputcsv($file, [
                'Código', 'Nombre', 'Categoría', 'Marca', 'Modelo',
                'Precio BS', 'Precio USD', 'Costo', 'Stock Actual',
                'Stock Mínimo', 'Unidad', 'Estado'
            ]);

            // Datos
            foreach ($products as $product) {
                fputcsv($file, [
                    $product->barcode,
                    $product->name,
                    $product->category->name ?? '',
                    $product->brand,
                    $product->model,
                    $product->base_price,
                    $product->usd_price,
                    $product->cost,
                    $product->current_stock,
                    $product->min_stock,
                    $product->unitMeasure->symbol ?? '',
                    $product->is_active ? 'Activo' : 'Inactivo'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
    }

    public function updated($propertyName)
    {
        // Calcular precio USD basado en precio base y tasa de cambio
        if ($propertyName === 'base_price' && $this->base_price > 0) {
            // Aquí podrías obtener la tasa de cambio actual
            // Por ahora, usamos una conversión fija de ejemplo
            $exchangeRate = 35.75; // Esto debería venir de la tabla exchange_rates
            $this->usd_price = round($this->base_price / $exchangeRate, 2);
        }

        // Calcular margen de ganancia
        if (($propertyName === 'base_price' || $propertyName === 'cost') && $this->cost > 0) {
            $this->margin = round((($this->base_price - $this->cost) / $this->cost) * 100, 2);
        }
    }
}