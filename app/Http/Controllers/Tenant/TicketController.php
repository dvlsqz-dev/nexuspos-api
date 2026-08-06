<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;

class TicketController extends Controller
{
    public function halfLetter(Sale $sale)
    {
        $this->authorizeSale($sale);

        $sale->load(['items.product', 'payments.paymentMethod', 'customer', 'branch.tenant']);

        $pdf = Pdf::loadView('tickets.half-letter', ['sale' => $sale])
            ->setPaper('letter', 'portrait');

        return $pdf->stream("venta-{$sale->sale_number}.pdf");
    }

    public function thermal(Sale $sale)
    {
        $this->authorizeSale($sale);

        $sale->load(['items.product', 'payments.paymentMethod', 'customer', 'branch.tenant']);

        $paperWidth = $sale->branch->tenant->ticket_paper_width;
        $widthPoints = $paperWidth === '58mm' ? 164 : 227; // mm a puntos (1mm = 2.8346pt aprox)

        $pdf = Pdf::loadView('tickets.thermal', ['sale' => $sale, 'paperWidth' => $paperWidth])
            ->setPaper([0, 0, $widthPoints, 1000], 'portrait');

        return $pdf->stream("venta-{$sale->sale_number}-termico.pdf");
    }

    private function authorizeSale(Sale $sale): void
    {
        abort_if($sale->tenant_id !== request()->user()->tenant_id, 404);
    }
}
