<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function registerMovement(
        Product $product,
        Branch $branch,
        string $type,
        int $quantity,
        User $user,
        ?string $reason = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
    ): InventoryMovement {
        return DB::transaction(function () use ($product, $branch, $type, $quantity, $user, $reason, $referenceType, $referenceId) {
            $stock = Stock::firstOrCreate(
                ['product_id' => $product->id, 'branch_id' => $branch->id],
                ['quantity' => 0, 'min_stock' => 0]
            );

            $stock->increment('quantity', $quantity);

            return InventoryMovement::create([
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'user_id' => $user->id,
                'type' => $type,
                'quantity' => $quantity,
                'reason' => $reason,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'created_at' => now(),
            ]);
        });
    }
}