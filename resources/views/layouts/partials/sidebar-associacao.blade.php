{{-- layouts/partials/sidebar-admin.blade.php (ou nome similar) --}}

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300"></div>

<!-- Sidebar -->
<div id="sidebar" class="fixed lg:relative w-72 bg-white dark:bg-sidebar-bg-dark text-gray-800 dark:text-text-light-gray flex flex-col z-50 transform -translate-x-full lg:translate-x-0 transition-all duration-300 ease-in-out h-full border-r border-gray-200 dark:border-gray-800 shadow-2xl">
    
    <!-- Seção do Logo e Progresso -->
    <div class="px-4 py-4 border-b border-gray-200 dark:border-gray-800">
        <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-4">
            <div class="flex justify-between items-center mb-2">
                {{-- Logo --}}
                <div class="flex-grow">
                <h1 class="text-4xl font-extrabold bg-clip-text text-transparent bg-gradient-to-r from-primary-blue to-accent-blue">
    {{ env('APP_NAME', 'Izus Payment') }}
</h1>
                </div>
                {{-- Botão de Fechar no Mobile --}}
                <button id="close-sidebar" class="lg:hidden p-1 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-md">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            @if(isset($globalGamificationData ))
            {{-- Dados de Gamificação --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-gray-600 dark:text-text-dark-gray">Faturamento</span>
                    <span class="text-xs font-bold bg-gradient-to-r from-primary-blue to-secondary-blue bg-clip-text text-transparent">
                        {{ number_format($globalGamificationData['progressPercentage'], 0) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-1">
                    <div class="bg-gradient-to-r from-primary-blue to-secondary-blue h-2 rounded-full transition-all duration-500" 
                         style="width: {{ $globalGamificationData['progressPercentage'] }}%"></div>
                </div>
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-text-dark-gray">
                    <span>R$ {{ number_format($globalGamificationData['currentRevenue'], 0, ',', '.') }}</span>
                    <span>R$ {{ number_format($globalGamificationData['nextLevelTarget'], 0, ',', '.') }}</span>
                </div>
                <div class="text-center mt-1">
                    <span class="text-xs font-medium bg-gradient-to-r from-primary-blue to-secondary-blue bg-clip-text text-transparent">
                        Nível: {{ $globalGamificationData['levelName'] }}
                    </span>
                </div>
            </div>
            @else
            {{-- Fallback caso os dados não existam --}}
            <p class="text-xs text-center text-gray-500 dark:text-gray-400">Carregando dados da jornada...</p>
            @endif
        </div>
    </div>

    <!-- Menu de Navegação -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto scrollbar-thin">
        @php
            $navItems = [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
                ['route' => 'associacao.products.index', 'label' => 'Produtos', 'icon' => 'package'],
                ['route' => 'associacao.vendas.index', 'label' => 'Vendas', 'icon' => 'shopping-cart'],
                ['route' => 'associacao.financeiro.index', 'label' => 'Financeiro', 'icon' => 'dollar-sign'],
                ['route' => 'associacao.configuracoes.edit', 'label' => 'Configurações', 'icon' => 'settings'],
            ];
        @endphp

        @foreach ($navItems as $item)
        <a href="{{ route($item['route']) }}" 
           class="sidebar-item {{ request()->routeIs($item['route'].'*') ? 'active' : '' }} group flex items-center space-x-3 px-4 py-3 rounded-xl text-sm font-medium transition-all duration-200 hover:bg-gray-100 dark:hover:bg-sidebar-item-hover relative overflow-hidden">
            <div class="flex items-center space-x-3 relative z-10">
                <div class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-800 group-hover:bg-primary-blue/10 dark:group-hover:bg-primary-blue/20 transition-colors duration-200">
                    <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4 text-gray-600 dark:text-gray-300 group-hover:text-primary-blue"></i>
                </div>
                <span class="font-medium text-gray-700 dark:text-gray-200">{{ $item['label'] }}</span>
            </div>
            @if(request()->routeIs($item['route'].'*'))
                <div class="absolute inset-0 bg-gradient-to-r from-primary-blue/10 to-secondary-blue/10 dark:from-primary-blue/20 dark:to-secondary-blue/20 rounded-xl"></div>
            @endif
        </a>
        @endforeach
    </nav>

    <!-- Perfil do Usuário -->
   
</div>
