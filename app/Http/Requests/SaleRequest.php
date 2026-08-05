<?php

namespace App\Http\Requests;

use App\Models\Stock;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('tenant_id', $tenantId)],
            'customer_id' => ['nullable', Rule::exists('customers', 'id')->where('tenant_id', $tenantId)],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', Rule::exists('products', 'id')->where('tenant_id', $tenantId)],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_method_id' => ['required', Rule::exists('payment_methods', 'id')->where('tenant_id', $tenantId)],
            'payments.*.amount' => ['required', 'numeric', 'min:0.01'],
            'payments.*.amount_tendered' => ['nullable', 'numeric', 'min:0'],
            'payments.*.reference' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateStockAvailability($validator);
        });
    }

    private function validateStockAvailability(Validator $validator): void
    {
        $branchId = $this->input('branch_id');
        $items = $this->input('items', []);

        foreach ($items as $index => $item) {
            $product = \App\Models\Product::find($item['product_id'] ?? null);

            if (! $product || ! $product->track_inventory) {
                continue;
            }

            $stock = Stock::where('product_id', $product->id)
                ->where('branch_id', $branchId)
                ->first();

            $available = $stock->quantity ?? 0;

            if ($available < $item['quantity']) {
                $validator->errors()->add(
                    "items.{$index}.quantity",
                    "Stock insuficiente para \"{$product->name}\". Disponible: {$available}."
                );
            }
        }
    }
}
