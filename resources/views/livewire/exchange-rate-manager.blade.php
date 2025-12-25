<div class="container-fluid">
    <div class="h3 mb-4 text-gray-800">
        <h2>Gestión de Tasas de Cambio</h2>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <button wire:click="create()" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Crear Nueva Tasa</button>

            <div class="w-50">
                <input type="text" class="form-control" placeholder="Buscar por tasa o fuente..." wire:model.live.debounce.300ms="search">
            </div>
        </div>
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Tasa</th>
                            <th>Fuente</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr>
                                <td>{{ $rate->id }}</td>
                                <td>{{ $rate->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ number_format($rate->rate, 2) }}</td>
                                <td>{{ $rate->source }}</td>
                                <td>
                                    <span class="badge bg-{{ $rate->is_active ? 'success' : 'secondary' }}" wire:click="toggleStatus({{ $rate->id }})" style="cursor: pointer;">
                                        {{ $rate->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <button wire:click="edit({{ $rate->id }})"
                                        class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></button>
                                    <button wire:click="delete({{ $rate->id }})" wire:confirm="¿Estás seguro de que quieres eliminar esta Tasa?"
                                        class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No hay tasas de cambio registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $rates->links() }}
            </div>

        </div>
    </div>

    <!-- Modal de Creación/Edición -->
    @if ($isModalOpen)
        <div class="modal d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $rate_id ? 'Editar Tasa de Cambio' : 'Crear Tasa de Cambio' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                    </div>
                    <form wire:submit.prevent="{{ $rate_id ? 'update' : 'store' }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="rate" class="form-label">Tasa:</label>
                                <input type="number" step="0.0001" class="form-control @error('rate') is-invalid @enderror"
                                    id="rate" placeholder="Ingrese la tasa" wire:model="rate">
                                @error('rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="source" class="form-label">Fuente:</label>
                                <input type="text" class="form-control @error('source') is-invalid @enderror"
                                    id="source" placeholder="Ingrese la fuente" wire:model="source">
                                @error('source')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                                <label for="is_active" class="form-check-label">Activo</label>
                                @error('is_active')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                            <button type="submit"
                                class="btn btn-primary">{{ $rate_id ? 'Actualizar' : 'Guardar' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

