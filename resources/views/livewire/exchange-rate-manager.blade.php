
<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">Gestión de Tasas de Cambio</h6>
                            <button wire:click="create()" class="btn btn-primary btn-sm">Crear Nueva Tasa</button>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <input wire:model.live.debounce.300ms="search" type="text" class="form-control" placeholder="Buscar por tasa o fuente...">
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                         @if (session()->has('message'))
                            <div class="alert alert-success m-3">{{ session('message') }}</div>
                        @endif
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" wire:click="sortBy('rate')">
                                            Tasa <i class="fas fa-sort"></i>
                                        </th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" wire:click="sortBy('source')">
                                            Fuente <i class="fas fa-sort"></i>
                                        </th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" wire:click="sortBy('is_active')">
                                            Estado <i class="fas fa-sort"></i>
                                        </th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" wire:click="sortBy('created_at')">
                                            Creado <i class="fas fa-sort"></i>
                                        </th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($rates as $rate)
                                    <tr>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ number_format($rate->rate, 4) }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $rate->source }}</p>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            <span class="badge badge-sm bg-gradient-{{ $rate->is_active ? 'success' : 'secondary' }}" wire:click="toggleStatus({{ $rate->id }})" style="cursor: pointer;">
                                                {{ $rate->is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">{{ $rate->created_at->format('d/m/Y') }}</span>
                                        </td>
                                        <td class="align-middle">
                                            <button wire:click="edit({{ $rate->id }})" class="btn btn-sm btn-info mb-0">Editar</button>
                                            <button wire:click="confirmDelete({{ $rate->id }})" class="btn btn-sm btn-danger mb-0">Eliminar</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 px-4">
                            {{ $rates->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    








    <!-- Create/Edit Modal -->
    <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="formModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formModalLabel">{{ $modalTitle }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="mb-3">
                            <label for="rate" class="form-label">Tasa</label>
                            <input type="number" step="0.0001" class="form-control" id="rate" wire:model.defer="rate">
                            @error('rate') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="source" class="form-label">Fuente</label>
                            <input type="text" class="form-control" id="source" wire:model.defer="source">
                            @error('source') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" wire:model.defer="is_active">
                            <label class="form-check-label" for="is_active">
                                Activo
                            </label>
                        </div>
                         @error('is_active') <span class="text-danger">{{ $message }}</span> @enderror
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" wire:click.prevent="save()">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true" wire:ignore.self>
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres eliminar esta tasa de cambio?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" wire:click.prevent="delete()">Eliminar</button>
                </div>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('livewire:initialized', () => {
            const formModal = new bootstrap.Modal(document.getElementById('formModal'));
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

            @this.on('show-modal', () => {
                formModal.show();
            });

            @this.on('hide-modal', () => {
                formModal.hide();
            });
            
            @this.on('show-delete-modal', () => {
                deleteModal.show();
            });

            @this.on('hide-delete-modal', () => {
                deleteModal.hide();
            });
        });
    </script>
    @endpush

</div>

