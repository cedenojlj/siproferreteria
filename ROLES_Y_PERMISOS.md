# Plan de Desarrollo: Componente Livewire para Gestión de Roles y Permisos

## Fase 1: Análisis y Preparación

1.  **Análisis de la Estructura Existente:**
    *   **Modelos:** Se ha verificado la existencia de los modelos `User.php`, `Role.php`, y `Permission.php`. El siguiente paso será asegurar que las relaciones Eloquent (`belongsToMany`) estén correctamente definidas.
    *   **Migraciones:** Se confirman las migraciones para `roles`, `permissions` y las tablas pivote (`role_user`, `permission_user`, `permission_role`), validando la estructura de la base de datos.
    *   **Convenciones:** El nuevo componente seguirá la arquitectura de los componentes CRUD de Livewire ya existentes en el proyecto.

2.  **Creación del Componente Livewire:**
    *   Se ejecutará el comando: `php artisan make:livewire RolePermissionManager`.
    *   Esto generará `app/Livewire/RolePermissionManager.php` y `resources/views/livewire/role-permission-manager.blade.php`.

## Fase 2: Desarrollo del Backend (Clase `RolePermissionManager.php`)

1.  **Definición de Propiedades Públicas:**
    *   Se definirán propiedades para manejar el estado de los roles, permisos, usuarios y la interfaz de usuario (modales, búsquedas).

2.  **Implementación del Método `render()`:**
    *   Obtendrá todos los roles, permisos y usuarios (con capacidad de búsqueda) para pasarlos a la vista.

3.  **Lógica para CRUD de Roles:**
    *   Implementación de los métodos `createRole()`, `editRole($id)`, `updateRole()`, y `deleteRole($id)`.

4.  **Lógica para CRUD de Permisos:**
    *   Implementación de métodos análogos a los de Roles: `createPermission()`, `editPermission($id)`, `updatePermission()`, y `deletePermission($id)`.

5.  **Lógica para Asignación:**
    *   **Mejor Práctica:** Se priorizará la asignación de Permisos a Roles, y Roles a Usuarios.
    *   `loadUserRolesAndPermissions($userId)`: Cargará la configuración actual de un usuario seleccionado.
    *   `syncUserRoles()`: Sincronizará los roles de un usuario.
    *   `syncUserPermissions()`: Sincronizará los permisos directos de un usuario.
    *   `syncRolePermissions()`: Sincronizará los permisos de un rol.

## Fase 3: Diseño de la Interfaz de Usuario (Vista `role-permission-manager.blade.php`)

1.  **Estructura de la Vista:**
    *   Se usará un diseño de 3 columnas:
        *   **Columna 1: Gestión de Roles** (Lista, formularios, botones de acción).
        *   **Columna 2: Gestión de Permisos** (Lista, formularios, botones de acción).
        *   **Columna 3: Asignación a Usuarios** (Buscador de usuarios, selectores de roles/permisos).

2.  **Uso de Modales:**
    *   Los formularios de creación/edición y otras acciones complejas se manejarán dentro de modales para una mejor experiencia de usuario.

## Fase 4: Integración y Seguridad

1.  **Creación de la Ruta:**
    *   En `routes/web.php`, se añadirá una ruta protegida con middleware, por ejemplo: `Route::get('/management/roles-permissions', RolePermissionManager::class)->middleware(['auth', 'can:manage_roles'])->name('roles.permissions.manager');`

2.  **Enlace en la Navegación:**
    *   Se añadirá un enlace en el menú lateral (`sidebar.blade.php`), visible solo para usuarios con el permiso adecuado (`@can('manage_roles')`).

3.  **Autorización en el Componente:**
    *   Se usará `$this->authorize('permission_name')` en los métodos del componente como una segunda capa de seguridad.

## Fase 5: Refinamiento

1.  **Notificaciones:** Uso de `session()->flash()` para feedback al usuario.
2.  **Validación en Tiempo Real:** Aprovechar la validación de Livewire en los formularios.
3.  **Confirmaciones:** Implementar diálogos de confirmación para acciones destructivas como eliminar.
