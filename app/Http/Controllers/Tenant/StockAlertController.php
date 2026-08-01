<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Stock;

class StockAlertController extends Controller
{
    public function index()
    {
        $lowStock = Stock::whereColumn('quantity', '<=', 'min_stock')
            ->where('min_stock', '>', 0)
            ->whereHas('product')
            ->with(['product', 'branch'])
            ->get();

        return response()->json($lowStock);
    }
}
