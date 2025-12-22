<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\BankAccountRequest;
use App\Models\BankAccount;

class BankAccountController extends Controller
{
    public function index()
    {
        $bankAccounts = BankAccount::where('association_id', auth()->user()->association_id)->get();

        return view('associacao.financeiro._bank_accounts', compact('bankAccounts'));
    }

    public function create()
    {
        return view('associacao.financeiro.create_edit_bank_account');
    }

    public function store(BankAccountRequest $request)
    {
        $data = $request->validated();
        $data['association_id'] = auth()->user()->association_id;

        // Se for a primeira conta ou se o usuário marcar como padrão, define como padrão
        if (BankAccount::where('association_id', $data['association_id'])->doesntExist() || ($data['is_default'] ?? false)) {
            BankAccount::where('association_id', $data['association_id'])->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        BankAccount::create($data);

        return redirect()->route('associacao.financeiro.index')->with('success', 'Conta bancária adicionada com sucesso!');
    }

    public function edit(BankAccount $bankAccount)
    {
        abort_if($bankAccount->association_id !== auth()->user()->association_id, 403, 'Acesso negado.');

        return view('associacao.financeiro.create_edit_bank_account', compact('bankAccount'));
    }

    public function update(BankAccountRequest $request, BankAccount $bankAccount)
    {
        abort_if($bankAccount->association_id !== auth()->user()->association_id, 403, 'Acesso negado.');

        $data = $request->validated();

        if ($data['is_default'] ?? false) {
            BankAccount::where('association_id', auth()->user()->association_id)->update(['is_default' => false]);
            $data['is_default'] = true;
        } else {
            $data['is_default'] = false;
        }

        $bankAccount->update($data);

        return redirect()->route('associacao.financeiro.bank-accounts.index')->with('success', 'Conta bancária atualizada com sucesso!');
    }

    public function destroy(BankAccount $bankAccount)
    {
        abort_if($bankAccount->association_id !== auth()->user()->association_id, 403, 'Acesso negado.');
        $bankAccount->delete();

        return redirect()->back()->with('success', 'Conta bancária excluída com sucesso!');
    }
}
