<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // Desactivar restricciones de clave foránea temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Limpiar tabla
        Company::truncate();

        // Crear empresa principal
        $company = Company::create([
            'name' => 'Ferretería El Constructor C.A.',
            'rif' => 'J-30123456-7',
            'address' => 'Av. Principal #123, Centro, Caracas 1010',
            'phone' => '0212-5551234',
            'email' => 'contacto@ferreteriaelconstructor.com',
            'tax_rate' => 16.00,
            'logo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear tasas de cambio iniciales
        DB::table('exchange_rates')->insert([
            [
                'company_id' => $company->id,
                'rate' => 35.7500,
                'source' => 'BCV',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'company_id' => $company->id,
                'rate' => 36.2500,
                'source' => 'PARALELO',
                'is_active' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Crear categorías iniciales
        $categories = [
            ['name' => 'Herramientas Manuales', 'description' => 'Martillos, destornilladores, alicates, etc.'],
            ['name' => 'Herramientas Eléctricas', 'description' => 'Taladros, sierras, pulidoras, etc.'],
            ['name' => 'Materiales de Construcción', 'description' => 'Cemento, varillas, bloques, etc.'],
            ['name' => 'Fontanería', 'description' => 'Tuberías, conexiones, grifería, etc.'],
            ['name' => 'Electricidad', 'description' => 'Cables, interruptores, tomacorrientes, etc.'],
            ['name' => 'Pinturas y Accesorios', 'description' => 'Pinturas, brochas, rodillos, etc.'],
            ['name' => 'Ferretería en General', 'description' => 'Tornillería, cerraduras, bisagras, etc.'],
            ['name' => 'Jardinería', 'description' => 'Herramientas para jardín, mangueras, etc.'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'company_id' => $company->id,
                'name' => $category['name'],
                'description' => $category['description'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear unidades de medida
        $unitMeasures = [
            ['name' => 'Unidad', 'symbol' => 'UN'],
            ['name' => 'Pulgada', 'symbol' => '"'],
            ['name' => 'Metro', 'symbol' => 'm'],
            ['name' => 'Centímetro', 'symbol' => 'cm'],
            ['name' => 'Milímetro', 'symbol' => 'mm'],
            ['name' => 'Kilogramo', 'symbol' => 'kg'],
            ['name' => 'Gramo', 'symbol' => 'g'],
            ['name' => 'Litro', 'symbol' => 'L'],
            ['name' => 'Mililitro', 'symbol' => 'ml'],
            ['name' => 'Pulgada cúbica', 'symbol' => 'in³'],
        ];

        foreach ($unitMeasures as $unit) {
            DB::table('unit_measures')->insert([                
                'name' => $unit['name'],
                'symbol' => $unit['symbol'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear roles del sistema
        $roles = [
            ['name' => 'Administrador', 'slug' => 'administrator', 'is_system' => true],
            ['name' => 'Gerente', 'slug' => 'manager', 'is_system' => true],
            ['name' => 'Cajero', 'slug' => 'cashier', 'is_system' => true],
            ['name' => 'Vendedor', 'slug' => 'seller', 'is_system' => true],
            ['name' => 'Almacenista', 'slug' => 'warehouse', 'is_system' => true],
            ['name' => 'Contador', 'slug' => 'accountant', 'is_system' => true],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'name' => $role['name'],
                'slug' => $role['slug'],
                'description' => null,
                'is_system' => $role['is_system'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear permisos básicos
        $permissions = [
            // Ventas
            ['name' => 'Crear ventas', 'slug' => 'sales.create'],
            ['name' => 'Ver ventas', 'slug' => 'sales.view'],
            ['name' => 'Editar ventas', 'slug' => 'sales.edit'],
            ['name' => 'Eliminar ventas', 'slug' => 'sales.delete'],
            ['name' => 'Imprimir tickets', 'slug' => 'sales.print'],
            
            // Productos
            ['name' => 'Crear productos', 'slug' => 'products.create'],
            ['name' => 'Ver productos', 'slug' => 'products.view'],
            ['name' => 'Editar productos', 'slug' => 'products.edit'],
            ['name' => 'Eliminar productos', 'slug' => 'products.delete'],
            
            // Compras
            ['name' => 'Crear compras', 'slug' => 'purchases.create'],
            ['name' => 'Ver compras', 'slug' => 'purchases.view'],
            ['name' => 'Editar compras', 'slug' => 'purchases.edit'],
            
            // Inventario
            ['name' => 'Ver inventario', 'slug' => 'inventory.view'],
            ['name' => 'Ajustar inventario', 'slug' => 'inventory.adjust'],
            ['name' => 'Ver movimientos', 'slug' => 'inventory.movements'],
            
            // Clientes
            ['name' => 'Crear clientes', 'slug' => 'customers.create'],
            ['name' => 'Ver clientes', 'slug' => 'customers.view'],
            ['name' => 'Editar clientes', 'slug' => 'customers.edit'],
            
            // Proveedores
            ['name' => 'Crear proveedores', 'slug' => 'suppliers.create'],
            ['name' => 'Ver proveedores', 'slug' => 'suppliers.view'],
            
            // Reportes
            ['name' => 'Ver reportes de ventas', 'slug' => 'reports.sales'],
            ['name' => 'Ver reportes de compras', 'slug' => 'reports.purchases'],
            ['name' => 'Ver reportes de inventario', 'slug' => 'reports.inventory'],
            ['name' => 'Ver reportes de productos', 'slug' => 'reports.products'],
            
            // Configuración
            ['name' => 'Gestionar usuarios', 'slug' => 'users.manage'],
            ['name' => 'Gestionar roles', 'slug' => 'roles.manage'],
            ['name' => 'Configurar empresa', 'slug' => 'company.configure'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'name' => $permission['name'],
                'slug' => $permission['slug'],
                'description' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Asignar permisos a roles (ejemplo básico)
        $adminPermissions = DB::table('permissions')->pluck('id')->toArray();
        $adminRoleId = DB::table('roles')->where('slug', 'administrator')->first()->id;
        
        foreach ($adminPermissions as $permissionId) {
            DB::table('permission_role')->insert([
                'permission_id' => $permissionId,
                'role_id' => $adminRoleId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear usuario administrador
        $adminId = DB::table('users')->insertGetId([
            'company_id' => $company->id,
            'name' => 'Administrador Principal',
            'email' => 'admin@ferreteria.com',
            'password' => bcrypt('admin123'),
            'phone' => '0414-5551234',
            'address' => 'Dirección del administrador',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Asignar rol de administrador
        DB::table('role_user')->insert([
            'role_id' => $adminRoleId,
            'user_id' => $adminId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear algunos clientes de ejemplo
        $customers = [
            [
                'document_type' => 'V',
                'document' => '12345678',
                'name' => 'Juan Pérez',
                'phone' => '0412-1234567',
                'email' => 'juan@example.com',
                'address' => 'Av. Libertador #456, Caracas',
                'credit_limit' => 5000.00,
                'pending_balance' => 0.00,
            ],
            [
                'document_type' => 'J',
                'document' => 'J-30123456-8',
                'name' => 'Constructora Santa María C.A.',
                'phone' => '0212-9876543',
                'email' => 'contacto@constructora.com',
                'address' => 'Zona Industrial, Valencia',
                'credit_limit' => 25000.00,
                'pending_balance' => 0.00,
            ],
            [
                'document_type' => 'V',
                'document' => '87654321',
                'name' => 'María García',
                'phone' => '0416-7654321',
                'email' => 'maria@example.com',
                'address' => 'Calle 10 #23-45, Maracay',
                'credit_limit' => 2000.00,
                'pending_balance' => 0.00,
            ],
        ];

        foreach ($customers as $customer) {
            DB::table('customers')->insert([
                'company_id' => $company->id,
                'document_type' => $customer['document_type'],
                'document' => $customer['document'],
                'name' => $customer['name'],
                'phone' => $customer['phone'],
                'email' => $customer['email'],
                'address' => $customer['address'],
                'credit_limit' => $customer['credit_limit'],
                'pending_balance' => $customer['pending_balance'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Crear algunos proveedores de ejemplo
        $suppliers = [
            [
                'rif' => 'J-30234567-1',
                'name' => 'Distribuidora de Materiales C.A.',
                'phone' => '0212-2345678',
                'email' => 'ventas@distribuidora.com',
                'address' => 'Av. Industrial, La Victoria',
                'contact_person' => 'Carlos Rodríguez',
            ],
            [
                'rif' => 'J-30345678-2',
                'name' => 'Herramientas Profesionales S.A.',
                'phone' => '0212-3456789',
                'email' => 'info@herramientaspro.com',
                'address' => 'Centro Comercial Plaza, Caracas',
                'contact_person' => 'Ana Martínez',
            ],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('suppliers')->insert([
                'company_id' => $company->id,
                'rif' => $supplier['rif'],
                'name' => $supplier['name'],
                'phone' => $supplier['phone'],
                'email' => $supplier['email'],
                'address' => $supplier['address'],
                'contact_person' => $supplier['contact_person'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Reactivar restricciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('✅ Seeder de compañía ejecutado exitosamente.');
        $this->command->info('📧 Usuario administrador: admin@ferreteria.com');
        $this->command->info('🔑 Contraseña: admin123');
    }
}
