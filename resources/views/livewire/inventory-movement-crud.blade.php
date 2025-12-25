@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Gestión de Movimientos de Inventario</h2>
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
        <input type="text" class="form-control" placeholder="Buscar movimientos por producto, tipo, notas o usuario..." wire:model.live="search">
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Ref. ID</th>
                    <th>Ref. Tipo</th>
                    <th>Usuario</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                <tr>
                    <td>{{ $movement->id }}</td>
                    <td>{{ $movement->product->name ?? 'N/A' }}</td>
                    <td><span class="badge {{
                        $movement->movement_type == 'in' ? 'bg-success' :
                        ($movement->movement_type == 'out' ? 'bg-danger' : 'bg-info')
                    }}">{{ $movement->movement_type }}</span></td>
                    <td>{{ $movement->quantity }}</td>
                    <td>{{ $movement->reference_id ?? 'N/A' }}</td>
                    <td>{{ $movement->reference_type ?? 'N/A' }}</td>
                    <td>{{ $movement->user->name ?? 'N/A' }}</td>
                    <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <button wire:click="edit({{ $movement->id }})" class="btn btn-sm btn-primary">Ver Detalles</button>
                        {{-- Edit/Delete buttons are intentionally absent/disabled from CRUD logic --}}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">No hay movimientos de inventario registrados.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center">
        {{ $movements->links('pagination::bootstrap-5') }}
    </div>

    <!-- Modal de Detalles de Movimiento -->
    @if($isModalOpen)
    <div class="modal d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles de Movimiento de Inventario #{{ $movement_id }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                {{-- Form is read-only, no wire:submit.prevent --}}
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="product_name" class="form-label">Producto:</label>
                            <input type="text" class="form-control" id="product_name" value="{{ \App\Models\Product::find($product_id)->name ?? 'N/A' }}" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="movement_type" class="form-label">Tipo de Movimiento:</label>
                            <input type="text" class="form-control text-capitalize" id="movement_type" value="{{ $movement_type }}" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label">Cantidad:</label>
                            <input type="text" class="form-control" id="quantity" value="{{ $quantity }}" disabled>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="reference_id" class="form-label">ID de Referencia:</label>
                            <input type="text" class="form-control" id="reference_id" value="{{ $reference_id ?? 'N/A' }}" disabled>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="reference_type" class="form-label">Tipo de Referencia:</label>
                            <input type="text" class="form-control text-capitalize" id="reference_type" value="{{ $reference_type ?? 'N/A' }}" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="exchange_rate" class="form-label">Tasa de Cambio:</label>
                            <input type="text" class="form-control" id="exchange_rate" value="{{ $exchange_rate ?? 'N/A' }}" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="user_name" class="form-label">Usuario:</label>
                            <input type="text" class="form-control" id="user_name" value="{{ \App\Models\User::find($user_id)->name ?? 'N/A' }}" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas:</label>
                        <textarea class="form-control" id="notes" rows="3" disabled>{{ $notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
