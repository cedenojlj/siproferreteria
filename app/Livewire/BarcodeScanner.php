<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;

class BarcodeScanner extends Component
{

    public $barcode = '';
    public $product = null;
    public $quantity = 1;
    
    protected $listeners = ['barcodeScanned' => 'handleBarcode'];
    
    public function handleBarcode($code)
    {
        $this->barcode = $code;
        $this->searchProduct();
    }
    
    public function searchProduct()
    {
        $this->product = Product::where('barcode', $this->barcode)
            ->where('is_active', true)
            ->first();
            
        if (!$this->product) {
            $this->dispatch('productNotFound', message: 'Producto no encontrado');
        } else {
            $this->dispatch('productFound', product: $this->product);
        }
    }
    
    public function addToCart()
    {
        if ($this->product && $this->quantity > 0) {
            $this->dispatch('addProductToCart', [
                'product_id' => $this->product->id,
                'quantity' => $this->quantity,
                'unit_price' => $this->product->base_price, // O según moneda
            ]);
            
            $this->reset(['barcode', 'product', 'quantity']);
        }
    }


    public function render()
    {
        return view('livewire.barcode-scanner');
    }
}
