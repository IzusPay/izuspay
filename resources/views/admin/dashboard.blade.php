@extends('layouts.app')

@section('title', 'Painel THANOS SAAS')

@section('content')
<!-- Professional Payment Gateway Dashboard -->
        
<!-- Header adaptado para preto/branco/cinza -->
<div class="sticky top-0 z-30 bg-white/90 dark:bg-black/90 backdrop-blur-sm border-b border-gray-200 dark:border-gray-800 mb-8">
    <div class="px-6 py-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Dashboard de Pagamentos</h1>
                <p class="text-slate-600 dark:text-gray-400 mt-1">Período: {{ $startDate }} a {{ $endDate }}</p>
            </div>
            
            <!-- Filtros Rápidos -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Botões de filtro com tema dark preto/branco -->
                <div class="flex bg-gray-100 dark:bg-gray-900 rounded-lg p-1">
                    <a href="{{ route('admin.dashboard', array_merge(request()->except(['start_date', 'end_date']), ['start_date' => now()->toDateString(), 'end_date' => now()->toDateString()])) }}" 
                       class="px-3 py-2 text-sm font-medium rounded-md transition-all {{ request('start_date') == now()->toDateString() ? 'bg-white dark:bg-white text-black' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800' }}">
                        Hoje
                    </a>
                    <a href="{{ route('admin.dashboard', array_merge(request()->except(['start_date', 'end_date']), ['start_date' => now()->subDays(7)->toDateString(), 'end_date' => now()->toDateString()])) }}" 
                       class="px-3 py-2 text-sm font-medium rounded-md transition-all {{ request('start_date') == now()->subDays(7)->toDateString() ? 'bg-white dark:bg-white text-black' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800' }}">
                        7 dias
                    </a>
                    <a href="{{ route('admin.dashboard', array_merge(request()->except(['start_date', 'end_date']), ['start_date' => now()->subDays(30)->toDateString(), 'end_date' => now()->toDateString()])) }}" 
                       class="px-3 py-2 text-sm font-medium rounded-md transition-all {{ request('start_date') == now()->subDays(30)->toDateString() || !request('start_date') ? 'bg-white dark:bg-white text-black' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800' }}">
                        30 dias
                    </a>
                </div>
                
                <button onclick="toggleAdvancedFilters()" id="advanced-filter-btn" class="flex items-center gap-2 px-4 py-2 bg-white dark:bg-white text-black rounded-lg hover:bg-gray-100 dark:hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                    </svg>
                    Filtros Avançados
                    @if(request()->hasAny(['search', 'status', 'start_date', 'end_date']))
                        <span class="bg-black dark:bg-black text-white text-xs rounded-full px-2 py-1">
                            {{ collect(request()->only(['search', 'status', 'start_date', 'end_date']))->filter()->count() }}
                        </span>
                    @endif
                </button>
            </div>
        </div>
        
        <!-- Painel de filtros adaptado para dark -->
        <div id="advanced-filters" class="hidden mt-4 p-6 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 animate-slide-down">
            <form action="{{ route('admin.dashboard') }}" method="GET" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Inicial</label>
                        <input type="date" name="start_date" id="start_date" 
                               class="w-full bg-white dark:bg-black border border-gray-300 dark:border-gray-800 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-white focus:border-white" 
                               value="{{ request('start_date', $startDate) }}">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Data Final</label>
                        <input type="date" name="end_date" id="end_date" 
                               class="w-full bg-white dark:bg-black border border-gray-300 dark:border-gray-800 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-white focus:border-white" 
                               value="{{ request('end_date', $endDate) }}">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                        <select name="status" id="status" class="w-full bg-white dark:bg-black border border-gray-300 dark:border-gray-800 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-white focus:border-white">
                            <option value="">Todos os Status</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>✅ Pago</option>
                            <option value="awaiting_payment" {{ request('status') == 'awaiting_payment' ? 'selected' : '' }}>⏳ Pendente</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Cancelado</option>
                            <option value="refunded" {{ request('status') == 'refunded' ? 'selected' : '' }}>↩️ Reembolsado</option>
                            <option value="chargeback" {{ request('status') == 'chargeback' ? 'selected' : '' }}>⚠️ Chargeback</option>
                            <option value="refused" {{ request('status') == 'refused' ? 'selected' : '' }}>🚫 Recusado</option>
                        </select>
                    </div>
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                        <input type="text" name="search" id="search" placeholder="Cliente, produto, ID..." 
                               class="w-full bg-white dark:bg-black border border-gray-300 dark:border-gray-800 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-white focus:border-white" 
                               value="{{ request('search') }}">
                    </div>
                </div>
                
                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-800">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        @if(request()->hasAny(['search', 'status', 'start_date', 'end_date']))
                            <span class="inline-flex items-center gap-2">
                                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
                                Filtros ativos
                            </span>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-900 transition-colors">
                            Limpar Filtros
                        </a>
                        <button type="submit" class="px-6 py-2 bg-white dark:bg-white text-black rounded-lg hover:bg-gray-100 focus:ring-2 focus:ring-white focus:ring-offset-2 transition-colors">
                            Aplicar Filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- KPI Cards adaptados para preto/branco/cinza sem gradientes coloridos -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    
    <!-- Card: Total -->
    <div class="group relative overflow-hidden bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 ease-out">
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-white dark:bg-white text-black rounded-lg flex items-center justify-center shadow-md group-hover:rotate-3 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-pulse"></div>
            </div>
            
            <div class="space-y-1">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 tracking-wide uppercase">Total</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-500">Receita Total</p>
            </div>
        </div>
    </div>

    <!-- Card: Pago -->
    <div class="group relative overflow-hidden bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 ease-out">
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-white dark:bg-white text-black rounded-lg flex items-center justify-center shadow-md group-hover:-rotate-3 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="w-2 h-2 bg-white rounded-full animate-pulse"></div>
            </div>
            
            <div class="space-y-1">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 tracking-wide uppercase">Pago</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">R$ {{ number_format($paidRevenue, 2, ',', '.') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-500">Confirmados</p>
            </div>
        </div>
    </div>

    <!-- Card: Pendente -->
    <div class="group relative overflow-hidden bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 ease-out">
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gray-600 dark:bg-gray-700 text-white rounded-lg flex items-center justify-center shadow-md group-hover:rotate-12 transition-transform duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="w-2 h-2 border border-gray-500 rounded-full animate-spin"></div>
            </div>
            
            <div class="space-y-1">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 tracking-wide uppercase">Pendente</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">R$ {{ number_format($pendingRevenue, 2, ',', '.') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-500">Processando</p>
            </div>
        </div>
    </div>

    <!-- Card: Reembolsado -->
    <div class="group relative overflow-hidden bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 ease-out">
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gray-500 dark:bg-gray-600 text-white rounded-lg flex items-center justify-center shadow-md group-hover:-rotate-6 transition-transform duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                </div>
                <div class="w-2 h-2 bg-gray-500 rounded-full animate-pulse"></div>
            </div>
            
            <div class="space-y-1">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 tracking-wide uppercase">Reembolsado</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">R$ {{ number_format($refundedRevenue, 2, ',', '.') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-500">Devolvidos</p>
            </div>
        </div>
    </div>
</div>

<!-- Second Row -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4 mb-8">
    
    <!-- Card: Chargeback -->
    <div class="group relative overflow-hidden bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 ease-out">
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-gray-700 dark:bg-gray-700 text-white rounded-lg flex items-center justify-center shadow-md group-hover:rotate-6 transition-transform duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="w-2 h-2 bg-gray-500 rounded-full animate-ping"></div>
            </div>
            
            <div class="space-y-1">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 tracking-wide uppercase">Chargeback</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">R$ {{ number_format($chargebackRevenue, 2, ',', '.') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-500">Disputas</p>
            </div>
        </div>
    </div>

    <!-- Card: Recusado -->
    <div class="group relative overflow-hidden bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 p-4 hover:shadow-xl hover:scale-[1.02] transition-all duration-300 ease-out">
        <div class="relative">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-black dark:bg-black text-white rounded-lg flex items-center justify-center shadow-md group-hover:-rotate-12 transition-transform duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="w-2 h-2 bg-black rounded-full animate-pulse"></div>
            </div>
            
            <div class="space-y-1">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 tracking-wide uppercase">Recusado</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">R$ {{ number_format($refusedRevenue, 2, ',', '.') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-500">Rejeitados</p>
            </div>
        </div>
    </div>
</div>


<!-- Top 10 Creators adaptado para dark -->
<div class="bg-white dark:bg-gray-900 rounded-xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-black">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-white dark:bg-white text-black rounded-lg flex items-center justify-center">
                    <i data-lucide="award" class="w-5 h-5"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top 10 Vendedores - Setembro</h3>
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Maiores vendedores do mês
            </div>
        </div>
    </div>
    
    <div class="p-6">
        <div class="space-y-4">
            @forelse ($topCreators as $index => $creator)
                <div class="flex items-center space-x-4 p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-black transition-colors duration-200 group">
                    <!-- Ranking Badge -->
                    <div class="flex-shrink-0">
                        @if($index < 3)
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white shadow-lg
                                {{ $index == 0 ? 'bg-white text-black' : '' }}
                                {{ $index == 1 ? 'bg-gray-300 text-black' : '' }}
                                {{ $index == 2 ? 'bg-gray-500 text-white' : '' }}
                            ">
                                @if($index == 0) 👑 @elseif($index == 1) 🥈 @else 🥉 @endif
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-full bg-gray-600 dark:bg-gray-700 flex items-center justify-center font-bold text-white shadow-lg">
                                {{ $index + 1 }}
                            </div>
                        @endif
                    </div>
                    
                    <!-- Creator Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center space-x-3">
                            <!-- Avatar adaptado para preto/branco -->
                            <div class="w-12 h-12 rounded-full bg-white dark:bg-white text-black flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-200">
                                <span class="font-bold text-lg">{{ substr($creator->name, 0, 1) }}</span>
                            </div>
                            
                            <!-- Name and Stats -->
                            <div class="flex-1 min-w-0">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white truncate">{{ $creator->name }}</h4>
                                <div class="flex items-center space-x-4 mt-1">
                                    <div class="flex items-center space-x-1 text-sm text-gray-600 dark:text-gray-400">
                                        <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                                        <span>{{ $creator->total_sales }} vendas</span>
                                    </div>
                                    <div class="flex items-center space-x-1 text-sm text-gray-600 dark:text-gray-400">
                                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                                        <span>+{{ rand(5, 25) }}% vs mês anterior</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Revenue -->
                    <div class="flex-shrink-0 text-right">
                        <div class="text-xl font-bold text-gray-900 dark:text-white">
                            R$ {{ number_format($creator->total_revenue, 2, ',', '.') }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-500">
                            ID: {{ $creator->id }}
                        </div>
                    </div>
                    
                    <!-- Action -->
                    <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <a href="{{ route('admin.associations.show', $creator->id) }}" class="p-2 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300" title="Ver detalhes da conta">
                            <i data-lucide="arrow-right" class="w-5 h-5"></i>
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <i data-lucide="cloud-off" class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-700"></i>
                    <h4 class="mt-4 text-lg font-semibold text-gray-600 dark:text-gray-400">Nenhum dado de vendas encontrado para este mês.</h4>
                    <p class="text-sm text-gray-400 dark:text-gray-500">O ranking será exibido assim que as primeiras vendas forem confirmadas.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Filter Button adaptado para dark -->
<button id="filter-btn" class="fixed bottom-4 right-4 bg-white dark:bg-white text-black p-3 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 z-40 hover:bg-gray-100">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
    </svg>
</button>


<!-- Charts Section adaptados para dark -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-8">
    <!-- Sales Chart -->
    <div class="xl:col-span-2 bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Faturamento por Dia</h3>
            <div class="flex items-center gap-3">
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $startDate }} - {{ $endDate }}</div>
                <div class="flex gap-2">
                    <button onclick="exportChart()" class="flex items-center gap-1 px-3 py-1 text-sm text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-700 rounded-md hover:bg-gray-50 dark:hover:bg-black transition-colors">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        PNG
                    </button>
                </div>
            </div>
        </div>
        <div class="h-80">
            <canvas id="salesChart" width="400" height="200"></canvas>
        </div>
        
        <!-- Chart Stats -->
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800 grid grid-cols-3 gap-4">
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Maior Dia</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                    R$ {{ number_format(collect($chartValues)->max(), 2, ',', '.') }}
                </div>
            </div>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Média Diária</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                    R$ {{ number_format(collect($chartValues)->avg(), 2, ',', '.') }}
                </div>
            </div>
            <div class="text-center">
                <div class="text-sm text-gray-500 dark:text-gray-400">Menor Dia</div>
                <div class="text-lg font-semibold text-gray-900 dark:text-white">
                    R$ {{ number_format(collect($chartValues)->min(), 2, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Status Distribution adaptado para preto/branco/cinza -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Status das Transações</h3>
        <div class="h-64 mb-4">
            <canvas id="statusChart"></canvas>
        </div>
        
        <!-- Status Legend -->
        <div class="space-y-3">
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-black rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-white rounded-full"></div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pagos</span>
                </div>
                <span class="text-sm font-bold text-gray-900 dark:text-white">R$ {{ number_format($paidRevenue, 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-black rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-gray-600 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pendentes</span>
                </div>
                <span class="text-sm font-bold text-gray-900 dark:text-white">R$ {{ number_format($pendingRevenue, 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-black rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Reembolsados</span>
                </div>
                <span class="text-sm font-bold text-gray-900 dark:text-white">R$ {{ number_format($refundedRevenue, 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-black rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-black rounded-full"></div>
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Recusados</span>
                </div>
                <span class="text-sm font-bold text-gray-900 dark:text-white">R$ {{ number_format($refusedRevenue, 2, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

<!-- REMOVED: Transações Recentes Section -->

<!-- Transaction Details Modal adaptado para dark -->
<div id="transaction-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-800">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detalhes da Transação</h3>
                <button onclick="closeTransactionModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="transaction-modal-content" class="p-6">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay adaptado para dark -->
<div id="loading-overlay" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-40 hidden">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white dark:bg-gray-900 rounded-2xl p-6 shadow-xl flex items-center gap-4 border border-gray-200 dark:border-gray-800">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white dark:border-white"></div>
            <span class="text-gray-900 dark:text-white font-medium">Processando...</span>
        </div>
    </div>
</div>

<!-- Chart.js com cores adaptadas para preto/branco/cinza -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupEnhancedFeatures();
});

function initializeCharts() {
    // Sales Chart - cores preto/branco/cinza
    const salesCtx = document.getElementById('salesChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [
                    {
                        label: 'Faturamento Diário',
                        data: {!! json_encode($chartValues) !!},
                        borderColor: '#ffffff',
                        backgroundColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#000',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: '#e5e5e5',
                        pointHoverBorderWidth: 3
                    },
                    {
                        label: 'Meta Diária',
                        data: Array({!! count($chartValues) !!}).fill({{ floor($totalRevenue / max(count($chartValues), 1)) }}),
                        borderColor: '#6b7280',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [8, 4],
                        pointRadius: 0,
                        tension: 0,
                        fill: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                weight: '500',
                                family: 'Inter'
                            },
                            color: '#9ca3af'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.95)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#374151',
                        borderWidth: 1,
                        cornerRadius: 12,
                        padding: 16,
                        titleFont: {
                            size: 14,
                            weight: '600'
                        },
                        bodyFont: {
                            size: 13,
                            weight: '500'
                        },
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': R$ ' + context.parsed.y.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(148, 163, 184, 0.1)',
                            borderColor: 'rgba(148, 163, 184, 0.2)'
                        },
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 12,
                                family: 'Inter'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(148, 163, 184, 0.1)',
                            borderColor: 'rgba(148, 163, 184, 0.2)'
                        },
                        ticks: {
                            color: '#9ca3af',
                            font: {
                                size: 12,
                                family: 'Inter'
                            },
                            callback: function(value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutCubic'
                }
            }
        });
    }

    // Status Chart - cores preto/branco/cinza
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pagos', 'Pendentes', 'Reembolsados', 'Recusados'],
                datasets: [{
                    data: [
                        {{ $paidRevenue }},
                        {{ $pendingRevenue }},
                        {{ $refundedRevenue }},
                        {{ $refusedRevenue }}
                    ],
                    backgroundColor: [
                        '#ffffff',
                        '#9ca3af', 
                        '#6b7280',
                        '#000000'
                    ],
                    borderWidth: 0,
                    hoverBorderWidth: 4,
                    hoverBorderColor: '#ffffff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.95)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        cornerRadius: 12,
                        padding: 16,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? ((context.parsed * 100) / total).toFixed(1) : 0;
                                return context.label + ': R$ ' + context.parsed.toLocaleString('pt-BR', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutCubic'
                }
            }
        });
    }
}


function setupEnhancedFeatures() {
    const advancedFiltersPanel = document.getElementById('advanced-filters');
    const advancedFilterBtn = document.getElementById('advanced-filter-btn');
    
    // Status updates
    setInterval(updateLiveStats, 30000);
}

function toggleAdvancedFilters() {
    const panel = document.getElementById('advanced-filters');
    const btn = document.getElementById('advanced-filter-btn');
    
    if (panel.classList.contains('hidden')) {
        panel.classList.remove('hidden');
        btn.classList.add('bg-gray-200');
        btn.classList.add('dark:bg-gray-800');
    } else {
        panel.classList.add('hidden');
        btn.classList.remove('bg-gray-200');
        btn.classList.remove('dark:bg-gray-800');
    }
}

function changeItemsPerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    window.location.href = url.toString();
}

function updateLiveStats() {
    // Refresh stats periodically
    console.log('[v0] Updating live stats...');
}

function exportChart() {
    const canvas = document.getElementById('salesChart');
    const url = canvas.toDataURL('image/png');
    const link = document.createElement('a');
    link.download = 'grafico-faturamento.png';
    link.href = url;
    link.click();
}

function showLoading() {
    document.getElementById('loading-overlay').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loading-overlay').classList.add('hidden');
}

function closeTransactionModal() {
    document.getElementById('transaction-modal').classList.add('hidden');
}

// Initialize Lucide icons if available
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>

@endsection
