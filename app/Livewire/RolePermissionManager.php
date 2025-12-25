<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RolePermissionManager extends Component
{
    // Propiedades para la gestión de Roles
    public $roles, $role_id, $role_name;

    // Propiedades para la gestión de Permisos
    public $permissions, $permission_id, $permission_name;

    // Propiedades para la asignación a usuarios
    public $users, $selected_user_id;
    public $user_selected_roles = [];
    public $user_selected_permissions = [];

    // Propiedades para la UI
    public $search_user = '';
    public $showModal = false;
    public $modal_mode = 'role'; // 'role', 'permission', 'user'

    public function render()
    {
        $this->roles = Role::all();
        $this->permissions = Permission::all();
        
        $this->users = User::where(function($query) {
            $query->where('name', 'like', '%' . $this->search_user . '%')
                  ->orWhere('email', 'like', '%' . $this->search_user . '%');
        })->get();

        return view('livewire.role-permission-manager');
    }

    // Métodos para Roles
    public function createRole()
    {
        $this->authorize('manage_roles');
        $this->validate(['role_name' => 'required|unique:roles,name']);
        Role::create(['name' => $this->role_name]);
        session()->flash('success', 'Rol creado con éxito.');
        $this->reset(['role_name', 'role_id']);
        $this->closeModal();
    }

    public function editRole($id)
    {
        $this->authorize('manage_roles');
        $role = Role::findOrFail($id);
        $this->role_id = $id;
        $this->role_name = $role->name;
        $this->openModal('role');
    }

    public function updateRole()
    {
        $this->authorize('manage_roles');
        $this->validate(['role_name' => 'required|unique:roles,name,' . $this->role_id]);
        $role = Role::findOrFail($this->role_id);
        $role->update(['name' => $this->role_name]);
        session()->flash('success', 'Rol actualizado con éxito.');
        $this->reset(['role_name', 'role_id']);
        $this->closeModal();
    }

    public function deleteRole($id)
    {
        $this->authorize('manage_roles');
        // Opcional: Añadir comprobación si el rol tiene usuarios asignados
        Role::findOrFail($id)->delete();
        session()->flash('success', 'Rol eliminado con éxito.');
    }

    // Métodos para Permisos
    public function createPermission()
    {
        $this->authorize('manage_roles');
        $this->validate(['permission_name' => 'required|unique:permissions,name']);
        Permission::create(['name' => $this->permission_name]);
        session()->flash('success', 'Permiso creado con éxito.');
        $this->reset(['permission_name', 'permission_id']);
        $this->closeModal();
    }

    public function editPermission($id)
    {
        $this->authorize('manage_roles');
        $permission = Permission::findOrFail($id);
        $this->permission_id = $id;
        $this->permission_name = $permission->name;
        $this->openModal('permission');
    }

    public function updatePermission()
    {
        $this->authorize('manage_roles');
        $this->validate(['permission_name' => 'required|unique:permissions,name,' . $this->permission_id]);
        $permission = Permission::findOrFail($this->permission_id);
        $permission->update(['name' => $this->permission_name]);
        session()->flash('success', 'Permiso actualizado con éxito.');
        $this->reset(['permission_name', 'permission_id']);
        $this->closeModal();
    }

    public function deletePermission($id)
    {
        $this->authorize('manage_roles');
        Permission::findOrFail($id)->delete();
        session()->flash('success', 'Permiso eliminado con éxito.');
    }

    // Métodos de Asignación
    public function loadUserRolesAndPermissions($userId)
    {
        $this->authorize('manage_roles');
        $this->selected_user_id = $userId;
        $user = User::findOrFail($userId);
        $this->user_selected_roles = $user->roles->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->user_selected_permissions = $user->permissions->pluck('id')->map(fn ($id) => (string) $id)->toArray();
    }

    public function syncUserRoles()
    {
        $this->authorize('manage_roles');
        if ($this->selected_user_id) {
            $user = User::findOrFail($this->selected_user_id);
            $user->syncRoles($this->user_selected_roles);
            session()->flash('success', 'Roles del usuario actualizados con éxito.');
        }
    }

    public function syncUserPermissions()
    {
        $this->authorize('manage_roles');
        if ($this->selected_user_id) {
            $user = User::findOrFail($this->selected_user_id);
            $user->syncPermissions($this->user_selected_permissions);
            session()->flash('success', 'Permisos directos del usuario actualizados con éxito.');
        }
    }


    // Métodos de ayuda para el Modal
    public function openModal($mode)
    {
        $this->authorize('manage_roles');
        $this->resetValidation();
        $this->reset(['role_name', 'role_id', 'permission_name', 'permission_id']);
        $this->modal_mode = $mode;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->reset(['role_name', 'role_id', 'permission_name', 'permission_id']);
    }
}
