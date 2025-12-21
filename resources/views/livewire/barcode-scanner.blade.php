<div>
    <div class="mb-6">
        <!-- Scanner activo -->
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-700">Escáner de Código de Barras</h3>
            <div class="flex items-center gap-2">
                <span class="text-sm {{ $isScanning ? 'text-green-600' : 'text-gray-500' }}">
                    {{ $isScanning ? '● Escaneando...' : '○ Listo' }}
                </span>
                <button wire:click="toggleScanner"
                        class="p-2 rounded-full {{ $isScanning ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600' }} hover:opacity-90">
                    <i class="fas fa-{{ $isScanning ? 'stop' : 'play' }}"></i>
                </button>
            </div>
        </div>

        <!-- Entrada manual -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Búsqueda Manual</label>
            <div class="flex gap-2">
                <input type="text" 
                       wire:model.live="barcodeInput"
                       wire:keydown.enter="searchByBarcode"
                       placeholder="Ingrese código o presione Enter"
                       class="flex-grow p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       autocomplete="off">
                <button wire:click="searchByBarcode"
                        class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <!-- Resultado del escaneo -->
        @if($scannedProduct)
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg animate-pulse">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fas fa-check-circle text-green-600"></i>
                        <span class="font-medium text-green-800">Producto encontrado!</span>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-800">{{ $scannedProduct->name }}</h4>
                    <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">Código:</span>
                            <span class="font-medium ml-2">{{ $scannedProduct->barcode }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Stock:</span>
                            <span class="font-medium ml-2">{{ $scannedProduct->current_stock }} unidades</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Precio BS:</span>
                            <span class="font-medium ml-2">{{ number_format($scannedProduct->base_price, 2) }}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Precio USD:</span>
                            <span class="font-medium ml-2">{{ number_format($scannedProduct->usd_price, 2) }}</span>
                        </div>
                    </div>
                </div>
                <button wire:click="clearScanned"
                        class="p-2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Controles de cantidad -->
            <div class="mt-4 flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <button wire:click="decreaseQuantity"
                            class="w-8 h-8 flex items-center justify-center bg-gray-200 rounded hover:bg-gray-300">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" 
                           wire:model="quantity"
                           min="1" 
                           max="{{ $scannedProduct->current_stock }}"
                           class="w-20 p-2 border border-gray-300 rounded text-center">
                    <button wire:click="increaseQuantity"
                            class="w-8 h-8 flex items-center justify-center bg-gray-200 rounded hover:bg-gray-300">
                        <i class="fas fa-plus"></i>
                    </button>
                    <span class="text-sm text-gray-600 ml-2">
                        de {{ $scannedProduct->current_stock }} disponibles
                    </span>
                </div>
                
                <button wire:click="addToCart"
                        class="flex-grow py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    <i class="fas fa-cart-plus mr-2"></i>
                    Agregar al Carrito ({{ number_format($scannedProduct->base_price * $quantity, 2) }} BS)
                </button>
            </div>
        </div>
        @endif

        <!-- Mensaje de error -->
        @if($errorMessage)
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex items-center gap-2 text-red-700">
                <i class="fas fa-exclamation-triangle"></i>
                <span>{{ $errorMessage }}</span>
            </div>
            @if($similarProducts->count() > 0)
            <div class="mt-3">
                <p class="text-sm text-gray-600 mb-2">¿Quizás quiso decir?</p>
                <div class="space-y-2">
                    @foreach($similarProducts as $product)
                    <button wire:click="selectSimilarProduct({{ $product->id }})"
                            class="w-full text-left p-2 hover:bg-gray-100 rounded">
                        <div class="flex justify-between">
                            <span>{{ $product->name }}</span>
                            <span class="text-blue-600">{{ $product->barcode }}</span>
                        </div>
                    </button>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endif

        <!-- Historial reciente -->
        @if($scanHistory->count() > 0)
        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Historial Reciente</h4>
            <div class="space-y-2">
                @foreach($scanHistory as $history)
                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                    <div>
                        <span class="font-medium">{{ $history['product_name'] }}</span>
                        <span class="text-sm text-gray-600 ml-2">({{ $history['barcode'] }})</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm">{{ $history['quantity'] }} unidades</span>
                        <button wire:click="reuseHistory('{{ $history['barcode'] }}')"
                                class="p-1 text-blue-600 hover:text-blue-800">
                            <i class="fas fa-redo"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let barcodeBuffer = '';
            let lastKeyTime = Date.now();
            const barcodeDelay = 100; // 100ms entre caracteres
            
            // Detectar entrada de código de barras
            document.addEventListener('keydown', function(e) {
                const currentTime = Date.now();
                
                // Ignorar teclas especiales
                if (e.key === 'Shift' || e.key === 'Control' || e.key === 'Alt' || 
                    e.key === 'Tab' || e.key === 'CapsLock') {
                    return;
                }
                
                // Si pasa mucho tiempo, reiniciar buffer
                if (currentTime - lastKeyTime > barcodeDelay) {
                    barcodeBuffer = '';
                }
                
                // Acumular caracteres
                barcodeBuffer += e.key;
                lastKeyTime = currentTime;
                
                // Si es Enter, procesar código
                if (e.key === 'Enter') {
                    e.preventDefault();
                    
                    if (barcodeBuffer.length > 3) { // Mínimo 4 caracteres para código
                        const barcode = barcodeBuffer.slice(0, -1); // Quitar el Enter
                        @this.dispatch('barcodeScanned', {barcode: barcode});
                        barcodeBuffer = '';
                    }
                }
                
                // Escuchar el evento emitido por Livewire
                Livewire.on('focusBarcodeInput', () => {
                    document.querySelector('[wire\\:model="barcodeInput"]').focus();
                });
            });
        });
    </script>
    @endpush
</div>