<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Catálogo
            'products.view', 'products.create', 'products.update', 'products.delete',
            'categories.manage', 'units.manage',

            // Inventario
            'inventory.view', 'inventory.adjust', 'batches.manage',

            // Descuentos
            'discounts.view', 'discounts.manage',

            // Clientes
            'customers.view', 'customers.manage',

            // Punto de venta
            'sales.create', 'sales.view', 'sales.void',

            // Caja
            'cash.open', 'cash.close', 'cash.movements',

            // Reportes
            'reports.view',

            // Configuración del negocio
            'tenant.config', 'branches.manage', 'payment_methods.manage',

            // Usuarios del negocio
            'users.view', 'users.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
