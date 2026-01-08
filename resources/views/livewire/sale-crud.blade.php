<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Ventas</h2>
        <button wire:click="create()" class="btn btn-primary">Crear Nueva Venta</button>
    </div>

    @if (session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    @if (session()->has('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="mb-3">
        <input type="text" class="form-control" placeholder="Buscar ventas por número de factura o cliente..." wire:model.live="search">
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Factura</th>
                    <th>Cliente</th>
                    <th>Total (USD)</th>
                    <th>Pendiente (USD)</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale->id }}</td>
                    <td>{{ $sale->invoice_number }}</td>
                    <td>{{ $sale->customer->name ?? 'N/A' }}</td>
                    <td>{{ number_format($sale->total_usd, 2) }}</td>
                    <td>{{ number_format($sale->pending_balance, 2) }}</td>
                    <td><span class="badge {{
                        $sale->status == 'completed' ? 'bg-success' :
                        ($sale->status == 'pending' ? 'bg-warning text-dark' :
                        ($sale->status == 'credit' ? 'bg-info text-dark' :
                        ($sale->status == 'cancelled' ? 'bg-danger' : 'bg-secondary')))
                    }}">{{ ucfirst($sale->status) }}</span></td>
                    <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                    <td class="d-flex">
                        <button wire:click="view({{ $sale->id }})" class="btn btn-sm btn-light me-1" title="Ver"><i class="fa fa-eye"></i></button>
                        <button wire:click="edit({{ $sale->id }})" class="btn btn-sm btn-info text-white me-1" title="Editar"><i class="fa fa-edit"></i></button>
                        @if($sale->pending_balance > 0)
                            <button wire:click="openPaymentModal({{ $sale->id }})" class="btn btn-sm btn-success me-1" title="Pagar"><i class="fa fa-dollar-sign"></i></button>
                        @endif
                        <button wire:click="delete({{ $sale->id }})" class="btn btn-sm btn-danger" title="Eliminar"><i class="fa fa-trash"></i></button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No hay ventas registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $sales->links('pagination::bootstrap-5') }}
    </div>

    <!-- Modal de Edición de Venta -->
    @if($isModalOpen)
    <div class="modal d-block" wire:ignore.self tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $sale_id ? 'Editar Venta' : 'Crear Venta (No disponible)' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="update">
                    <div class="modal-body">
                        {{-- Form fields for editing a sale --}}
                        {{-- Customer, Seller, etc. --}}
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="invoice_number" class="form-label">Número de Factura:</label>
                                <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" id="invoice_number" wire:model="invoice_number">
                                @error('invoice_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="customer_id" class="form-label">Cliente:</label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" wire:model="customer_id">
                                    <option value="">Seleccione un cliente</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- 'status', ['pending', 'completed', 'cancelled', 'credit'] --}}
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Estado de la Venta:</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" wire:model="status">
                                    <option value="pending">Pendiente</option>
                                    <option value="completed">Completada</option>
                                    <option value="cancelled">Cancelada</option>
                                    <option value="credit">Crédito</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>              
                        </div>

                        <div class="row">                           

                            {{-- payment_currency ['BS', 'USD'] -- default 'USD' --}}
                            <div class="col-md-4  mb-3"> 
                                <label for="payment_currency" class="form-label">Moneda de Pago:</label>
                                <select class="form-select @error('payment_currency') is-invalid @enderror" id="payment_currency" wire:model="payment_currency">
                                    <option value="USD">USD</option>
                                    <option value="BS">BS</option>
                                </select>
                                @error('payment_currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- payment_method, ['EFECTIVO', 'DEBITO','TRANSFERENCIA', 'PAGO_MOVIL', 'ZELLE', 'BANESCO_PANAMA', 'OTRO']--}}
                            <div class="col-md-4  mb-3">
                                <label for="payment_method" class="form-label">Método de Pago:</label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" wire:model="payment_method">
                                    <option value="EFECTIVO">Efectivo</option>
                                    <option value="DEBITO">Débito</option>
                                    <option value="TRANSFERENCIA">Transferencia</option>
                                    <option value="PAGO_MOVIL">Pago Móvil</option>
                                    <option value="ZELLE">Zelle</option>
                                    <option value="BANESCO_PANAMA">Banesco Panamá</option>
                                    <option value="OTRO">Otro</option>
                                </select>
                                @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- 'payment_type', ['EFECTIVO', 'CREDITO']--}}
                            <div class="col-md-4  mb-3">
                                <label for="payment_type" class="form-label">Tipo de Pago:</label>
                                <select class="form-select @error('payment_type') is-invalid @enderror" id="payment_type" wire:model="payment_type">
                                    <option value="EFECTIVO">Efectivo</option>
                                    <option value="CREDITO">Crédito</option>
                                </select>
                                @error('payment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>  
                            
                        </div>
                        <hr>
                        {{-- Products section with search --}}
                        <h5 class="mt-4">Productos en esta Venta</h5>
                        <div class="mb-3 position-relative">
                            <label for="productSearch" class="form-label">Buscar y Agregar Producto:</label>
                            <input type="text" id="productSearch" class="form-control" 
                                   placeholder="Buscar por nombre o código de barras..." 
                                   wire:model.live.debounce.300ms="productSearch">
                            
                            @if(count($this->productSearchResults))
                                <ul class="list-group mt-1 position-absolute w-100" style="z-index: 1000;">
                                    @foreach($this->productSearchResults as $product)
                                        <li class="list-group-item list-group-item-action" 
                                            wire:click="addProduct({{ $product->id }})"
                                            style="cursor: pointer;">
                                            {{ $product->name }} - ({{ $product->barcode }})
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                {{-- table headers --}}
                                <tbody>
                                    @forelse($saleItems as $index => $item)
                                    <tr>
                                        <td>{{ $item['product_name'] ?? 'Producto no encontrado' }}</td>
                                        <td><input type="number" wire:model.live="saleItems.{{ $index }}.quantity" class="form-control form-control-sm"></td>
                                        <td><input type="number" wire:model.live="saleItems.{{ $index }}.unit_price" class="form-control form-control-sm"></td>
                                        <td>{{ number_format($item['subtotal_usd'], 2) }}</td>
                                        <td><button type="button" wire:click="removeProduct({{ $index }})" class="btn btn-danger btn-sm">Quitar</button></td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="5" class="text-center">Aún no hay productos.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de Ver Venta -->
    @if($isViewModalOpen && $viewSale)
    <div class="modal d-block" wire:ignore.self tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ver Venta #{{ $viewSale->invoice_number }}</h5>
                    <button type="button" class="btn-close" wire:click="closeViewModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- Sale details --}}
                    <p><strong>Cliente:</strong> {{ $viewSale->customer->name ?? 'N/A' }}</p>
                    {{-- ... other details --}}
                    <h5 class="mt-4">Productos</h5>
                    <table class="table table-bordered">
                        {{-- table headers --}}
                        <tbody>
                            @foreach($viewSale->saleItems as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ number_format($item->subtotal_usd, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- Totals --}}
                    <p class="text-end"><strong>Subtotal:</strong> {{ number_format($viewSale->subtotal_usd, 2) }} USD</p>
                    <p class="text-end"><strong>Total:</strong> {{ number_format($viewSale->total_usd, 2) }} USD</p>
                    <p class="text-end"><strong>Pendiente:</strong> {{ number_format($viewSale->pending_balance, 2) }} USD</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeViewModal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de Pagos -->
    @if($isPaymentModalOpen && $paymentSale)
    <div class="modal d-block" wire:ignore.self tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Pago para Venta #{{ $paymentSale->invoice_number }}</h5>
                    <button type="button" class="btn-close" wire:click="closePaymentModal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="storePayment">
                    <div class="modal-body">
                        <p><strong>Cliente:</strong> {{ $paymentSale->customer->name ?? 'N/A' }}</p>
                        <p><strong>Total Venta:</strong> {{ number_format($paymentSale->total_usd, 2) }} USD</p>
                        <p><strong>Saldo Pendiente:</strong> {{ number_format($paymentSale->pending_balance, 2) }} USD</p>
                        <hr>
                        <div class="mb-3">
                            <label for="payment_amount_usd" class="form-label">Monto a Pagar (USD)</label>
                            <input type="number" step="0.01" id="payment_amount_usd" class="form-control @error('payment_amount_usd') is-invalid @enderror" wire:model="payment_amount_usd">
                            @error('payment_amount_usd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="payment_payment_method" class="form-label">Método de Pago</label>
                            <select id="payment_payment_method" class="form-select @error('payment_payment_method') is-invalid @enderror" wire:model="payment_payment_method">
                                <option value="CASH">Efectivo</option>
                                <option value="WIRE_TRANSFER">Transferencia</option>
                                <option value="MOBILE_PAYMENT">Pago Móvil</option>
                                <option value="ZELLE">Zelle</option>
                                <option value="BANESCO_PANAMA">Banesco Panamá</option>
                                <option value="OTHER">Otro</option>
                            </select>
                             @error('payment_payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="payment_reference" class="form-label">Referencia</label>
                            <input type="text" id="payment_reference" class="form-control @error('payment_reference') is-invalid @enderror" wire:model="payment_reference">
                            @error('payment_reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="payment_notes" class="form-label">Notas</label>
                            <textarea id="payment_notes" class="form-control @error('payment_notes') is-invalid @enderror" wire:model="payment_notes" rows="2"></textarea>
                            @error('payment_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-info" wire:click="showPayments">Ver Pagos</button>
                        <div>
                            <button type="button" class="btn btn-secondary" wire:click="closePaymentModal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Pago</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal de Lista de Pagos -->
    @if($isPaymentsListModalOpen)
    <div class="modal d-block" wire:ignore.self tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.6);">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pagos para la Venta #{{ $paymentSale->invoice_number }}</h5>
                    <button type="button" class="btn-close" wire:click="closePaymentsListModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Monto (USD)</th>
                                    <th>Método</th>
                                    <th>Referencia</th>
                                    <th>Registrado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salePayments as $payment)
                                <tr>
                                    <td>{{ $payment->id }}</td>
                                    <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                                    <td>{{ number_format($payment->amount_usd, 2) }}</td>
                                    <td>{{ $payment->payment_method }}</td>
                                    <td>{{ $payment->reference ?? 'N/A' }}</td>
                                    <td>{{ $payment->user->name ?? 'N/A' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay pagos registrados para esta venta.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closePaymentsListModal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

