<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RelatoriosController extends Controller
{
    public function index()
    {
        $associationId = auth()->user()->association_id;
        $currentMonthStart = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $previousMonthStart = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();
        $currentYearStart = Carbon::now()->startOfYear();

        // ===== MÉTRICAS DE ASSINANTES (Ajustado para campos disponíveis) =====
        $totalSubscriptions = Subscription::where('association_id', $associationId)->count();

        $totalActiveSubscriptions = Subscription::where('association_id', $associationId)
            ->where('status', 'active')
            ->count();

        $newSubscriptionsThisMonth = Subscription::where('association_id', $associationId)
            ->whereBetween('starts_at', [$currentMonthStart, $currentMonthEnd])
            ->count();

        $newSubscriptionsLastMonth = Subscription::where('association_id', $associationId)
            ->whereBetween('starts_at', [$previousMonthStart, $previousMonthEnd])
            ->count();

        $subscriptionsToRenewThisMonth = Subscription::where('association_id', $associationId)
            ->where('status', 'active')
            ->whereBetween('renews_at', [$currentMonthStart, $currentMonthEnd])
            ->count();

        // Como não temos campo canceled_at, vamos usar subscriptions canceladas
        $canceledSubscriptionsThisMonth = Subscription::where('association_id', $associationId)
            ->where('status', 'canceled')
            ->whereBetween('updated_at', [$currentMonthStart, $currentMonthEnd]) // Usando updated_at como proxy
            ->count();

        // Como não temos campo expires_at, vamos usar subscriptions expiradas
        $expiredSubscriptionsThisMonth = Subscription::where('association_id', $associationId)
            ->where('status', 'expired')
            ->whereBetween('updated_at', [$currentMonthStart, $currentMonthEnd]) // Usando updated_at como proxy
            ->count();

        // Taxa de Churn ajustada
        $activeSubscriptionsStartOfMonth = Subscription::where('association_id', $associationId)
            ->where('status', 'active')
            ->where('starts_at', '<', $currentMonthStart)
            ->count();

        $churnRate = $activeSubscriptionsStartOfMonth > 0
            ? (($canceledSubscriptionsThisMonth + $expiredSubscriptionsThisMonth) / $activeSubscriptionsStartOfMonth) * 100
            : 0;

        // Taxa de crescimento de assinantes
        $subscriptionGrowthRate = $newSubscriptionsLastMonth > 0
            ? (($newSubscriptionsThisMonth - $newSubscriptionsLastMonth) / $newSubscriptionsLastMonth) * 100
            : ($newSubscriptionsThisMonth > 0 ? 100 : 0);

        // ===== ANÁLISE FINANCEIRA =====
        $totalRevenue = Sale::where('association_id', $associationId)
            ->where('status', 'paid')
            ->sum('total_price');

        $monthlyRevenue = Sale::where('association_id', $associationId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->sum('total_price');

        $previousMonthlyRevenue = Sale::where('association_id', $associationId)
            ->where('status', 'paid')
            ->whereBetween('created_at', [$previousMonthStart, $previousMonthEnd])
            ->sum('total_price');

        $yearlyRevenue = Sale::where('association_id', $associationId)
            ->where('status', 'paid')
            ->where('created_at', '>=', $currentYearStart)
            ->sum('total_price');

        $revenueGrowth = $previousMonthlyRevenue > 0
            ? (($monthlyRevenue - $previousMonthlyRevenue) / $previousMonthlyRevenue) * 100
            : ($monthlyRevenue > 0 ? 100 : 0);

        // Receita média por assinante
        $averageRevenuePerUser = $totalActiveSubscriptions > 0
            ? $monthlyRevenue / $totalActiveSubscriptions
            : 0;

        // Valor do tempo de vida do cliente (LTV estimado)
        $averageSubscriptionDuration = 12; // meses (estimativa)
        $customerLifetimeValue = $averageRevenuePerUser * $averageSubscriptionDuration;

        // ===== ANÁLISE DE VENDAS =====
        $totalSales = Sale::where('association_id', $associationId)->count();

        $salesThisMonth = Sale::where('association_id', $associationId)
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->count();

        $salesByStatus = Sale::where('association_id', $associationId)
            ->select('status', DB::raw('COUNT(*) as total'), DB::raw('SUM(total_price) as revenue'))
            ->groupBy('status')
            ->get();

        $conversionRate = $totalSales > 0
            ? ($salesByStatus->where('status', 'paid')->first()->total ?? 0) / $totalSales * 100
            : 0;

        // ===== DESEMPENHO DOS PLANOS =====
        $salesByPlan = Sale::select(
            'plans.name',
            'plans.id',
            DB::raw('COUNT(sales.id) as total_sales_count'),
            DB::raw('SUM(sales.total_price) as total_revenue'),
            DB::raw('AVG(sales.total_price) as average_price')
        )
            ->join('plans', 'sales.plan_id', '=', 'plans.id')
            ->where('sales.association_id', $associationId)
            ->where('sales.status', 'paid')
            ->groupBy('plans.name', 'plans.id')
            ->orderByDesc('total_revenue')
            ->get();

        $activeSubscriptionsByPlan = Subscription::select(
            'plans.name',
            DB::raw('COUNT(subscriptions.id) as active_count')
        )
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.association_id', $associationId)
            ->where('subscriptions.status', 'active')
            ->groupBy('plans.name')
            ->get();

        // ===== MÉTODOS DE PAGAMENTO =====
        $revenueByPaymentMethod = Sale::where('association_id', $associationId)
            ->where('status', 'paid')
            ->select(
                'payment_method',
                DB::raw('SUM(total_price) as total_revenue'),
                DB::raw('COUNT(*) as total_sales')
            )
            ->groupBy('payment_method')
            ->get();

        // ===== CRESCIMENTO TEMPORAL =====
        $subscriptionGrowth = Subscription::select(
            DB::raw('DATE_FORMAT(starts_at, "%Y-%m") as month_year'),
            DB::raw('COUNT(*) as new_subs')
        )
            ->where('association_id', $associationId)
            ->where('starts_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month_year')
            ->orderBy('month_year', 'asc')
            ->get();

        $revenueGrowthChart = Sale::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month_year'),
            DB::raw('SUM(total_price) as monthly_revenue')
        )
            ->where('association_id', $associationId)
            ->where('status', 'paid')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month_year')
            ->orderBy('month_year', 'asc')
            ->get();

        // ===== ANÁLISE DE USUÁRIOS =====
        // Análise por perfis usando o sistema de relacionamentos
        try {
            $usersByProfile = DB::table('users')
                ->join('user_perfis', 'users.id', '=', 'user_perfis.user_id')
                ->join('perfil_models', 'user_perfis.perfil_id', '=', 'perfil_models.id')
                ->where('users.association_id', $associationId)
                ->where('user_perfis.status', 1)
                ->select('perfil_models.name as profile', DB::raw('COUNT(DISTINCT users.id) as total'))
                ->groupBy('perfil_models.name')
                ->get();
        } catch (\Exception $e) {
            // Se houver erro nas tabelas de perfil, usar dados básicos
            $usersByProfile = collect();
        }

        // Se não houver perfis ou houver erro, criar dados básicos
        if ($usersByProfile->isEmpty()) {
            $totalUsers = User::where('association_id', $associationId)->count();
            $usersWithSubscriptions = User::where('association_id', $associationId)
                ->whereHas('subscriptions', function ($query) {
                    $query->where('status', 'active');
                })
                ->count();

            $usersByProfile = collect([
                (object) ['profile' => 'Total de Usuários', 'total' => $totalUsers],
                (object) ['profile' => 'Com Assinaturas Ativas', 'total' => $usersWithSubscriptions],
                (object) ['profile' => 'Sem Assinaturas Ativas', 'total' => $totalUsers - $usersWithSubscriptions],
            ]);
        }

        $newUsersThisMonth = User::where('association_id', $associationId)
            ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd])
            ->count();

        // Análise de usuários por status
        $usersByStatus = User::where('association_id', $associationId)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        // ===== MÉTRICAS DE RETENÇÃO =====
        $retentionRate = $totalActiveSubscriptions > 0
            ? (($totalActiveSubscriptions - $canceledSubscriptionsThisMonth) / $totalActiveSubscriptions) * 100
            : 0;

        // ===== TOP PERFORMERS =====
        $topPlans = $salesByPlan->take(5);
        $worstPlans = $salesByPlan->sortBy('total_sales_count')->take(3);

        // ===== ANÁLISE DE STATUS DAS ASSINATURAS =====
        $subscriptionsByStatus = Subscription::where('association_id', $associationId)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        return view('associacao.relatorios.index', compact(
            // Métricas de Assinantes
            'totalSubscriptions',
            'totalActiveSubscriptions',
            'newSubscriptionsThisMonth',
            'newSubscriptionsLastMonth',
            'subscriptionsToRenewThisMonth',
            'canceledSubscriptionsThisMonth',
            'churnRate',
            'subscriptionGrowthRate',
            'retentionRate',
            'subscriptionsByStatus',

            // Métricas Financeiras
            'totalRevenue',
            'monthlyRevenue',
            'yearlyRevenue',
            'revenueGrowth',
            'averageRevenuePerUser',
            'customerLifetimeValue',

            // Métricas de Vendas
            'totalSales',
            'salesThisMonth',
            'conversionRate',
            'salesByStatus',

            // Análises
            'salesByPlan',
            'activeSubscriptionsByPlan',
            'revenueByPaymentMethod',
            'subscriptionGrowth',
            'revenueGrowthChart',
            'usersByProfile',
            'usersByStatus',
            'newUsersThisMonth',
            'topPlans',
            'worstPlans'
        ));
    }

    public function exportPdf()
    {
        // TODO: Implementar exportação em PDF
        return response()->json(['message' => 'Funcionalidade em desenvolvimento']);
    }

    public function exportExcel()
    {
        // TODO: Implementar exportação em Excel
        return response()->json(['message' => 'Funcionalidade em desenvolvimento']);
    }
}
