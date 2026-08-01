<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryMovementRequest extends FormRequest
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
            'type' => ['required', Rule::in(['entrada', 'salida', 'ajuste'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'direction' => ['required_if:type,ajuste', Rule::in(['incrementar', 'reducir'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
