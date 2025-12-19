<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 sidebar-overlay z-40 hidden lg:hidden"></div>

<!-- Updated to pure black/white/gray theme -->
<div id="sidebar" class="fixed lg:relative w-72 bg-black text-white flex flex-col z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out h-full border-r border-gray-800">
            <!-- Logo/Brand -->
            <div class="p-4 lg:p-6 border-b border-gray-800">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <!-- Changed icon background to white with black icon -->
                        <div class="w-8 lg:w-10 h-8 lg:h-10 bg-white rounded-lg flex items-center justify-center">
                            <i data-lucide="zap" class="w-4 lg:w-6 h-4 lg:h-6 text-black"></i>
                        </div>
                        <div>
                            <h1 class="text-base lg:text-lg font-semibold text-white">{{ config('app.name') }}</h1>
                            <p class="text-xs text-gray-400 hidden lg:block">Admin Panel</p>
                        </div>
                    </div>
                    <!-- Close button for mobile -->
                    <button id="close-sidebar" class="lg:hidden p-2 text-gray-400 hover:text-white transition-colors">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>

            <!-- Navigation Menu -->
        <!-- Updated all menu items to black/white/gray theme -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
  <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->is('*dashboard*') ? 'bg-white text-black' : 'text-gray-300' }} flex items-center space-x-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-gray-900 hover:text-white transition-colors">
    <i data-lucide="layout-dashboard" class="h-5 w-5 {{ request()->is('*dashboard*') ? 'text-black' : 'text-gray-400' }}"></i>
    <span>Resumo</span>
  </a>

  <a href="{{ route('admin.produtos.index') }}" class="sidebar-item {{ request()->is('*produtos*') ? 'bg-white text-black' : 'text-gray-300' }} flex items-center space-x-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-gray-900 hover:text-white transition-colors">
    <i data-lucide="package" class="h-5 w-5 {{ request()->is('*produtos*') ? 'text-black' : 'text-gray-400' }}"></i>
    <span>Produtos</span>
  </a>
  
  <a href="{{ route('admin.contas.index') }}" class="sidebar-item {{ request()->is('*contas*') ? 'bg-white text-black' : 'text-gray-300' }} flex items-center space-x-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-gray-900 hover:text-white transition-colors">
    <i data-lucide="wallet" class="h-5 w-5 {{ request()->is('*contas*') ? 'text-black' : 'text-gray-400' }}"></i>
    <span>Contas</span>
  </a>
  
  <a href="{{ route('admin.sales.index') }}" class="sidebar-item {{ request()->is('*vendas*') ? 'bg-white text-black' : 'text-gray-300' }} flex items-center space-x-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-gray-900 hover:text-white transition-colors">
    <i data-lucide="shopping-cart" class="h-5 w-5 {{ request()->is('*vendas*') ? 'text-black' : 'text-gray-400' }}"></i>
    <span>Vendas</span>
  </a>

  <a href="{{ route('admin.financial.index') }}" class="sidebar-item {{ request()->is('*financeiro*') ? 'bg-white text-black' : 'text-gray-300' }} flex items-center space-x-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-gray-900 hover:text-white transition-colors">
    <i data-lucide="credit-card" class="h-5 w-5 {{ request()->is('*financeiro*') ? 'text-black' : 'text-gray-400' }}"></i>
    <span>Painel Financeiro</span>
  </a>
  
  <a href="{{ route('admin.gateways.index') }}" class="sidebar-item {{ request()->is('*gateways*') ? 'bg-white text-black' : 'text-gray-300' }} flex items-center space-x-3 px-3 py-3 rounded-lg text-sm font-medium hover:bg-gray-900 hover:text-white transition-colors">
    <i data-lucide="credit-card" class="h-5 w-5 {{ request()->is('*gateways*') ? 'text-black' : 'text-gray-400' }}"></i>
    <span>Gateways Integrados</span>
  </a>
  
  {{-- <a href="#" class="sidebar-item flex items-center space-x-3 px-3 py-3 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-900 hover:text-white transition-colors">
    <i data-lucide="shield-alert" class="h-5 w-5 text-gray-400"></i>
    <span>Chargeback</span>
  </a> --}}

  {{-- <a href="#" class="sidebar-item flex items-center space-x-3 px-3 py-3 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-900 hover:text-white transition-colors">
    <i data-lucide="bar-chart-2" class="h-5 w-5 text-gray-400"></i>
    <span>Relatórios</span>
  </a> --}}

  {{-- <a href="#" class="sidebar-item flex items-center space-x-3 px-3 py-3 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-900 hover:text-white transition-colors">
    <i data-lucide="building-2" class="h-5 w-5 text-gray-400"></i>
    <span>Empresa</span>
  </a>

  <a href="#" class="sidebar-item flex items-center space-x-3 px-3 py-3 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-900 hover:text-white transition-colors">
    <i data-lucide="file-text" class="h-5 w-5 text-gray-400"></i>
    <span>Faturas do Whitelabel</span>
  </a>

  <a href="#" class="sidebar-item flex items-center space-x-3 px-3 py-3 rounded-lg text-sm font-medium text-gray-300 hover:bg-gray-900 hover:text-white transition-colors">
    <i data-lucide="check-square" class="h-5 w-5 text-gray-400"></i>
    <span>Lista de Aprovação</span>
  </a> --}}
</nav>


            <!-- User Profile -->
           <!-- User Profile & Logout -->
<!-- Updated user profile section to black/white/gray theme -->
<div class="p-3 lg:p-4 border-t border-gray-800 relative">
    <!-- Botão do Perfil -->
    <button id="user-profile-toggle-btn" class="w-full flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-900 transition-colors">
        <!-- Changed avatar gradient to white background with black text -->
        <div class="w-8 lg:w-10 h-8 lg:h-10 bg-white rounded-full flex items-center justify-center flex-shrink-0">
            <span class="text-xs lg:text-sm font-semibold text-black">{{ auth()->user()->iniciais ?? 'AD' }}</span>
        </div>
        <div class="flex-1 min-w-0 text-left">
            <p class="text-sm font-medium truncate text-white">{{ auth()->user()->name ?? 'Usuário' }}</p>
            <p class="text-xs text-gray-400 truncate">{{ auth()->user()->perfil_atual_nome ?? 'Admin' }}</p>
        </div>
        <i id="user-profile-chevron" data-lucide="chevron-up" class="w-4 h-4 text-gray-400 transition-transform"></i>
    </button>

    <!-- Menu Dropdown de Logout -->
    <!-- Updated dropdown to match black theme -->
    <div id="user-profile-menu" class="absolute bottom-full left-3 right-3 mb-2 bg-gray-900 rounded-lg shadow-lg border border-gray-700 overflow-hidden hidden">
        <div class="p-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center space-x-3 px-3 py-2 rounded-md text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                    <span>Sair da Conta</span>
                </button>
            </form>
        </div>
    </div>
</div>


        </div>
        <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('user-profile-toggle-btn');
        const menu = document.getElementById('user-profile-menu');
        const chevron = document.getElementById('user-profile-chevron');

        if (toggleBtn && menu && chevron) {
            toggleBtn.addEventListener('click', function(event) {
                event.stopPropagation(); // Impede que o clique feche o menu imediatamente
                menu.classList.toggle('hidden');
                chevron.classList.toggle('rotate-180');
            });

            // Fecha o menu se clicar fora
            document.addEventListener('click', function(event) {
                if (!menu.contains(event.target) && !toggleBtn.contains(event.target)) {
                    menu.classList.add('hidden');
                    chevron.classList.remove('rotate-180');
                }
            });
        }
    });
</script>
