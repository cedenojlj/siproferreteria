<div>
    <div class="container mx-auto p-6">
        <!-- Encabezado -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Punto de Venta</h1>
            <p class="text-gray-600">Vendedor: {{ Auth::user()->name }} | {{ date('d/m/Y H:i') }}</p>
        </div>

        <!-- Mensajes -->
        @if (session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('message') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Panel izquierdo: Búsqueda y productos -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-semibold mb-4">Búsqueda de Productos</h2>
                    
                    <!-- Búsqueda por código de barras -->
                    <div class="mb-4">
                        <livewire:barcode-scanner />
                    </div>

                    <!-- Búsqueda manual -->
                    <div class="mb-6">
                        <div class="flex gap-2">
                            <input type="text" wire:model.live="searchQuery" 
                                   placeholder="Buscar producto por nombre o código..."
                                   class="flex-grow p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <button wire:click="clearSearch" class="px-4 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                Limpiar
                            </button>
                        </div>
                    </div>

                    <!-- Lista de productos -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium mb-3">Productos Disponibles</h3>
                        @if($products->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($products as $product)
                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition">
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-medium text-gray-800">{{ $product->name }}</h4>
                                        <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                            {{ $product->current_stock }} disp.
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600 mb-2">{{ $product->category->name ?? 'Sin categoría' }}</p>
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-lg font-bold text-blue-600">
                                            {{ number_format($product->base_price, 2) }} BS
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ number_format($product->usd_price, 2) }} USD
                                        </span>
                                    </div>
                                    <div class="flex gap-2">
                                        <input type="number" wire:model="quantities.{{ $product->id }}" 
                                               min="1" max="{{ $product->current_stock }}"
                                               value="1"
                                               class="w-20 p-2 border border-gray-300 rounded text-center">
                                        <button wire:click="addToCart({{ $product->id }})"
                                                class="flex-grow bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition">
                                            Agregar
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            
                            <div class="mt-4">
                                {{ $products->links() }}
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-search text-4xl mb-3"></i>
                                <p>No se encontraron productos</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Panel derecho: Carrito y resumen -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                    <h2 class="text-xl font-semibold mb-4">Venta en Proceso</h2>

                    <!-- Información del cliente -->
                    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                        <h3 class="font-medium mb-2">Cliente</h3>
                        <div class="flex items-center gap-2">
                            <select wire:model="customerId" class="flex-grow p-2 border border-gray-300 rounded">
                                <option value="">Cliente general</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">
                                    {{ $customer->name }} ({{ $customer->document }})
                                </option>
                                @endforeach
                            </select>
                            <button wire:click="openCustomerModal" 
                                    class="p-2 bg-blue-100 text-blue-600 rounded hover:bg-blue-200">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                        @if($selectedCustomer)
                        <div class="mt-2 text-sm">
                            <p>Crédito disponible: {{ number_format($selectedCustomer->available_credit, 2) }} BS</p>
                        </div>
                        @endif
                    </div>

                    <!-- Método de pago -->
                    <div class="mb-6">
                        <h3 class="font-medium mb-2">Método de Pago</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <button wire:click="setPaymentMethod('CASH')"
                                    class="p-3 border rounded-lg text-center {{ $paymentMethod == 'CASH' ? 'bg-green-100 border-green-500' : 'border-gray-300' }}">
                                <i class="fas fa-money-bill-wave mb-1"></i>
                                <p class="text-sm">Efectivo</p>
                            </button>
                            <button wire:click="setPaymentMethod('TRANSFER')"
                                    class="p-3 border rounded-lg text-center {{ $paymentMethod == 'TRANSFER' ? 'bg-blue-100 border-blue-500' : 'border-gray-300' }}">
                                <i class="fas fa-university mb-1"></i>
                                <p class="text-sm">Transferencia</p>
                            </button>
                        </div>
                    </div>

                    <!-- Moneda y tasa -->
                    <div class="mb-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-1">Moneda</label>
                                <select wire:model="paymentCurrency" class="w-full p-2 border border-gray-300 rounded">
                                    <option value="BS">Bolívares (BS)</option>
                                    <option value="USD">Dólares (USD)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-1">Tasa de Cambio</label>
                                <input type="number" wire:model="exchangeRate" step="0.0001"
                                       class="w-full p-2 border border-gray-300 rounded">
                            </div>
                        </div>
                    </div>

                    <!-- Items del carrito -->
                    <div class="mb-6">
                        <h3 class="font-medium mb-3">Productos en la Venta</h3>
                        @if(count($cartItems) > 0)
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                @foreach($cartItems as $index => $item)
                                <div class="flex items-center justify-between p-3 border border-gray-200 rounded">
                                    <div class="flex-grow">
                                        <p class="font-medium">{{ $item['product_name'] }}</p>
                                        <div class="flex justify-between text-sm text-gray-600">
                                            <span>{{ $item['quantity'] }} x {{ number_format($item['unit_price'], 2) }}</span>
                                            <span>{{ number_format($item['subtotal'], 2) }} BS</span>
                                        </div>
                                    </div>
                                    <button wire:click="removeFromCart({{ $index }})"
                                            class="ml-2 p-1 text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4 text-gray-500">
                                <i class="fas fa-shopping-cart text-3xl mb-2"></i>
                                <p>No hay productos en el carrito</p>
                            </div>
                        @endif
                    </div>

                    <!-- Resumen de totales -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span class="font-medium">{{ number_format($subtotal, 2) }} BS</span>
                            </div>
                            <div class="flex justify-between">
                                <span>IVA ({{ $taxRate }}%):</span>
                                <span class="font-medium">{{ number_format($tax, 2) }} BS</span>
                            </div>
                            <div class="flex justify-between text-lg font-bold border-t pt-2">
                                <span>Total:</span>
                                <span>{{ number_format($total, 2) }} BS</span>
                            </div>
                            @if($paymentCurrency == 'USD')
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>Total USD:</span>
                                <span>{{ number_format($totalUSD, 2) }} USD</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Botones de acción -->
                    <div class="space-y-3">
                        <button wire:click="processSale"
                                @if(count($cartItems) == 0) disabled @endif
                                class="w-full py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <i class="fas fa-check-circle mr-2"></i>
                            Procesar Venta
                        </button>
                        
                        <div class="grid grid-cols-2 gap-3">
                            <button wire:click="saveDraft"
                                    class="py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                                <i class="fas fa-save mr-2"></i>
                                Guardar
                            </button>
                            <button wire:click="clearCart"
                                    @if(count($cartItems) == 0) disabled @endif
                                    class="py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 disabled:bg-gray-200 disabled:text-gray-400">
                                <i class="fas fa-times mr-2"></i>
                                Cancelar
                            </button>
                        </div>

                        @if($saleId)
                        <button wire:click="printTicket"
                                class="w-full py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                            <i class="fas fa-print mr-2"></i>
                            Imprimir Ticket
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para nuevo cliente -->
        @if($showCustomerModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4">Nuevo Cliente</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Tipo de Documento</label>
                        <select wire:model="newCustomer.document_type" class="w-full p-2 border rounded">
                            <option value="V">Venezolano</option>
                            <option value="J">Jurídico</option>
                            <option value="G">Gubernamental</option>
                            <option value="P">Pasaporte</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Documento</label>
                        <input type="text" wire:model="newCustomer.document" 
                               class="w-full p-2 border rounded">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Nombre</label>
                        <input type="text" wire:model="newCustomer.name" 
                               class="w-full p-2 border rounded">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Teléfono</label>
                        <input type="text" wire:model="newCustomer.phone" 
                               class="w-full p-2 border rounded">
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="closeCustomerModal" 
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Cancelar
                    </button>
                    <button wire:click="saveCustomer" 
                            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                        Guardar
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-focus en búsqueda
            Livewire.on('focusSearch', () => {
                document.querySelector('[wire\\:model="searchQuery"]').focus();
            });

            // Confirmar antes de cancelar
            window.addEventListener('confirmClearCart', event => {
                if(confirm('¿Está seguro de cancelar la venta? Se perderán todos los items.')) {
                    Livewire.dispatch('confirmClearCart');
                }
            });
        });
    </script>
    @endpush
</div>