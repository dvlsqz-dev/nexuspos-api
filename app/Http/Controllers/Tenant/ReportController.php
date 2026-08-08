<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    private function dateRange(Request $request): array
    {
        $from = $request->query('date_from')
            ? Carbon::parse($request->query('date_from'))->startOfDay()
            : now()->startOfDay();

        $to = $request->query('date_to')
            ? Carbon::parse($request->query('date_to'))->endOfDay()
            : now()->endOfDay();

        return [$from, $to];
    }

    public function salesSummary(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $query = Sale::where('tenant_id', $request->user()->tenant_id)
            ->where('status', 'completada')
            ->whereBetween('created_at', [$from, $to]);

        if ($branchId = $request->query('branch_id')) {
            $query->where('branch_id', $branchId);
        }

        $totalSales = $query->sum('total');
        $salesCount = $query->count();

        return response()->json([
            'date_from' => $from->toDateString(),
            'date_to' => $to->toDateString(),
            'total_sales' => round($totalSales, 2),
            'sales_count' => $salesCount,
            'average_ticket' => $salesCount > 0 ? round($totalSales / $salesCount, 2) : 0,
        ]);
    }

    public function topProducts(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $limit = (int) $request->query('limit', 10);
        $tenantId = $request->user()->tenant_id;

        $topProducts = SaleItem::selectRaw('product_id, SUM(quantity) as total_quantity, SUM(subtotal) as total_revenue')
            ->whereHas('sale', function ($query) use ($from, $to, $request, $tenantId) {
                $query->where('tenant_id', $tenantId)
                    ->where('status', 'completada')
                    ->whereBetween('created_at', [$from, $to]);

                if ($branchId = $request->query('branch_id')) {
                    $query->where('branch_id', $branchId);
                }
            })
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->with('product:id,name,sku')
            ->get();

        return response()->json($topProducts);
    }

    public function paymentMethodsBreakdown(Request $request)
    {
        [$from, $to] = $this->dateRange($request);
        $tenantId = $request->user()->tenant_id;

        $breakdown = SalePayment::selectRaw('payment_method_id, SUM(amount) as total')
            ->whereHas('sale', function ($query) use ($from, $to, $request, $tenantId) {
                $query->where('tenant_id', $tenantId)
                    ->where('status', 'completada')
                    ->whereBetween('created_at', [$from, $to]);

                if ($branchId = $request->query('branch_id')) {
                    $query->where('branch_id', $branchId);
                }
            })
            ->groupBy('payment_method_id')
            ->with('paymentMethod:id,name')
            ->get();

        return response()->json($breakdown);
    }

    public function salesByDay(Request $request)
    {
        [$from, $to] = $this->dateRange($request);

        $sales = Sale::selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('status', 'completada')
            ->whereBetween('created_at', [$from, $to])
            ->when($request->query('branch_id'), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($sales);
    }
}
