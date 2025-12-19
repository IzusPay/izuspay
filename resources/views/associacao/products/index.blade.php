
@extends('layouts.app')

@section('title', 'Produtos')
@section('page-title', 'Produtos')

@section('content')
<div class="space-y-8">
    {{-- CABEÇALHO PRINCIPAL --}}
    <div class="relative rounded-2xl p-8 overflow-hidden bg-slate-900 border border-blue-500/20 shadow-2xl">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-900/50 via-black to-black opacity-80"></div>
        <div class="absolute -top-10 -right-10 w-48 h-48 bg-gradient-to-br from-cyan-500 to-blue-600 rounded-full opacity-20 blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div>
                <div class="flex items-center space-x-4 mb-2">
                    <div class="w-16 h-16 bg-black/30 backdrop-blur-sm border border-white/10 rounded-xl flex items-center justify-center shadow-lg">
                        <i data-lucide="package" class="w-8 h-8 text-blue-300"></i>
                    </div>
                    <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-300 to-cyan-300 bg-clip-text text-transparent">
                        Gestão de Produtos
                    </h1>
                </div>
                <p class="text-blue-200/80 ml-20 sm:ml-0">Adicione, edite e gerencie seus produtos digitais e físicos.</p>
            </div>
            <div class="flex items-center gap-3 self-start sm:self-center">
                <button onclick="openProductModal()" class="inline-flex items-center space-x-2 bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i data-lucide="plus" class="w-5 h-5"></i>
                    <span>Novo Produto</span>
                </button>
            </div>
        </div>
    </div>

    {{-- GRID DE PRODUTOS --}}
    @if($products->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                <div class="relative bg-slate-800/50 backdrop-blur-md border border-white/10 rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 group">
                    <!-- Imagem do Produto -->
                    <div class="relative overflow-hidden h-48">
                        @if($product->image)
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-slate-900 flex items-center justify-center">
                                <i data-lucide="image-off" class="w-12 h-12 text-blue-500/30"></i>
                            </div>
                        @endif
                        <!-- Badge de Status -->
                        <div class="absolute top-3 right-3">
                            @if($product->is_active)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-500/80 text-white backdrop-blur-sm border border-white/20">
                                    <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i> Ativo
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-500/80 text-white backdrop-blur-sm border border-white/20">
                                    <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i> Inativo
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Informações do Produto -->
                    <div class="p-5 flex flex-col h-[calc(100%-12rem)]">
                        <div class="flex-grow">
                            <h3 class="text-lg font-bold text-white group-hover:text-cyan-300 transition-colors duration-300 line-clamp-2 mb-2">
                                {{ $product->name }}
                            </h3>
                            @if($product->description)
                                <p class="text-gray-400 text-sm mb-4 line-clamp-2">{{ $product->description }}</p>
                            @endif
                        </div>
                        
                        <div class="flex items-center justify-between text-xs text-gray-400 mb-4">
                            @if($product->categoria_produto)
                                <span class="text-xs px-3 py-1 bg-blue-900/50 text-blue-300 rounded-full font-medium border border-blue-500/30">
                                    {{ \App\Enums\CategoriaProduto::all()[$product->categoria_produto] ?? 'N/A' }}
                                </span>
                            @endif
                            <span class="flex items-center">
                                <i data-lucide="{{ $product->tipo_produto == 1 ? 'monitor' : 'package' }}" class="w-3 h-3 mr-1.5"></i>
                                {{ $product->tipo_produto == 1 ? 'Digital' : 'Físico' }}
                            </span>
                        </div>
                        
                        <div class="border-t border-white/10 pt-4">
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-2xl font-bold text-cyan-300">
                                    R$ {{ number_format($product->price, 2, ',', '.') }}
                                </div>
                            </div>
                            
                            <!-- Ações -->
                            <div class="flex space-x-2">
                                <a href="{{ route('checkout.show', $product->hash_id) }}" class="flex-1 inline-flex items-center justify-center px-3 py-2.5 bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white rounded-lg text-sm font-semibold transition-all duration-300 shadow-lg hover:shadow-blue-500/20">
                                    <i data-lucide="shopping-cart" class="w-4 h-4 mr-2"></i>
                                    <span>Checkout</span>
                                </a>
                                
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="px-3 py-2.5 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm font-medium transition-all duration-300">
                                        <i data-lucide="more-vertical" class="w-4 h-4"></i>
                                    </button>
                                    
                                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 bottom-full mb-2 w-48 bg-slate-800 rounded-lg shadow-xl border border-white/10 z-10">
                                        <button onclick="editProduct({{ $product->id }}, '{{ $product->name }}', '{{ $product->description }}', {{ $product->price }}, {{ $product->is_active ? 'true' : 'false' }}, {{ $product->tipo_produto ?? 'null' }}, {{ $product->categoria_produto ?? 'null' }}, '{{ $product->url_venda ?? '' }}', '{{ $product->nome_sac ?? '' }}', '{{ $product->email_sac ?? '' }}', '{{ $product->image ? Storage::url($product->image) : '' }}')" 
                                                class="w-full flex items-center px-4 py-3 text-sm text-gray-300 hover:bg-blue-900/30 hover:text-white transition-colors duration-200 rounded-t-lg">
                                            <i data-lucide="edit" class="w-4 h-4 mr-3 text-blue-400"></i> Editar
                                        </button>
                                        <button onclick="confirmDelete('{{ $product->id }}', '{{ $product->name }}')" 
                                                class="w-full flex items-center px-4 py-3 text-sm text-red-400 hover:bg-red-900/30 hover:text-red-300 transition-colors duration-200 border-t border-white/10 rounded-b-lg">
                                            <i data-lucide="trash-2" class="w-4 h-4 mr-3"></i> Excluir
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($products->hasPages())
            <div class="mt-8">{{ $products->links() }}</div>
        @endif
    @else
        <!-- Estado Vazio -->
        <div class="bg-slate-900/50 backdrop-blur-md border border-blue-500/20 rounded-2xl p-12 text-center shadow-xl">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
                <i data-lucide="package-search" class="w-10 h-10 text-white"></i>
            </div>
            <h3 class="text-2xl font-bold bg-gradient-to-r from-blue-300 to-cyan-400 bg-clip-text text-transparent mb-3">Nenhum produto encontrado</h3>
            <p class="text-gray-400 mb-8 max-w-md mx-auto">Comece a vender agora mesmo. Clique no botão abaixo para cadastrar seu primeiro produto.</p>
            <button onclick="openProductModal()" class="inline-flex items-center space-x-2 px-8 py-3 bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-blue-500/20">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Criar Primeiro Produto</span>
            </button>
        </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 bg-black/70 z-50 hidden backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl max-w-md w-full p-8 transform transition-all shadow-2xl border border-purple-500/30">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i data-lucide="trash-2" class="w-6 h-6 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold bg-gradient-to-r from-red-400 to-pink-400 bg-clip-text text-transparent">Confirmar Exclusão</h3>
                </div>
                <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>
            
            <div class="mb-8">
                <p class="text-gray-300 mb-3">
                    Tem certeza que deseja excluir o produto <strong id="product-name" class="text-white"></strong>?
                </p>
                <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4">
                    <p class="text-sm text-red-400 flex items-center">
                        <i data-lucide="alert-triangle" class="w-4 h-4 mr-2"></i>
                        Esta ação não pode ser desfeita e removerá permanentemente o produto.
                    </p>
                </div>
            </div>

            <div class="flex space-x-4">
                <button onclick="closeDeleteModal()" 
                        class="flex-1 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-xl font-medium transition-all duration-300">
                    Cancelar
                </button>
                <form id="delete-form" method="POST" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full px-6 py-3 bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white rounded-xl font-medium transition-all duration-300 shadow-lg hover:shadow-xl">
                        Excluir Produto
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Produto (Criar/Editar) -->
<div id="product-modal" class="fixed inset-0 bg-black/70 z-50 hidden backdrop-blur-sm transition-opacity duration-300">
    <div class="flex items-center justify-center min-h-screen p-4">
        
        <div class="bg-white dark:bg-slate-800 rounded-2xl max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl border border-blue-500/20 transform transition-all duration-300">
            
            <!-- Cabeçalho do Modal -->
            <div class="sticky top-0 bg-gradient-to-r from-blue-600 to-cyan-500 p-5 rounded-t-2xl flex items-center justify-between flex-shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i data-lucide="package" class="w-5 h-5 text-white"></i>
                    </div>
                    <h3 id="modal-title" class="text-xl font-bold text-white">Novo Produto</h3>
                </div>
                <button onclick="closeProductModal()" class="text-white/80 hover:text-white transition-colors">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </button>
            </div>

            <!-- Corpo do Modal com Scroll -->
            <form id="product-form" method="POST" enctype="multipart/form-data" class="p-6 space-y-6 overflow-y-auto">
                @csrf
                <div id="method-field"></div>

                <!-- Seção: Informações Básicas -->
                <div class="p-5 bg-gray-50 dark:bg-slate-900/50 rounded-xl border border-gray-200 dark:border-white/10 space-y-4">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                        <i data-lucide="info" class="w-5 h-5 mr-2 text-blue-500"></i>
                        Informações Básicas
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="product-name-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome do Produto *</label>
                            <input type="text" name="name" id="product-name-input" required
                                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all">
                        </div>
                        
                        <div>
                            <label for="product-price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Preço (R$) *</label>
                            <input type="number" name="price" id="product-price" step="0.01" min="0" required
                                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="product-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Descrição</label>
                        <textarea name="description" id="product-description" rows="3"
                                  class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all resize-none"></textarea>
                    </div>
                </div>

                <!-- Seção: Configurações -->
                <div class="p-5 bg-gray-50 dark:bg-slate-900/50 rounded-xl border border-gray-200 dark:border-white/10 space-y-4">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                        <i data-lucide="settings" class="w-5 h-5 mr-2 text-blue-500"></i>
                        Configurações
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="product-status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                            <select name="is_active" id="product-status"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="product-type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
                            <select name="tipo_produto" id="product-type"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all">
                                <option value="">Selecione</option>
                                <option value="1">Digital</option>
                                <option value="0">Físico</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="product-category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoria</label>
                            <select name="categoria_produto" id="product-category"
                                    class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all">
                                <option value="">Selecione</option>
                                @if(class_exists('\App\Enums\CategoriaProduto'))
                                    @foreach(\App\Enums\CategoriaProduto::all() as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Seção: Imagem -->
                <div class="p-5 bg-gray-50 dark:bg-slate-900/50 rounded-xl border border-gray-200 dark:border-white/10 space-y-4">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                        <i data-lucide="image" class="w-5 h-5 mr-2 text-blue-500"></i>
                        Imagem do Produto
                    </h4>
                    
                    <div class="flex items-center space-x-4">
                        <div id="image-preview" class="w-20 h-20 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center border-2 border-dashed border-gray-300 dark:border-gray-600 hidden flex-shrink-0">
                            <img id="preview-img" class="w-full h-full object-cover rounded-lg" alt="Preview">
                        </div>
                        <div class="flex-1">
                            <label for="product-image" class="w-full flex items-center text-sm px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-500 dark:text-gray-400 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-600 transition-all">
                                <i data-lucide="upload-cloud" class="w-4 h-4 inline-block mr-2"></i>
                                <span id="file-name" class="truncate">Clique para enviar uma imagem</span>
                            </label>
                            <input type="file" name="image" id="product-image" accept="image/*" class="hidden">
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Formatos: JPG, PNG, GIF (máx. 2MB)</p>
                        </div>
                    </div>
                </div>

                <!-- Seção: Informações de Suporte -->
                <div class="p-5 bg-gray-50 dark:bg-slate-900/50 rounded-xl border border-gray-200 dark:border-white/10 space-y-4">
                    <h4 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center">
                        <i data-lucide="life-buoy" class="w-5 h-5 mr-2 text-blue-500"></i>
                        UPSELL e SAC
                    </h4>
                    
                    <div>
                        <label for="product-url" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Link do Upsell - (Direcionado após a compra)</label>
                        <input type="url" name="url_venda" id="product-url"
                               class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="product-sac-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nome do SAC (Opcional)</label>
                            <input type="text" name="nome_sac" id="product-sac-name"
                                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all">
                        </div>
                        
                        <div>
                            <label for="product-sac-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email SAC (Opcional)</label>
                            <input type="email" name="email_sac" id="product-sac-email"
                                   class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white transition-all">
                        </div>
                    </div>
                </div>

            </form>

            <!-- Rodapé do Modal -->
            <div class="p-5 mt-auto border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/50 rounded-b-2xl flex-shrink-0">
                <div class="flex space-x-4">
                    <button type="button" onclick="closeProductModal()" 
                            class="flex-1 px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-xl font-semibold transition-all">
                        Cancelar
                    </button>
                    <button type="submit" form="product-form"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 text-white rounded-xl font-semibold transition-all shadow-lg hover:shadow-blue-500/30">
                        <span id="submit-text">Criar Produto</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Adicione este pequeno script no final da sua view, dentro da seção @section('content') --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('product-image');
    const fileNameSpan = document.getElementById('file-name');
    const imagePreviewContainer = document.getElementById('image-preview');
    const imagePreviewImg = document.getElementById('preview-img');

    // Lógica para o campo de upload de imagem
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                fileNameSpan.textContent = file.name;

                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreviewImg.src = e.target.result;
                    imagePreviewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                fileNameSpan.textContent = 'Clique para enviar uma imagem';
                imagePreviewContainer.classList.add('hidden');
            }
        });
    }

    // Renomeei o campo de SAC para evitar conflito de ID com a função `editProduct`
    const sacNameInput = document.getElementById('product-sac-name');
    const sacEmailInput = document.getElementById('product-sac-email');

    // A função `editProduct` precisa ser atualizada para usar os novos IDs
    // (Isso deve ser feito no seu script principal da página de produtos)
    // Exemplo de como a chamada da função deve ser:
    // editProduct(id, name, ..., url, sacName, sacEmail, imageUrl)
});
</script>

{{-- Adicione este pequeno script no final da sua view, dentro da seção @section('content') --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const fileInput = document.getElementById('product-image');
    const fileNameSpan = document.getElementById('file-name');

    if (fileInput) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileNameSpan.textContent = this.files[0].name;
            } else {
                fileNameSpan.textContent = 'Clique para enviar uma imagem';
            }
        });
    }
});
</script>

<script>
    function toggleDarkMode() {
        const html = document.documentElement;
        const isDark = html.classList.contains('dark');
        
        if (isDark) {
            html.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            html.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    }

    function initializeTheme() {
        const savedTheme = localStorage.getItem('theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    function confirmDelete(productId, productName) {
        document.getElementById('product-name').textContent = productName;
        document.getElementById('delete-form').action = `/associacao/products/${productId}`;
        document.getElementById('delete-modal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    let isEditMode = false;
    let editProductId = null;

    function openProductModal() {
        isEditMode = false;
        editProductId = null;
        document.getElementById('modal-title').textContent = 'Novo Produto';
        document.getElementById('submit-text').textContent = 'Criar Produto';
        document.getElementById('product-form').action = '{{ route("associacao.products.store") }}';
        document.getElementById('method-field').innerHTML = '';
        
        // Limpar formulário
        document.getElementById('product-form').reset();
        document.getElementById('image-preview').classList.add('hidden');
        
        document.getElementById('product-modal').classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function editProduct(id, name, description, price, isActive, type, category, url, sacName, sacEmail, imageUrl) {
    isEditMode = true;
    editProductId = id;
    document.getElementById('modal-title').textContent = 'Editar Produto';
    document.getElementById('submit-text').textContent = 'Atualizar Produto';
    document.getElementById('product-form').action = `/associacao/products/${id}`;
    document.getElementById('method-field').innerHTML = '<input type="hidden" name="_method" value="PUT">';

    // Preencher os campos
    document.getElementById('product-name-input').value = name;
    document.getElementById('product-description').value = description;
    document.getElementById('product-price').value = price;
    document.getElementById('product-status').value = isActive ? 1 : 0;
    document.getElementById('product-type').value = type ?? '';
    document.getElementById('product-category').value = category ?? '';
    document.getElementById('product-url').value = url ?? '';

    // AQUI estava faltando
    document.getElementById('product-sac-name').value = sacName ?? '';
    document.getElementById('product-sac-email').value = sacEmail ?? '';

    // Imagem
    if (imageUrl) {
        const imagePreviewContainer = document.getElementById('image-preview');
        const imagePreviewImg = document.getElementById('preview-img');
        imagePreviewImg.src = imageUrl;
        imagePreviewContainer.classList.remove('hidden');
    }

    // Abrir modal
    document.getElementById('product-modal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}


    function closeProductModal() {
        document.getElementById('product-modal').classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    // Preview de imagem
    document.getElementById('product-image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-img').src = e.target.result;
                document.getElementById('image-preview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById('image-preview').classList.add('hidden');
        }
    });

    // Fechar modal com Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
            closeProductModal();
        }
    });

    // Fechar modal clicando fora
    document.getElementById('delete-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    document.getElementById('product-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeProductModal();
        }
    });

    // Inicializa os ícones da página
    document.addEventListener('DOMContentLoaded', () => {
        initializeTheme();
        lucide.createIcons();
    });
</script>
@endsection
