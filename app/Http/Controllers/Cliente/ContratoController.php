<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

class ContratoController extends Controller
{
    /**
     * Exibe o contrato para o cliente.
     */
    public function index()
    {
        $user = auth()->user();
        // Assume que há um contrato pendente para o usuário
        $contract = Contract::where('user_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        return view('cliente.contrato.index', compact('contract'));
    }

    /**
     * Lógica para assinar o contrato.
     */
    public function sign(Request $request)
    {
        $user = auth()->user();
        $contract = Contract::where('user_id', $user->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $contract->update([
            'status' => 'signed',
            'signed_at' => now(),
        ]);

        // Finalmente, atualiza o status do usuário para ativo
        $user->status = 'ativo';
        $user->save();

        return redirect()->route('cliente.dashboard')->with('success', 'Contrato assinado com sucesso! Sua associação está ativa.');
    }
}
