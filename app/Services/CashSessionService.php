<?php

namespace App\Services;

use App\Models\CashSession;

class CashSessionService
{
    public function calculateExpectedAmount(CashSession $session): float
    {
        $movementsTotal = $session->movements()
            ->selectRaw("SUM(CASE WHEN type = 'ingreso' THEN amount ELSE -amount END) as total")
            ->value('total') ?? 0;

        // NOTA: cuando construyamos feature/punto-de-venta, aquí se sumarán
        // también los sale_payments en efectivo de las ventas registradas
        // durante esta sesión. Por ahora, el cálculo solo contempla
        // movimientos manuales de caja.

        return (float) $session->opening_amount + (float) $movementsTotal;
    }
}