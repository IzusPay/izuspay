{{-- layouts/partials/sidebar-admin.blade.php --}}

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300"></div>

<!-- Sidebar -->
<div id="sidebar" class="fixed lg:relative w-72 bg-[#1a1a1a] text-white flex flex-col z-50 transform -translate-x-full lg:translate-x-0 transition-all duration-300 ease-in-out h-full border-r border-white/5 shadow-2xl">
    
    {{-- Logo sem ícone, apenas nome do app --}}
    <div class="px-4 py-4 border-b border-white/5">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-extrabold text-white">
                {{ env('APP_NAME', 'GhostsPay') }}
            </h1>
            {{-- Botão de Fechar no Mobile --}}
            <button id="close-sidebar" class="lg:hidden p-1 text-gray-400 hover:text-white rounded-md">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    {{-- Seção Minha carteira com azul neon --}}
    <div class="px-4 py-4 border-b border-white/5">
        <div class="bg-gradient-to-br from-cyan-500/10 to-blue-500/10 rounded-xl p-4 border border-cyan-400/20">
            <p class="text-xs text-gray-400 mb-1">Minha carteira</p>
            <p class="text-2xl font-bold text-white">
                @if(isset($globalGamificationData))
                    R$ {{ number_format($globalGamificationData['currentRevenue'], 2, ',', '.') }}
                @else
                    R$ 0,00
                @endif
            </p>
        </div>
    </div>

    {{-- Meta de faturamento simples --}}
    <div class="px-4 py-3 border-b border-white/5">
        <div class="flex items-center justify-between text-xs">
            <span class="text-gray-400">Meta de faturamento</span>
            <span class="text-white font-semibold">
                @if(isset($globalGamificationData))
                    {{ number_format($globalGamificationData['progressPercentage'], 0) }}%
                @else
                    0%
                @endif
            </span>
        </div>
        <div class="w-full bg-white/10 rounded-full h-1.5 mt-2">
            <div class="bg-gradient-to-r from-cyan-400 to-blue-500 h-1.5 rounded-full transition-all duration-500" 
                 style="width: {{ isset($globalGamificationData) ? $globalGamificationData['progressPercentage'] : 0 }}%"></div>
        </div>
    </div>

    <!-- Menu de Navegação -->
    <div class="px-4 py-2 border-b border-white/5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Recursos</p>
    </div>

    {{-- Menu com azul neon quando ativo --}}
    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto scrollbar-thin">
        @php
            $navItems = [
                ['route' => 'dashboard', 'label' => 'Início', 'icon' => 'home'],
                ['route' => 'associacao.vendas.index', 'label' => 'Vendas', 'icon' => 'shopping-cart'],
                ['route' => 'associacao.products.index', 'label' => 'Carteira', 'icon' => 'wallet'],
                ['route' => 'associacao.financeiro.index', 'label' => 'Clientes', 'icon' => 'users'],
                ['route' => 'associacao.configuracoes.edit', 'label' => 'Taxas', 'icon' => 'percent'],
                ['route' => 'associacao.configuracoes.edit', 'label' => 'Integrações', 'icon' => 'plug'],
                ['route' => 'associacao.configuracoes.edit', 'label' => 'Link de Pagamento', 'icon' => 'link'],
                ['route' => 'associacao.configuracoes.edit', 'label' => 'Configurações', 'icon' => 'settings'],
                ['route' => 'associacao.configuracoes.edit', 'label' => 'Sua Empresa', 'icon' => 'building'],
            ];
        @endphp

        @foreach ($navItems as $item)
        <a href="{{ route($item['route']) }}" 
           class="sidebar-item {{ request()->routeIs($item['route'].'*') ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/20 text-white border-l-2 border-cyan-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }} flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200">
            <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4"></i>
            <span>{{ $item['label'] }}</span>
        </a>
        @endforeach
    </nav>
</div>
