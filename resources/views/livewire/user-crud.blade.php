
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Administración de Usuarios</h1>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <button wire:click="create()" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Crear Nuevo Usuario
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="text-center">
                                <button wire:click="edit({{ $user->id }})" class="btn btn-warning btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button wire:click="delete({{ $user->id }})" wire:confirm="¿Estás seguro de que quieres eliminar a este usuario?" class="btn btn-danger btn-sm" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">No se encontraron usuarios.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-end">
                {{ $users->links() }}
            </div>
        </div>
    </div>

    <!-- Modal para Crear/Editar Usuario -->
    @if($isModalOpen)
    <div class="modal fade show" tabindex="-1" style="display: block;" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $user_id ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}</h5>
                    <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="{{ $user_id ? 'update' : 'store' }}">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model.lazy="name" placeholder="John Doe">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" wire:model.lazy="email" placeholder="john.doe@example.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" wire:model.lazy="password" placeholder="{{ $user_id ? 'Dejar en blanco para no cambiar' : '' }}">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="company_id" class="form-label">Compañía</label>
                            <select class="form-select @error('company_id') is-invalid @enderror" id="company_id" wire:model.lazy="company_id">
                                <option value="">Seleccione una compañía</option>
                                @foreach($company as $comp)
                                    <option value="{{ $comp->id }}">{{ $comp->name }}</option>
                                @endforeach
                            </select>
                            @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" wire:model.lazy="phone" placeholder="(123) 456-7890">
                            @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Dirección</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" id="address" wire:model.lazy="address" placeholder="123 Calle Principal, Ciudad, País"></textarea>
                            @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" wire:model.lazy="is_active">
                            <label class="form-check-label" for="is_active">
                                Usuario Activo
                            </label>
                        </div>  
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeModal">Cancelar</button>
                    <button type="button" class="btn btn-primary" wire:click="{{ $user_id ? 'update' : 'store' }}">
                        {{ $user_id ? 'Actualizar Usuario' : 'Crear Usuario' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>

{{-- @push('styles')
<!-- Asegúrate de tener Font Awesome si usas esos íconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
@endpush
 --}}