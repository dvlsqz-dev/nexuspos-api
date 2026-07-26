<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterTenantRequest extends FormRequest
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
            // Datos del negocio
            'business_name' => ['required', 'string', 'max:255'],
            'nit' => ['required', 'string', 'max:20'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'business_phone' => ['required', 'string', 'max:20'],
            'tax_regime' => ['required', 'in:pequeno_contribuyente,general'],

            // Datos del representante/dueño
            'user_name' => ['required', 'string', 'max:255'],
            'dpi' => ['required', 'string', 'size:13'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'user_phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Documentos
            'dpi_frontal' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'dpi_trasero' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'rtu' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'comprobante_domicilio' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}
