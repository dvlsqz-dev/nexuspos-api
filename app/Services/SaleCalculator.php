<?php

namespace App\Services;

use App\Models\Tenant;

class SaleCalculator
{
    public function __construct(private readonly DiscountResolver $discountResolver) {
        
    }

    public function calculate(array $items, Tenant $tenant): array
    {
        $pricesIncludeTax = (bool) $tenant->prices_include_tax;

        $lines = [];
        $subtotal = 0;
        $tax = 0;
        $discountTotal = 0;

        foreach ($items as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];
            $unitPrice = (float) $product->price;
            $taxRate = $product->effectiveTaxRate();

            $discountPercentage = $item['discount_percentage']
                ?? optional($this->discountResolver->resolveFor($product))->percentage
                ?? 0;

            $grossLine = $unitPrice * $quantity;
            $discountAmount = round($grossLine * ($discountPercentage / 100), 2);
            $netLine = $grossLine - $discountAmount;

            if ($pricesIncludeTax) {
                $lineSubtotal = round($netLine / (1 + $taxRate / 100), 2);
                $lineTax = round($netLine - $lineSubtotal, 2);
            } else {
                $lineSubtotal = round($netLine, 2);
                $lineTax = round($netLine * ($taxRate / 100), 2);
            }

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_percentage' => $discountPercentage,
                'subtotal' => $lineSubtotal,
            ];

            $subtotal += $lineSubtotal;
            $tax += $lineTax;
            $discountTotal += $discountAmount;
        }

        return [
            'lines' => $lines,
            'subtotal' => round($subtotal, 2),
            'tax' => round($tax, 2),
            'discount' => round($discountTotal, 2),
            'total' => round($subtotal + $tax, 2),
        ];
    }
}