
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Administración de Unidades de Medida</h1>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <button wire:click="create()" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Nueva Unidad de Medida
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Símbolo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($unit_measures as $unit_measure)
                        <tr>
                            <td>{{ $unit_measure->id }}</td>
                            <td>{{ $unit_measure->name }}</td>
                            <td>{{ $unit_measure->symbol }}</td>
                            <td class="text-center">
                                <button wire:click="edit({{ $unit_measure->id }})" class="btn btn-warning btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="delete({{ $unit_measure->id }})" wire:confirm="¿Estás seguro de que quieres eliminar esta unidad de medida?" class="btn btn-danger btn-sm" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No se encontraron unidades de medida.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $unit_measures->links() }}
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Unidad de Medida -->
    @if($isModalOpen)
    <div class="modal fade show" tabindex="-1" style="display: block;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $unit_measure_id ? 'Editar Unidad de Medida' : 'Crear Nueva Unidad de Medida' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $unit_measure_id ? 'update' : 'store' }}">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.lazy="name" placeholder="Ej: Kilogramo">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="symbol" class="form-label">Símbolo</label>
                            <input type="text" class="form-control @error('symbol') is-invalid @enderror" id="symbol" wire:model.lazy="symbol" placeholder="Ej: kg">
                            @error('symbol') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="{{ $unit_measure_id ? 'update' : 'store' }}">
                        {{ $unit_measure_id ? 'Actualizar Unidad' : 'Crear Unidad' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>

