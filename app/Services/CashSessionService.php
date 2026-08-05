<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\SalePayment;

class CashSessionService
{
    public function calculateExpectedAmount(CashSession $session): float
    {
        $movementsTotal = $session->movements()
            ->selectRaw("SUM(CASE WHEN type = 'ingreso' THEN amount ELSE -amount END) as total")
            ->value('total') ?? 0;

        $cashSalesTotal = SalePayment::whereHas('sale', function ($query) use ($session) {
                $query->where('cash_session_id', $session->id);
            })
            ->whereHas('paymentMethod', function ($query) {
                $query->where('name', 'Efectivo');
            })
            ->sum('amount');

        return (float) $session->opening_amount + (float) $movementsTotal + (float) $cashSalesTotal;
    }
}