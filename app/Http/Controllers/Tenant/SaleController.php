<?php

namespace App\Http\Controllers\Tenant;


use App\Http\Controllers\Controller;
use App\Http\Requests\SaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Stock;
use App\Services\CashSessionResolver;
use App\Services\InventoryService;
use App\Services\SaleCalculator;
use App\Services\SaleNumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function __construct(
        private readonly CashSessionResolver $cashSessionResolver,
        private readonly SaleCalculator $saleCalculator,
        private readonly SaleNumberGenerator $saleNumberGenerator,
        private readonly InventoryService $inventoryService,
    ) {}

    public function index()
    {
        return response()->json(
            Sale::with(['customer', 'user'])->latest()->paginate(20)
        );
    }

    public function show(Sale $sale)
    {
        abort_if($sale->tenant_id !== request()->user()->tenant_id, 404);

        return response()->json($sale->load(['items.product', 'payments.paymentMethod', 'customer', 'user', 'branch']));
    }

    public function store(SaleRequest $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;
        $validated = $request->validated();

        $cashSession = $this->cashSessionResolver->requireCurrentFor($user);

        $sale = DB::transaction(function () use ($validated, $user, $tenant, $cashSession) {
            $items = collect($validated['items'])->map(function ($item) {
                $product = Product::findOrFail($item['product_id']);

                return [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'discount_percentage' => $item['discount_percentage'] ?? null,
                ];
            })->all();

            // Bloqueamos las filas de stock involucradas ANTES de calcular,
            // para que ninguna otra venta simultánea pueda consumir el mismo
            // stock entre el chequeo y el descuento real.
            foreach ($items as $item) {
                if ($item['product']->track_inventory) {
                    $stock = Stock::where('product_id', $item['product']->id)
                        ->where('branch_id', $validated['branch_id'])
                        ->lockForUpdate()
                        ->first();

                    $available = $stock->quantity ?? 0;

                    if ($available < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => "Stock insuficiente para \"{$item['product']->name}\" (disponible: {$available}).",
                        ]);
                    }
                }
            }

            $calculation = $this->saleCalculator->calculate($items, $tenant);

            $paymentsTotal = round(collect($validated['payments'])->sum('amount'), 2);

            if ($paymentsTotal !== $calculation['total']) {
                throw ValidationException::withMessages([
                    'payments' => "La suma de los pagos (Q{$paymentsTotal}) no coincide con el total de la venta (Q{$calculation['total']}).",
                ]);
            }

            $saleNumber = $this->saleNumberGenerator->generateFor($tenant);

            $sale = Sale::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $validated['branch_id'],
                'customer_id' => $validated['customer_id'] ?? null,
                'user_id' => $user->id,
                'cash_session_id' => $cashSession->id,
                'sale_number' => $saleNumber,
                'subtotal' => $calculation['subtotal'],
                'tax' => $calculation['tax'],
                'discount' => $calculation['discount'],
                'total' => $calculation['total'],
                'status' => 'completada',
            ]);

            foreach ($calculation['lines'] as $line) {
                $sale->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'discount_percentage' => $line['discount_percentage'],
                    'subtotal' => $line['subtotal'],
                ]);

                if ($line['product']->track_inventory) {
                    $this->inventoryService->registerMovement(
                        product: $line['product'],
                        branch: \App\Models\Branch::find($validated['branch_id']),
                        type: 'salida',
                        quantity: -$line['quantity'],
                        user: $user,
                        reason: "Venta {$saleNumber}",
                        referenceType: Sale::class,
                        referenceId: $sale->id,
                    );
                }
            }

            foreach ($validated['payments'] as $payment) {
                $sale->payments()->create($payment);
            }

            return $sale;
        });

        return response()->json(
            $sale->load(['items.product', 'payments.paymentMethod']),
            201
        );
    }

    public function void(Sale $sale)
    {
        abort_if($sale->tenant_id !== request()->user()->tenant_id, 404);
        abort_if($sale->status !== 'completada', 422, 'Esta venta ya está anulada.');

        DB::transaction(function () use ($sale) {
            foreach ($sale->items()->with('product')->get() as $item) {
                if ($item->product->track_inventory) {
                    $this->inventoryService->registerMovement(
                        product: $item->product,
                        branch: $sale->branch,
                        type: 'entrada',
                        quantity: $item->quantity,
                        user: request()->user(),
                        reason: "Venta {$sale->sale_number} anulada",
                        referenceType: Sale::class,
                        referenceId: $sale->id,
                    );
                }
            }

            $sale->update(['status' => 'anulada']);
        });

        return response()->json($sale->fresh()->load(['items.product', 'payments']));
    }
}
