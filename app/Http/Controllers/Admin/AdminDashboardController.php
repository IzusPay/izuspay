<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\Sale; // Importe o modelo Association
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Importe o Facade DB para consultas complexas

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Filtros de Data (continua igual)
        $startDate = Carbon::parse($request->input('start_date', Carbon::now()->subDays(30)))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date', Carbon::now()))->endOfDay();

        // Query base para os KPIs
        $salesQuery = Sale::query()->whereBetween('created_at', [$startDate, $endDate]);

        // 2. KPIs para os Cards (continua igual)
        $totalRevenue = (clone $salesQuery)->sum('total_price');
        $paidRevenue = (clone $salesQuery)->where('status', 'paid')->sum('total_price');
        $pendingRevenue = (clone $salesQuery)->where('status', 'awaiting_payment')->sum('total_price');
        $refundedRevenue = (clone $salesQuery)->where('status', 'refunded')->sum('total_price');
        $chargebackRevenue = (clone $salesQuery)->where('status', 'chargeback')->sum('total_price');
        $refusedRevenue = (clone $salesQuery)->where('status', 'refused')->sum('total_price');

        // 3. Dados para o Gráfico (continua igual)
        $dailySales = Sale::query()
            ->where('status', 'paid') // Gráfico geralmente mostra apenas o que foi pago
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_price) as total'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $chartLabels = $dailySales->pluck('date')->map(fn ($date) => Carbon::parse($date)->format('d/m'));
        $chartValues = $dailySales->pluck('total');

        // 4. Transações Recentes (continua igual)
        $recentTransactions = Sale::with(['user', 'product', 'plan'])
            ->latest()
            ->limit(10)
            ->get();

        // ===================================================================
        // 5. NOVA LÓGICA: Top 10 Vendedores (Criadores/Associações) do Mês
        // ===================================================================
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Começamos a consulta a partir do DB::table('sales') para mais controle.
        // Esta abordagem é mais direta e evita problemas de JOIN com dados "órfãos".
        $topCreators = DB::table('sales')
            ->select([
                'associations.id',
                'associations.nome as name',
                DB::raw('SUM(sales.total_price) as total_revenue'),
                DB::raw('COUNT(sales.id) as total_sales'),
            ])
            // O JOIN agora é feito a partir da tabela de vendas.
            ->join('associations', 'sales.association_id', '=', 'associations.id')

            // CONDIÇÕES OBRIGATÓRIAS
            ->where('sales.status', 'paid')
            ->whereNotNull('sales.association_id') // Garante que a venda tem um vendedor associado.
            ->whereBetween('sales.created_at', [$startOfMonth, $endOfMonth])

            // Agrupamento e Ordenação
            ->groupBy('associations.id', 'associations.nome')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        // 6. Enviando todos os dados para a View
        return view('admin.dashboard', [
            // KPIs
            'totalRevenue' => $totalRevenue,
            'paidRevenue' => $paidRevenue,
            'pendingRevenue' => $pendingRevenue,
            'refundedRevenue' => $refundedRevenue,
            'chargebackRevenue' => $chargebackRevenue,
            'refusedRevenue' => $refusedRevenue,

            // Gráfico
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,

            // Tabela de Recentes
            'recentTransactions' => $recentTransactions,

            // NOVO: Top 10 Criadores
            'topCreators' => $topCreators,

            // Filtros
            'startDate' => $startDate->format('d/m/Y'), // Formato mais amigável
            'endDate' => $endDate->format('d/m/Y'),
            'request' => $request,
        ]);
    }
}
