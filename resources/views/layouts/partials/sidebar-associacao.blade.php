{{-- layouts/partials/sidebar-admin.blade.php --}}

<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-40 hidden lg:hidden transition-opacity duration-300"></div>

<!-- Sidebar -->
@php
    $accent = session('assoc_ui_color', 'pink');
@endphp
<div id="sidebar"
     x-data="{
        openColor: false,
        accent: '{{ $accent }}',
        pulse: true,
        clickFx: false,
        showPalette: false,
        showTheme: false,
        mode: 'palette',
        setAccent(c) {
          const map = { pink:'#ff4d8d', cyan:'#22d3ee', violet:'#a78bfa', blue:'#60a5fa', orange:'#f97316', lime:'#84cc16' };
          this.accent = c;
          const hex = map[c] || map.pink;
          document.documentElement.style.setProperty('--accent', hex);
          fetch('{{ route('associacao.ui.color') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify({ color: c })
          });
        }
     }"
     x-init="(() => { const m={pink:'#ff4d8d',cyan:'#22d3ee',violet:'#a78bfa',blue:'#60a5fa',orange:'#f97316',lime:'#84cc16'}; document.documentElement.style.setProperty('--accent', m[accent]||m.pink); setTimeout(()=>{pulse=false},5000); })()"
     class="fixed lg:relative w-72 bg-gradient-to-b from-[#0e131f] via-[#1b1724] to-[#0e131f] text-slate-200 flex flex-col z-50 transform -translate-x-full lg:translate-x-0 transition-all duration-300 ease-in-out h-full border-r border-slate-800 shadow-2xl">
 
    
    {{-- Header centralizado --}}
    <div class="px-5 py-6">
        <div class="flex items-center justify-center">
            <span class="text-lg font-semibold tracking-wide text-white">izusPass</span>
        </div>
    </div>
    <div class="px-5"><div class="h-px bg-white/10"></div></div>

    <nav class="flex-1 px-5 py-4 space-y-2 overflow-y-auto">
        @php
            $navItems = [
                ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home'],
                ['route' => 'associacao.financeiro.index', 'label' => 'Financeiro', 'icon' => 'wallet'],
                ['route' => 'associacao.vendas.index', 'label' => 'Extrato', 'icon' => 'shopping-cart'],
                ['route' => 'associacao.disputas.index', 'label' => 'Disputas', 'icon' => 'shield-alert'],
                ['route' => 'api-keys.index', 'label' => 'Integrações', 'icon' => 'plug'],
                ['route' => 'associacao.eventos.index', 'label' => 'Eventos', 'icon' => 'calendar'],
                ['route' => 'associacao.qr-reader.index', 'label' => 'Leitor de QR Code', 'icon' => 'scan'],
                ['route' => 'associacao.products.index', 'label' => 'Link de Pagamento', 'icon' => 'link-2'],
                ['route' => 'associacao.webhooks.index', 'label' => 'Webhooks', 'icon' => 'settings'],
            ];
        @endphp
        @foreach ($navItems as $item)
        <a href="{{ route($item['route']) }}" class="relative group flex items-center gap-3 px-3 py-3 rounded-2xl text-[13px] font-medium transition-all duration-200
           {{ request()->routeIs($item['route'].'*') 
                ? 'bg-transparent text-[var(--brand-dark)] border border-black/10 dark:bg-[#14233b] dark:text-white dark:border-transparent shadow-sm' 
                : 'text-[var(--brand-dark)] border border-black/5 hover:border-black/10 dark:text-white dark:hover:bg-[#0f1627] dark:hover:text-white' }}">
            <span class="relative inline-flex items-center justify-center w-9 h-9 rounded-xl 
                {{ request()->routeIs($item['route'].'*') ? 'bg-transparent' : 'bg-transparent' }}">
                <i data-lucide="{{ $item['icon'] }}" 
                   class="w-5 h-5 transition-colors duration-200 text-[var(--brand-dark)] dark:text-white" 
                   style="{{ request()->routeIs($item['route'].'*') ? 'fill: var(--accent); fill-opacity: 0.5; stroke-width: 2;' : 'fill: none;' }}">
                </i>
                @if(($item['notify'] ?? false) === true)
                    <span class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full" style="background: var(--accent)"></span>
                @endif
            </span>
            <span>{{ $item['label'] }}</span>
        </a>
        @endforeach
    </nav>
    
    <div class="mt-auto px-5 py-4">
        <div class="flex items-center justify-between">
            <div class="relative">
                <button @click="
                        if(mode==='palette'){
                          openColor=true; showPalette=true; showTheme=false; mode='moon';
                        } else if(mode==='moon'){
                          openColor=true; showPalette=false; showTheme=true;
                          document.documentElement.classList.add('dark'); localStorage.setItem('theme','dark');
                          mode='sun';
                        } else {
                          document.documentElement.classList.remove('dark'); localStorage.setItem('theme','light');
                          openColor=true; showTheme=false; showPalette=true; mode='palette';
                        }"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-white transition transform duration-150 shadow-sm"
                        :class="[pulse ? 'ring-2 ring-[var(--accent)]/40 animate-pulse' : '', clickFx ? 'ring-2 ring-[var(--accent)] shadow-[0_0_10px_var(--accent)] scale-95' : '']"
                        @mousedown="clickFx=true; setTimeout(()=>{clickFx=false},200)"
                        :style="'background:' + ({ pink:'#ff4d8d', cyan:'#22d3ee', violet:'#a78bfa', blue:'#60a5fa', orange:'#f97316', lime:'#84cc16' }[accent] || '#ff4d8d')">
                    <i data-lucide="palette" class="w-5 h-5" style="color:#0e131f" x-show="mode==='palette'"></i>
                    <i data-lucide="moon" class="w-5 h-5 text-white" x-show="mode==='moon'"></i>
                    <i data-lucide="sun" class="w-5 h-5" style="color:#0e131f" x-show="mode==='sun'"></i>
                </button>
                <div x-show="openColor" x-transition class="absolute left-12 top-1/2 -translate-y-1/2 z-50">
                    <div class="flex items-center gap-2" x-show="showPalette">
                        <button @click="setAccent('pink'); openColor=false" title="Rosa"
                                class="w-5 h-5 rounded-full"
                                :class="accent==='pink' ? 'scale-110' : 'ring-1 ring-white/20'"
                                :style="accent==='pink' ? 'background:#ff4d8d; box-shadow:0 0 0 2px var(--accent)' : 'background:#ff4d8d'"></button>
                        <button @click="setAccent('cyan'); openColor=false" title="Ciano"
                                class="w-5 h-5 rounded-full"
                                :class="accent==='cyan' ? 'scale-110' : 'ring-1 ring-white/20'"
                                :style="accent==='cyan' ? 'background:#22d3ee; box-shadow:0 0 0 2px var(--accent)' : 'background:#22d3ee'"></button>
                        <button @click="setAccent('violet'); openColor=false" title="Violeta"
                                class="w-5 h-5 rounded-full"
                                :class="accent==='violet' ? 'scale-110' : 'ring-1 ring-white/20'"
                                :style="accent==='violet' ? 'background:#a78bfa; box-shadow:0 0 0 2px var(--accent)' : 'background:#a78bfa'"></button>
                        <button @click="setAccent('blue'); openColor=false" title="Azul"
                                class="w-5 h-5 rounded-full"
                                :class="accent==='blue' ? 'scale-110' : 'ring-1 ring-white/20'"
                                :style="accent==='blue' ? 'background:#60a5fa; box-shadow:0 0 0 2px var(--accent)' : 'background:#60a5fa'"></button>
                        <button @click="setAccent('orange'); openColor=false" title="Laranja"
                                class="w-5 h-5 rounded-full"
                                :class="accent==='orange' ? 'scale-110' : 'ring-1 ring-white/20'"
                                :style="accent==='orange' ? 'background:#f97316; box-shadow:0 0 0 2px var(--accent)' : 'background:#f97316'"></button>
                        <button @click="setAccent('lime'); openColor=false" title="Lima"
                                class="w-5 h-5 rounded-full"
                                :class="accent==='lime' ? 'scale-110' : 'ring-1 ring-white/20'"
                                :style="accent==='lime' ? 'background:#84cc16; box-shadow:0 0 0 2px var(--accent)' : 'background:#84cc16'"></button>
                        <div class="ml-3 flex items-center gap-2 px-2 py-1 rounded-md bg-[#0f1627] ring-1 ring-white/10">
                            <span class="inline-block w-1.5 h-4 rounded-sm" style="background: var(--accent)"></span>
                            <span class="text-xs text-white" x-text="({pink:'Rosa', cyan:'Ciano', violet:'Violeta', blue:'Azul', orange:'Laranja', lime:'Lima'})[accent] || 'Tema'"></span>
                        </div>
                    </div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="ml-auto">
                @csrf
                <button type="submit" class="px-3 py-2 rounded-xl bg-[#14233b] text-white hover:bg-[#1b2a44] transition" title="Sair">
                    <i data-lucide="log-out" class="w-5 h-5"></i>
                </button>
            </form>
        </div>
    </div>
</div>
