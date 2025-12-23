<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\News;
use App\Models\Plan;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $associationId = $request->user()->association_id;

        $userLayout = optional($request->user()->dashboardSetting)->layout ?? null;

        $filterMonth = $request->input('filter_month', null);
        $filterDay = $request->input('filter_day', null);

        // 4. DADOS PARA GRÁFICO DE RECEITA MENSAL (ÚLTIMOS 12 MESES)
        $monthlyRevenueQuery = Sale::where('association_id', $associationId)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(11));

        $chartLabels = [];
        $chartData = [];

        if ($filterMonth) {
            if ($filterDay) {
                $currentYear = now()->year;

                $revenueByHour = $monthlyRevenueQuery
                    ->whereMonth('created_at', $filterMonth)
                    ->whereDay('created_at', $filterDay)
                    ->whereYear('created_at', $currentYear)
                    ->select(
                        DB::raw('SUM(total_price) as total'),
                        DB::raw('HOUR(created_at) as hour')
                    )
                    ->groupBy(DB::raw('HOUR(created_at)'))
                    ->orderBy('hour', 'asc')
                    ->get();

                // Cria array com todas as 24 horas, preenchendo com 0 onde não há dados
                for ($hour = 0; $hour < 24; $hour++) {
                    $chartLabels[] = sprintf('%02d:00', $hour);
                    $hourData = $revenueByHour->where('hour', $hour)->first();
                    $chartData[] = $hourData ? (float) $hourData->total : 0;
                }
            } else {
                // Agrupa por dia do mês selecionado
                $revenueByDay = $monthlyRevenueQuery
                    ->whereMonth('created_at', $filterMonth)
                    ->select(
                        DB::raw('SUM(total_price) as total'),
                        DB::raw('DAY(created_at) as day')
                    )
                    ->groupBy(DB::raw('DAY(created_at)'))
                    ->orderBy('day', 'asc')
                    ->get();

                // Cria array com todos os dias do mês
                $daysInMonth = now()->month($filterMonth)->daysInMonth;
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $chartLabels[] = str_pad($day, 2, '0', STR_PAD_LEFT);
                    $dayData = $revenueByDay->where('day', $day)->first();
                    $chartData[] = $dayData ? (float) $dayData->total : 0;
                }
            }
        } else {
            $revenueByMonth = $monthlyRevenueQuery
                ->select(
                    DB::raw('SUM(total_price) as total'),
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month")
                )
                ->groupBy('month')
                ->orderBy('month', 'asc')
                ->get();

            // Cria array com últimos 12 meses
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthKey = $date->format('Y-m');
                $monthLabel = $date->format('M/y');

                $chartLabels[] = $monthLabel;
                $monthData = $revenueByMonth->where('month', $monthKey)->first();
                $chartData[] = $monthData ? (float) $monthData->total : 0;
            }
        }

        $revenueChartData = [
            'labels' => $chartLabels,
            'data' => $chartData,
        ];

        // Layout Padrão (se o usuário nunca salvou um)
        $defaultLayout = [
            'totalUsers' => ['visible' => true, 'size' => 'col-span-1'],
            'totalMembers' => ['visible' => true, 'size' => 'col-span-1'],
            'totalRevenue' => ['visible' => true, 'size' => 'col-span-1'],
            'pendingRevenue' => ['visible' => true, 'size' => 'col-span-1'],
            'revenueChart' => ['visible' => true, 'size' => 'col-span-1 lg:col-span-2'],
            'newMembersChart' => ['visible' => true, 'size' => 'col-span-1 lg:col-span-2'],
            'averageTicket' => ['visible' => true, 'size' => 'col-span-1'],
            'onboardingConversionRate' => ['visible' => true, 'size' => 'col-span-1'],
            'activeMembers' => ['visible' => true, 'size' => 'col-span-1'],
            'inactiveMembers' => ['visible' => true, 'size' => 'col-span-1'],
            'recentActivity' => ['visible' => true, 'size' => 'col-span-1 lg:col-span-2'],
            'gamificationLevel' => ['visible' => true, 'size' => 'col-span-1 lg:col-span-2'], // Increased size for better rewards display
            'rewardsHistory' => ['visible' => true, 'size' => 'col-span-1 lg:col-span-2'], // Added rewards history card
        ];

        // Mescla o layout do usuário com o padrão para garantir que novos cards apareçam
        $layout = $userLayout ? array_merge($defaultLayout, $userLayout) : $defaultLayout;

        $userLayoutConfig = auth()->user()->dashboardSetting->layout ?? [];
        // Reordena os cards com base na ordem salva pelo usuário, se existir
        if ($userLayout) {
            $layout = array_replace(array_flip(array_keys($userLayout)), $layout);
        }

        $association = $request->user()->association;
        $balanceDetails = $association ? $association->balanceDetails : [
            'available' => 0,
            'total_gross' => 0,
            'total_withdrawn' => 0,
            'pending_release' => 0,
            'retained' => 0,
            'pending_withdrawal' => 0,
            'last_update' => now()->diffForHumans(),
        ];
        $saldo = $balanceDetails['available'];
        $totalRevenue = Sale::where('association_id', $associationId)->where('status', 'paid')->sum('total_price');
        $totalSales = Sale::where('association_id', $associationId)->count();
        $totalUsers = User::where('association_id', $associationId)->count();
        $totalMembers = User::comPerfil('Membro')->where('association_id', $associationId)->count();
        $totalClients = User::comPerfil('Cliente')->where('association_id', $associationId)->count();
        $afiliados = $totalMembers;

        // Enhanced levels with more rewards and achievements
        $levels = [
            1 => ['min' => 0, 'max' => 10000, 'name' => 'Iniciante', 'badge' => 'seedling', 'color' => 'green', 'rewards' => ['Dashboard básico'], 'description' => 'Começando a jornada.'],
            2 => ['min' => 10000, 'max' => 100000, 'name' => 'Intermediário', 'badge' => 'medal', 'color' => 'amber', 'rewards' => ['Relatórios mensais'], 'description' => 'Subindo de nível.'],
            3 => ['min' => 100000, 'max' => 500000, 'name' => 'Avançado', 'badge' => 'star', 'color' => 'gray', 'rewards' => ['Analytics avançado'], 'description' => 'Crescimento consistente.'],
            4 => ['min' => 500000, 'max' => 1000000, 'name' => 'Expert', 'badge' => 'trophy', 'color' => 'yellow', 'rewards' => ['API personalizada'], 'description' => 'Excelência reconhecida.'],
            5 => ['min' => 1000000, 'max' => 5000000, 'name' => 'Master', 'badge' => 'platinum-trophy', 'color' => 'blue', 'rewards' => ['Consultoria exclusiva'], 'description' => 'Elite do mercado.'],
            6 => ['min' => 5000000, 'max' => PHP_INT_MAX, 'name' => 'Lendário', 'badge' => 'diamond', 'color' => 'purple', 'rewards' => ['Parceria estratégica'], 'description' => 'Top do topo.'],
        ];

        $currentLevelInfo = null;
        $currentLevel = 1;
        foreach ($levels as $level => $info) {
            if ($totalRevenue >= $info['min']) {
                $currentLevelInfo = $info;
                $currentLevelInfo['level'] = $level;
                $currentLevel = $level;
            } else {
                break;
            }
        }

        // Calculate progress to next level
        $nextLevel = $currentLevel + 1;
        $nextLevelMin = $levels[$nextLevel]['min'] ?? $currentLevelInfo['max'];
        $currentLevelMin = $currentLevelInfo['min'];

        $range = $nextLevelMin - $currentLevelMin;
        $progress = $totalRevenue - $currentLevelMin;
        $progressPercentage = ($range > 0) ? min(100, ($progress / $range) * 100) : 100;

        $gamificationData = [
            'levelName' => $currentLevelInfo['name'],
            'levelBadge' => $currentLevelInfo['badge'],
            'levelColor' => $currentLevelInfo['color'],
            'levelDescription' => $currentLevelInfo['description'],
            'currentLevel' => $currentLevel,
            'currentRevenue' => $totalRevenue,
            'nextLevelTarget' => $nextLevelMin,
            'progressPercentage' => $progressPercentage,
            'remainingToNext' => max(0, $nextLevelMin - $totalRevenue),
            'rewards' => $currentLevelInfo['rewards'],
            'nextLevelRewards' => $levels[$nextLevel]['rewards'] ?? [],
            'nextLevelName' => $levels[$nextLevel]['name'] ?? 'Máximo',
            'allLevels' => $levels,
            'achievements' => $this->calculateAchievements($totalRevenue, $totalSales, $totalMembers),
            'milestones' => $this->calculateMilestones($totalRevenue, $totalSales, $totalMembers),
        ];

        // === MÉTRICAS DE USUÁRIOS E PERFIS (EXISTENTES) ===
        $totalUsers = User::where('association_id', $associationId)->count();
        $totalMembers = User::comPerfil('Membro')->where('association_id', $associationId)->count();
        $totalClients = User::comPerfil('Cliente')->where('association_id', $associationId)->count();

        // === MÉTRICAS DO FUNIL DE ONBOARDING (EXISTENTES) ===
        $docsPendingUploadCount = User::where('association_id', $associationId)->where('status', 'documentation_pending')->count();
        $docsUnderReviewCount = User::where('association_id', $associationId)->where('status', 'docs_under_review')->count();
        $paymentPendingCount = User::where('association_id', $associationId)->where('status', 'payment_pending')->count();

        // === MÉTRICAS DE VENDAS (EXISTENTES) ===
        $pendingRevenue = Sale::where('association_id', $associationId)->where('status', 'awaiting_payment')->sum('total_price');
        $activePlans = Plan::where('association_id', $associationId)->where('is_active', true)->count();
        $totalPlans = Plan::where('association_id', $associationId)->count();

        // === MÉTRICAS DE CONTEÚDO (EXISTENTES) ===
        $publishedNews = News::where('association_id', $associationId)->published()->count();
        $draftNews = News::where('association_id', $associationId)->draft()->count();

        // === ATIVIDADE RECENTE (EXISTENTE) ===
        $recentSales = Sale::where('association_id', $associationId)
            ->with(['user', 'plan', 'product'])
            ->latest()
            ->take(5)
            ->get();

        // 1. TICKET MÉDIO POR VENDA
        $averageTicket = Sale::where('association_id', $associationId)
            ->where('status', 'paid')
            ->avg('total_price') ?? 0;
        $pixHoje = Sale::where('association_id', $associationId)
            ->where('status', 'paid')
            ->whereDate('created_at', now()->toDateString())
            ->where('payment_method', 'pix')
            ->sum('total_price');
        $cartaoHoje = Sale::where('association_id', $associationId)
            ->where('status', 'paid')
            ->whereDate('created_at', now()->toDateString())
            ->where('payment_method', 'credit_card')
            ->sum('total_price');
        $boletoHoje = Sale::where('association_id', $associationId)
            ->where('status', 'paid')
            ->whereDate('created_at', now()->toDateString())
            ->where('payment_method', 'boleto')
            ->sum('total_price');

        // 2. TAXA DE CONVERSÃO DE ONBOARDING
        $activeMembersCount = User::comPerfil('Membro')->where('association_id', $associationId)->where('status', 'active')->count();
        $totalOnboardingUsers = $activeMembersCount + $docsPendingUploadCount + $docsUnderReviewCount + $paymentPendingCount;
        $onboardingConversionRate = ($totalOnboardingUsers > 0) ? ($activeMembersCount / $totalOnboardingUsers) * 100 : 0;

        // 3. MEMBROS ATIVOS VS INATIVOS
        $inactiveMembersCount = User::comPerfil('Membro')->where('association_id', $associationId)->where('status', 'inactive')->count();

        // 4. DADOS PARA GRÁFICO DE RECEITA MENSAL (ÚLTIMOS 12 MESES)
        $monthlyRevenue = Sale::where('association_id', $associationId)
            ->where('status', 'paid')
            ->where('created_at', '>=', now()->subMonths(11))
            ->select(
                DB::raw('SUM(total_price) as total'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month")
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->pluck('total', 'month')
            ->all();

        // 5. DADOS PARA GRÁFICO DE NOVOS MEMBROS (ÚLTIMOS 12 MESES)
        $newMembersByMonth = User::comPerfil('Membro')
            ->where('association_id', $associationId)
            ->where('created_at', '>=', now()->subMonths(11))
            ->select(
                DB::raw('COUNT(id) as count'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month")
            )
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get()
            ->pluck('count', 'month')
            ->all();

        // Preencher meses sem dados para os gráficos
        $revenueChartData = [];
        $membersChartData = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->subMonths($i)->format('Y-m');
            $monthLabel = now()->subMonths($i)->format('M/y');

            $revenueChartData['labels'][] = $monthLabel;
            $revenueChartData['data'][] = $monthlyRevenue[$month] ?? 0;

            $membersChartData['labels'][] = $monthLabel;
            $membersChartData['data'][] = $newMembersByMonth[$month] ?? 0;
        }

        $user = $request->user();
        $association = $user->association;

        $convertedSales = Sale::where('association_id', $associationId)
            ->whereIn('status', ['paid'])
            ->count();

        $totalSales = Sale::where('association_id', $associationId)
            ->count();

        $conversionRate = $totalSales > 0
            ? round(($convertedSales / $totalSales) * 100, 2)
            : 0;

        $transacoes = $totalSales;

        $banners = Banner::where('association_id', $associationId)
            ->where('status', 'active')
            ->orderByDesc('id')
            ->get();

        return view('associacao.dashboard.index', compact(
            'totalUsers', 'totalMembers', 'totalClients',
            'docsPendingUploadCount', 'docsUnderReviewCount', 'paymentPendingCount',
            'totalRevenue', 'pendingRevenue', 'conversionRate',
            'publishedNews', 'draftNews', 'recentSales',
            'averageTicket',
            'onboardingConversionRate',
            'activeMembersCount',
            'inactiveMembersCount',
            'revenueChartData',
            'membersChartData',
            'gamificationData',
            'layout',
            'userLayoutConfig',
            'association',
            'filterMonth',
            'filterDay',
            'saldo',
            'afiliados',
            'transacoes',
            'pixHoje',
            'cartaoHoje',
            'boletoHoje',
            'banners'
        ));
    }

    private function calculateAchievements($revenue, $sales, $members)
    {
        $achievements = [];

        // Revenue-based achievements
        if ($revenue >= 10000) {
            $achievements[] = ['name' => 'Primeira Receita', 'icon' => 'dollar-sign', 'unlocked' => true];
        }
        if ($revenue >= 100000) {
            $achievements[] = ['name' => 'Seis Dígitos', 'icon' => 'trending-up', 'unlocked' => true];
        }
        if ($revenue >= 1000000) {
            $achievements[] = ['name' => 'Milionário', 'icon' => 'crown', 'unlocked' => true];
        }

        // Sales-based achievements
        if ($sales >= 10) {
            $achievements[] = ['name' => 'Vendedor Iniciante', 'icon' => 'shopping-cart', 'unlocked' => true];
        }
        if ($sales >= 100) {
            $achievements[] = ['name' => 'Vendedor Expert', 'icon' => 'award', 'unlocked' => true];
        }
        if ($sales >= 1000) {
            $achievements[] = ['name' => 'Máquina de Vendas', 'icon' => 'zap', 'unlocked' => true];
        }

        // Member-based achievements
        if ($members >= 50) {
            $achievements[] = ['name' => 'Comunidade Ativa', 'icon' => 'users', 'unlocked' => true];
        }
        if ($members >= 500) {
            $achievements[] = ['name' => 'Grande Comunidade', 'icon' => 'globe', 'unlocked' => true];
        }

        return $achievements;
    }

    private function calculateMilestones($revenue, $sales, $members)
    {
        $milestones = [];

        // Next revenue milestones
        $revenueMilestones = [25000, 50000, 250000, 750000, 2500000, 10000000];
        foreach ($revenueMilestones as $milestone) {
            if ($revenue < $milestone) {
                $milestones[] = [
                    'type' => 'revenue',
                    'target' => $milestone,
                    'current' => $revenue,
                    'progress' => ($revenue / $milestone) * 100,
                    'description' => 'Receita de R$ '.number_format($milestone, 0, ',', '.'),
                ];
                break; // Only show next milestone
            }
        }

        // Next sales milestones
        $salesMilestones = [25, 50, 250, 500, 1500];
        foreach ($salesMilestones as $milestone) {
            if ($sales < $milestone) {
                $milestones[] = [
                    'type' => 'sales',
                    'target' => $milestone,
                    'current' => $sales,
                    'progress' => ($sales / $milestone) * 100,
                    'description' => $milestone.' vendas realizadas',
                ];
                break;
            }
        }

        // Next member milestones
        $memberMilestones = [25, 100, 250, 750, 1500];
        foreach ($memberMilestones as $milestone) {
            if ($members < $milestone) {
                $milestones[] = [
                    'type' => 'members',
                    'target' => $milestone,
                    'current' => $members,
                    'progress' => ($members / $milestone) * 100,
                    'description' => $milestone.' membros ativos',
                ];
                break;
            }
        }

        return $milestones;
    }

    // Removido: detecção de gênero externa para evitar latência e inconsistências
}
