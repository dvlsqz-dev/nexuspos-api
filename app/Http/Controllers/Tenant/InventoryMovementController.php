<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryMovementRequest;
use App\Models\Branch;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class InventoryMovementController extends Controller
{
    public function __construct(
        private readonly InventoryService $inventoryService
    ) {}

    public function index(Product $product)
    {
        return response()->json(
            $product->inventoryMovements()->with(['branch', 'user'])->latest('created_at')->paginate(20)
        );
    }

    public function store(InventoryMovementRequest $request, Product $product)
    {
        $validated = $request->validated();

        $branch = Branch::findOrFail($validated['branch_id']);

        $sign = match (true) {
            $validated['type'] === 'entrada' => 1,
            $validated['type'] === 'salida' => -1,
            $validated['type'] === 'ajuste' && $validated['direction'] === 'incrementar' => 1,
            $validated['type'] === 'ajuste' && $validated['direction'] === 'reducir' => -1,
        };

        $movement = $this->inventoryService->registerMovement(
            product: $product,
            branch: $branch,
            type: $validated['type'],
            quantity: $validated['quantity'] * $sign,
            user: $request->user(),
            reason: $validated['reason'] ?? null,
        );

        return response()->json($movement->load(['branch', 'user']), 201);
    }
}
