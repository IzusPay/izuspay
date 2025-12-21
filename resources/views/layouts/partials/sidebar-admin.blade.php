<div id="sidebar-overlay" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300"></div>

<div id="sidebar" class="fixed lg:relative w-72 bg-white dark:bg-black text-black dark:text-white flex flex-col z-50 transform -translate-x-full lg:translate-x-0 transition-all duration-300 ease-in-out h-full border-r border-gray-200 dark:border-white/5 shadow-2xl">
    <div class="px-4 py-4 border-b border-gray-200 dark:border-white/5">
        <div class="flex justify-between items-center">
            <h1 class="text-xl font-extrabold text-black dark:text-white">
                {{ env('APP_NAME', config('app.name')) }}
            </h1>
            <button id="close-sidebar" class="lg:hidden p-1 text-gray-600 dark:text-gray-400 hover:text-black dark:hover:text-white rounded-md">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    <div class="px-4 py-2 border-b border-gray-200 dark:border-white/5">
        <p class="text-xs font-semibold text-gray-700 dark:text-gray-500 uppercase tracking-wider mb-2">Recursos</p>
    </div>

    <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto scrollbar-thin">
        @php
            $navItems = [
                ['route' => 'admin.dashboard', 'label' => 'Resumo', 'icon' => 'layout-dashboard'],
                ['route' => 'admin.contas.index', 'label' => 'Contas', 'icon' => 'wallet'],
                ['route' => 'admin.sales.index', 'label' => 'Vendas', 'icon' => 'shopping-cart'],
                ['route' => 'admin.financial.index', 'label' => 'Financeiro', 'icon' => 'credit-card'],
                ['route' => 'admin.webhooks.index', 'label' => 'Webhooks', 'icon' => 'webhook'],
                ['route' => 'admin.gateways.index', 'label' => 'Integração Bancária', 'icon' => 'credit-card'],
            ];
        @endphp

        @foreach ($navItems as $item)
        <a href="{{ route($item['route']) }}" 
           class="sidebar-item {{ request()->routeIs($item['route'].'*') 
                ? 'bg-gradient-to-r from-cyan-500/20 to-blue-500/20 text-black dark:text-white border-l-2 border-cyan-400' 
                : 'text-gray-700 dark:text-gray-400 hover:bg-black/5 dark:hover:bg-white/5 hover:text-black dark:hover:text-white' }} flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200">
            <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4"></i>
            <span>{{ $item['label'] }}</span>
        </a>
        @endforeach
    </nav>

    <div class="mt-auto px-4 py-4 border-t border-gray-200 dark:border-white/5">
        <div class="flex items-center gap-3 px-3 py-3 rounded-xl bg-black/5 dark:bg-white/5">
            <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center">
                <i data-lucide="user" class="w-5 h-5 text-gray-600 dark:text-gray-400"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium truncate text-black dark:text-white">{{ auth()->user()->name ?? 'Usuário' }}</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ auth()->user()->email ?? 'email@dominio.com' }}</p>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="ml-auto">
                @csrf
                <button type="submit" class="p-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-black/10 dark:hover:bg-white/10 transition-colors" title="Sair">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                </button>
            </form>
        </div>
    </div>
</div>
