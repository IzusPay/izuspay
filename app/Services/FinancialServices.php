<?php

namespace App\Services;

use App\Models\LedgerEntry;
use Carbon\Carbon;

class FinancialServices
{
    /**
     * O construtor é vazio para permitir a injeção de dependência automática pelo Laravel.
     */
    public function __construct()
    {
        // Construtor vazio.
    }

    /**
     * Retorna os dados de KPI para um período específico.
     */
    public function getKpiData(Carbon $startDate, Carbon $endDate): array
    {
        $query = LedgerEntry::whereBetween('created_at', [$startDate, $endDate]);

        $grossRevenue = (clone $query)->where('type', 'sale_revenue')->sum('amount');
        $platformRevenue = abs((clone $query)->where('type', 'platform_fee')->sum('amount'));
        $acquirerCost = (clone $query)->where('type', 'acquirer_fixed_cost')->sum('amount');
        $mdrCost = (clone $query)->where('type', 'mdr_cost')->sum('amount');
        $netMargin = ($grossRevenue > 0) ? ($platformRevenue / $grossRevenue) * 100 : 0;

        return [
            'gross_revenue' => $grossRevenue,
            'platform_revenue' => $platformRevenue,
            'net_margin' => $netMargin,
            'acquirer_fixed_cost' => $acquirerCost,
            'mdr_cost' => $mdrCost,
            'mdr_received' => 0,
            'interest_received' => 0,
            'whitelabel_fee' => 0,
        ];
    }

    /**
     * Retorna as movimentações paginadas para um período específico.
     */
    public function getMovements(Carbon $startDate, Carbon $endDate, int $limit = 100)
    {
        return LedgerEntry::whereBetween('created_at', [$startDate, $endDate])
            ->with('association')
            ->latest()
            ->paginate($limit)
            ->withQueryString();
    }
}
