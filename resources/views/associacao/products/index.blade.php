
@extends('layouts.app')

@section('title', 'Link de Pagamento')
@section('page-title', 'Link de Pagamento')

@section('content')
<div x-data="{ filterOpen: false }" class="space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    {{-- CABEÇALHO PRINCIPAL --}}
    <div>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div>
                <div class="flex items-center space-x-4 mb-2">
                  
                    <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Links de Pagamento</h1>
                </div>
                
            </div>
            <div class="flex items-center gap-2 self-start sm:self-center">
                
                <a href="{{ route('associacao.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg text-sm hover:bg-gray-900 dark:hover:bg-black/10 transition">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Novo Link</span>
                </a>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-6">
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Links Totais</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $metrics['total_links'] }}</p>
                    <p class="text-xs text-slate-600 dark:text-slate-400">{{ $metrics['active_links'] }} ativos</p>
                </div>
                <i data-lucide="link-2" class="w-6 h-6 text-slate-400"></i>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800 border-l-4 border-green-500">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Receita Total</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">R$ {{ number_format($metrics['total_revenue'], 2, ',', '.') }}</p>
                    <p class="text-xs text-slate-600 dark:text-slate-400">Ticket médio: R$ {{ number_format($metrics['avg_ticket'], 2, ',', '.') }}</p>
                </div>
                <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Tráfego</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $metrics['traffic'] }}</p>
                    <p class="text-xs text-slate-600 dark:text-slate-400">visitantes únicos</p>
                </div>
                <i data-lucide="activity" class="w-6 h-6 text-slate-400"></i>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800 border-l-4 border-yellow-400">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Taxa de Conversão</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">{{ number_format($metrics['conversion'], 2, ',', '.') }}%</p>
                    <p class="text-xs text-slate-600 dark:text-slate-400">Taxa de conversão geral</p>
                </div>
                <i data-lucide="clock" class="w-6 h-6 text-yellow-400"></i>
            </div>
        </div>
    </div>

    <div x-show="filterOpen" class="fixed inset-0 z-50">
        <div @click="filterOpen=false" class="fixed inset-0 bg-black/75"></div>
        <div class="fixed inset-0 flex items-center justify-center p-4">
            <div class="w-full max-w-2xl bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Filtros</h3>
                    <button @click="filterOpen=false" class="w-9 h-9 inline-flex items-center justify-center rounded-lg text-slate-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                <form action="{{ route('associacao.products.index') }}" method="GET" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="relative">
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Buscar por nome..." class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0">
                            <i data-lucide="search" class="absolute right-3 top-2.5 w-5 h-5 text-gray-500 dark:text-slate-400"></i>
                        </div>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0">
                            <option value="">Status</option>
                            <option value="ativo" @selected(request('status')==='ativo')>Ativo</option>
                            <option value="inativo" @selected(request('status')==='inativo')>Inativo</option>
                        </select>
                        <select name="categoria" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0">
                            <option value="">Categoria</option>
                            @isset($categorias)
                                @foreach($categorias as $key => $cat)
                                    <option value="{{ $key }}" @selected(request('categoria')==$key)>{{ $cat }}</option>
                                @endforeach
                            @endisset
                        </select>
                        <div class="flex gap-2">
                            <input type="date" name="from" value="{{ request('from') }}" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0">
                            <input type="date" name="to" value="{{ request('to') }}" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0">
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('associacao.products.index') }}" class="px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800">Limpar</a>
                        <a href="{{ route('associacao.products.index', array_merge(request()->query(), ['export'=>'csv'])) }}" class="px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800">Exportar CSV</a>
                        <button class="px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg text-sm hover:bg-gray-800 dark:hover:bg-black/10">Aplicar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    {{-- GRID DE PRODUTOS --}}
    @if($products->count() > 0)
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="relative bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow hover:shadow-lg transition-all duration-300 group">
                    <!-- Imagem do Produto -->
                    <div class="relative overflow-hidden h-48">
                        @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-slate-900 flex items-center justify-center">
                                <i data-lucide="image-off" class="w-12 h-12 text-cyan-400/40"></i>
                            </div>
                        @endif
                        
                    </div>
                    
                    <!-- Informações do Produto -->
                    <div class="p-5 flex flex-col h-[calc(100%-12rem)]">
                        <div class="flex-grow">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white group-hover:text-slate-900 dark:group-hover:text-white transition-colors duration-300 line-clamp-2 mb-2">
                                {{ $product->name }}
                            </h3>
                            
                        </div>
                        
                        <div class="flex items-center justify-between text-xs text-gray-400 mb-4">
                            <span class="flex items-center">
                                <i data-lucide="{{ $product->tipo_produto == 1 ? 'monitor' : 'package' }}" class="w-3 h-3 mr-1.5"></i>
                                {{ $product->tipo_produto == 1 ? 'Digital' : 'Físico' }}
                            </span>
                        </div>
                        
                        <div class="border-t border-white/10 pt-4">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-2xl font-semibold text-slate-900 dark:text-white">
                                    R$ {{ number_format($product->price, 2, ',', '.') }}
                                </div>
                            </div>
                            
                            <!-- Ações -->
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('checkout.show', $product->hash_id) }}" title="Checkout" target="_blank" rel="noopener noreferrer" class="p-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                                    <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                </a>
                                <a href="{{ route('associacao.products.show', $product) }}" title="Detalhes" class="p-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                                    <i data-lucide="eye" class="w-5 h-5"></i>
                                </a>
                                <span title="{{ $product->is_active ? 'Ativo' : 'Inativo' }}" class="p-2 rounded-lg border border-slate-300 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                                    <i data-lucide="dollar-sign" class="w-5 h-5 {{ $product->is_active ? 'text-green-500' : 'text-red-500' }}"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($products->hasPages())
            <div class="mt-8">{{ $products->appends(request()->query())->links() }}</div>
        @endif
    @else
        <!-- Estado Vazio -->
        <div class="mt-8 bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center shadow">
            <div class="w-20 h-20 bg-gray-100 dark:bg-slate-900 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i data-lucide="package-search" class="w-10 h-10 text-slate-500 dark:text-slate-300"></i>
            </div>
            <h3 class="text-2xl font-semibold text-slate-900 dark:text-white mb-3">Nenhum link encontrado</h3>
            <p class="text-slate-600 dark:text-slate-300 mb-8 max-w-md mx-auto">Crie seu primeiro link de pagamento para começar a vender.</p>
            <a href="{{ route('associacao.products.create') }}" class="inline-flex items-center space-x-2 px-8 py-3 bg-black dark:bg-white text-white dark:text-black rounded-xl font-semibold transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Criar Primeiro Link</span>
            </a>
        </div>
    @endif
</div>
@endsection
