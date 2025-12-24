<!DOCTYPE html>
<html lang="pt-BR" class=""> <!-- A classe 'dark' será adicionada aqui pelo JS -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Izus Payment')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    {{-- BIBLIOTECAS PARA GRÁFICOS E OUTROS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Alpine.js para interatividade --}}
    <script src="//unpkg.com/alpinejs" defer></script>

    <script>
        // CONFIGURAÇÃO COMPLETA DO TAILWIND PARA O TEMA AZUL
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        'primary-blue': '#1e40af',      // Azul primário (escuro )
                        'secondary-blue': '#3b82f6',    // Azul secundário (médio)
                        'accent-blue': '#60a5fa',       // Azul de destaque (claro)
                        'dark-blue': '#1e3a8a',         // Variação escura
                        'light-blue': '#dbeafe',        // Variação clara
                        'sidebar-bg-dark': '#111827',   // Fundo da sidebar no modo escuro
                        'sidebar-item-hover': '#1f2937',// Hover do item da sidebar
                        'text-light-gray': '#e5e7eb',   // Texto claro
                        'text-dark-gray': '#9ca3af',    // Texto cinza escuro
                    },
                }
            }
        }
    </script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-item { transition: all 0.2s ease-in-out; }
        .sidebar-item:hover { background-color: rgba(59, 130, 246, 0.1); transform: translateX(4px); }
        .sidebar-item.active { background-color: rgba(59, 130, 246, 0.2); border-right: 3px solid #3b82f6; }
        .dark .sidebar-item.active { background-color: rgba(59, 130, 246, 0.3); }
        .sidebar-overlay { backdrop-filter: blur(4px); }
        @media (max-width: 1024px) { .sidebar-item:hover { transform: none; } }

        /* Animações de Notificação */
        .notification { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .notification.removing { animation: slideOut 0.3s ease-in forwards; }
        @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }

        /* Scrollbar customizada com o tema azul */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #e5e7eb; }
        .dark ::-webkit-scrollbar-track { background: #1f2937; }
        ::-webkit-scrollbar-thumb { background: #3b82f6; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #1e40af; }
        /* Scrollbar do sidebar com cor de destaque */
        #sidebar nav { scrollbar-width: thin; scrollbar-color: var(--accent) transparent; }
        #sidebar nav::-webkit-scrollbar { width: 8px; }
        #sidebar nav::-webkit-scrollbar-track { background: rgba(255,255,255,0.08); }
        #sidebar nav::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 9999px; }
        #sidebar nav::-webkit-scrollbar-thumb:hover { background: var(--accent); }
        .icon-base { color: var(--brand-dark); }
        .dark .icon-base { color: #ffffff; }
        .icon-accent { color: var(--accent); }
        :root { --brand-dark: #0e131f; }
        html:not(.dark) body { background: #ffffff !important; }
        html:not(.dark) #sidebar { background: #ffffff !important; border-color: #e5e7eb !important; }
        html:not(.dark) .dashboard-bg { background: #ffffff !important; }
        html:not(.dark) .card-bg { background: #ffffff !important; border-color: #e5e7eb !important; }
        html:not(.dark) #sidebar, html:not(.dark) .dashboard-bg { color: var(--brand-dark); }
        html:not(.dark) .card-bg .text-white { color: var(--brand-dark) !important; }
        html:not(.dark) .card-bg .text-white\/70 { color: #334155 !important; }
        html:not(.dark) .card-bg .text-white\/50 { color: #475569 !important; }
        html:not(.dark) #sidebar .text-white, 
        html:not(.dark) #sidebar .text-white\/70, 
        html:not(.dark) #sidebar .text-slate-300 { color: var(--brand-dark) !important; }
        html:not(.dark) #sidebar .bg-[#0f1627], 
        html:not(.dark) #sidebar .bg-[#1b2a44] { background: #f8fafc !important; }
        html:not(.dark) #sidebar .border-slate-800 { border-color: #e5e7eb !important; }
        html:not(.dark) #sidebar a { color: var(--brand-dark) !important; }
    </style>
    @stack('styles')
</head>
<body class="transition-colors duration-200" style="background: linear-gradient(135deg, #0e131f 0%, #1b1724 50%, #0e131f 100%);">
    <div class="flex h-screen relative">
        @if(auth()->check())
            @php
                $perfilAtual = auth()->user()->perfilAtual();
                $perfilNome = $perfilAtual ? $perfilAtual->name : null;
            @endphp
            
            @if($perfilNome === 'Administrador')
                @include('layouts.partials.sidebar-admin')
            @elseif($perfilNome === 'Cliente')
                @include('layouts.partials.floating-client-menu')
            @elseif($perfilNome === 'Associacao')
                @include('layouts.partials.sidebar-associacao')
            @elseif($perfilNome === 'Membro')
                @include('layouts.partials.sidebar-member')
            @elseif($perfilNome === 'Moderador')
                @include('layouts.partials.sidebar-moderador')
            @endif
        @endif
        @if(!auth()->check())
            @include('layouts.partials.floating-client-menu')
        @endif

        <!-- Mobile Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden lg:hidden"></div>

        <div class="flex-1 flex flex-col overflow-hidden">
            @if(auth()->check())
                @php
                    $perfilAtual = auth()->user()->perfilAtual();
                    $perfilNome = $perfilAtual ? $perfilAtual->name : null;
                @endphp
                
                {{-- Inclui o header correto baseado no perfil --}}
                @if(request()->routeIs('associacao.dashboard') || request()->routeIs('dashboard'))
                    {{-- sem header no dashboard da associação --}}
                @else
                    @if($perfilNome === 'Administrador')
                        @include('layouts.partials.header-admin')
                    @elseif($perfilNome === 'Cliente')
                        @include('layouts.partials.header-client')
                    @elseif($perfilNome === 'Associacao')
                        @include('layouts.partials.header-associacao')
                    @elseif($perfilNome === 'Membro')
                        @include('layouts.partials.header-member')
                    @elseif($perfilNome === 'Moderador')
                        @include('layouts.partials.header-moderador')
                    @else
                        @include('layouts.partials.header-default')
                    @endif
                @endif
            @endif

            <main class="flex-1 overflow-x-hidden overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Container de Notificações -->
    <div aria-live="assertive" class="fixed inset-0 flex items-end px-4 py-6 pointer-events-none sm:p-6 sm:items-start">
        <div id="notification-container" class="w-full flex flex-col items-center space-y-4 sm:items-end"></div>
    </div>

    @stack('scripts')
    
    <script>
        // Inicializa os ícones do Lucide
        lucide.createIcons();

        // --- GERENCIAMENTO DE TEMA (MODO CLARO/ESCURO) ---
        const themeToggleBtn = document.getElementById('theme-toggle');
        const docHtml = document.documentElement;

        // Aplica o tema salvo no carregamento da página
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            docHtml.classList.add('dark');
        } else {
            docHtml.classList.remove('dark');
        }

        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', () => {
                docHtml.classList.toggle('dark');
                const theme = docHtml.classList.contains('dark') ? 'dark' : 'light';
                localStorage.setItem('theme', theme);
            });
        }

        // --- FUNCIONALIDADE DO MENU MOBILE ---
        const openSidebarBtn = document.getElementById('open-sidebar'); // Botão no header para abrir
        const closeSidebarBtn = document.getElementById('close-sidebar'); // Botão 'X' dentro da sidebar
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        const openSidebar = () => {
            if (sidebar && sidebarOverlay) {
                sidebar.classList.remove('-translate-x-full');
                sidebarOverlay.classList.remove('hidden');
            }
        };

        const closeSidebar = () => {
            if (sidebar && sidebarOverlay) {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            }
        };

        if (openSidebarBtn) openSidebarBtn.addEventListener('click', openSidebar);
        if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', closeSidebar);
        if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

        // --- SISTEMA DE NOTIFICAÇÃO ---
        window.showNotification = (message, type = 'success') => {
            const container = document.getElementById('notification-container');
            if (!container) return;

            const notification = document.createElement('div');
            let bgColor, icon;

            switch (type) {
                case 'error':
                    bgColor = 'bg-red-500';
                    icon = 'x-circle';
                    break;
                case 'info':
                    bgColor = 'bg-blue-500';
                    icon = 'info';
                    break;
                default: // success
                    bgColor = 'bg-green-500';
                    icon = 'check-circle';
            }

            notification.className = `max-w-sm w-full ${bgColor} text-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden notification`;
            notification.innerHTML = `
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0"><i data-lucide="${icon}"></i></div>
                        <div class="ml-3 w-0 flex-1 pt-0.5"><p class="text-sm font-medium">${message}</p></div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button type="button" class="inline-flex text-white/70 hover:text-white">
                                <span class="sr-only">Fechar</span>
                                <i data-lucide="x" class="h-5 w-5"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            const closeBtn = notification.querySelector('button');
            const removeNotif = () => {
                notification.classList.add('removing');
                notification.addEventListener('animationend', () => notification.remove());
            };

            closeBtn.addEventListener('click', removeNotif);
            setTimeout(removeNotif, 5000);

            container.appendChild(notification);
            lucide.createIcons();
        };

        // Exibe notificações do Laravel (se houver)
        @if(session('success'))
            window.showNotification('{{ session('success') }}', 'success');
        @endif
        @if(session('error'))
            window.showNotification('{{ session('error') }}', 'error');
        @endif
        @if(session('info'))
            window.showNotification('{{ session('info') }}', 'info');
        @endif
    </script>
</body>
</html>
