<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssociationRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $tipo = $this->input('tipo');

        $baseRules = [
            // Dados básicos
            'tipo' => ['required', 'string', Rule::in(['pf', 'cnpj'])],

            // Dados da associação
            'nome' => ['required', 'string', 'max:255'],
            'documento_associacao' => [
                'required',
                'string',
                $tipo === 'pf' ? 'size:14' : 'size:18', // CPF formatado: 14 chars, CNPJ formatado: 18 chars
                'unique:associations,documento',
            ],
            'email_associacao' => ['required', 'string', 'email', 'max:255', 'unique:associations,email'],
            'telefone_associacao' => ['required', 'string', 'max:20'],

            // Endereço
            'endereco' => ['required', 'string', 'max:255'],
            'numero' => ['required', 'string', 'max:10'],
            'complemento' => ['nullable', 'string', 'max:255'],
            'bairro' => ['required', 'string', 'max:255'],
            'cidade' => ['required', 'string', 'max:255'],
            'estado' => ['required', 'string', 'size:2'],
            'cep' => ['required', 'string', 'size:9'], // CEP formatado: 00000-000

            // Dados opcionais
            'data_fundacao' => ['nullable', 'date'],
            'descricao' => ['nullable', 'string', 'max:1000'],
            'site' => ['nullable', 'url', 'max:255'],

            // Senha
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // Regras específicas para Pessoa Física
        if ($tipo === 'pf') {
            $baseRules = array_merge($baseRules, [
                'nome_responsavel' => ['required', 'string', 'max:255'],
                'documento_responsavel' => [
                    'required',
                    'string',
                    'size:14', // CPF formatado: 000.000.000-00
                    'unique:users,documento',
                ],
                'email_responsavel' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'telefone_responsavel' => ['required', 'string', 'max:20'],
            ]);
        }

        // Regras específicas para Pessoa Jurídica
        if ($tipo === 'cnpj') {
            $baseRules = array_merge($baseRules, [
                'representante_nome' => ['required', 'string', 'max:255'],
                'representante_cpf' => [
                    'required',
                    'string',
                    'size:14', // CPF formatado: 000.000.000-00
                    'unique:associations,representante_cpf',
                ],
                'representante_email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'representante_telefone' => ['required', 'string', 'max:20'],
            ]);
        }

        return $baseRules;
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            // Dados básicos
            'tipo.required' => 'O tipo de associação é obrigatório.',
            'tipo.in' => 'O tipo de associação deve ser Pessoa Física ou Pessoa Jurídica.',

            // Dados da associação
            'nome.required' => 'O nome da associação é obrigatório.',
            'nome.max' => 'O nome da associação não pode ter mais de 255 caracteres.',

            'documento_associacao.required' => 'O documento (CPF/CNPJ) da associação é obrigatório.',
            'documento_associacao.size' => 'O formato do documento está inválido.',
            'documento_associacao.unique' => 'Este documento já está cadastrado.',

            'email_associacao.required' => 'O e-mail da associação é obrigatório.',
            'email_associacao.email' => 'O e-mail da associação deve ser um endereço válido.',
            'email_associacao.unique' => 'Este e-mail já está cadastrado.',

            'telefone_associacao.required' => 'O telefone da associação é obrigatório.',

            // Endereço
            'endereco.required' => 'O endereço é obrigatório.',
            'numero.required' => 'O número do endereço é obrigatório.',
            'bairro.required' => 'O bairro é obrigatório.',
            'cidade.required' => 'A cidade é obrigatória.',
            'estado.required' => 'O estado é obrigatório.',
            'estado.size' => 'O estado deve ter 2 caracteres.',
            'cep.required' => 'O CEP é obrigatório.',
            'cep.size' => 'O CEP deve estar no formato 00000-000.',

            // Dados opcionais
            'data_fundacao.date' => 'A data de fundação deve ser uma data válida.',
            'descricao.max' => 'A descrição não pode ter mais de 1000 caracteres.',
            'site.url' => 'O site deve ser uma URL válida.',

            // Senha
            'password.required' => 'A senha é obrigatória.',
            'password.min' => 'A senha deve ter pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação da senha não confere.',

            // Responsável (PF)
            'nome_responsavel.required' => 'O nome do responsável é obrigatório.',
            'documento_responsavel.required' => 'O CPF do responsável é obrigatório.',
            'documento_responsavel.size' => 'O CPF deve estar no formato 000.000.000-00.',
            'documento_responsavel.unique' => 'Este CPF já está cadastrado.',
            'email_responsavel.required' => 'O e-mail do responsável é obrigatório.',
            'email_responsavel.email' => 'O e-mail do responsável deve ser um endereço válido.',
            'email_responsavel.unique' => 'Este e-mail já está cadastrado.',
            'telefone_responsavel.required' => 'O telefone do responsável é obrigatório.',

            // Representante (CNPJ)
            'representante_nome.required' => 'O nome do representante é obrigatório.',
            'representante_cpf.required' => 'O CPF do representante é obrigatório.',
            'representante_cpf.size' => 'O CPF deve estar no formato 000.000.000-00.',
            'representante_cpf.unique' => 'Este CPF já está cadastrado.',
            'representante_email.required' => 'O e-mail do representante é obrigatório.',
            'representante_email.email' => 'O e-mail do representante deve ser um endereço válido.',
            'representante_email.unique' => 'Este e-mail já está cadastrado.',
            'representante_telefone.required' => 'O telefone do representante é obrigatório.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Remove formatação dos documentos para validação no banco
        if ($this->has('documento_associacao')) {
            $this->merge([
                'documento_associacao_clean' => preg_replace('/\D/', '', $this->documento_associacao),
            ]);
        }

        if ($this->has('documento_responsavel')) {
            $this->merge([
                'documento_responsavel_clean' => preg_replace('/\D/', '', $this->documento_responsavel),
            ]);
        }

        if ($this->has('representante_cpf')) {
            $this->merge([
                'representante_cpf_clean' => preg_replace('/\D/', '', $this->representante_cpf),
            ]);
        }

        if ($this->has('cep')) {
            $this->merge([
                'cep_clean' => preg_replace('/\D/', '', $this->cep),
            ]);
        }

        if ($this->has('telefone_associacao')) {
            $this->merge([
                'telefone_associacao_clean' => preg_replace('/\D/', '', $this->telefone_associacao),
            ]);
        }

        if ($this->has('telefone_responsavel')) {
            $this->merge([
                'telefone_responsavel_clean' => preg_replace('/\D/', '', $this->telefone_responsavel),
            ]);
        }

        if ($this->has('representante_telefone')) {
            $this->merge([
                'representante_telefone_clean' => preg_replace('/\D/', '', $this->representante_telefone),
            ]);
        }
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'nome' => 'nome da associação',
            'documento_associacao' => 'documento da associação',
            'email_associacao' => 'e-mail da associação',
            'telefone_associacao' => 'telefone da associação',
            'nome_responsavel' => 'nome do responsável',
            'documento_responsavel' => 'CPF do responsável',
            'email_responsavel' => 'e-mail do responsável',
            'telefone_responsavel' => 'telefone do responsável',
            'representante_nome' => 'nome do representante',
            'representante_cpf' => 'CPF do representante',
            'representante_email' => 'e-mail do representante',
            'representante_telefone' => 'telefone do representante',
        ];
    }
}
