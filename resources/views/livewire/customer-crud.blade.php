
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Administración de Clientes</h1>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <button wire:click="create()" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Nuevo Cliente
            </button>
            <div class="w-50">
                <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="Buscar por nombre, documento o email...">
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo Doc.</th>
                            <th>Documento</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->id }}</td>
                            <td>{{ $customer->document_type }}</td>
                            <td>{{ $customer->document }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->email }}</td>
                            <td class="text-center">
                                <button wire:click="edit({{ $customer->id }})" class="btn btn-warning btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="delete({{ $customer->id }})" wire:confirm="¿Estás seguro de que quieres eliminar este cliente?" class="btn btn-danger btn-sm" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No se encontraron clientes.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $customers->links() }}
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Cliente -->
    @if($isModalOpen)
    <div class="modal fade show" tabindex="-1" style="display: block;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $customer_id ? 'Editar Cliente' : 'Crear Nuevo Cliente' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $customer_id ? 'update' : 'store' }}">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="document_type" class="form-label">Tipo Documento</label>
                                <select class="form-select @error('document_type') is-invalid @enderror" id="document_type" wire:model="document_type">
                                    <option value="V">V - Venezolano</option>
                                    <option value="J">J - Jurídico</option>
                                    <option value="G">G - Gobierno</option>
                                    <option value="P">P - Pasaporte</option>
                                </select>
                                @error('document_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="document" class="form-label">Número Documento</label>
                                <input type="text" class="form-control @error('document') is-invalid @enderror" id="document" wire:model.lazy="document">
                                @error('document') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre Completo / Razón Social</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.lazy="name">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Teléfono (Opcional)</label>
                                <input type="text" class="form-control" id="phone" wire:model.lazy="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email (Opcional)</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" wire:model.lazy="email">
                                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Dirección (Opcional)</label>
                            <textarea class="form-control" id="address" wire:model.lazy="address" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="{{ $customer_id ? 'update' : 'store' }}">
                        {{ $customer_id ? 'Actualizar Cliente' : 'Crear Cliente' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>

