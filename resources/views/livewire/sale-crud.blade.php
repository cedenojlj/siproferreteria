@extends('layouts.app')

@section('content')
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
        <input type="text" class="form-control" placeholder="Buscar ventas por número de factura, cliente, vendedor o cajero..." wire:model.live="search">
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Factura</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th>Cajero</th>
                    <th>Total (Local)</th>
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
                    <td>{{ $sale->seller->name ?? 'N/A' }}</td>
                    <td>{{ $sale->cashier->name ?? 'N/A' }}</td>
                    <td>{{ number_format($sale->total_local, 2) }} {{ $sale->payment_currency }}</td>
                    <td><span class="badge {{
                        $sale->status == 'completed' ? 'bg-success' :
                        ($sale->status == 'pending' ? 'bg-warning text-dark' :
                        ($sale->status == 'cancelled' ? 'bg-danger' : 'bg-info'))
                    }}">{{ $sale->status }}</span></td>
                    <td>{{ $sale->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <button wire:click="edit({{ $sale->id }})" class="btn btn-sm btn-info text-white me-1">Editar</button>
                        <button wire:click="delete({{ $sale->id }})" class="btn btn-sm btn-danger">Eliminar</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">No hay ventas registradas.</td>
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
    <div class="modal d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $sale_id ? 'Editar Venta' : 'Crear Venta (No disponible)' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <form wire:submit.prevent="update">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="invoice_number" class="form-label">Número de Factura:</label>
                                <input type="text" class="form-control @error('invoice_number') is-invalid @enderror" id="invoice_number" wire:model="invoice_number">
                                @error('invoice_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="customer_id" class="form-label">Cliente:</label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" wire:model="customer_id">
                                    <option value="">Seleccione un cliente</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="seller_id" class="form-label">Vendedor:</label>
                                <select class="form-select @error('seller_id') is-invalid @enderror" id="seller_id" wire:model="seller_id">
                                    <option value="">Seleccione un vendedor</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('seller_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cashier_id" class="form-label">Cajero:</label>
                                <select class="form-select @error('cashier_id') is-invalid @enderror" id="cashier_id" wire:model="cashier_id">
                                    <option value="">Seleccione un cajero</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('cashier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="payment_currency" class="form-label">Moneda:</label>
                                <select class="form-select @error('payment_currency') is-invalid @enderror" id="payment_currency" wire:model="payment_currency">
                                    <option value="BS">Bolívares (BS)</option>
                                    <option value="USD">Dólares (USD)</option>
                                </select>
                                @error('payment_currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="payment_method" class="form-label">Método de Pago:</label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" id="payment_method" wire:model="payment_method">
                                    <option value="CASH">Efectivo</option>
                                    <option value="WIRE_TRANSFER">Transferencia</option>
                                    <option value="MOBILE_PAYMENT">Pago Móvil</option>
                                    <option value="ZELLE">Zelle</option>
                                    <option value="BANESCO_PANAMA">Banesco Panamá</option>
                                    <option value="OTHER">Otro</option>
                                </select>
                                @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="payment_type" class="form-label">Tipo de Pago:</label>
                                <select class="form-select @error('payment_type') is-invalid @enderror" id="payment_type" wire:model="payment_type">
                                    <option value="cash">Contado</option>
                                    <option value="credit">Crédito</option>
                                </select>
                                @error('payment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="exchange_rate" class="form-label">Tasa de Cambio:</label>
                                <input type="number" step="0.0001" class="form-control @error('exchange_rate') is-invalid @enderror" id="exchange_rate" wire:model="exchange_rate">
                                @error('exchange_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="subtotal_local" class="form-label">Subtotal (Local):</label>
                                <input type="number" step="0.01" class="form-control @error('subtotal_local') is-invalid @enderror" id="subtotal_local" wire:model="subtotal_local">
                                @error('subtotal_local') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="subtotal_usd" class="form-label">Subtotal (USD):</label>
                                <input type="number" step="0.01" class="form-control @error('subtotal_usd') is-invalid @enderror" id="subtotal_usd" wire:model="subtotal_usd">
                                @error('subtotal_usd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tax_local" class="form-label">Impuesto (Local):</label>
                                <input type="number" step="0.01" class="form-control @error('tax_local') is-invalid @enderror" id="tax_local" wire:model="tax_local">
                                @error('tax_local') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="total_local" class="form-label">Total (Local):</label>
                                <input type="number" step="0.01" class="form-control @error('total_local') is-invalid @enderror" id="total_local" wire:model="total_local">
                                @error('total_local') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="total_usd" class="form-label">Total (USD):</label>
                                <input type="number" step="0.01" class="form-control @error('total_usd') is-invalid @enderror" id="total_usd" wire:model="total_usd">
                                @error('total_usd') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pending_balance" class="form-label">Saldo Pendiente:</label>
                                <input type="number" step="0.01" class="form-control @error('pending_balance') is-invalid @enderror" id="pending_balance" wire:model="pending_balance">
                                @error('pending_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Estado:</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" wire:model="status">
                                    <option value="pending">Pendiente</option>
                                    <option value="completed">Completada</option>
                                    <option value="cancelled">Cancelada</option>
                                    <option value="credit">Crédito</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notas:</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" rows="3" wire:model="notes"></textarea>
                            @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
</div>
@endsection
