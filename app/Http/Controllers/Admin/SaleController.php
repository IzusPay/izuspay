<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Exibe a lista de vendas com filtros.
     */
    public function index(Request $request)
    {
        $query = Sale::with(['user', 'product', 'plan', 'association']);

        // Filtro por busca (ID da venda, nome/email do usuário)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('transaction_hash', 'like', "%{$search}%") // Adicionado busca por hash
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtro por método de pagamento
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.sales.index', compact('sales'));
    }

    /**
     * Retorna os dados de uma venda específica em JSON para o modal.
     */
    public function show(Sale $sale)
    {
        // Carrega todos os relacionamentos necessários para exibir no modal
        $sale->load([
            'user',
            'product',
            'plan',
            'association.creatorProfile',
            'transactions', // Essencial para detalhes como o código PIX
        ]);

        // Retorna os dados como JSON
        return response()->json($sale);
    }
}
