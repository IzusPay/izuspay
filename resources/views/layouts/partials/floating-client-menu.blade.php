@php
    use Illuminate\Support\Facades\Route;
    $docRoute = Route::has('cliente.documentos.index') ? 'cliente.documentos.index' : 'documentos.index';
    $navItems = [
        ['route' => 'cliente.dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
        ['route' => 'cliente.eventos.index', 'label' => 'Eventos', 'icon' => 'calendar'],
        ['route' => $docRoute, 'label' => 'Documentos', 'icon' => 'file-text'],
        ['route' => 'cliente.pagamento.index', 'label' => 'Pagamento', 'icon' => 'credit-card'],
        ['route' => 'cliente.news.index', 'label' => 'Notícias', 'icon' => 'newspaper'],
    ];
@endphp

<div x-data="floatingMenu()" x-init="init()" class="fixed bottom-6 right-6 z-50" data-auth="{{ auth()->check() ? '1' : '0' }}">
    <button x-show="!open" @click="toggle()" aria-label="Abrir menu"
        class="w-14 h-14 rounded-full bg-gray-700 hover:bg-gray-800 text-white shadow-lg flex items-center justify-center transition-colors">
        <i data-lucide="ticket" class="w-7 h-7"></i>
    </button>

    <div x-show="open" @click.outside="close()"
         x-transition:enter="transform transition ease-out duration-200"
         x-transition:enter-start="translate-x-10 opacity-0"
         x-transition:enter-end="translate-x-0 opacity-100"
         x-transition:leave="transform transition ease-in duration-150"
         x-transition:leave-start="translate-x-0 opacity-100"
         x-transition:leave-end="translate-x-10 opacity-0"
         class="mt-3 w-[36rem] max-w-[90vw] rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-2xl overflow-hidden">
        @if(auth()->check())
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                        <i data-lucide="user" class="w-5 h-5 text-gray-600 dark:text-gray-300"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold truncate text-gray-900 dark:text-white">{{ auth()->user()->name ?? 'Cliente' }}</p>
                        <p class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ auth()->user()->email ?? 'email@dominio.com' }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="ml-auto">
                        @csrf
                        <button type="submit" class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" title="Sair">
                            <i data-lucide="log-out" class="w-5 h-5"></i>
                        </button>
                    </form>
                    <button @click="close()" class="ml-2 p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Fechar">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
            </div>
            <nav class="py-2">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm {{ request()->routeIs($item['route'].'*') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                        <i data-lucide="{{ $item['icon'] }}" class="w-4 h-4"></i>
                        <span>{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>
        @else
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Pesquisa</p>
                <p class="text-xs text-gray-600 dark:text-gray-400">Encontre seu show</p>
                <button @click="close()" class="ml-auto p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 float-right" aria-label="Fechar">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-4">
                <div class="relative">
                    <input type="text"
                           x-model="guest.value"
                           :placeholder="guest.placeholderBase + guest.dots"
                           class="w-full px-5 py-4 pr-12 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-gray-500 text-lg"
                           aria-label="Pesquisar eventos">
                    <i data-lucide="search" class="absolute right-4 top-4 w-5 h-5 text-gray-400"></i>
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">conheca a ia que vai te ajudar a ter experiencias extraordinarias pelo melhor custo.</p>
                <div class="mt-3 flex items-center gap-3 text-sm">
                    <a href="{{ route('login') }}" class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Entrar</a>
                    <span class="text-gray-300 dark:text-gray-600">•</span>
                    <a href="{{ route('register') }}" class="text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">Criar conta</a>
                </div>
            </div>
        @endif
    </div>

    <script>
        function floatingMenu() {
            return {
                open: false,
                isAuth: false,
                guest: {
                    message: 'conheca a ia que vai te ajudar a ter experiencias extraordinarias pelo melhor custo.',
                    value: '',
                    placeholderBase: 'Encontre a melhor agora',
                    dots: '',
                    typingTimer: null,
                    deletingTimer: null,
                    dotsTimer: null,
                },
                init() {
                    this.isAuth = this.$el.getAttribute('data-auth') === '1';
                },
                toggle() {
                    this.open = !this.open;
                    if (this.open) this.onOpen();
                    else this.onClose();
                },
                close() { this.open = false; this.onClose(); },
                onOpen() {
                    if (!this.isAuth) {
                        this.startGuestAnimation();
                        this.$nextTick(() => {
                            const input = this.$el.querySelector('input[type=\"text\"]');
                            if (input) input.focus();
                        });
                    }
                    const sb = document.getElementById('sidebar');
                    const sbo = document.getElementById('sidebar-overlay');
                    if (sb) sb.classList.add('hidden');
                    if (sbo) sbo.classList.add('hidden');
                    if (window.lucide && typeof lucide.createIcons === 'function') lucide.createIcons();
                },
                onClose() {
                    clearInterval(this.guest.typingTimer);
                    clearInterval(this.guest.deletingTimer);
                    clearInterval(this.guest.dotsTimer);
                    this.guest.value = '';
                    this.guest.dots = '';
                    const sb = document.getElementById('sidebar');
                    const sbo = document.getElementById('sidebar-overlay');
                    if (sb) sb.classList.remove('hidden');
                    if (sbo) sbo.classList.add('hidden');
                },
                startGuestAnimation() {
                    this.guest.value = '';
                    let i = 0;
                    this.guest.typingTimer = setInterval(() => {
                        if (i < this.guest.message.length) {
                            this.guest.value += this.guest.message[i++];
                        } else {
                            clearInterval(this.guest.typingTimer);
                            let j = 0;
                            this.guest.deletingTimer = setInterval(() => {
                                if (j < this.guest.message.length) {
                                    this.guest.value = this.guest.value.substring(1);
                                    j++;
                                } else {
                                    clearInterval(this.guest.deletingTimer);
                                    this.guest.dotsTimer = setInterval(() => {
                                        this.guest.dots = this.guest.dots.length >= 3 ? '' : this.guest.dots + '.';
                                    }, 700);
                                }
                            }, 60);
                        }
                    }, 80);
                },
            }
        }
    </script>
</div>
