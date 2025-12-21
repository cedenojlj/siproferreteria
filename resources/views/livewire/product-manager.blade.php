<div>
    <div class="container mx-auto px-4 py-6">
        <!-- Encabezado con estadísticas -->
        <div class="mb-8">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Gestión de Productos</h1>
                    <p class="text-gray-600">Administra el inventario de productos de la ferretería</p>
                </div>
                <div class="flex gap-3">
                    <button wire:click="exportToCSV" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                        <i class="fas fa-file-export"></i> Exportar CSV
                    </button>
                    <button wire:click="create"
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2">
                        <i class="fas fa-plus"></i> Nuevo Producto
                    </button>
                </div>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Total Productos</p>
                            <p class="text-2xl font-bold">{{ $totalProducts }}</p>
                        </div>
                        <i class="fas fa-boxes text-blue-500 text-2xl"></i>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Productos Activos</p>
                            <p class="text-2xl font-bold">{{ $activeProducts }}</p>
                        </div>
                        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Bajo Stock</p>
                            <p class="text-2xl font-bold">{{ $lowStockProducts }}</p>
                        </div>
                        <i class="fas fa-exclamation-triangle text-yellow-500 text-2xl"></i>
                    </div>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow border-l-4 border-red-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Agotados</p>
                            <p class="text-2xl font-bold">{{ $outOfStockProducts }}</p>
                        </div>
                        <i class="fas fa-times-circle text-red-500 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros y búsqueda -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Búsqueda -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <div class="relative">
                        <input type="text" 
                               wire:model.live="search"
                               placeholder="Código, nombre, descripción..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Filtro por categoría -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                    <select wire:model.live="categoryFilter" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <!-- Filtro por estado -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select wire:model.live="statusFilter" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Todos</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
                
                <!-- Botón de limpiar filtros -->
                <div class="flex items-end">
                    <button wire:click="$set('search', '')"
                            class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        <i class="fas fa-redo mr-2"></i> Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de productos -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('barcode')" class="flex items-center gap-1">
                                    Código
                                    @if($sortField === 'barcode')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </button>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <button wire:click="sortBy('name')" class="flex items-center gap-1">
                                    Producto
                                    @if($sortField === 'name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </button>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Categoría
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Stock
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Precios
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Estado
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                        <tr class="hover:bg-gray-50 {{ $product->current_stock <= $product->min_stock ? 'bg-yellow-50' : '' }} {{ $product->current_stock == 0 ? 'bg-red-50' : '' }}">
                            <!-- Código de barras -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    @if($product->barcode)
                                        <div class="flex flex-col">
                                            <span>{{ $product->barcode }}</span>
                                            @if($product->barcode)
                                            <div class="mt-1">
                                                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($product->barcode, 'C128', 2, 30) }}" 
                                                     alt="{{ $product->barcode }}"
                                                     class="h-8">
                                            </div>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400">Sin código</span>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Información del producto -->
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    @if($product->image_path)
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full object-cover" 
                                             src="{{ Storage::url($product->image_path) }}" 
                                             alt="{{ $product->name }}">
                                    </div>
                                    @endif
                                    <div class="{{ $product->image_path ? 'ml-4' : '' }}">
                                        <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                        <div class="text-sm text-gray-500">
                                            {{ $product->brand ?? 'Sin marca' }} {{ $product->model ? " - {$product->model}" : '' }}
                                        </div>
                                        @if($product->description)
                                        <div class="text-xs text-gray-400 mt-1 line-clamp-2">
                                            {{ Str::limit($product->description, 60) }}
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Categoría -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $product->category->name ?? 'Sin categoría' }}
                                </span>
                            </td>
                            
                            <!-- Stock -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">
                                    <div class="flex items-center">
                                        <div class="w-24">
                                            <div class="text-gray-900 font-medium">{{ $product->current_stock }} {{ $product->unitMeasure->symbol ?? 'un' }}</div>
                                            <div class="text-xs text-gray-500">Mín: {{ $product->min_stock }}</div>
                                        </div>
                                        <div class="ml-4 w-32">
                                            @if($product->current_stock > 0)
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                @php
                                                    $percentage = min(100, ($product->current_stock / max($product->min_stock * 2, 1)) * 100);
                                                    $color = $product->current_stock <= $product->min_stock ? 'bg-red-500' : 'bg-green-500';
                                                @endphp
                                                <div class="h-2 rounded-full {{ $color }}" style="width: {{ $percentage }}%"></div>
                                            </div>
                                            @else
                                            <span class="text-xs text-red-600 font-bold">AGOTADO</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <!-- Precios -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm">
                                    <div class="text-gray-900 font-medium">{{ number_format($product->base_price, 2) }} BS</div>
                                    <div class="text-gray-500">{{ number_format($product->usd_price, 2) }} USD</div>
                                    <div class="text-xs text-gray-400">Costo: {{ number_format($product->cost, 2) }}</div>
                                </div>
                            </td>
                            
                            <!-- Estado -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button wire:click="toggleStatus({{ $product->id }})"
                                        class="px-3 py-1 rounded-full text-sm font-medium {{ $product->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                </button>
                            </td>
                            
                            <!-- Acciones -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex gap-2">
                                    <button wire:click="edit({{ $product->id }})"
                                            class="text-blue-600 hover:text-blue-900 p-1">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $product->id }})"
                                            class="text-red-600 hover:text-red-900 p-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <a href="{{ route('products.show', $product->id) }}"
                                       class="text-gray-600 hover:text-gray-900 p-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <i class="fas fa-box-open text-4xl mb-4"></i>
                                    <p class="text-lg">No se encontraron productos</p>
                                    <p class="text-sm mt-2">Intenta ajustar los filtros de búsqueda</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <!-- Modal para crear/editar producto -->
    @if($showModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">{{ $modalTitle }}</h3>
                    <button wire:click="closeModal" 
                            class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>

                <form wire:submit.prevent="save">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Columna izquierda -->
                        <div class="space-y-6">
                            <!-- Código de barras -->
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <label class="block text-sm font-medium text-gray-700">Código de Barras</label>
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center text-sm text-gray-600">
                                            <input type="checkbox" 
                                                   wire:model="generateBarcode"
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                            Generar automático
                                        </label>
                                        @if($generateBarcode && !$productId)
                                        <button type="button" 
                                                wire:click="generateRandomBarcode"
                                                class="text-sm text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-redo mr-1"></i> Regenerar
                                        </button>
                                        @endif
                                    </div>
                                </div>
                                <input type="text" 
                                       wire:model="barcode"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Código de barras único">
                                @if($barcode)
                                <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                                    <p class="text-sm text-gray-600 mb-2">Vista previa del código:</p>
                                    @php
                                        $barcodeImage = $this->generateBarcodeImage($barcode);
                                    @endphp
                                    @if($barcodeImage)
                                    <img src="data:image/png;base64,{{ $barcodeImage }}" 
                                         alt="{{ $barcode }}"
                                         class="h-16">
                                    @endif
                                </div>
                                @endif
                            </div>

                            <!-- Nombre y descripción -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre del Producto *</label>
                                <input type="text" 
                                       wire:model="name"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Ej: Martillo de carpintero" required>
                                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                                <textarea wire:model="description"
                                          rows="3"
                                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Descripción detallada del producto..."></textarea>
                            </div>

                            <!-- Marca y modelo -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Marca</label>
                                    <input type="text" 
                                           wire:model="brand"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Ej: Stanley">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Modelo</label>
                                    <input type="text" 
                                           wire:model="model"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Ej: XT-500">
                                </div>
                            </div>

                            <!-- Imagen del producto -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Imagen del Producto</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                                    @if($photo)
                                        <div class="mb-4">
                                            <img src="{{ $photo->temporaryUrl() }}" 
                                                 alt="Vista previa"
                                                 class="h-32 mx-auto object-cover rounded-lg">
                                        </div>
                                        <button type="button" 
                                                wire:click="$set('photo', null)"
                                                class="text-sm text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash mr-1"></i> Eliminar imagen
                                        </button>
                                    @else
                                        <div class="text-gray-500 mb-2">
                                            <i class="fas fa-cloud-upload-alt text-3xl"></i>
                                            <p class="mt-2">Arrastra una imagen o haz clic para seleccionar</p>
                                        </div>
                                        <input type="file" 
                                               wire:model="photo"
                                               accept="image/*"
                                               class="hidden"
                                               id="photoUpload">
                                        <label for="photoUpload" 
                                               class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer">
                                            Seleccionar Imagen
                                        </label>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Columna derecha -->
                        <div class="space-y-6">
                            <!-- Precios -->
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h4 class="font-medium text-blue-800 mb-4">Información de Precios</h4>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Precio Base (BS) *</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">Bs.</span>
                                            <input type="number" 
                                                   step="0.01"
                                                   wire:model.live="base_price"
                                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="0.00" required>
                                        </div>
                                        @error('base_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Precio USD *</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">$</span>
                                            <input type="number" 
                                                   step="0.01"
                                                   wire:model="usd_price"
                                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="0.00" required>
                                        </div>
                                        @error('usd_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Costo *</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-2 text-gray-500">Bs.</span>
                                            <input type="number" 
                                                   step="0.01"
                                                   wire:model.live="cost"
                                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="0.00" required>
                                        </div>
                                        @error('cost') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        
                                        @if($cost > 0 && $base_price > 0)
                                        <div class="mt-2 text-sm">
                                            @php
                                                $margin = (($base_price - $cost) / $cost) * 100;
                                                $color = $margin >= 30 ? 'text-green-600' : ($margin >= 10 ? 'text-yellow-600' : 'text-red-600');
                                            @endphp
                                            <span class="{{ $color }} font-medium">
                                                Margen: {{ number_format($margin, 1) }}%
                                            </span>
                                            <span class="text-gray-500 ml-2">
                                                (Ganancia: {{ number_format($base_price - $cost, 2) }} BS)
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Stock -->
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="font-medium text-green-800 mb-4">Control de Inventario</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Stock Actual *</label>
                                        <input type="number" 
                                               wire:model="current_stock"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="0" required>
                                        @error('current_stock') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Stock Mínimo *</label>
                                        <input type="number" 
                                               wire:model="min_stock"
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="10" required>
                                        @error('min_stock') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @if($current_stock <= $min_stock)
                                <div class="mt-4 p-3 rounded-lg {{ $current_stock == 0 ? 'bg-red-100 border border-red-200' : 'bg-yellow-100 border border-yellow-200' }}">
                                    <div class="flex items-center">
                                        <i class="fas fa-exclamation-triangle {{ $current_stock == 0 ? 'text-red-600' : 'text-yellow-600' }} mr-2"></i>
                                        <span class="{{ $current_stock == 0 ? 'text-red-800' : 'text-yellow-800' }} text-sm">
                                            {{ $current_stock == 0 ? 'Producto agotado' : 'Stock por debajo del mínimo' }}
                                        </span>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Categoría y unidad de medida -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Categoría *</label>
                                    <select wire:model="category_id"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                        <option value="">Seleccionar categoría</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Unidad de Medida *</label>
                                    <select wire:model="unit_measure_id"
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                                        <option value="">Seleccionar unidad</option>
                                        @foreach($unitMeasures as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                                        @endforeach
                                    </select>
                                    @error('unit_measure_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           wire:model="is_active"
                                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 mr-2">
                                    <span class="text-sm font-medium text-gray-700">Producto activo</span>
                                </label>
                                <p class="text-xs text-gray-500 mt-1">
                                    Los productos inactivos no aparecerán en el punto de venta ni en las búsquedas.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Botones del formulario -->
                    <div class="flex justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                        <button type="button" 
                                wire:click="closeModal"
                                class="px-6 py-3 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            {{ $productId ? 'Actualizar Producto' : 'Crear Producto' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de confirmación para eliminar -->
    @if($showDeleteModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">¿Eliminar producto?</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Esta acción no se puede deshacer. El producto será eliminado permanentemente del sistema.
                </p>
                <div class="flex justify-center gap-3">
                    <button wire:click="closeModal"
                            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button wire:click="delete"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('styles')
    <style>
        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        .border-l-4 {
            border-left-width: 4px;
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        // Escuchar eventos de Livewire
        document.addEventListener('livewire:init', () => {
            Livewire.on('productSaved', () => {
                // Mostrar notificación de éxito
                showNotification('Producto guardado exitosamente', 'success');
            });

            Livewire.on('productDeleted', () => {
                // Mostrar notificación de eliminación
                showNotification('Producto eliminado exitosamente', 'success');
            });

            Livewire.on('error', (message) => {
                // Mostrar notificación de error
                showNotification(message, 'error');
            });
        });

        function showNotification(message, type) {
            // Usar Toastr si está disponible, o alert nativo
            if (typeof toastr !== 'undefined') {
                toastr[type === 'success' ? 'success' : 'error'](message);
            } else {
                alert(message);
            }
        }
    </script>
    @endpush
</div>