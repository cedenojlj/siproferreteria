@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Devoluciones</h2>
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
        <input type="text" class="form-control" placeholder="Buscar devoluciones por estado, motivo, venta o cliente..." wire:model.live="search">
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Venta Ref.</th>
                    <th>Cliente</th>
                    <th>Usuario</th>
                    <th>Monto Total (USD)</th>
                    <th>Estado</th>
                    <th>Método</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($refunds as $refund)
                <tr>
                    <td>{{ $refund->id }}</td>
                    <td>{{ $refund->sale->invoice_number ?? 'N/A' }}</td>
                    <td>{{ $refund->customer->name ?? 'N/A' }}</td>
                    <td>{{ $refund->user->name ?? 'N/A' }}</td>
                    <td>{{ number_format($refund->total_amount_usd, 2) }} USD</td>
                    <td><span class="badge {{
                        $refund->status == 'completed' ? 'bg-success' :
                        ($refund->status == 'approved' ? 'bg-primary' :
                        ($refund->status == 'rejected' ? 'bg-danger' :
                        ($refund->status == 'cancelled' ? 'bg-secondary' : 'bg-warning text-dark')))
                    }}">{{ $refund->status }}</span></td>
                    <td>{{ $refund->refund_method }}</td>
                    <td>{{ $refund->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <button wire:click="edit({{ $refund->id }})" class="btn btn-sm btn-primary">Ver/Editar</button>
                        {{-- Delete button is intentionally absent/disabled from CRUD logic --}}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">No hay devoluciones registradas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $refunds->links('pagination::bootstrap-5') }}
    </div>

    <!-- Modal de Edición/Detalles de Devolución -->
    @if($isModalOpen)
    <div class="modal d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles de Devolución #{{ $refund_id }}</h5>
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
                                <label for="total_amount_usd" class="form-label">Monto Total (USD):</label>
                                <input type="text" class="form-control" id="total_amount_usd" value="{{ number_format($total_amount_usd, 2) }}" disabled>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="tax_returned_local" class="form-label">Impuesto Devuelto (Local):</label>
                                <input type="text" class="form-control" id="tax_returned_local" value="{{ number_format($tax_returned_local, 2) }}" disabled>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="refund_method" class="form-label">Método de Devolución:</label>
                                <input type="text" class="form-control text-capitalize" id="refund_method" value="{{ $refund_method }}" disabled>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="credit_note_number" class="form-label">Número Nota de Crédito:</label>
                                <input type="text" class="form-control" id="credit_note_number" value="{{ $credit_note_number ?? 'N/A' }}" disabled>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Estado:</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" wire:model="status">
                                <option value="pending">Pendiente</option>
                                <option value="approved">Aprobada</option>
                                <option value="rejected">Rechazada</option>
                                <option value="completed">Completada</option>
                                <option value="cancelled">Cancelada</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Razón:</label>
                            <textarea class="form-control @error('reason') is-invalid @enderror" id="reason" rows="3" wire:model="reason"></textarea>
                            @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
@endsection
