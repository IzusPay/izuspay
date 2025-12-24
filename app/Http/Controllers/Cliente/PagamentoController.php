<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\TicketOrder;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagamentoController extends Controller
{
    /**
     * Exibe a tela de pagamento pendente.
     */
    public function index()
    {
        $user = Auth::user();
        $pendingSale = Sale::where('user_id', $user->id)
            ->where('status', 'awaiting_payment')
            ->with('plan')
            ->first();

        $pendingOrders = TicketOrder::with(['event', 'ticketType', 'sale'])
            ->where('user_id', $user->id)
            ->where(function ($q) {
                $q->where('status', 'awaiting_payment')
                    ->orWhereHas('sale', function ($s) {
                        $s->where('status', 'awaiting_payment');
                    });
            })
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        $myTickets = Ticket::with(['ticketType.event', 'order'])
            ->where('owner_user_id', $user->id)
            ->orderBy('id', 'desc')
            ->limit(8)
            ->get();

        return view('cliente.pagamento.index', compact('pendingSale', 'pendingOrders', 'myTickets'));
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
