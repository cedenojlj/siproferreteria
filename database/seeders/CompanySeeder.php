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
                'rate' => 344.5071,
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
            ['name' => 'Crear_ventas', 'slug' => 'sales.create'],
            ['name' => 'Ver_ventas', 'slug' => 'sales.view'],
            ['name' => 'Editar_ventas', 'slug' => 'sales.edit'],
            ['name' => 'Eliminar_ventas', 'slug' => 'sales.delete'],
            ['name' => 'Imprimir_tickets', 'slug' => 'sales.print'],
            ['name' => 'POS', 'slug' => 'sales.pos'],
            ['name' => 'Caja', 'slug' => 'sales.caja'],
            
            // Productos
            ['name' => 'Crear_productos', 'slug' => 'products.create'],
            ['name' => 'Ver_productos', 'slug' => 'products.view'],
            ['name' => 'Editar_productos', 'slug' => 'products.edit'],
            ['name' => 'Eliminar_productos', 'slug' => 'products.delete'],

            // Compras
            ['name' => 'Crear_compras', 'slug' => 'purchases.create'],
            ['name' => 'Ver_compras', 'slug' => 'purchases.view'],
            ['name' => 'Editar_compras', 'slug' => 'purchases.edit'],
            ['name' => 'Eliminar_compras', 'slug' => 'purchases.delete'],
            
            // Inventario
            ['name' => 'Ver_inventario', 'slug' => 'inventory.view'],            
            ['name' => 'Ver_movimientos', 'slug' => 'inventory.movements'],
            
            // Devoluciones
            ['name' => 'Crear_devoluciones', 'slug' => 'inventory.returns.create'],
            ['name' => 'Ver_devoluciones', 'slug' => 'inventory.returns.view'],

            //Pagos
            ['name' => 'Crear_pagos', 'slug' => 'payments.create'],
            ['name' => 'Ver_pagos', 'slug' => 'payments.view'],
            ['name' => 'Editar_pagos', 'slug' => 'payments.edit'],
            ['name' => 'Eliminar_pagos', 'slug' => 'payments.delete'],

            
            // Clientes
            ['name' => 'Crear_clientes', 'slug' => 'customers.create'],
            ['name' => 'Ver_clientes', 'slug' => 'customers.view'],
            ['name' => 'Editar_clientes', 'slug' => 'customers.edit'],
            ['name' => 'Eliminar_clientes', 'slug' => 'customers.delete'],
            
            // Proveedores
            ['name' => 'Crear_proveedores', 'slug' => 'suppliers.create'],
            ['name' => 'Ver_proveedores', 'slug' => 'suppliers.view'],
            ['name' => 'Editar_proveedores', 'slug' => 'suppliers.edit'],
            ['name' => 'Eliminar_proveedores', 'slug' => 'suppliers.delete'],
            
            // Reportes
            ['name' => 'Ver_reportes_de_ventas', 'slug' => 'reports.sales'],
            ['name' => 'Ver_reportes_de_compras', 'slug' => 'reports.purchases'],
            ['name' => 'Ver_reportes_de_inventario', 'slug' => 'reports.inventory'],
            ['name' => 'Ver_reportes_de_productos', 'slug' => 'reports.products'],
            ['name' => 'Ver_reportes_general', 'slug' => 'reports.general'],
            
            // Configuración
            ['name' => 'Gestionar_usuarios', 'slug' => 'users.manage'],
            ['name' => 'Gestionar_roles', 'slug' => 'roles.manage'],
            ['name' => 'Configurar_empresa', 'slug' => 'company.configure'],
            ['name' => 'Configurar_tasas_de_cambio', 'slug' => 'exchange-rates.configure'],
            ['name' => 'Configurar_permisos', 'slug' => 'permissions.configure'],
            ['name' => 'Configurar_categorías', 'slug' => 'categories.configure'],
            ['name' => 'Configurar_unidades_de_medida', 'slug' => 'unit-measures.configure'],            
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

        // lee la migración de products y crea algunos productos de ejemplo minimo 4 
        // toma como modelo los campos de la migración de productos para crear los productos
        $products = [
            [
                'company_id' => $company->id,
                'category_id' => 1,
                'unit_measure_id' => 1,
                'barcode' => '1234567890123',
                'name' => 'Martillo de Acero',
                'description' => 'Martillo resistente para uso general.',
                'brand' => 'FerroTools',
                'model' => 'FT-HM100',
                'base_price' => 15.00,
                'usd_price' => 0.42,
                'cost' => 10.00,
                'min_stock' => 20,
                'current_stock' => 50,
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'category_id' => 1,
                'unit_measure_id' => 1,
                'barcode' => '2345678901234',
                'name' => 'Destornillador Phillips',
                'description' => 'Destornillador de alta calidad para tornillos Phillips.',
                'brand' => 'ToolMaster',
                'model' => 'TM-DS200',
                'base_price' => 8.00,
                'usd_price' => 0.22,
                'cost' => 5.00,
                'min_stock' => 30,
                'current_stock' => 80,
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'category_id' => 2,
                'unit_measure_id' => 1,
                'barcode' => '3456789012345',
                'name' => 'Taladro Eléctrico 500W',
                'description' => 'Taladro potente para trabajos de perforación.',
                'brand' => 'PowerDrill',
                'model' => 'PD-500W',
                'base_price' => 75.00,
                'usd_price' => 2.10,
                'cost' => 50.00,
                'min_stock' => 10,
                'current_stock' => 25,
                'is_active' => true,
            ],
            [
                'company_id' => $company->id,
                'category_id' => 3,
                'unit_measure_id' => 1,
                'barcode' => '4567890123456',
                'name' => 'Cemento Portland 50kg',
                'description' => 'Cemento de alta resistencia para construcción.',
                'brand' => 'ConstruMix',
                'model' => 'CMX-50',
                'base_price' => 12.00,
                'usd_price' => 0.34,
                'cost' => 8.00,
                'min_stock' => 100,
                'current_stock' => 200,
                'is_active' => true,
            ]             
        ];

        foreach ($products as $product) {
            DB::table('products')->insert(array_merge($product, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        

        // Reactivar restricciones de clave foránea
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->info('✅ Seeder de compañía ejecutado exitosamente.');
        $this->command->info('📧 Usuario administrador: admin@ferreteria.com');
        $this->command->info('🔑 Contraseña: admin123');
    }
}
