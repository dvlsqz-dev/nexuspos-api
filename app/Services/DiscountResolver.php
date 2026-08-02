<?php

namespace App\Services;

use App\Models\Discount;
use App\Models\Product;

class DiscountResolver
{
    public function resolveFor(Product $product): ?Discount
    {
        $productDiscount = Discount::currentlyActive()
            ->where('scope', 'product')
            ->where('scope_id', $product->id)
            ->first();

        if ($productDiscount) {
            return $productDiscount;
        }

        if ($product->category_id) {
            $categoryDiscount = Discount::currentlyActive()
                ->where('scope', 'category')
                ->where('scope_id', $product->category_id)
                ->first();

            if ($categoryDiscount) {
                return $categoryDiscount;
            }
        }

        return Discount::currentlyActive()
            ->where('scope', 'all')
            ->first();
    }

    public function effectivePrice(Product $product): array
    {
        $discount = $this->resolveFor($product);
        $originalPrice = (float) $product->price;

        if (! $discount) {
            return [
                'original_price' => $originalPrice,
                'final_price' => $originalPrice,
                'discount_applied' => null,
            ];
        }

        $discountAmount = round($originalPrice * ($discount->percentage / 100), 2);

        return [
            'original_price' => $originalPrice,
            'final_price' => round($originalPrice - $discountAmount, 2),
            'discount_applied' => [
                'id' => $discount->id,
                'name' => $discount->name,
                'percentage' => (float) $discount->percentage,
            ],
        ];
    }
}