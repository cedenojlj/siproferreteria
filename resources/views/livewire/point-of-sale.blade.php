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
                        <input type="text" id="productSearch" wire:model.live.debounce.300ms="productSearch" class="form-control"
                            placeholder="Buscar producto por código o nombre...">
                        <!-- Product Search Results -->
                        @if (!empty($productSearch) && count($products) > 0)
                            <div class="list-group mt-2">
                                @foreach ($products as $product)
                                    <a href="#" wire:click.prevent="addProduct({{ $product->id }})"
                                        class="list-group-item list-group-item-action">
                                        {{ $product->name }} ({{ $product->current_stock }} en stock) -
                                        {{ $product->price }}
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
                                            <input type="number"
                                                wire:model.live="saleItems.{{ $index }}.quantity"
                                                wire:change="updateQuantity({{ $index }}, $event.target.value)"
                                                wire:keydown.enter="updateQuantity({{ $index }}, $event.target.value)"                                                
                                                min="1" class="form-control form-control-sm">
                                        </td>
                                        <td>{{ number_format($item['price'], 2) }}</td>
                                        <td>{{ number_format(!empty($item['quantity']) ? ($item['quantity'] * $item['price']) : 0, 2) }}</td>
                                        <td>
                                            <button wire:click="removeItem({{ $index }})"
                                                class="btn btn-danger btn-sm">X</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay productos en el
                                            carrito.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Datos Financieros -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Datos Financieros</h5>
                    </div>
                    <div class="card-body p-3">

                        <div class="container">
                            <!-- Content here -->
                            <div class="row">

                                {{-- agregar payment_currency --}}
                                <div class="col mb-3">
                                    <label for="payment_currency" class="form-label">Moneda de Pago</label>
                                    <select class="form-select" id="payment_currency" wire:model="payment_currency">
                                        @foreach ($paymentCurrency as $currency)
                                            <option value="{{ $currency }}">{{ $currency }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- agregar payment_method --}}
                                <div class="col mb-3">
                                    <label for="payment_method" class="form-label">Método de Pago</label>
                                    <select class="form-select" id="payment_method" wire:model="payment_method">
                                        @foreach ($paymentMethods as $method)
                                            <option value="{{ $method }}">{{ $method }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- agregar payment_type --}}
                                <div class="col mb-3">
                                    <label for="payment_type" class="form-label">Tipo de Pago</label>
                                    <select class="form-select" id="payment_type" wire:model="payment_type">
                                        @foreach ($paymentTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                {{-- tasa de cambio --}}
                                <div class="col mb-3">
                                    <label for="exchange_rate" class="form-label">Tasa de Cambio</label>
                                    <input type="number" class="form-control" id="exchange_rate"
                                        wire:model.live="exchange_rate" step="0.01" min="0" readonly>
                                </div>

                            </div>

                            <div class="row">
                                <div class="col mb-3">
                                    <small class="text-muted">* La tasa de cambio se utiliza para convertir entre
                                        monedas si es necesario.</small>
                                </div>
                            </div>


                        </div>




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
                        <input type="text" wire:model.live.debounce.300ms="customerSearch" class="form-control mb-2"
                            placeholder="Buscar cliente por nombre o RIF...">

                        @if (!empty($customerSearch) && count($customers) > 0)
                            <div class="list-group mb-2">
                                @foreach ($customers as $customer)
                                    <a href="#" wire:click.prevent="selectCustomer({{ $customer->id }})"
                                        class="list-group-item list-group-item-action">
                                        {{ $customer->name }}
                                        ({{ $customer->document_type }}-{{ $customer->document }})
                                    </a>
                                @endforeach
                            </div>
                        @endif

                        @if ($selectedCustomer)
                            <div class="mt-2 p-2 border rounded bg-light">
                                <strong>Cliente Seleccionado:</strong>
                                <p class="mb-0">{{ $selectedCustomer->name }}</p>
                                <p class="mb-0">{{ $selectedCustomer->document_number ?? $selectedCustomer->rif }}
                                </p>
                                <button wire:click="cambiarCliente" class="btn btn-sm btn-outline-danger mt-2">Cambiar
                                    Cliente</button>
                            </div>
                        @else
                            <p class="text-muted">No hay cliente seleccionado.</p>
                            <button wire:click="abrirClienteModal" class="btn btn-sm btn-success">Crear Nuevo
                                Cliente</button>
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
                            <li
                                class="list-group-item d-flex justify-content-between align-items-center font-weight-bold">
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
                        <button class="btn btn-success btn-lg" @if (!$selectedCustomer || count($saleItems) == 0) disabled @endif
                            wire:click="finalizeSale">Finalizar Venta</button>
                        <button class="btn btn-warning btn-lg" wire:click="cancelSale">Cancelar Venta</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($lastSaleId)
        <div class="modal fade show" style="display: block; background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Venta Finalizada</h5>
                    </div>
                    <div class="modal-body text-center">
                        @if (session()->has('message'))
                            <div class="alert alert-success">
                                {{ session('message') }}
                            </div>
                        @endif
                        <p>¿Qué desea hacer a continuación?</p>
                    </div>
                    <div class="modal-footer d-flex justify-content-center">
                        <a href="{{ route('sales.ticket', $lastSaleId) }}" target="_blank" class="btn btn-primary">
                             Ver / Imprimir Ticket
                        </a>
                        <button wire:click="startNewSale" class="btn btn-secondary">
                            Nueva Venta
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($abrirModalCliente)
        <!-- Customer Creation Modal -->
        <div class="modal fade show" id="customerModal" tabindex="-1" aria-hidden="false" style="display: block;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="customerModalLabel">Crear Nuevo Cliente</h5>
                        <button type="button" class="btn-close" wire:click="cerrarClienteModal"></button>
                    </div>
                    <form wire:submit.prevent="storeCustomer">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="customerName" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="customerName"
                                    wire:model="newCustomer.name" required>
                                @error('newCustomer.name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="customerDocument" class="form-label">Documento/RIF</label>
                                <input type="text" class="form-control" id="customerDocument"
                                    wire:model="newCustomer.document">
                                @error('newCustomer.document')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="customerAddress" class="form-label">Dirección</label>
                                <input type="text" class="form-control" id="customerAddress"
                                    wire:model="newCustomer.address">
                                @error('newCustomer.address')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="customerPhone" class="form-label">Teléfono</label>
                                <input type="text" class="form-control" id="customerPhone"
                                    wire:model="newCustomer.phone">
                                @error('newCustomer.phone')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="customerEmail" class="form-label">Email</label>
                                <input type="email" class="form-control" id="customerEmail"
                                    wire:model="newCustomer.email">
                                @error('newCustomer.email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                wire:click="cerrarClienteModal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>

@script
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Listener for product search focus
            Livewire.on('focus-product-search', () => {
               let input = document.getElementById('productSearch');
               if (input) {
                   input.focus();
                   input.select();
               }
           });

            // Modal related logic
            const customerModalEl = document.getElementById('customerModal');
            if (customerModalEl) {
                let customerModal;

                // Ensure Bootstrap Modal is initialized only once.
                customerModalEl.addEventListener('shown.bs.modal', () => {
                    if (!customerModal) {
                        customerModal = bootstrap.Modal.getInstance(customerModalEl);
                    }
                });

                Livewire.on('show-customer-modal', () => {
                    // Use the getter to be safe, or initialize a new one.
                    if (!customerModal) {
                        customerModal = new bootstrap.Modal(customerModalEl);
                    }
                    customerModal.show();
                });

                Livewire.on('hide-customer-modal', () => {
                    if (customerModal) {
                        customerModal.hide();
                    }
                });
            }
        });
    </script>
@endscript
