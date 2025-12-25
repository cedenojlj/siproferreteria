
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Administración de Productos</h1>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <button wire:click="create()" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Nuevo Producto
            </button>
            <div class="w-50">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Buscar por nombre o código de barras...">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Unidad</th>
                            <th>Precio Base</th>
                            <th>Stock Actual</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>{{ $product->unitMeasure->name ?? 'N/A' }}</td>
                            <td>{{ number_format($product->base_price, 2) }}</td>
                            <td>{{ $product->current_stock }}</td>
                            <td class="text-center">
                                <button wire:click="edit({{ $product->id }})" class="btn btn-warning btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="delete({{ $product->id }})" wire:confirm="¿Estás seguro de que quieres eliminar este producto?" class="btn btn-danger btn-sm" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No se encontraron productos.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Producto -->
    @if($isModalOpen)
    <div class="modal fade show" tabindex="-1" style="display: block;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $product_id ? 'Editar Producto' : 'Crear Nuevo Producto' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $product_id ? 'update' : 'store' }}">
                        <div class="row">
                            <!-- Columna Izquierda -->
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="name" class="form-label">Nombre del Producto</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.lazy="name">
                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="barcode" class="form-label">Código de Barras</label>
                                        <input type="text" class="form-control @error('barcode') is-invalid @enderror" id="barcode" wire:model.lazy="barcode">
                                        @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="description" wire:model.lazy="description" rows="3"></textarea>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="brand" class="form-label">Marca</label>
                                        <input type="text" class="form-control" id="brand" wire:model.lazy="brand">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="model" class="form-label">Modelo</label>
                                        <input type="text" class="form-control" id="model" wire:model.lazy="model">
                                    </div>
                                </div>
                            </div>
                            <!-- Columna Derecha -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Categoría</label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" wire:model="category_id">
                                        <option value="">Seleccione una categoría</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="unit_measure_id" class="form-label">Unidad de Medida</label>
                                    <select class="form-select @error('unit_measure_id') is-invalid @enderror" id="unit_measure_id" wire:model="unit_measure_id">
                                        <option value="">Seleccione una unidad</option>
                                        @foreach($unitMeasures as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->symbol }})</option>
                                        @endforeach
                                    </select>
                                    @error('unit_measure_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="current_stock" class="form-label">Stock Actual</label>
                                        <input type="number" class="form-control @error('current_stock') is-invalid @enderror" id="current_stock" wire:model.lazy="current_stock">
                                        @error('current_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="min_stock" class="form-label">Stock Mínimo</label>
                                        <input type="number" class="form-control @error('min_stock') is-invalid @enderror" id="min_stock" wire:model.lazy="min_stock">
                                        @error('min_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                             <div class="col-md-4 mb-3">
                                <label for="cost" class="form-label">Costo</label>
                                <input type="text" class="form-control @error('cost') is-invalid @enderror" id="cost" wire:model.lazy="cost">
                                @error('cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="base_price" class="form-label">Precio USD (Full)</label>
                                <input type="text" class="form-control @error('base_price') is-invalid @enderror" id="base_price" wire:model.lazy="base_price">
                                @error('base_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                           
                            <div class="col-md-4 mb-3">
                                <label for="usd_price" class="form-label">Precio USD (Rebaja)</label>
                                <input type="text" class="form-control" id="usd_price" wire:model.lazy="usd_price">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="{{ $product_id ? 'update' : 'store' }}">
                        {{ $product_id ? 'Actualizar Producto' : 'Crear Producto' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>

