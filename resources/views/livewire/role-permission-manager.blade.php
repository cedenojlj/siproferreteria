<div>
    <style>
        /* Estilos para resaltar el usuario seleccionado */
        .user-list-item.selected {
            background-color: #e0e7ff; /* Un color de fondo suave */
        }
    </style>

    {{-- 1. Sección de Notificaciones --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        {{-- 2. Columna de Roles --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Roles</h5>
                </div>
                <div class="card-body">
                    <button wire:click="openModal('role')" class="btn btn-primary btn-sm mb-3">Crear Rol</button>
                    <ul class="list-group">
                        @forelse($roles as $role)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $role->name }}
                                <div>
                                    <button wire:click="editRole({{ $role->id }})" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></button>
                                    <button wire:click="deleteRole({{ $role->id }})" wire:confirm="¿Estás seguro de que quieres eliminar este rol?" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item">No hay roles definidos.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- 3. Columna de Permisos --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Permisos</h5>
                </div>
                <div class="card-body">
                    <button wire:click="openModal('permission')" class="btn btn-primary btn-sm mb-3">Crear Permiso</button>
                    <ul class="list-group">
                        @forelse($permissions as $permission)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $permission->name }}
                                <div>
                                    <button wire:click="editPermission({{ $permission->id }})" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i></button>
                                    <button wire:click="deletePermission({{ $permission->id }})" wire:confirm="¿Estás seguro de que quieres eliminar este permiso?" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item">No hay permisos definidos.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- 4. Columna de Usuarios y Asignaciones --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Asignar a Usuario</h5>
                </div>
                <div class="card-body">
                    <input type="text" wire:model.live.debounce.300ms="search_user" class="form-control mb-3" placeholder="Buscar usuario por nombre o email...">
                    
                    <ul class="list-group" style="max-height: 200px; overflow-y: auto;">
                        @forelse($users as $user)
                            <li 
                                class="list-group-item user-list-item {{ $selected_user_id == $user->id ? 'selected' : '' }}" 
                                wire:click="loadUserRolesAndPermissions({{ $user->id }})"
                                style="cursor: pointer;"
                            >
                                {{ $user->name }} ({{ $user->email }})
                            </li>
                        @empty
                            <li class="list-group-item">No se encontraron usuarios.</li>
                        @endforelse
                    </ul>

                    @if($selected_user_id)
                        <hr>
                        <h6>Asignar Roles a: <strong>{{ $users->find($selected_user_id)->name }}</strong></h6>
                        <div class="mb-3" style="max-height: 150px; overflow-y: auto;">
                            @foreach($roles as $role)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $role->id }}" id="role_{{ $role->id }}" wire:model="user_selected_roles">
                                    <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->name }}</label>
                                </div>
                            @endforeach
                        </div>
                        <button wire:click="syncUserRoles" class="btn btn-success btn-sm">Sincronizar Roles</button>
                        
                        <hr>
                        <h6>Asignar Permisos Directos</h6>
                        <div class="mb-3" style="max-height: 150px; overflow-y: auto;">
                             @foreach($permissions as $permission)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" id="perm_{{ $permission->id }}" wire:model="user_selected_permissions">
                                    <label class="form-check-label" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                                </div>
                            @endforeach
                        </div>
                        <button wire:click="syncUserPermissions" class="btn btn-success btn-sm">Sincronizar Permisos</button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 5. Modal para Crear/Editar Roles y Permisos --}}
    <div class="modal {{ $showModal ? 'd-block' : '' }}" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    @if($modal_mode === 'role')
                        <h5 class="modal-title">{{ $role_id ? 'Editar' : 'Crear' }} Rol</h5>
                    @else
                        <h5 class="modal-title">{{ $permission_id ? 'Editar' : 'Crear' }} Permiso</h5>
                    @endif
                    <button type="button" wire:click="closeModal" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    @if($modal_mode === 'role')
                        <form wire:submit.prevent="{{ $role_id ? 'updateRole' : 'createRole' }}">
                            <div class="mb-3">
                                <label for="role_name" class="form-label">Nombre del Rol</label>
                                <input type="text" id="role_name" wire:model.defer="role_name" class="form-control @error('role_name') is-invalid @enderror">
                                @error('role_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    @else
                        <form wire:submit.prevent="{{ $permission_id ? 'updatePermission' : 'createPermission' }}">
                            <div class="mb-3">
                                <label for="permission_name" class="form-label">Nombre del Permiso</label>
                                <input type="text" id="permission_name" wire:model.defer="permission_name" class="form-control @error('permission_name') is-invalid @enderror">
                                @error('permission_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" wire:click="closeModal" class="btn btn-secondary">Cancelar</button>
                    @if($modal_mode === 'role')
                        <button type="button" wire:click="{{ $role_id ? 'updateRole' : 'createRole' }}" class="btn btn-primary">Guardar Rol</button>
                    @else
                        <button type="button" wire:click="{{ $permission_id ? 'updatePermission' : 'createPermission' }}" class="btn btn-primary">Guardar Permiso</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>