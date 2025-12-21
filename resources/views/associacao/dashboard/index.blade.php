@extends('layouts.app')

@section('title', 'Dashboard - Izus Payment')

@push('styles')
@endpush

@section('content')
<div class="container mx-auto p-4 lg:p-8 space-y-6 text-slate-800 dark:text-slate-200">
    
    {{-- Header --}}
       

    {{-- Primeira Linha de Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white dark:bg-black p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-2 mb-3 text-slate-500">
                <i data-lucide="qr-code" class="w-4 h-4"></i>
                <span class="text-sm font-medium">Pix (Total)</span>
            </div>
            <p class="text-2xl font-bold text-slate-900 dark:text-white">R$ {{ number_format($totalRevenue ?? 20, 2, ',', '.') }}</p>
        </div>

        <div class="bg-slate-900 dark:bg-black p-5 rounded-xl shadow-lg border border-slate-800 flex flex-col justify-between">
            <div class="flex items-center gap-2 text-slate-400">
                <i data-lucide="wallet" class="w-4 h-4"></i>
                <span class="text-sm font-medium">Saldo disponível</span>
            </div>
            <p class="text-2xl font-bold text-white mt-2">R$ {{ number_format($saldo ?? 0.73, 2, ',', '.') }}</p>
        </div>
    </div>

    {{-- Segunda Linha: Gráfico e Perfil --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-black p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Gráfico de Vendas <span class="text-slate-400 font-normal">(Últimos 30 Dias)</span></h3>
                <button class="text-xs text-slate-400 dark:text-slate-300 flex items-center gap-1"><i data-lucide="eye-off" class="w-3 h-3"></i> Ocultar</button>
            </div>
            <div class="h-[250px]">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-black p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center border border-slate-100 dark:border-slate-700 mb-4">
                <i data-lucide="building-2" class="w-10 h-10 text-slate-400"></i>
            </div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ auth()->user()->company_name ?? 'ArMatch' }}</h2>
            <p class="text-xs text-slate-400 dark:text-slate-400 mb-6">CNPJ: {{ auth()->user()->cnpj ?? '34562746000100' }}</p>
            
            <div class="grid grid-cols-2 w-full pt-4 border-t border-slate-50 dark:border-slate-800">
                <div>
                    <p class="text-xs text-slate-400 dark:text-slate-400">Afiliados</p>
                    <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $afiliados ?? 0 }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 dark:text-slate-400">Transações</p>
                    <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $transacoes ?? 18 }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Terceira Linha: Relatório Diário e Ticket Médio --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-3 bg-white dark:bg-black p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300">Relatório Diário</h3>
                <button class="text-xs text-slate-400 dark:text-slate-300 flex items-center gap-1"><i data-lucide="eye-off" class="w-3 h-3"></i> Ocultar</button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-100 dark:border-slate-700">
                    <p class="text-xs text-slate-500 dark:text-slate-300 mb-1">Pix (Hoje)</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-white">R$ {{ number_format($pixHoje ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-100 dark:border-slate-700">
                    <p class="text-xs text-slate-500 dark:text-slate-300 mb-1">Cartão (Hoje)</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-white">R$ {{ number_format($cartaoHoje ?? 0, 2, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-100 dark:border-slate-700">
                    <p class="text-xs text-slate-500 dark:text-slate-300 mb-1">Boleto (Hoje)</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-white">R$ {{ number_format($boletoHoje ?? 0, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-black p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-300 mb-1 text-center">Ticket médio</p>
            <p class="text-2xl font-bold text-slate-900 dark:text-white text-center mb-4">R$ {{ number_format($averageTicket ?? 45.42, 2, ',', '.') }}</p>
            <div class="flex items-end justify-center gap-1 h-16">
                <div class="w-2 bg-slate-200 rounded-t h-[40%]"></div>
                <div class="w-2 bg-slate-800 rounded-t h-[80%]"></div>
                <div class="w-2 bg-slate-400 rounded-t h-[60%]"></div>
                <div class="w-2 bg-slate-900 rounded-t h-[100%]"></div>
                <div class="w-2 bg-slate-300 rounded-t h-[50%]"></div>
            </div>
        </div>
    </div>
</div>

{{-- Modal de Filtros --}}
<div id="filterModal" class="fixed inset-0 bg-slate-900/40 dark:bg-black/60 z-50 hidden backdrop-blur-sm transition-opacity">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-black rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Filtrar Dados</h3>
                <button onclick="closeFilterModal()" class="text-slate-400 dark:text-slate-300 hover:text-slate-600 dark:hover:text-white transition-colors"><i data-lucide="x" class="w-5 h-5"></i></button>
            </div>
            <form method="GET" action="{{ route('associacao.dashboard') }}" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-300 mb-1 uppercase">Mês</label>
                    <select name="filter_month" class="w-full bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 rounded-lg text-sm focus:ring-slate-500 focus:border-slate-500 dark:focus:ring-slate-400 dark:focus:border-slate-400">
                        <option value="">Todos os meses</option>
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ request('filter_month') == $i ? 'selected' : '' }}>{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
                        @endfor
                    </select>
                </div>
                <div class="flex gap-3 pt-4">
                    <a href="{{ route('associacao.dashboard') }}" class="flex-1 text-center py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-200 rounded-lg text-sm font-semibold">Limpar</a>
                    <button type="submit" class="flex-1 py-2 bg-slate-900 dark:bg-white text-white dark:text-black rounded-lg text-sm font-semibold">Aplicar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script type="application/json" id="revenueLabels">@json($revenueChartData['labels'] ?? ['1'])</script>
<script type="application/json" id="revenueData">@json($revenueChartData['data'] ?? [2])</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    function openFilterModal() { document.getElementById('filterModal').classList.remove('hidden'); }
    function closeFilterModal() { document.getElementById('filterModal').classList.add('hidden'); }

    document.addEventListener('DOMContentLoaded', () => {
        lucide.createIcons();

        const ctx = document.getElementById('revenueChart').getContext('2d');
        const revenueLabels = JSON.parse(document.getElementById('revenueLabels')?.textContent || '[]');
        const revenueData = JSON.parse(document.getElementById('revenueData')?.textContent || '[]');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Vendas',
                    data: revenueData,
                    borderColor: '#1e293b',
                    backgroundColor: 'rgba(30, 41, 59, 0.05)',
                    borderWidth: 2,
                    tension: 0, // Linhas retas como na imagem
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#1e293b',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { borderDash: [5, 5], color: '#f1f5f9' },
                        ticks: { font: { size: 10 }, color: '#94a3b8' }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#94a3b8' }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
