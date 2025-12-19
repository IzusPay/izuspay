@extends('layouts.app')

@section('title', 'Dashboard - Izus Payment')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    /* Atualizado para usar azul neon ao invés de rosa */
    .card-metric {
        transition: all 0.3s ease;
        background: linear-gradient(135deg, rgba(6, 182, 212, 0.05) 0%, rgba(59, 130, 246, 0.05) 100%);
    }
    
    .card-metric:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(6, 182, 212, 0.2);
    }
    
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(6, 182, 212, 0.3);
        border-radius: 10px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(6, 182, 212, 0.5);
    }

    .filter-input {
        background: #1a1a1a;
        border: 1px solid rgba(6, 182, 212, 0.2);
        transition: all 0.3s ease;
    }

    .filter-input:focus {
        background: #1f1f1f;
        border-color: rgba(6, 182, 212, 0.5);
        box-shadow: 0 0 20px rgba(6, 182, 212, 0.2);
    }

    .circular-progress {
        transform: rotate(-90deg);
    }
</style>
@endpush

@section('content')
<div class="space-y-8">
    
    {{-- Cabeçalho com nome, documento e avatar com iniciais em azul neon --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-white">
                Bem-vindo, <span class="text-cyan-400">{{ auth()->user()->name ?? 'Usuário' }}</span>
            </h2>
        </div>
        <div class="flex items-center gap-4">
            {{-- Data --}}
            <div class="flex items-center gap-3 bg-cyan-500/10 border border-cyan-400/20 rounded-lg px-4 py-2">
                <i data-lucide="calendar" class="w-5 h-5 text-cyan-400"></i>
                <span class="text-sm text-white font-medium">{{ date('d/m/Y - H:i') }}</span>
            </div>
            
            {{-- Perfil do usuário --}}
            <div class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-lg px-4 py-2">
                <div>
                    <p class="text-sm font-semibold text-white text-right">{{ auth()->user()->name ?? 'Usuário' }}</p>
                    <p class="text-xs text-gray-400 text-right">
                        CPF: {{ auth()->user()->cpf ?? '000.000.000-00' }}
                    </p>
                </div>
                {{-- Avatar com iniciais em azul neon --}}
                <div class="w-10 h-10 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full flex items-center justify-center">
                    <span class="text-sm font-bold text-white">
                        {{ strtoupper(substr(auth()->user()->name ?? 'VD', 0, 2)) }}
                    </span>
                </div>
            </div>
        </div>
    </div>
   
    {{-- Cards com bordas azul neon --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- Card: Minhas Vendas --}}
        <div class="card-metric bg-[#1a1a1a] border border-cyan-400/20 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-cyan-500/20 rounded-lg flex items-center justify-center">
                    <i data-lucide="shopping-cart" class="w-6 h-6 text-cyan-400"></i>
                </div>
            </div>
            <p class="text-gray-400 text-sm font-medium mb-2">Minhas Vendas</p>
            <p class="text-3xl font-bold text-white">
                R$ {{ number_format($totalRevenue ?? 0, 2, ',', '.') }}
            </p>
        </div>

        {{-- Card: Ticket Médio --}}
        <div class="card-metric bg-[#1a1a1a] border border-cyan-400/20 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-cyan-500/20 rounded-lg flex items-center justify-center">
                    <i data-lucide="target" class="w-6 h-6 text-cyan-400"></i>
                </div>
            </div>
            <p class="text-gray-400 text-sm font-medium mb-2">Ticket médio</p>
            <p class="text-3xl font-bold text-white">
                R$ {{ number_format($averageTicket ?? 0, 2, ',', '.') }}
            </p>
        </div>

        {{-- Card: Taxa de Aprovação --}}
        <div class="card-metric bg-[#1a1a1a] border border-cyan-400/20 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-cyan-500/20 rounded-lg flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-6 h-6 text-cyan-400"></i>
                </div>
            </div>
            <p class="text-gray-400 text-sm font-medium mb-2">Taxa de Aprovação</p>
            <p class="text-3xl font-bold text-white">
                {{ $conversionRate }}%
            </p>
        </div>
    </div>

    {{-- GRÁFICO E ÍNDICES --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Gráfico com filtros funcionais --}}
        <div class="lg:col-span-2">
            <div class="relative bg-[#1a1a1a] border border-cyan-400/20 rounded-2xl p-8 shadow-2xl">
                <div class="relative z-10">
                    {{-- Cabeçalho com filtro --}}
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white flex items-center gap-2">
                                Gráfico de receitas
                                @if(request('filter_month') || request('filter_day'))
                                    <span class="text-sm font-normal text-gray-400">(Filtrado)</span>
                                @endif
                            </h3>
                            <div class="flex items-center gap-4 mt-2 text-xs">
                                <button class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <span>Pagos</span>
                                </button>
                                <button class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <span>Pendentes</span>
                                </button>
                                <button class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                    <span>Estornados</span>
                                </button>
                            </div>
                        </div>
                        {{-- Botão de filtro --}}
                        <button onclick="openFilterModal()" class="flex items-center space-x-2 text-gray-300 hover:text-white bg-cyan-500/10 hover:bg-cyan-500/20 px-4 py-2 rounded-lg transition-colors border border-cyan-400/20">
                            <i data-lucide="filter" class="w-4 h-4"></i>
                            <span>Filtros</span>
                        </button>
                    </div>

                    {{-- Status dos filtros aplicados --}}
                    @if(request('filter_month') || request('filter_day'))
                    <div class="mb-4 p-3 bg-cyan-500/10 border border-cyan-400/20 rounded-lg text-xs text-gray-300">
                        <i data-lucide="info" class="w-4 h-4 inline mr-2 align-middle text-cyan-400"></i>
                        <span class="font-semibold">Filtros aplicados:</span>
                        @if(request('filter_month')) 
                            <span class="ml-2">Mês: {{ DateTime::createFromFormat('!m', request('filter_month'))->format('F') }}</span>
                        @endif
                        @if(request('filter_day'))
                            <span class="ml-2">Dia: {{ str_pad(request('filter_day'), 2, '0', STR_PAD_LEFT) }}</span>
                        @endif
                    </div>
                    @endif

                    {{-- CANVAS DO GRÁFICO --}}
                    <div class="h-[400px] bg-black/30 rounded-xl p-4 border border-cyan-400/10">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Índices com azul neon --}}
        <div class="lg:col-span-1 bg-[#1a1a1a] border border-cyan-400/20 rounded-2xl shadow-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-6">Índices</h3>
            
            <div class="space-y-6">
                {{-- Cartão --}}
                <div class="flex items-center gap-4">
                    <div class="relative w-16 h-16">
                        <svg class="w-16 h-16 circular-progress">
                            <circle cx="32" cy="32" r="28" stroke="rgba(6, 182, 212, 0.2)" stroke-width="4" fill="none" />
                            <circle cx="32" cy="32" r="28" stroke="rgb(6, 182, 212)" stroke-width="4" fill="none" 
                                    stroke-dasharray="176" stroke-dashoffset="176" stroke-linecap="round" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-bold text-white">0%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Cartão</p>
                        <p class="text-xs text-gray-400">R$ 0,00</p>
                    </div>
                </div>

                {{-- PIX --}}
                <div class="flex items-center gap-4">
                    <div class="relative w-16 h-16">
                        <svg class="w-16 h-16 circular-progress">
                            <circle cx="32" cy="32" r="28" stroke="rgba(6, 182, 212, 0.2)" stroke-width="4" fill="none" />
                            <circle cx="32" cy="32" r="28" stroke="rgb(6, 182, 212)" stroke-width="4" fill="none" 
                                    stroke-dasharray="176" stroke-dashoffset="{{ 176 - (176 * ($conversionRate / 100)) }}" stroke-linecap="round" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-bold text-white">{{ $conversionRate }}%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">PIX</p>
                        <p class="text-xs text-gray-400">R$ {{ number_format($totalRevenue ?? 0, 2, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Boleto --}}
                <div class="flex items-center gap-4">
                    <div class="relative w-16 h-16">
                        <svg class="w-16 h-16 circular-progress">
                            <circle cx="32" cy="32" r="28" stroke="rgba(6, 182, 212, 0.2)" stroke-width="4" fill="none" />
                            <circle cx="32" cy="32" r="28" stroke="rgb(6, 182, 212)" stroke-width="4" fill="none" 
                                    stroke-dasharray="176" stroke-dashoffset="176" stroke-linecap="round" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-bold text-white">0%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Boleto</p>
                        <p class="text-xs text-gray-400">R$ 0,00</p>
                    </div>
                </div>

                {{-- Estornos --}}
                <div class="flex items-center gap-4">
                    <div class="relative w-16 h-16">
                        <svg class="w-16 h-16 circular-progress">
                            <circle cx="32" cy="32" r="28" stroke="rgba(6, 182, 212, 0.2)" stroke-width="4" fill="none" />
                            <circle cx="32" cy="32" r="28" stroke="rgb(6, 182, 212)" stroke-width="4" fill="none" 
                                    stroke-dasharray="176" stroke-dashoffset="176" stroke-linecap="round" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-bold text-white">0%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Estornos</p>
                        <p class="text-xs text-gray-400">R$ 0,00</p>
                    </div>
                </div>

                {{-- Chargeback --}}
                <div class="flex items-center gap-4">
                    <div class="relative w-16 h-16">
                        <svg class="w-16 h-16 circular-progress">
                            <circle cx="32" cy="32" r="28" stroke="rgba(6, 182, 212, 0.2)" stroke-width="4" fill="none" />
                            <circle cx="32" cy="32" r="28" stroke="rgb(6, 182, 212)" stroke-width="4" fill="none" 
                                    stroke-dasharray="176" stroke-dashoffset="176" stroke-linecap="round" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-bold text-white">0%</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Chargeback</p>
                        <p class="text-xs text-gray-400">R$ 0,00</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de filtros funcionais --}}
<div id="filterModal" class="fixed inset-0 bg-black/80 z-50 hidden backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-[#1a1a1a] rounded-2xl max-w-lg w-full p-8 shadow-2xl border border-cyan-400/20">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white">Filtros de Receita</h3>
                <button onclick="closeFilterModal()" class="text-gray-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            <form method="GET" action="{{ route('associacao.dashboard') }}" class="space-y-4">
                <div>
                    <label for="filter_month" class="block text-sm font-medium text-gray-300 mb-2">Mês</label>
                    <select id="filter_month" name="filter_month" class="filter-input w-full px-4 py-2.5 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        <option value="">Todos os meses</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ request('filter_month') == $i ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="filter_day" class="block text-sm font-medium text-gray-300 mb-2">Dia</label>
                    <select id="filter_day" name="filter_day" class="filter-input w-full px-4 py-2.5 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        <option value="">Todos os dias</option>
                        @for($i = 1; $i <= 31; $i++)
                            <option value="{{ $i }}" {{ request('filter_day') == $i ? 'selected' : '' }}>
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="flex justify-end space-x-4 pt-4">
                    <a href="{{ route('associacao.dashboard') }}" class="px-6 py-2.5 bg-white/10 text-white rounded-lg hover:bg-white/20 transition-colors font-semibold border border-white/20">Limpar</a>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-cyan-400 to-blue-500 text-white rounded-lg hover:from-cyan-500 hover:to-blue-600 transition-colors font-semibold">Aplicar Filtros</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // Funções do modal de filtro funcionais
    function openFilterModal() { 
        document.getElementById('filterModal').classList.remove('hidden');
        setTimeout(() => lucide.createIcons(), 50);
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
            const ctx = ctxRevenue.getContext('2d');
            
            // Gradiente laranja/amarelo
            const gradientOrange = ctx.createLinearGradient(0, 0, 0, 400);
            gradientOrange.addColorStop(0, 'rgba(251, 191, 36, 0.8)');
            gradientOrange.addColorStop(0.5, 'rgba(245, 158, 11, 0.6)');
            gradientOrange.addColorStop(1, 'rgba(217, 119, 6, 0.3)');
            
            // Gradiente rosa/vermelho
            const gradientPink = ctx.createLinearGradient(0, 0, 0, 400);
            gradientPink.addColorStop(0, 'rgba(236, 72, 153, 0.8)');
            gradientPink.addColorStop(0.5, 'rgba(219, 39, 119, 0.6)');
            gradientPink.addColorStop(1, 'rgba(190, 24, 93, 0.3)');

            new Chart(ctxRevenue, {
                type: 'line',
                data: {
                    labels: revenueLabels,
                    datasets: [
                        {
                            label: 'Pagos',
                            data: revenueData,
                            backgroundColor: gradientOrange,
                            borderColor: 'rgb(251, 191, 36)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: 'rgb(251, 191, 36)',
                            pointBorderColor: '#000',
                            pointBorderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                        },
                        {
                            label: 'Pendentes',
                            data: revenueData.map(v => v * 0.4),
                            backgroundColor: gradientPink,
                            borderColor: 'rgb(236, 72, 153)',
                            borderWidth: 2,
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: 'rgb(236, 72, 153)',
                            pointBorderColor: '#000',
                            pointBorderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.9)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: 'rgba(6, 182, 212, 0.5)',
                            borderWidth: 1,
                            padding: 12,
                            cornerRadius: 8,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + formatCurrency(context.parsed.y);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)',
                                borderColor: 'transparent',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#6b7280',
                                font: { size: 11 }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(255, 255, 255, 0.05)',
                                borderColor: 'transparent',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#6b7280',
                                font: { size: 11 },
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
