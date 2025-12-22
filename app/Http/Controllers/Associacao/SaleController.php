<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaleRequest;
use App\Models\Plan; // Importe a model User
use App\Models\Sale; // Importe a model Plan
use App\Models\Subscription; // Importe o SaleRequest
use App\Models\User;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Exibe a lista de vendas da associação.
     */
    public function index(Request $request)
    {
        $associationId = auth()->user()->association_id;

        $query = Sale::where('association_id', $associationId)
            ->with(['user', 'plan'])
            ->latest();

        // Aplicar filtros da requisição
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('from_date')) {
            $query->where('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('created_at', '<=', $request->to_date);
        }

        $sales = $query->paginate(10);

        // Calcular métricas para os cards de estatísticas (sem filtros)
        $totalRevenue = Sale::where('association_id', $associationId)->where('status', 'paid')->sum('total_price');
        $pendingRevenue = Sale::where('association_id', $associationId)->where('status', 'awaiting_payment')->sum('total_price');
        $approvedSales = Sale::where('association_id', $associationId)->where('status', 'paid')->count();
        $cancelledSales = Sale::where('association_id', $associationId)->where('status', 'cancelled')->count();

        return view('associacao.vendas.index', compact('sales', 'totalRevenue', 'pendingRevenue', 'approvedSales', 'cancelledSales'));
    }

    /**
     * Exibe o formulário para criar uma nova venda.
     */
    public function create()
    {
        $associationId = auth()->user()->association_id;
        $users = User::where('association_id', $associationId)->get(); // Clientes da associação
        $plans = Plan::where('association_id', $associationId)->where('is_active', true)->get(); // Planos ativos da associação

        return view('associacao.vendas.create_edit', compact('users', 'plans'));
    }

    /**
     * Armazena uma nova venda no banco de dados.
     */
    public function store(Request $request)
    {
        $request['association_id'] = auth()->user()->association_id;

        Sale::create($request->all());

        return redirect()->route('associacao.vendas.index')
            ->with('success', 'Venda criada com sucesso!');
    }

    /**
     * Exibe os detalhes de uma venda específica.
     */
    public function show(Sale $sale)
    {
        if ($sale->association_id !== auth()->user()->association_id) {
            abort(403, 'Acesso negado.');
        }

        $sale->load(['user', 'plan', 'transactions']);

        return view('associacao.vendas.show', compact('sale'));
    }

    /**
     * Exibe o formulário para editar uma venda existente.
     */
    public function edit(Sale $sale)
    {
        if ($sale->association_id !== auth()->user()->association_id) {
            abort(403, 'Acesso negado.');
        }

        $associationId = auth()->user()->association_id;
        $users = User::where('association_id', $associationId)->get();
        $plans = Plan::where('association_id', $associationId)->where('is_active', true)->get();

        return view('associacao.vendas.create_edit', compact('sale', 'users', 'plans'));
    }

    /**
     * Atualiza uma venda no banco de dados.
     */
    public function update(Request $request, Sale $sale)
    {
        if ($sale->association_id !== auth()->user()->association_id) {
            abort(403, 'Acesso negado.');
        }

        $sale->update($request->all());

        return redirect()->route('associacao.vendas.index')
            ->with('success', 'Venda atualizada com sucesso!');
    }

    /**
     * Exclui uma venda do banco de dados.
     */
    public function destroy(Sale $sale)
    {
        if ($sale->association_id !== auth()->user()->association_id) {
            abort(403, 'Acesso negado.');
        }

        $sale->delete();

        return redirect()->route('associacao.vendas.index')
            ->with('success', 'Venda excluída com sucesso!');
    }

    /**
     * Atualiza o status de uma venda e cria a assinatura se for pago.
     */
    public function updateStatus(Request $request, Sale $sale)
    {
        if ($sale->association_id !== auth()->user()->association_id) {
            abort(403, 'Acesso negado.');
        }

        $request->validate(['status' => 'required|string|in:paid,cancelled,refunded,awaiting_payment']);

        $sale->update(['status' => $request->status]);

        if ($sale->status === 'paid' && $sale->plan_id) {
            $this->activateSubscription($sale);
        }

        return redirect()->back()->with('success', 'Status da venda atualizado com sucesso!');
    }

    /**
     * Lógica para ativar uma assinatura a partir de uma venda.
     */
    private function activateSubscription(Sale $sale): void
    {
        if (! $sale->plan_id || ! $sale->plan) {
            return; // Cannot create subscription without a plan
        }

        // Verifica se já existe uma assinatura para esta venda
        if (Subscription::where('sale_id', $sale->id)->exists()) {
            return; // A assinatura já foi criada
        }

        // 1. Cria a nova assinatura
        Subscription::create([
            'user_id' => $sale->user_id,
            'plan_id' => $sale->plan_id,
            'association_id' => $sale->association_id,
            'sale_id' => $sale->id,
            'status' => 'active',
            'starts_at' => now(),
            // A data de renovação depende da recorrência do plano
            'renews_at' => ($sale->plan->recurrence === 'monthly') ? now()->addMonth() : now()->addYear(),
        ]);

        // 2. Atualiza o status do usuário para 'ativo'
        $sale->user->update(['status' => 'ativo']);
    }
}
