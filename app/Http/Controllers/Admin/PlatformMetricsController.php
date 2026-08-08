<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Tenant;

class PlatformMetricsController extends Controller
{
    public function index()
    {
        $tenantsByStatus = Tenant::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $totalSales = Sale::where('status', 'completada')->sum('total');
        $totalSalesCount = Sale::where('status', 'completada')->count();

        $salesLast30Days = Sale::where('status', 'completada')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('total');

        return response()->json([
            'tenants' => [
                'total' => Tenant::count(),
                'pendiente' => $tenantsByStatus->get('pendiente', 0),
                'activo' => $tenantsByStatus->get('activo', 0),
                'suspendido' => $tenantsByStatus->get('suspendido', 0),
                'rechazado' => $tenantsByStatus->get('rechazado', 0),
            ],
            'sales' => [
                'total_all_time' => round($totalSales, 2),
                'total_count' => $totalSalesCount,
                'total_last_30_days' => round($salesLast30Days, 2),
            ],
        ]);
    }
}
