<div class="container-fluid">
    <div class="h3 mb-4 text-gray-800">
        <h2>Gestión de Proveedores</h2>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <button wire:click="create()" class="btn btn-primary"><i class="fas fa-plus-circle"></i> Crear Nuevo Proveedor</button>

            <div class="w-50">
                <input type="text" class="form-control" placeholder="Buscar proveedores..." wire:model.live.debounce.300ms="search">
            </div>
        </div>
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>RIF</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Dirección</th>
                            <th>Contacto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                            <tr>
                                <td>{{ $supplier->id }}</td>
                                <td>{{ $supplier->rif }}</td>
                                <td>{{ $supplier->name }}</td>
                                <td>{{ $supplier->phone }}</td>
                                <td>{{ $supplier->email }}</td>
                                <td>{{ $supplier->address }}</td>
                                <td>{{ $supplier->contact_person }}</td>
                                <td>
                                    <button wire:click="edit({{ $supplier->id }})"
                                        class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-edit"></i></button>
                                    <button wire:click="delete({{ $supplier->id }})" wire:confirm="¿Estás seguro de que quieres eliminar este Proveedor?"
                                        class="btn btn-sm btn-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay proveedores registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $suppliers->links() }}
            </div>


        </div>
    </div>





    <!-- Modal de Creación/Edición -->
    @if ($isModalOpen)
        <div class="modal d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $supplier_id ? 'Editar Proveedor' : 'Crear Proveedor' }}</h5>
                        <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                    </div>
                    <form wire:submit.prevent="{{ $supplier_id ? 'update' : 'store' }}">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="rif" class="form-label">RIF:</label>
                                <input type="text" class="form-control @error('rif') is-invalid @enderror"
                                    id="rif" placeholder="Ingrese el RIF" wire:model="rif">
                                @error('rif')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre:</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                    id="name" placeholder="Ingrese el nombre" wire:model="name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Teléfono:</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                    id="phone" placeholder="Ingrese el teléfono" wire:model="phone">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" placeholder="Ingrese el email" wire:model="email">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Dirección:</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" id="address" rows="3"
                                    placeholder="Ingrese la dirección" wire:model="address"></textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="contact_person" class="form-label">Persona de Contacto:</label>
                                <input type="text" class="form-control @error('contact_person') is-invalid @enderror"
                                    id="contact_person" placeholder="Ingrese la persona de contacto"
                                    wire:model="contact_person">
                                @error('contact_person')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                            <button type="submit"
                                class="btn btn-primary">{{ $supplier_id ? 'Actualizar' : 'Guardar' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
