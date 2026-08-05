<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Models\Tenant;

class TenantActivationService
{
    public function activate(Tenant $tenant): void
    {
        $tenant->update(['status' => 'activo']);

        if ($tenant->branches()->count() === 0) {
            $tenant->branches()->create([
                'name' => 'Sucursal Principal',
                'address' => $tenant->address,
                'phone' => $tenant->phone,
                'is_main' => true,
            ]);
        }

        if ($tenant->paymentMethods()->count() === 0) {
            $tenant->paymentMethods()->createMany([
                ['name' => 'Efectivo'],
                ['name' => 'Tarjeta'],
            ]);
        }
    }
}