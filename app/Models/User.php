<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'password',
        'phone',
        'address',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function salesAsSeller(): HasMany
    {
        return $this->hasMany(Sale::class, 'seller_id');
    }

    public function salesAsCashier(): HasMany
    {
        return $this->hasMany(Sale::class, 'cashier_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'user_id');
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'user_id');
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'user_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    // Métodos personalizados para Roles y Permisos (sin Spatie)

    /**
     * Sincroniza los roles del usuario.
     * @param array $roleIds
     */
    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
    }

    /**
     * Sincroniza los permisos directos del usuario.
     * @param array $permissionIds
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    /**
     * Asigna un rol al usuario.
     * @param Role $role
     */
    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching($role->id);
    }

    /**
     * Verifica si el usuario tiene un permiso específico, ya sea directamente o a través de sus roles.
     * @param string $permissionName
     * @return bool
     */
    public function hasPermissionTo(string $permissionName): bool
    {
        // 1. Verificar permisos directos
        if ($this->permissions()->where('name', $permissionName)->exists()) {
            return true;
        }

        // 2. Verificar permisos a través de los roles
        // Carga los roles con sus permisos anidados
        $roles = $this->roles()->with('permissions')->get();

        foreach ($roles as $role) {
            if ($role->permissions()->where('name', $permissionName)->exists()) {
                return true;
            }
        }

        return false;
    }
}
