@extends('layouts.app')

@section('title', 'Detalhes do Link de Pagamento')
@section('page-title', 'Link de Pagamento')

@section('content')
<div class="space-y-6" x-data="{ editing: false }">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                    <i data-lucide="link-2" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $product->name }}</h2>
                    <p class="text-gray-600 dark:text-gray-400">Detalhes completos do link de pagamento.</p>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="button"
                   @click="editing = !editing"
                   class="inline-flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white rounded-lg transition-all">
                    <i data-lucide="edit" class="w-4 h-4"></i>
                    <span x-text="editing ? 'Cancelar Edição' : 'Editar'"></span>
                </button>
                <a href="{{ route('checkout.show', $product->hash_id) }}" 
                   class="inline-flex items-center space-x-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                    <span>Checkout</span>
                </a>
                <a href="{{ route('associacao.products.index') }}" 
                   class="inline-flex items-center space-x-2 px-4 py-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700" x-show="!editing">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informações</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Preço</p>
                        <p class="text-xl font-bold text-gray-900 dark:text-white">
                            R$ {{ number_format($product->price, 2, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                            {{ $product->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Tipo</p>
                        <p class="text-gray-900 dark:text-white">{{ $product->tipo_produto == 1 ? 'Digital' : 'Físico' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Categoria</p>
                        <p class="text-gray-900 dark:text-white">
                            {{ \App\Enums\CategoriaProduto::all()[$product->categoria_produto] ?? 'N/A' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700" x-show="!editing">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Descrição</h3>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">{{ $product->description ?? 'Sem descrição' }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700" x-show="!editing">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Suporte</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nome do SAC</p>
                        <p class="text-gray-900 dark:text-white">{{ $product->nome_sac ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">E-mail do SAC</p>
                        <p class="text-gray-900 dark:text-white">{{ $product->email_sac ?? '-' }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Link de Upsell</p>
                        @if($product->url_venda)
                        <a href="{{ $product->url_venda }}" target="_blank" class="text-cyan-600 dark:text-cyan-400 underline break-all">{{ $product->url_venda }}</a>
                        @else
                        <p class="text-gray-900 dark:text-white">-</p>
                        @endif
                    </div>
                </div>
            </div>

            <form x-show="editing" action="{{ route('associacao.products.update', $product) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Editar Informações</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Preço</label>
                            <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                            <select name="is_active" class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                                <option value="1" {{ old('is_active', $product->is_active) == 1 ? 'selected' : '' }}>Ativo</option>
                                <option value="0" {{ old('is_active', $product->is_active) == 0 ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descrição</label>
                            <textarea name="description" rows="3" class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Editar Suporte</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nome do SAC</label>
                            <input type="text" name="nome_sac" value="{{ old('nome_sac', $product->nome_sac) }}" class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">E-mail do SAC</label>
                            <input type="email" name="email_sac" value="{{ old('email_sac', $product->email_sac) }}" class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Link de Upsell</label>
                            <input type="url" name="url_venda" value="{{ old('url_venda', $product->url_venda) }}" class="w-full px-4 py-3 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="editing=false" class="inline-flex items-center space-x-2 px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <i data-lucide="x" class="w-4 h-4"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="submit" class="inline-flex items-center space-x-2 px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-white rounded-lg transition-all">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Salvar alterações</span>
                    </button>
                </div>
            </form>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Imagem</h3>
                @if($product->image)
                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full rounded-lg border border-gray-200 dark:border-gray-700">
                @else
                    <div class="w-full h-48 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-700">
                        <i data-lucide="image-off" class="w-8 h-8 text-gray-400"></i>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
