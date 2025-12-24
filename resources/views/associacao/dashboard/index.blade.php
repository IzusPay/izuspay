@extends('layouts.app')

@section('title', 'Dashboard - Izus Payment')

@push('styles')
@endpush

@section('content')
<div x-data="{ showRevenueChart: true, showRelatorioDiario: true }" x-init="showRevenueChart = localStorage.getItem('assoc_showRevenueChart') !== '0'; showRelatorioDiario = localStorage.getItem('assoc_showRelatorioDiario') !== '0'" class="min-h-screen w-full max-w-7xl mx-auto p-6 lg:p-10 space-y-6 text-white dashboard-bg" style="background: linear-gradient(135deg, #0e131f 0%, #1b1724 50%, #0e131f 100%);">
    
    {{-- Header --}}
       

    {{-- Banner + Métricas (70/30) --}}
    <div class="grid grid-cols-1 lg:grid-cols-10 gap-6">
        {{-- Carrossel de Banners (70%) --}}
        <div class="lg:col-span-7 p-0 rounded-2xl border border-slate-800 shadow-xl overflow-hidden ring-1 ring-white/10 hover:ring-[var(--accent)]/40 transition cursor-pointer card-bg" style="background: linear-gradient(135deg, #0e131f 0%, #1b1724 50%, #0e131f 100%); box-shadow: inset 0 0 0 1px rgba(255,255,255,0.03);">
            <div class="relative h-full">
                <div id="bannerCarousel" class="w-full h-full overflow-hidden">
                    <div class="whitespace-nowrap transition-transform duration-500 ease-in-out" x-data="{ index: 0, total: {{ count($banners) }} }" x-init="
                        setInterval(() => { if (total > 1) { index = (index + 1) % total; updateCarousel(index); } }, 5000);
                        window.updateCarousel = (i) => {
                            const track = document.getElementById('bannerTrack');
                            if (track) track.style.transform = `translateX(-${i * 100}%)`;
                        };
                    ">
                        <div id="bannerTrack" class="flex w-full h-full">
                            @forelse($banners as $banner)
                                <a href="{{ $banner->link ?? '#' }}" class="block w-full h-full flex-shrink-0">
                                    <img src="{{ $banner->image_url }}" alt="{{ $banner->name }}" class="w-full h-full object-cover object-center">
                                </a>
                            @empty
                                <div class="w-full h-full flex items-center justify-center bg-[#0f172a]">
                                    <div class="text-center">
                                        <i data-lucide="image-off" class="w-10 h-10 text-slate-400 mx-auto mb-2"></i>
                                        <p class="text-white/70">Nenhum banner cadastrado</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                    @if(count($banners) > 1)
                    <div class="absolute inset-y-0 left-0 flex items-center">
                        <button type="button" class="m-3 p-2 rounded-full bg-white/70 dark:bg-black/50 border border-slate-200 dark:border-slate-700 hover:bg-white dark:hover:bg-black" onclick="prevBanner()">
                            <i data-lucide="chevron-left" class="w-5 h-5"></i>
                        </button>
                    </div>
                    <div class="absolute inset-y-0 right-0 flex items-center">
                        <button type="button" class="m-3 p-2 rounded-full bg-white/70 dark:bg-black/50 border border-slate-200 dark:border-slate-700 hover:bg-white dark:hover:bg-black" onclick="nextBanner()">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        {{-- Métricas (30%) --}}
        <div class="lg:col-span-3 space-y-6">
            <div class="p-5 rounded-2xl shadow-lg border border-slate-800 flex items-center justify-between ring-1 ring-white/10 hover:ring-[var(--accent)]/40 transition cursor-pointer active:scale-[0.98] card-bg" style="background: linear-gradient(135deg, #0e131f 0%, #1b1724 50%, #0e131f 100%);">
                <div class="flex items-center gap-2 mb-3 text-white/70">
                    <i data-lucide="qr-code" class="w-4 h-4"></i>
                    <span class="text-sm font-medium">Receita Total</span>
                </div>
                <p class="text-2xl font-bold text-white">R$ {{ number_format($totalRevenue, 2, ',', '.') }}</p>
            </div>
            <div class="p-5 rounded-2xl shadow-lg border border-slate-800 flex items-center justify-between ring-1 ring-white/10 hover:ring-[var(--accent)]/40 transition cursor-pointer active:scale-[0.98] card-bg" style="background: linear-gradient(135deg, #0e131f 0%, #1b1724 50%, #0e131f 100%);">
                <div class="flex items-center gap-2 text-white/70">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    <span class="text-sm font-medium">Saldo disponível</span>
                </div>
                <p class="text-2xl font-bold text-white mt-2">R$ {{ number_format($saldo, 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    {{-- Segunda Linha: Gráfico e Perfil --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 p-6 rounded-2xl border border-slate-800 shadow-lg ring-1 ring-white/10 hover:ring-[var(--accent)]/40 transition cursor-pointer active:scale-[0.99] card-bg" style="background: linear-gradient(135deg, #0e131f 0%, #1b1724 50%, #0e131f 100%);">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-sm font-semibold text-white">Gráfico de Vendas <span class="text-white/50 font-normal">(Últimos 30 Dias)</span></h3>
            </div>
            <div class="h-[250px]" x-show="showRevenueChart">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <div class="p-6 rounded-2xl border border-slate-800 shadow-lg flex flex-col items-center justify-center text-center ring-1 ring-white/10 hover:ring-[var(--accent)]/40 transition cursor-pointer active:scale-[0.99] card-bg" style="background: linear-gradient(135deg, #0e131f 0%, #1b1724 50%, #0e131f 100%);">
            <div class="w-20 h-20 rounded-full flex items-center justify-center border border-slate-700 mb-4 bg-[#0b1220]">
                <i data-lucide="building-2" class="w-10 h-10 text-white/70"></i>
            </div>
            <h2 class="text-xl font-bold text-white">{{ request()->user()->company_name ?? 'Associação' }}</h2>
            <p class="text-xs text-white/60 mb-6">CNPJ: {{ request()->user()->cnpj ?? '—' }}</p>
            
            <div class="grid grid-cols-2 w-full pt-4 border-t border-white/10">
                <div>
                    <p class="text-xs text-white/60">Afiliados</p>
                    <p class="text-lg font-bold text-white">{{ $afiliados }}</p>
                </div>
                <div>
                    <p class="text-xs text-white/60">Transações</p>
                    <p class="text-lg font-bold text-white">{{ $transacoes }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Terceira Linha: Relatório Diário e Ticket Médio --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <div class="lg:col-span-3 p-6 rounded-2xl border border-slate-800 shadow-lg ring-1 ring-white/10 hover:ring-[var(--accent)]/40 transition cursor-pointer active:scale-[0.99] card-bg" style="background: linear-gradient(135deg, #0e131f 0%, #1b1724 50%, #0e131f 100%);">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-sm font-semibold text-white">Relatório Diário</h3>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" x-show="showRelatorioDiario">
                <div class="p-4 rounded-xl border border-slate-700 shadow-lg ring-1 ring-white/10 hover:ring-[var(--accent)]/40 transition cursor-pointer active:scale-[0.98] card-bg" style="background: linear-gradient(135deg, #0e131f 0%, #1b1724 50%, #0e131f 100%);">
                    <p class="text-xs text-white/70 mb-1">Pix (Hoje)</p>
                    <p class="text-xl font-bold text-white">R$ {{ number_format($pixHoje, 2, ',', '.') }}</p>
                </div>
                <div class="p-4 rounded-xl border border-slate-700 shadow-lg ring-1 ring-white/10 hover:ring-[var(--accent)]/40 transition cursor-pointer active:scale-[0.98] card-bg" style="background: linear-gradient(135deg, #0e131f 0%, #1b1724 50%, #0e131f 100%);">
                    <p class="text-xs text-white/70 mb-1">Cartão (Hoje)</p>
                    <p class="text-xl font-bold text-white">R$ {{ number_format($cartaoHoje, 2, ',', '.') }}</p>
                </div>
                <div class="p-4 rounded-xl border border-slate-700 shadow-lg ring-1 ring-white/10 hover:ring-[var(--accent)]/40 transition cursor-pointer active:scale-[0.98] card-bg" style="background: linear-gradient(135deg, #0e131f 0%, #1b1724 50%, #0e131f 100%);">
                    <p class="text-xs text-white/70 mb-1">Boleto (Hoje)</p>
                    <p class="text-xl font-bold text-white">R$ {{ number_format($boletoHoje, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="p-6 rounded-2xl border border-slate-800 shadow-lg ring-1 ring-white/10 hover:ring-[var(--accent)]/40 transition cursor-pointer active:scale-[0.99] card-bg" style="background: linear-gradient(135deg, #0e131f 0%, #1b1724 50%, #0e131f 100%);">
            <p class="text-xs text-white/70 mb-1 text-center">Ticket médio</p>
            <p class="text-2xl font-bold text-white text-center mb-4">R$ {{ number_format($averageTicket, 2, ',', '.') }}</p>
            <div class="flex items-end justify-center gap-1 h-16">
                <div class="w-2 rounded-t h-[40%]" style="background: linear-gradient(180deg, #22d3ee, #7c3aed);"></div>
                <div class="w-2 rounded-t h-[80%]" style="background: linear-gradient(180deg, #a78bfa, #22d3ee);"></div>
                <div class="w-2 rounded-t h-[60%]" style="background: linear-gradient(180deg, #7c3aed, #a78bfa);"></div>
                <div class="w-2 rounded-t h-[100%]" style="background: linear-gradient(180deg, #22d3ee, #a21caf);"></div>
                <div class="w-2 rounded-t h-[50%]" style="background: linear-gradient(180deg, #a78bfa, #22d3ee);"></div>
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
<script>
    let currentBannerIndex = 0;
    const getTotalBanners = () => {{ count($banners) }};
    function nextBanner() {
        const total = getTotalBanners();
        if (total < 2) return;
        currentBannerIndex = (currentBannerIndex + 1) % total;
        window.updateCarousel(currentBannerIndex);
    }
    function prevBanner() {
        const total = getTotalBanners();
        if (total < 2) return;
        currentBannerIndex = (currentBannerIndex - 1 + total) % total;
        window.updateCarousel(currentBannerIndex);
    }
</script>
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
                    borderColor: '#ff4d8d',
                    backgroundColor: 'rgba(255, 77, 141, 0.08)',
                    borderWidth: 3,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#ff4d8d',
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
                        grid: { borderDash: [5, 5], color: '#1f2937' },
                        ticks: { font: { size: 10 }, color: '#9ca3af' }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { font: { size: 10 }, color: '#9ca3af' }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
