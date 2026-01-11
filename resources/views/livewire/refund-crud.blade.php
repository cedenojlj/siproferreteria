<div>
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Gestión de Devoluciones</h1>

        @if (session()->has('message'))
            <div class="alert alert-success">{{ session('message') }}</div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Crear Nueva Devolución</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="saleSearch">Buscar Venta por ID o Número de Factura</label>
                            <div class="input-group">
                                <input type="text" id="saleSearch" class="form-control"
                                    placeholder="Ingrese ID de la venta..." wire:model.defer="saleSearch">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" wire:click="searchSale">Buscar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($selectedSale)
                    <hr>
                    <h5 class="mb-3">Detalles de la Venta #{{ $selectedSale->id }}</h5>
                    <p><strong>Cliente:</strong> {{ $selectedSale->customer->name ?? 'N/A' }}</p>
                    <p><strong>Fecha de Venta:</strong> {{ $selectedSale->created_at->format('d/m/Y') }}</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="refund_method">Método de Devolución</label>
                                <select id="refund_method"
                                    class="form-control @error('refund_method') is-invalid @enderror"
                                    wire:model.defer="refund_method">
                                    <option value="cash">Efectivo</option>
                                    <option value="credit_note">Nota de Crédito</option>
                                    <option value="exchange">Intercambio</option>
                                    <option value="bank_transfer">Transferencia Bancaria</option>
                                </select>
                                @error('refund_method')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">                           
                            <div class="form-group">
                                <label for="status">Estado</label>
                                <select id="status" class="form-control @error('status') is-invalid @enderror"
                                    wire:model.defer="status">
                                    <option value="pending">Pendiente</option>
                                    <option value="completed">Completado</option>
                                    <option value="cancelled">Cancelado</option>
                                </select>
                                @error('status')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>


                    <hr>

                    <h6 class="mt-4">Productos a Devolver</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th style="width: 150px;">Cantidad a Devolver</th>
                                    <th>Cantidad Comprada</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                    <th>Condiciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($refundItems as $index => $item)
                                    <tr>
                                        <td>{{ $item['product_name'] }}</td>
                                        <td>
                                            <input type="number"
                                                class="form-control @error('refundItems.' . $index . '.quantity') is-invalid @enderror"
                                                wire:model.live="refundItems.{{ $index }}.quantity"
                                                min="0" max="{{ $item['max_quantity'] }}">
                                        </td>
                                        <td>{{ $item['max_quantity'] }}</td>
                                        <td>${{ number_format($item['unit_price'], 2) }}</td>
                                        <td>${{ number_format($item['subtotal'], 2) }}</td>
                                        <td>
                                            <select
                                                class="form-control @error('refundItems.' . $index . '.condition') is-invalid @enderror"
                                                wire:model.live="refundItems.{{ $index }}.condition">
                                                <option value="">Seleccione</option>
                                                <option value="new">Nuevo</option>
                                                <option value="used">Usado</option>
                                                <option value="damaged">Dañado</option>
                                                <option value="defective">Defectuoso</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reason">Motivo de la Devolución</label>
                                <textarea id="reason" class="form-control @error('reason') is-invalid @enderror" wire:model.defer="reason"
                                    rows="3"></textarea>
                                @error('reason')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 text-right">
                            <h4 class="mt-4">Total a Devolver:
                                <strong>${{ number_format($total_amount, 2) }}</strong>
                            </h4>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button class="btn btn-success" wire:click="store" wire:loading.attr="disabled">
                            <span wire:loading.remove>Guardar Devolución</span>
                            <span wire:loading>Procesando...</span>
                        </button>
                        <button class="btn btn-secondary" wire:click="resetForm">Cancelar</button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Listado de devoluciones existentes -->
        <div class="card shadow mt-5">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Historial de Devoluciones</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <input type="text" class="form-control" placeholder="Buscar por ID de devolución o cliente..."
                        wire:model.live="search">
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID Devolución</th>
                                <th>ID Venta</th>
                                <th>Cliente</th>
                                <th>Total Devuelto</th>
                                <th>Motivo</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($refunds as $refund)
                                <tr>
                                    <td>{{ $refund->id }}</td>
                                    <td>{{ $refund->sale_id }}</td>
                                    <td>{{ $refund->customer->name ?? 'N/A' }}</td>
                                    <td>${{ number_format($refund->total_amount, 2) }}</td>
                                    <td>{{ $refund->reason }}</td>
                                    <td>{{ $refund->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <button class="btn btn-info btn-sm">Ver</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No se han encontrado devoluciones.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $refunds->links() }}
            </div>
        </div>
    </div>
</div>
