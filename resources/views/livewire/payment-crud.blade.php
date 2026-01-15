<div>
    {{-- Módulo de Registro de Abonos a Créditos --}}
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">Registro de Abonos a Créditos</h4>
        </div>
        <div class="card-body">
            <div class="row gx-3">
                {{-- 1. Selector de Cliente --}}
                <div class="col-md-6 mb-3">
                    <label for="customer" class="form-label">1. Seleccione el Cliente</label>
                    <select id="customer" class="form-select" wire:model.live="selectedCustomerId">
                        <option value="">-- Seleccionar un cliente --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- 2. Selector de Venta a Crédito --}}
                @if($selectedCustomerId)
                <div class="col-md-6 mb-3">
                    <label for="sale" class="form-label">2. Seleccione la Venta a Crédito</label>
                    <div wire:loading wire:target="selectedCustomerId" class="text-muted">Cargando ventas...</div>
                    <select id="sale" class="form-select" wire:model.live="selectedSaleId" wire:loading.remove>
                        <option value="">-- Seleccionar una venta --</option>
                        @forelse($creditSales as $sale)
                            <option value="{{ $sale->id }}">
                                Factura #{{ $sale->invoice_number }} (Pendiente: ${{ number_format($sale->pending_balance, 2) }})
                            </option>
                        @empty
                            <option value="" disabled>No hay ventas a crédito pendientes para este cliente.</option>
                        @endforelse
                    </select>
                </div>
                @endif
            </div>

            {{-- 3. Formulario de Abono --}}
            @if($selectedSale)
            <div class="border-top pt-3 mt-3">
                <h5 class="mb-3">3. Registrar Abono para Factura #{{ $selectedSale->invoice_number }}</h5>
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="new_payment_amount" class="form-label">Monto a abonar (USD)</label>
                        <input type="number" id="new_payment_amount" class="form-control @error('new_payment_amount') is-invalid @enderror" wire:model.live="new_payment_amount" step="0.01">
                        @error('new_payment_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3">
                        <label for="new_payment_method" class="form-label">Método de Pago</label>
                        <select id="new_payment_method" class="form-select" wire:model.live="new_payment_method">
                             <option value="CASH">Efectivo</option>
                            <option value="WIRE_TRANSFER">Transferencia</option>
                            <option value="MOBILE_PAYMENT">Pago Móvil</option>
                            <option value="ZELLE">Zelle</option>
                            <option value="BANESCO_PANAMA">Banesco Panamá</option>
                            <option value="OTHER">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="new_payment_reference" class="form-label">Referencia (Opcional)</label>
                        <input type="text" id="new_payment_reference" class="form-control" wire:model.live="new_payment_reference">
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-success w-100" wire:click="addPayment" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="addPayment">Abonar</span>
                            <span wire:loading wire:target="addPayment">Abonando...</span>
                        </button>
                    </div>
                </div>
                <div class="mt-2 text-muted">
                    Saldo Total: ${{ number_format($selectedSale->total_usd, 2) }} |
                    Saldo Pendiente: <span class="fw-bold">${{ number_format($selectedSale->pending_balance, 2) }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>


    {{-- Gestión de Pagos (Tabla existente) --}}
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Historial de Pagos Recientes</h2>
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

        @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="mb-3">
            <input type="text" class="form-control" placeholder="Buscar pagos por referencia, método, venta, cliente o usuario..." wire:model.live="search">
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Venta Ref.</th>
                        <th>Cliente</th>
                        <th>Monto (USD)</th>
                        <th>Método</th>
                        <th>Referencia</th>
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td>{{ $payment->id }}</td>
                        <td>{{ $payment->sale->invoice_number ?? 'N/A' }}</td>
                        <td>{{ $payment->customer->name ?? 'N/A' }}</td>
                        <td>{{ number_format($payment->amount_usd, 2) }}</td>
                        <td>{{ $payment->payment_method }}</td>
                        <td>{{ $payment->reference ?? 'N/A' }}</td>
                        <td>{{ $payment->user->name ?? 'N/A' }}</td>
                        <td>{{ $payment->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <button wire:click="edit({{ $payment->id }})" class="btn btn-sm btn-primary">Ver/Editar</button>
                            {{-- Delete button is intentionally absent/disabled from CRUD logic --}}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center">No hay pagos registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $payments->links('pagination::bootstrap-5') }}
        </div>

        <!-- Modal de Edición/Detalles de Pago -->
        @if($isModalOpen)
        <div class="modal d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles de Pago #{{ $payment_id }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                    </div>
                    <form wire:submit.prevent="update">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="sale_invoice_number" class="form-label">Venta Referencia:</label>
                                    <input type="text" class="form-control" id="sale_invoice_number" value="{{ \App\Models\Sale::find($sale_id)->invoice_number ?? 'N/A' }}" disabled>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="customer_name" class="form-label">Cliente:</label>
                                    <input type="text" class="form-control" id="customer_name" value="{{ \App\Models\Customer::find($customer_id)->name ?? 'N/A' }}" disabled>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="user_name" class="form-label">Usuario:</label>
                                    <input type="text" class="form-control" id="user_name" value="{{ \App\Models\User::find($user_id)->name ?? 'N/A' }}" disabled>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="amount_local" class="form-label">Monto (Local):</label>
                                    <input type="text" class="form-control" id="amount_local" value="{{ number_format($amount_local, 2) }}" disabled>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="amount_usd" class="form-label">Monto (USD):</label>
                                    <input type="text" class="form-control" id="amount_usd" value="{{ number_format($amount_usd, 2) }}" disabled>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="payment_method" class="form-label">Método de Pago:</label>
                                    <input type="text" class="form-control text-capitalize" id="payment_method" value="{{ $payment_method }}" disabled>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="reference" class="form-label">Referencia:</label>
                                    <input type="text" class="form-control @error('reference') is-invalid @enderror" id="reference" wire:model="reference">
                                    @error('reference') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notas:</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" rows="3" wire:model="notes"></textarea>
                                @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
