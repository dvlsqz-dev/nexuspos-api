<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DiscountRequest extends FormRequest
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
        $scope = $this->input('scope');

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'scope' => ['required', Rule::in(['all', 'category', 'product'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ];

        $rules['scope_id'] = match ($scope) {
            'category' => ['required', Rule::exists('categories', 'id')->where('tenant_id', $tenantId)],
            'product' => ['required', Rule::exists('products', 'id')->where('tenant_id', $tenantId)],
            default => ['prohibited'],
        };

        return $rules;
    }
}
