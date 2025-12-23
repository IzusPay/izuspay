<div id="sidebar" class="fixed lg:relative w-72 bg-gray-900 dark:bg-gray-800 text-white flex flex-col z-50 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out h-full">
    <!-- Logo/Brand -->
    <div class="p-4 lg:p-6 border-b border-gray-800 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 lg:w-10 h-8 lg:h-10 bg-green-500 rounded-lg flex items-center justify-center">
                    <i data-lucide="zap" class="w-4 lg:w-6 h-4 lg:h-6 text-white"></i>
                </div>
                <div>
                    <h1 class="text-base lg:text-lg font-semibold">AssociaçõesPro</h1>
                    <p class="text-xs text-gray-400 hidden lg:block">
                        @if(auth()->user()->tipo == 'adm')
                            Admin Panel
                        @elseif(auth()->user()->tipo == 'cliente')
                            Cliente Panel
                        @else
                            Membro Panel
                        @endif
                    </p>
                </div>
            </div>
            <!-- Close button for mobile -->
            <button id="close-sidebar" class="lg:hidden p-2 text-gray-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    </div>
    
    <!-- Navigation Menu -->
    <nav class="flex-1 px-3 lg:px-4 py-4 lg:py-6 space-y-1 lg:space-y-2 overflow-y-auto">
        @if(auth()->user()->tipo == 'adm')
            <!-- Admin Menu -->
            <a href="{{ route('admin.dashboard') }}" data-route="/admin/dashboard" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5 flex-shrink-0"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="{{ route('admin.users.index') }}" data-route="/admin/users" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.users.*') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i>
                <span>Usuários</span>
            </a>
            
            <a href="{{ route('admin.associations.index') }}" data-route="/admin/associations" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.associations.*') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="building" class="w-5 h-5 flex-shrink-0"></i>
                <span>Associações</span>
            </a>
            
            <a href="{{ route('admin.analytics') }}" data-route="/admin/analytics" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.analytics') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="bar-chart-3" class="w-5 h-5 flex-shrink-0"></i>
                <span>Analytics</span>
            </a>
            
         
            <a href="{{ route('admin.financial.index') }}" data-route="/admin/financial" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.financial.*') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="credit-card" class="w-5 h-5 flex-shrink-0"></i>
                <span>Financeiro</span>
            </a>
            
            <a href="{{ route('admin.reports.index') }}" data-route="/admin/reports" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.reports.*') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="file-text" class="w-5 h-5 flex-shrink-0"></i>
                <span>Relatórios</span>
            </a>
            
            <a href="{{ route('admin.settings.index') }}" data-route="/admin/settings" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('admin.settings.*') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="settings" class="w-5 h-5 flex-shrink-0"></i>
                <span>Configurações</span>
            </a>
            
        @elseif(auth()->user()->tipo == 'cliente')
            <!-- Client Menu -->
            <a href="{{ route('client.dashboard') }}" data-route="/client/dashboard" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('client.dashboard') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5 flex-shrink-0"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="{{ route('client.members.index') }}" data-route="/client/members" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('client.members.*') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i>
                <span>Membros</span>
            </a>
            
            <a href="{{ route('client.news.index') }}" data-route="/client/news" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('client.news.*') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="newspaper" class="w-5 h-5 flex-shrink-0"></i>
                <span>Notícias</span>
            </a>
            
            <a href="{{ route('client.plans.index') }}" data-route="/client/plans" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('client.plans.*') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="package" class="w-5 h-5 flex-shrink-0"></i>
                <span>Planos</span>
            </a>
            
            <a href="{{ route('client.financial.index') }}" data-route="/client/financial" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('client.financial.*') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="credit-card" class="w-5 h-5 flex-shrink-0"></i>
                <span>Financeiro</span>
            </a>
            
        @else
            <!-- Member Menu -->
            <a href="{{ route('member.dashboard') }}" data-route="/member/dashboard" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('member.dashboard') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-5 h-5 flex-shrink-0"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="{{ route('member.news.index') }}" data-route="/member/news" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('member.news.*') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="newspaper" class="w-5 h-5 flex-shrink-0"></i>
                <span>Notícias</span>
            </a>
            
            <a href="{{ route('member.profile') }}" data-route="/member/profile" class="sidebar-item flex items-center space-x-3 px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('member.profile') ? 'active' : 'text-gray-300 hover:text-white' }}">
                <i data-lucide="user" class="w-5 h-5 flex-shrink-0"></i>
                <span>Meu Perfil</span>
            </a>
        @endif
        
        <!-- Notifications (for all users) -->
        <a href="{{ route('notifications.index') }}" data-route="/notifications" class="sidebar-item flex items-center justify-between px-3 lg:px-4 py-3 rounded-lg text-sm font-medium {{ request()->routeIs('notifications.*') ? 'active' : 'text-gray-300 hover:text-white' }}">
            <div class="flex items-center space-x-3">
                <i data-lucide="bell" class="w-5 h-5 flex-shrink-0"></i>
                <span>Notificações</span>
            </div>
            <span class="bg-orange-500 text-white text-xs px-2 py-1 rounded-full flex-shrink-0">3</span>
        </a>
    </nav>
    
    <!-- User Profile -->
    <div class="p-3 lg:p-4 border-t border-gray-800 dark:border-gray-700">
        <button id="user-profile-btn" class="w-full flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-700 transition-colors">
            <div class="w-8 lg:w-10 h-8 lg:h-10 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-xs lg:text-sm font-semibold text-white">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </span>
            </div>
            <div class="flex-1 min-w-0 text-left">
                <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 truncate">
                    @if(auth()->user()->tipo == 'adm')
                        Administrador
                    @elseif(auth()->user()->tipo == 'cliente')
                        Cliente
                    @else
                        Membro
                    @endif
                </p>
            </div>
            <i data-lucide="chevron-up" class="w-4 h-4 text-gray-400"></i>
        </button>
    </div>
</div>
