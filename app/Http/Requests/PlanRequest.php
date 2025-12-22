<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Altere para a sua lógica de autorização
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'recurrence' => ['required', 'string', Rule::in(['monthly', 'annually'])],
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'client_type' => ['required', 'string', Rule::in(['pf', 'pj', 'both'])], // Adicione a validação aqui
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome do plano é obrigatório.',
            'recurrence.required' => 'A recorrência do plano é obrigatória.',
            'client_type.required' => 'O tipo de cliente é obrigatório.',
            'client_type.in' => 'O tipo de cliente selecionado é inválido.',
            'product_ids.required' => 'O plano deve ter pelo menos um produto.',
            'product_ids.*.exists' => 'Um dos produtos selecionados não é válido.',
        ];
    }
}
