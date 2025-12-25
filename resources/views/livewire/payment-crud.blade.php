
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Pagos</h2>
        {{-- Direct creation not allowed, so no create button --}}
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

