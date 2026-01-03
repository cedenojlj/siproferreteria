<div>
    <div class="container-fluid mt-3">
        <div class="row">
            <!-- Columna Izquierda (70%) -->
            <div class="col-md-7">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Búsqueda de Productos</h5>
                    </div>
                    <div class="card-body">
                        <input type="text" wire:model.live.debounce.300ms="productSearch" class="form-control" placeholder="Buscar producto por código o nombre...">
                        <!-- Product Search Results -->
                        @if(!empty($productSearch) && count($products) > 0)
                            <div class="list-group mt-2">
                                @foreach($products as $product)
                                    <a href="#" wire:click.prevent="addProduct({{ $product->id }})" class="list-group-item list-group-item-action">
                                        {{ $product->name }} ({{ $product->current_stock }} en stock) - {{ $product->price }}
                                    </a>
                                @endforeach
                            </div>
                        @endif
                        <!-- Lista de productos de búsqueda aquí -->
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Productos Agregados (Carrito)</h5>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th width="150px">Cantidad</th>
                                    <th width="120px">Precio Unitario</th>
                                    <th width="120px">Subtotal</th>
                                    <th width="50px"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($saleItems as $index => $item)
                                    <tr>
                                        <td>{{ $item['name'] }}</td>
                                        <td>
                                            <input type="number" wire:model.live="saleItems.{{ $index }}.quantity" wire:change="updateQuantity({{ $index }}, $event.target.value)" min="1" class="form-control form-control-sm">
                                        </td>
                                        <td>{{ number_format($item['price'], 2) }}</td>
                                        <td>{{ number_format($item['quantity'] * $item['price'], 2) }}</td>
                                        <td>
                                            <button wire:click="removeItem({{ $index }})" class="btn btn-danger btn-sm">X</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay productos en el carrito.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Columna Derecha (30%) -->
            <div class="col-md-5">
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Información del Cliente</h5>
                    </div>
                    <div class="card-body">
                        <input type="text" wire:model.live.debounce.300ms="customerSearch" class="form-control mb-2" placeholder="Buscar cliente por nombre o RIF...">

                        @if(!empty($customerSearch) && count($customers) > 0)
                            <div class="list-group mb-2">
                                @foreach($customers as $customer)
                                    <a href="#" wire:click.prevent="selectCustomer({{ $customer->id }})" class="list-group-item list-group-item-action">
                                        {{ $customer->name }} ({{ $customer->document_number ?? $customer->rif }})
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if($selectedCustomer)
                            <div class="mt-2 p-2 border rounded bg-light">
                                <strong>Cliente Seleccionado:</strong>
                                <p class="mb-0">{{ $selectedCustomer->name }}</p>
                                <p class="mb-0">{{ $selectedCustomer->document_number ?? $selectedCustomer->rif }}</p>
                                <button wire:click="$set('selectedCustomer', null)" class="btn btn-sm btn-outline-danger mt-2">Cambiar Cliente</button>
                            </div>
                        @else
                            <p class="text-muted">No hay cliente seleccionado.</p>
                            <button wire:click="$toggle('showCustomerModal')" class="btn btn-sm btn-success">Crear Nuevo Cliente</button>
                        @endif
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Resumen de la Venta</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Subtotal
                                <span>{{ number_format($subtotal, 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                Impuestos (IVA)
                                <span>{{ number_format($tax, 2) }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center font-weight-bold">
                                Total
                                <h4>{{ number_format($total, 2) }}</h4>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0">Acciones de Venta</h5>
                    </div>
                    <div class="card-body d-grid gap-2">
                        <button class="btn btn-success btn-lg" @if(!$selectedCustomer || count($saleItems) == 0) disabled @endif wire:click="finalizeSale">Finalizar Venta</button>
                        <button class="btn btn-warning btn-lg" wire:click="cancelSale">Cancelar Venta</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Creation Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Crear Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="storeCustomer">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="customerName" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="customerName" wire:model="newCustomer.name" required>
                            @error('newCustomer.name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="customerDocument" class="form-label">Documento/RIF</label>
                            <input type="text" class="form-control" id="customerDocument" wire:model="newCustomer.document_number">
                            @error('newCustomer.document_number') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="customerAddress" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="customerAddress" wire:model="newCustomer.address">
                            @error('newCustomer.address') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="customerPhone" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="customerPhone" wire:model="newCustomer.phone">
                            @error('newCustomer.phone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="customerEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="customerEmail" wire:model="newCustomer.email">
                            @error('newCustomer.email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   
</div>

@script
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Show/hide customer creation modal
            Livewire.on('show-customer-modal', () => {
                var customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
                customerModal.show();
            });
            Livewire.on('hide-customer-modal', () => {
                var customerModal = bootstrap.Modal.getInstance(document.getElementById('customerModal'));
                customerModal.hide();
            });
        });
    </script>
@endscript