<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\Plan;
use App\Models\Sale;
use App\Models\User;

class AssociacaoController extends Controller
{
    public function index()
    {
        $associationId = auth()->user()->association_id;

        // Metricas da Associacao
        $totalUsers = User::where('association_id', $associationId)->count();
        $activeUsers = User::where('association_id', $associationId)->where('status', 'ativo')->count();
        $pendingUsers = User::where('association_id', $associationId)->where('status', 'pendente')->count();
        $inactiveUsers = User::where('association_id', $associationId)->where('status', 'inativo')->count();

        // Metrica de Membros (associados)
        $totalMembers = User::comPerfil('Membro')->where('association_id', $associationId)->count();

        // Metrica de Clientes
        $totalClients = User::comPerfil('Cliente')->where('association_id', $associationId)->count();

        // Metricas de Vendas
        $totalSales = Sale::where('association_id', $associationId)->count();
        $totalRevenue = Sale::where('association_id', $associationId)->where('status', 'paid')->sum('total_price');
        $pendingRevenue = Sale::where('association_id', $associationId)->where('status', 'awaiting_payment')->sum('total_price');
        $activePlans = Plan::where('association_id', $associationId)->where('is_active', true)->count();
        $totalPlans = Plan::where('association_id', $associationId)->count();

        // Metricas de Conteudo
        $publishedNews = News::where('association_id', $associationId)->published()->count();
        $draftNews = News::where('association_id', $associationId)->draft()->count();

        // Atividade Recente
        $recentSales = Sale::where('association_id', $associationId)
            ->with(['user', 'plan'])
            ->latest()
            ->take(5)
            ->get();

        $newUsersThisMonth = User::where('association_id', $associationId)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return view('associacao.dashboard.index', compact(
            'totalUsers', 'activeUsers', 'pendingUsers', 'inactiveUsers',
            'totalMembers', 'totalClients',
            'totalSales', 'totalRevenue', 'pendingRevenue', 'activePlans',
            'publishedNews', 'draftNews', 'recentSales', 'newUsersThisMonth',
            'totalPlans'
        ));
    }
}
