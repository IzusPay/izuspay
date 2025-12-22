
<div class="space-y-8">
    {{-- CARDS DE MÉTRICAS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @php
            $metrics = [
                ['title' => 'Saldo Disponível', 'value' => 'R$ ' . number_format(($balanceDetails['available'] ?? 0), 2, ',', '.'), 'icon' => 'wallet', 'color' => 'green-emerald'],
                ['title' => 'Receita Total', 'value' => 'R$ ' . number_format($totalRevenue, 2, ',', '.'), 'icon' => 'bar-chart-2', 'color' => 'blue-cyan'],
                ['title' => 'Saques Pendentes', 'value' => 'R$ ' . number_format($pendingWithdrawals, 2, ',', '.'), 'icon' => 'hourglass', 'color' => 'orange-amber'],
                ['title' => 'Vendas Pendentes', 'value' => 'R$ ' . number_format($pendingRevenue, 2, ',', '.'), 'icon' => 'clock', 'color' => 'red-rose'],
            ];
            $colors = [
                'green-emerald' => ['from' => 'from-green-500', 'to' => 'to-emerald-500', 'text' => 'text-green-400'],
                'blue-cyan' => ['from' => 'from-blue-500', 'to' => 'to-cyan-500', 'text' => 'text-blue-300'],
                'orange-amber' => ['from' => 'from-orange-500', 'to' => 'to-amber-500', 'text' => 'text-orange-400'],
                'red-rose' => ['from' => 'from-red-500', 'to' => 'to-rose-500', 'text' => 'text-red-400'],
            ];
        @endphp

        @foreach($metrics as $metric)
        <div class="relative bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl p-6 hover:-translate-y-1 transition-transform duration-300 overflow-hidden">
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-gradient-to-br {{ $colors[$metric['color']]['from'] }} {{ $colors[$metric['color']]['to'] }} opacity-10 blur-2xl"></div>
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium {{ $colors[$metric['color']]['text'] }} mb-1">{{ $metric['title'] }}</p>
                    <p class="text-3xl font-bold text-white">{{ $metric['value'] }}</p>
                </div>
                <div class="w-14 h-14 bg-black/20 border border-white/10 rounded-2xl flex items-center justify-center shadow-lg">
                    <i data-lucide="{{ $metric['icon'] }}" class="w-7 h-7 {{ $colors[$metric['color']]['text'] }}"></i>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- LISTA DE VENDAS RECENTES --}}
    <div class="bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl">
        <div class="px-6 py-4 border-b border-white/10">
            <h3 class="text-lg font-semibold text-white flex items-center">
                <i data-lucide="activity" class="w-5 h-5 mr-2 text-blue-400"></i>
                Vendas Recentes
            </h3>
        </div>
        <div class="p-4">
            <ul role="list" class="divide-y divide-white/10">
                @forelse($recentSales as $sale)
                <li class="py-4 px-2 hover:bg-blue-900/20 rounded-lg transition-colors duration-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-10 h-10 bg-black/20 border border-white/10 rounded-lg flex items-center justify-center">
                                <i data-lucide="shopping-cart" class="w-5 h-5 text-gray-300"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-100">
                                    @if($sale->plan_id && $sale->plan)
                                        Venda do Plano: {{ $sale->plan->name }}
                                    @elseif($sale->product_id && $sale->product)
                                        Venda do Produto: {{ $sale->product->name }}
                                    @else
                                        Venda #{{ $sale->id }}
                                    @endif
                                </p>
                                <p class="text-xs text-gray-400">
                                    Cliente: {{ $sale->user->name }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 ml-4">
                            <p class="text-sm font-bold text-green-400">
                                + R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $sale->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </li>
                @empty
                <li class="py-12 text-center text-gray-500">
                    <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-3"></i>
                    <p>Nenhuma venda recente para exibir.</p>
                </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
