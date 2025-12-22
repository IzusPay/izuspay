<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adapte a lógica de autorização se necessário
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1'],
            'bank_account_id' => ['nullable', 'exists:bank_accounts,id'],
            'pix_key' => ['required_without:bank_account_id', 'string', 'max:255'],
            'pix_key_type' => ['required_without:bank_account_id', 'string', 'in:cpf,cnpj,email,phone,random'],
            'method' => ['required', 'in:pix'],
        ];
    }
}
