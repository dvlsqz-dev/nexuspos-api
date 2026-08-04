<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OpenCashSessionRequest extends FormRequest
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
        return [
            'cash_register_id' => [
                'required',
                Rule::exists('cash_registers', 'id')->where(function ($query) {
                    $query->whereIn('branch_id', $this->user()->tenant->branches->pluck('id'));
                }),
            ],
            'opening_amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
