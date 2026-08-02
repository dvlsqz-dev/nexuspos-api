<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TenantRoleProvisioner
{
    private const ROLE_PERMISSIONS = [
        'admin' => [
            'products.view', 'products.create', 'products.update', 'products.delete',
            'categories.manage', 'units.manage',
            'inventory.view', 'inventory.adjust', 'batches.manage',
            'discounts.view', 'discounts.manage',
            'customers.view', 'customers.manage',
            'sales.create', 'sales.view', 'sales.void',
            'cash.open', 'cash.close', 'cash.movements',
            'reports.view',
            'tenant.config', 'branches.manage', 'payment_methods.manage',
            'users.view', 'users.manage',
        ],
        'gerente' => [
            'products.view', 'products.create', 'products.update', 'products.delete',
            'categories.manage', 'units.manage',
            'inventory.view', 'inventory.adjust', 'batches.manage',
            'discounts.view', 'discounts.manage',
            'customers.view', 'customers.manage',
            'sales.create', 'sales.view', 'sales.void',
            'cash.open', 'cash.close', 'cash.movements',
            'reports.view',
        ],
        'cajero' => [
            'customers.view',
            'sales.create', 'sales.view',
            'cash.open', 'cash.close', 'cash.movements',
        ],
    ];

    public function provisionForTenant(Tenant $tenant): void
    {
        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();

        $registrar->setPermissionsTeamId($tenant->id);

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
                'team_id' => $tenant->id,
            ]);

            $role->syncPermissions($permissions);
        }

        $registrar->setPermissionsTeamId($previousTeamId);
    }

    public function assignOwnerRole(User $user): void
    {
        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();

        $registrar->setPermissionsTeamId($user->tenant_id);
        $user->assignRole('admin');
        $registrar->setPermissionsTeamId($previousTeamId);
    }
}