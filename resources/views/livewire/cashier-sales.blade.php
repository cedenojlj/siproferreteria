<div>
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Ventas Pendientes</h1>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Listado de Ventas</h6>
            </div>
            <div class="card-body">
                @if(session()->has('message'))
                    <div class="alert alert-success">{{ session('message') }}</div>
                @endif

                @if ($isEditing)
                    <div>
                        <h4>Editando Venta #{{ $saleId }}</h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <select wire:model="customer_id" class="form-control">
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Método de Pago</label>
                                    <select wire:model="payment_method" class="form-control">
                                        <option value="cash">Efectivo</option>
                                        <option value="card">Tarjeta</option>
                                        <option value="transfer">Transferencia</option>
                                    </select>
                                </div>
                            </div>
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label>Estado</label>
                                    <select wire:model="status" class="form-control">
                                        <option value="pendiente">Pendiente</option>
                                        <option value="completed">Completada</option>
                                        <option value="cancelled">Cancelada</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Lista de productos --}}
                        <h5 class="mt-4">Productos</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unit.</th>
                                    <th>Subtotal</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($saleItems as $index => $item)
                                    <tr>
                                        <td>{{ $item['name'] }}</td>
                                        <td><input type="number" class="form-control" wire:model.live="saleItems.{{ $index }}.quantity"></td>
                                        <td>{{ number_format($item['price'], 2) }}</td>
                                        <td>{{ number_format($item['subtotal'], 2) }}</td>
                                        <td>
                                            <button class="btn btn-danger btn-sm" wire:click="removeItem({{ $index }})">Eliminar</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Total:</strong></td>
                                    <td colspan="2"><strong>{{ number_format($total_amount, 2) }}</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                        
                        <div class="mt-4">
                             <button class="btn btn-primary" wire:click="updateSale">Actualizar Venta</button>
                             <button class="btn btn-info" wire:click="printTicket">Imprimir Ticket</button>
                             <button class="btn btn-secondary" wire:click="cancelEdit">Cancelar</button>
                        </div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID Venta</th>
                                    <th>Cliente</th>
                                    <th>Vendedor</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sales as $sale)
                                    <tr>
                                        <td>{{ $sale->id }}</td>
                                        <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                                        <td>{{ $sale->user->name ?? 'N/A' }}</td>
                                        <td>{{ number_format($sale->total_usd, 2) }} $ </td>
                                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <button class="btn btn-success btn-sm" wire:click="editSale({{ $sale->id }})">
                                                <i class="fas fa-cash-register"></i> Cobrar
                                            </button>
                                            <button class="btn btn-danger btn-sm" wire:click="confirmDelete({{ $sale->id }})">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay ventas pendientes.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $sales->links() }}
                @endif
            </div>
        </div>
    </div>
</div>