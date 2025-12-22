<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'agency' => ['required', 'string', 'max:10'],
            'account_number' => ['required', 'string', 'max:20'],
            'account_digit' => ['nullable', 'string', 'max:2'],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'account_holder_document' => ['required', 'string', 'max:20'],
            'pix_key_type' => ['nullable', 'string', Rule::in(['cpf', 'cnpj', 'email', 'phone', 'aleatoria'])],
            'pix_key' => ['nullable', 'string'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
