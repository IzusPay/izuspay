<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class PagamentoController extends Controller
{
    /**
     * Exibe a tela de pagamento pendente.
     */
    public function index()
    {
        $user = auth()->user();
        // Assume que há uma venda com status 'awaiting_payment'
        $pendingSale = Sale::where('user_id', $user->id)
            ->where('status', 'awaiting_payment')
            ->with('plan')
            ->firstOrFail();

        return view('cliente.pagamento.index', compact('pendingSale'));
    }

    /**
     * Lógica para processar o pagamento.
     */
    public function store(Request $request)
    {
        // Esta é a lógica que levaria ao seu gateway de pagamento.
        // Por agora, vamos apenas simular um redirecionamento.
        // O checkout do plano já tem essa lógica, então esta tela funciona como um "botão"
        // que leva o cliente ao checkout.

        return redirect()->route('cliente.pagamento.index')->with('success', 'Você foi redirecionado para a página de pagamento.');
    }
}
