<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class SaleNumberGenerator
{
    public function generateFor(Tenant $tenant): string
    {
        return DB::transaction(function () use ($tenant) {
            // Bloqueamos la fila del tenant (que siempre existe) para
            // serializar la generación de números entre ventas concurrentes.
            Tenant::where('id', $tenant->id)->lockForUpdate()->first();

            $lastNumber = Sale::where('tenant_id', $tenant->id)->max('sale_number');
            $next = $lastNumber ? ((int) $lastNumber) + 1 : 1;

            return str_pad($next, 6, '0', STR_PAD_LEFT);
        });
    }
}