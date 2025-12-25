@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Compras</h2>
        <button wire:click="create()" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Crear Nueva Compra</button>
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
        <input type="text" class="form-control" placeholder="Buscar compras por número de factura, proveedor o usuario..." wire:model.live="search">
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Factura</th>
                    <th>Proveedor</th>
                    <th>Usuario</th>
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
                    <td>{{ $purchase->user->name ?? 'N/A' }}</td>
                    <td>{{ number_format($purchase->total, 2) }} {{ $purchase->payment_currency }}</td>
                    <td><span class="badge {{
                        $purchase->status == 'received' ? 'bg-success' :
                        ($purchase->status == 'pending' ? 'bg-warning text-dark' : 'bg-danger')
                    }}">{{ $purchase->status }}</span></td>
                    <td>{{ $purchase->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <button wire:click="edit({{ $purchase->id }})" class="btn btn-sm btn-info text-white me-1">Editar</button>
                        <button wire:click="delete({{ $purchase->id }})" class="btn btn-sm btn-danger">Eliminar</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No hay compras registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $purchases->links('pagination::bootstrap-5') }}
    </div>

    <!-- Modal de Edición de Compra -->
    @if($isModalOpen)
    <div class="modal d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $purchase_id ? 'Editar Compra' : 'Crear Compra (No disponible)' }}</h5>
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
                                <label for="supplier_id" class="form-label">Proveedor:</label>
                                <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" wire:model="supplier_id">
                                    <option value="">Seleccione un proveedor</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                                @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="user_id" class="form-label">Usuario:</label>
                                <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" wire:model="user_id">
                                    <option value="">Seleccione un usuario</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                @error('user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment_currency" class="form-label">Moneda de Pago:</label>
                                <select class="form-select @error('payment_currency') is-invalid @enderror" id="payment_currency" wire:model="payment_currency">
                                    <option value="BS">Bolívares (BS)</option>
                                    <option value="USD">Dólares (USD)</option>
                                </select>
                                @error('payment_currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="exchange_rate" class="form-label">Tasa de Cambio:</label>
                                <input type="number" step="0.0001" class="form-control @error('exchange_rate') is-invalid @enderror" id="exchange_rate" wire:model="exchange_rate">
                                @error('exchange_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="subtotal" class="form-label">Subtotal:</label>
                                <input type="number" step="0.01" class="form-control @error('subtotal') is-invalid @enderror" id="subtotal" wire:model="subtotal">
                                @error('subtotal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tax" class="form-label">Impuesto:</label>
                                <input type="number" step="0.01" class="form-control @error('tax') is-invalid @enderror" id="tax" wire:model="tax">
                                @error('tax') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="total" class="form-label">Total:</label>
                                <input type="number" step="0.01" class="form-control @error('total') is-invalid @enderror" id="total" wire:model="total">
                                @error('total') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Estado:</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" wire:model="status">
                                    <option value="pending">Pendiente</option>
                                    <option value="received">Recibida</option>
                                    <option value="cancelled">Cancelada</option>
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
