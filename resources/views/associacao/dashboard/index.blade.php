@extends('layouts.app')

@section('title', 'Dashboard - Izus Payment')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    .gradient-bg {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(34, 197, 230, 0.1) 100%);
    }
    
    .card-metric {
        transition: all 0.3s ease;
    }
    
    .card-metric:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(59, 130, 246, 0.2);
    }
    
    .glow-border {
        border: 1px solid rgba(59, 130, 246, 0.3);
        box-shadow: inset 0 0 20px rgba(59, 130, 246, 0.1);
    }
    
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(59, 130, 246, 0.4);
        border-radius: 10px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(59, 130, 246, 0.6);
    }

    .filter-input {
        background: rgba(51, 65, 85, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }

    .filter-input:focus {
        background: rgba(71, 85, 105, 0.8);
        border-color: rgba(59, 130, 246, 0.5);
        box-shadow: 0 0 20px rgba(59, 130, 246, 0.2);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
   
    {{-- GRID DE MÉTRICAS PRINCIPAIS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Card: Total de Receita --}}
        <div class="card-metric bg-gradient-to-br from-slate-800/60 to-slate-900/40 backdrop-blur-xl border border-blue-500/30 rounded-xl p-6 glow-border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm font-medium mb-2">Total de Receita</p>
                    <p class="text-3xl font-bold text-transparent bg-gradient-to-r from-blue-300 to-cyan-400 bg-clip-text">
                        R$ {{ number_format($totalRevenue ?? 0, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Faturamento total</p>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-6 h-6 text-blue-400"></i>
                </div>
            </div>
        </div>

        {{-- Card: Receita Pendente --}}
        <div class="card-metric bg-gradient-to-br from-slate-800/60 to-slate-900/40 backdrop-blur-xl border border-yellow-500/30 rounded-xl p-6 glow-border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm font-medium mb-2">Receita Pendente</p>
                    <p class="text-3xl font-bold text-transparent bg-gradient-to-r from-yellow-300 to-orange-400 bg-clip-text">
                        R$ {{ number_format($pendingRevenue ?? 0, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Aguardando confirmação</p>
                </div>
                <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                    <i data-lucide="clock" class="w-6 h-6 text-yellow-400"></i>
                </div>
            </div>
        </div>

        {{-- Card: Planos Ativos --}}
        <div class="card-metric bg-gradient-to-br from-slate-800/60 to-slate-900/40 backdrop-blur-xl border border-green-500/30 rounded-xl p-6 glow-border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm font-medium mb-2">Taxa de Conversão</p>
                    <p class="text-3xl font-bold text-green-400">
                        {{ $conversionRate }}%
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Vendas convertidas</p>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-6 h-6 text-green-400"></i>
                </div>
            </div>
        </div>

        {{-- Card: Ticket Médio --}}
        <div class="card-metric bg-gradient-to-br from-slate-800/60 to-slate-900/40 backdrop-blur-xl border border-purple-500/30 rounded-xl p-6 glow-border">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm font-medium mb-2">Ticket Médio</p>
                    <p class="text-3xl font-bold text-transparent bg-gradient-to-r from-purple-300 to-pink-400 bg-clip-text">
                        R$ {{ number_format($averageTicket ?? 0, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-2">Valor médio por venda</p>
                </div>
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <i data-lucide="bar-chart-3" class="w-6 h-6 text-purple-400"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- GRÁFICO DE RECEITA COM FILTRO INTEGRADO --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- GRÁFICO PRINCIPAL COM FILTRO --}}
        <div class="lg:col-span-2">
            <div class="relative bg-gradient-to-br from-slate-900/80 to-slate-800/60 backdrop-blur-xl border border-blue-500/20 rounded-2xl p-8 shadow-2xl overflow-hidden">
                {{-- Efeito de Glow --}}
                <div class="absolute -top-1/4 -right-1/4 w-1/2 h-1/2 bg-gradient-to-br from-blue-600 to-cyan-500 rounded-full opacity-15 blur-3xl"></div>
                <div class="absolute -bottom-1/4 -left-1/4 w-1/2 h-1/2 bg-gradient-to-tr from-blue-600 to-purple-500 rounded-full opacity-10 blur-3xl"></div>

                <div class="relative z-10">
                    {{-- Cabeçalho do Gráfico com Botão de Filtro Minimalista --}}
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold bg-gradient-to-r from-blue-300 to-cyan-400 bg-clip-text text-transparent">
                                <i data-lucide="line-chart" class="w-7 h-7 inline mr-2 align-middle"></i>
                                Receita Mensal
                                @if($filterMonth || $filterDay)
                                    <span class="text-sm font-normal text-gray-400">(Filtrada)</span>
                                @endif
                            </h3>
                        </div>
                        {{-- Botão minimalista de filtro em lugar dos selects inline --}}
                        <button onclick="openFilterModal()" class="flex items-center space-x-2 text-gray-300 hover:text-white bg-slate-700/50 hover:bg-slate-700 px-4 py-2 rounded-lg transition-colors">
                            <i data-lucide="filter" class="w-4 h-4"></i>
                            <span>Filtros</span>
                        </button>
                    </div>

                    {{-- Status dos Filtros --}}
                    @if($filterMonth || $filterDay)
                    <div class="mb-6 p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg text-xs text-blue-300">
                        <i data-lucide="info" class="w-4 h-4 inline mr-2 align-middle"></i>
                        <span class="font-semibold">Filtros aplicados:</span>
                        @if($filterMonth) 
                            <span class="ml-2">Mês: {{ DateTime::createFromFormat('!m', $filterMonth)->format('F') }}</span>
                        @endif
                        @if($filterDay)
                            <span class="ml-2">Dia: {{ str_pad($filterDay, 2, '0', STR_PAD_LEFT) }}</span>
                        @endif
                    </div>
                    @endif

                    {{-- CANVAS DO GRÁFICO --}}
                    <div class="h-[350px] bg-slate-900/30 rounded-xl p-4 border border-white/5">
                        <canvas id="revenueChart"></canvas>
                    </div>

                    <div class="mt-4 text-center text-gray-400 text-sm">
                        @if($filterMonth || $filterDay)
                            <i data-lucide="info" class="w-4 h-4 inline mr-1 align-middle"></i>
                            Análise de faturamento com filtros aplicados.
                        @else
                            <i data-lucide="chart-bar" class="w-4 h-4 inline mr-1 align-middle"></i>
                            Análise do seu crescimento de faturamento ao longo do tempo.
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ATIVIDADE RECENTE --}}
        <div class="lg:col-span-1 bg-gradient-to-br from-slate-800/60 to-slate-900/40 backdrop-blur-xl border border-white/10 rounded-2xl shadow-xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <i data-lucide="activity" class="w-5 h-5 text-blue-300"></i>
                    Atividade Recente
                </h3>
            </div>
            <div class="p-4 max-h-[480px] overflow-y-auto custom-scrollbar">
                <ul role="list" class="divide-y divide-white/10">
                    @forelse($recentSales as $sale)
                    <li class="py-4 px-2 hover:bg-blue-900/20 rounded-lg transition-colors duration-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500/30 to-cyan-500/30 border border-blue-500/50 rounded-lg flex items-center justify-center">
                                    <i data-lucide="{{ $sale->plan_id ? 'credit-card' : 'package' }}" class="w-5 h-5 text-blue-300"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-100">
                                        @if($sale->plan_id && $sale->plan) {{ $sale->plan->name }}
                                        @elseif($sale->product_id && $sale->product) {{ $sale->product->name }}
                                        @else Venda Realizada @endif
                                    </p>
                                    <p class="text-xs text-gray-400">{{ Str::limit($sale->user->name, 15) }}</p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-2">
                                <p class="text-sm font-bold text-green-400">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</p>
                                <p class="text-xs text-gray-500">{{ $sale->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="py-8 text-center text-gray-500">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                        <p class="text-sm">Nenhuma venda recente.</p>
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE FILTROS (igual à view de vendas) --}}
<div id="filterModal" class="fixed inset-0 bg-black/70 z-50 hidden backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-slate-800 rounded-2xl max-w-lg w-full p-8 shadow-2xl border border-blue-500/20">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Filtros de Receita</h3>
                <button onclick="closeFilterModal()" class="text-gray-400 hover:text-white transition-colors"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <form method="GET" action="{{ route('associacao.dashboard') }}" class="space-y-4">
                <div>
                    <label for="filter_month" class="block text-sm font-medium text-gray-300 mb-1">Mês</label>
                    <select id="filter_month" name="filter_month" class="w-full px-4 py-2.5 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todos os meses</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $filterMonth == $i ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="filter_day" class="block text-sm font-medium text-gray-300 mb-1">Dia</label>
                    <select id="filter_day" name="filter_day" class="w-full px-4 py-2.5 border border-gray-600 rounded-lg bg-gray-700 text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Todos os dias</option>
                        @for($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}" {{ $filterDay == $i ? 'selected' : '' }}>
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="flex justify-end space-x-4 pt-4">
                    <a href="{{ route('associacao.dashboard') }}" class="px-6 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-semibold">Limpar</a>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-500 to-cyan-600 text-white rounded-lg hover:from-blue-600 hover:to-cyan-700 transition-colors font-semibold">Aplicar Filtros</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // Funções do modal de filtro
    function openFilterModal() { 
        document.getElementById('filterModal').classList.remove('hidden'); 
    }
    function closeFilterModal() { 
        document.getElementById('filterModal').classList.add('hidden'); 
    }

    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();

        const revenueLabels = @json($revenueChartData['labels']);
        const revenueData = @json($revenueChartData['data']);

        const formatCurrency = (value) => {
            return 'R$ ' + new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 0 }).format(value);
        };

        const ctxRevenue = document.getElementById('revenueChart');
        if (ctxRevenue) {
            new Chart(ctxRevenue, {
                type: 'line',
                data: {
                    labels: revenueLabels,
                    datasets: [{
                        label: 'Receita Total',
                        data: revenueData,
                        backgroundColor: 'rgba(59, 130, 246, 0.25)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 3,
                        tension: 0.45,
                        fill: true,
                        pointBackgroundColor: 'rgb(59, 130, 246)',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: 'rgb(59, 130, 246)',
                        borderCapStyle: 'round',
                        borderJoinStyle: 'round'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.95)',
                            titleColor: '#93c5fd',
                            bodyColor: '#f0f9ff',
                            borderColor: 'rgba(59, 130, 246, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            callbacks: {
                                label: function(context) {
                                    return formatCurrency(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)',
                                borderColor: 'rgba(255, 255, 255, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#9ca3af',
                                font: { size: 12 }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)',
                                borderColor: 'rgba(255, 255, 255, 0.1)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#9ca3af',
                                font: { size: 12 },
                                callback: function(value) {
                                    return formatCurrency(value);
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush

@endsection
