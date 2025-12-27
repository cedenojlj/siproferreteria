<div class="container-fluid">
    <div class="h3 mb-4 text-gray-800">
        <h2>Gestión de Compras</h2>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <button wire:click="create()" class="btn btn-primary">Crear Nueva Compra</button>
            <div class="w-50">
                <input type="text" class="form-control" placeholder="Buscar por N° de factura o proveedor..." wire:model.live.debounce.300ms="search">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Factura</th>
                            <th>Proveedor</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->id }}</td>
                            <td>{{ $purchase->invoice_number }}</td>
                            <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                            <td>{{ number_format($purchase->total, 2) }} {{ $purchase->payment_currency }}</td>
                            <td><span class="badge {{
                                $purchase->status == 'received' ? 'bg-success' :
                                ($purchase->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger')
                            }}">{{ $purchase->status }}</span></td>
                            <td>{{ $purchase->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <button wire:click="edit({{ $purchase->id }})" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                <button wire:click="delete({{ $purchase->id }})" wire:confirm="¿Estás seguro de que quieres eliminar esta compra?" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No hay compras registradas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>

    <!-- Modal de Creación/Edición -->
    @if($isModalOpen)
    <div class="modal d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $purchase_id ? 'Editar Compra' : 'Crear Nueva Compra' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="{{ $purchase_id ? 'update' : 'store' }}">
                    <div class="modal-body">
                        {{-- Purchase Details --}}
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="invoice_number" class="form-label">Número de Factura:</label>
                                <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" id="invoice_number" wire:model.defer="invoice_number">
                                @error('invoice_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="supplier_id" class="form-label">Proveedor:</label>
                                <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" wire:model.defer="supplier_id">
                                    <option value="">Seleccione un proveedor</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                             <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Estado:</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" wire:model.defer="status">
                                    <option value="pending">Pendiente</option>
                                    <option value="received">Recibida</option>
                                    <option value="cancelled">Cancelada</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Barcode and Product Search --}}
                        <hr>
                        <div class="row align-items-end">
                            <div class="col-md-12 mb-3">
                                <label for="barcode" class="form-label">Escanear Producto:</label>
                                <input type="text" class="form-control @error('barcode') is-invalid @enderror" id="barcode"
                                       wire:model.live.debounce.500ms="barcode" placeholder="Escanear o ingresar código de barras y presionar Enter">
                                @error('barcode') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Items Table --}}
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th width="120px">Cantidad</th>
                                        <th width="150px">Precio Unit.</th>
                                        <th width="150px">Subtotal</th>
                                        <th width="50px"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($items as $index => $item)
                                        <tr>
                                            <td>{{ $item['name'] }}</td>
                                            <td>
                                                <input type="number" class="form-control form-control-sm" min="1"
                                                       wire:model.live="items.{{ $index }}.quantity">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control form-control-sm" min="0"
                                                       wire:model.live="items.{{ $index }}.price">
                                            </td>
                                            <td>{{ number_format(floatval($item['quantity'] ?? 0) * floatval($item['price'] ?? 0), 2) }}</td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-danger btn-sm" wire:click="removeItem({{ $index }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No hay productos agregados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                             @error('items') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        {{-- Totals and Currency --}}
                        <div class="row justify-content-end">
                            <div class="col-md-5">
                                <div class="row mb-2">
                                    <label for="subtotal" class="col-sm-4 col-form-label">Subtotal:</label>
                                    <div class="col-sm-8">
                                        <input type="number" step="0.01" class="form-control" id="subtotal" wire:model.live="subtotal" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <label for="tax" class="col-sm-4 col-form-label">Impuesto (16%):</label>
                                    <div class="col-sm-8">
                                        <input type="number" step="0.01" class="form-control" id="tax" wire:model.live="tax" readonly>
                                    </div>
                                </div>
                                <div class="row mb-2 fw-bold">
                                    <label for="total" class="col-sm-4 col-form-label">Total:</label>
                                    <div class="col-sm-8">
                                        <input type="number" step="0.01" class="form-control" id="total" wire:model.live="total" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <div class="row">
                             <div class="col-md-4 mb-3">
                                <label for="payment_currency" class="form-label">Moneda de Pago:</label>
                                <select class="form-select @error('payment_currency') is-invalid @enderror" id="payment_currency" wire:model.defer="payment_currency">
                                    <option value="BS">Bolívares (BS)</option>
                                    <option value="USD">Dólares (USD)</option>
                                </select>
                                @error('payment_currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="exchange_rate" class="form-label">Tasa de Cambio:</label>
                                <input type="number" step="0.01" class="form-control @error('exchange_rate') is-invalid @enderror" id="exchange_rate" wire:model.live="exchange_rate">
                                @error('exchange_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notas:</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" rows="2" wire:model.defer="notes"></textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">{{ $purchase_id ? 'Actualizar' : 'Guardar' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
