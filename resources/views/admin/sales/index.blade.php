@extends('layouts.app')

@section('title', 'Vendas - Administração')

{{-- Adicionando CSS para as animações da aba de IA --}}

@section('content')


{{-- O x-data agora gerencia dois modais: o de detalhes e o de IA --}}
<div class="space-y-6" x-data="salesPage()">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Vendas</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie todas as vendas da plataforma</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-white/5 p-6">
        <form action="{{ route('admin.sales.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                    <input type="text" id="search" name="search" placeholder="ID, hash, nome, email..." value="{{ request('search') }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <option value="">Todos</option>
                        <option value="paid" @selected(request('status') == 'paid')>Pago</option>
                        <option value="awaiting_payment" @selected(request('status') == 'awaiting_payment')>Pendente</option>
                        <option value="cancelled" @selected(request('status') == 'cancelled')>Cancelado</option>
                        <option value="refunded" @selected(request('status') == 'refunded')>Reembolsado</option>
                        <option value="chargeback" @selected(request('status') == 'chargeback')>Chargeback</option>
                    </select>
                </div>
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Método de Pagamento</label>
                    <select id="payment_method" name="payment_method" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        <option value="">Todos</option>
                        <option value="credit_card" @selected(request('payment_method') == 'credit_card')>Cartão de Crédito</option>
                        <option value="pix" @selected(request('payment_method') == 'pix')>Pix</option>
                        <option value="boleto" @selected(request('payment_method') == 'boleto')>Boleto</option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <a href="{{ route('admin.sales.index') }}" class="w-full text-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-lg">Limpar</a>
                    <button type="submit" class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg">
                        <i data-lucide="filter" class="w-4 h-4 mr-2 inline"></i>Filtrar
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabela de Vendas -->
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white dark:bg-black">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Transação</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-black divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-black/5 dark:hover:bg-white/5">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $sale->transaction_hash ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $sale->id }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $sale->user->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $sale->user->email ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->product->name ?? $sale->plan->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">R$ {{ number_format($sale->total_price, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{!! $sale->getStatusBadge() !!}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            {{-- Este botão chama a função openModal() do Alpine.js com a URL para buscar os dados --}}
                            <button @click="openModal('{{ route('admin.sales.show', $sale) }}')" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 font-semibold">
                                Detalhes
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-12">
                            <div class="flex flex-col items-center">
                                <i data-lucide="shopping-cart" class="w-12 h-12 text-gray-400 mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Nenhuma venda encontrada</h3>
                                <p class="text-gray-500 dark:text-gray-400">Nenhuma venda corresponde aos filtros aplicados.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="bg-white dark:bg-black px-6 py-3 border-t border-gray-200 dark:border-white/5">
            {{ $sales->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    <!-- =================================================================== -->
    <!-- MODAL DE DETALHES DA VENDA (Controlado pelo Alpine.js) -->
    <!-- =================================================================== -->
    <div x-show="isModalOpen" @keydown.escape.window="closeModal()" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
       <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            
            <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" @click="closeModal()" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="isModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                 class="inline-block align-bottom modal-green-gradient text-gray-200 rounded-lg text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-emerald-500/20">
                
                <div class="px-6 pt-5 pb-4 border-b border-emerald-500/10">
                    <div class="flex justify-between items-center">
                        <h3 class="text-xl leading-6 font-bold text-white" id="modal-title">
                            Detalhes da Transação <span class="text-emerald-400" x-text="sale ? '#' + sale.transaction_hash : ''"></span>
                        </h3>
                        <button @click="closeModal()" class="text-gray-400 hover:text-white">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>
                </div>

                {{-- Spinner de carregamento --}}
                <div x-show="loading" class="p-12 text-center min-h-[350px] flex items-center justify-center">
                    <div>
                        <i data-lucide="loader-2" class="w-12 h-12 animate-spin mx-auto text-gray-400"></i>
                        <p class="mt-4">Carregando detalhes...</p>
                    </div>
                </div>

                {{-- Conteúdo principal do modal, com as abas --}}
                <div x-show="!loading && sale" class="p-6" x-data="{ modalTab: 'venda' }">
                    <div class="border-b border-emerald-500/20">
                        <nav class="-mb-px flex space-x-6 overflow-x-auto" aria-label="Tabs">
                            <button @click="modalTab = 'venda'" :class="{ 'border-emerald-400 text-emerald-400': modalTab === 'venda', 'border-transparent text-gray-400 hover:text-white': modalTab !== 'venda' }" class="whitespace-nowrap pb-3 px-1 border-b-2 font-semibold text-sm flex items-center"><i data-lucide="receipt" class="w-4 h-4 mr-2"></i>VENDA</button>
                            <button @click="modalTab = 'produtos'" :class="{ 'border-emerald-400 text-emerald-400': modalTab === 'produtos', 'border-transparent text-gray-400 hover:text-white': modalTab !== 'produtos' }" class="whitespace-nowrap pb-3 px-1 border-b-2 font-semibold text-sm flex items-center"><i data-lucide="package" class="w-4 h-4 mr-2"></i>PRODUTOS</button>
                            <button @click="modalTab = 'cliente'" :class="{ 'border-emerald-400 text-emerald-400': modalTab === 'cliente', 'border-transparent text-gray-400 hover:text-white': modalTab !== 'cliente' }" class="whitespace-nowrap pb-3 px-1 border-b-2 font-semibold text-sm flex items-center"><i data-lucide="user" class="w-4 h-4 mr-2"></i>CLIENTE</button>
                            <button @click="modalTab = 'envolvidos'" :class="{ 'border-emerald-400 text-emerald-400': modalTab === 'envolvidos', 'border-transparent text-gray-400 hover:text-white': modalTab !== 'envolvidos' }" class="whitespace-nowrap pb-3 px-1 border-b-2 font-semibold text-sm flex items-center"><i data-lucide="users" class="w-4 h-4 mr-2"></i>ENVOLVIDOS</button>
                            <button @click="modalTab = 'utms'" :class="{ 'border-emerald-400 text-emerald-400': modalTab === 'utms', 'border-transparent text-gray-400 hover:text-white': modalTab !== 'utms' }" class="whitespace-nowrap pb-3 px-1 border-b-2 font-semibold text-sm flex items-center"><i data-lucide="radio-tower" class="w-4 h-4 mr-2"></i>UTM'S</button>
                            <button @click="modalTab = 'notificacoes'" :class="{ 'border-emerald-400 text-emerald-400': modalTab === 'notificacoes', 'border-transparent text-gray-400 hover:text-white': modalTab !== 'notificacoes' }" class="whitespace-nowrap pb-3 px-1 border-b-2 font-semibold text-sm flex items-center"><i data-lucide="mail" class="w-4 h-4 mr-2"></i>NOTIFICAÇÕES</button>
                            <button @click.prevent="openIaModal()" class="ia-pulse whitespace-nowrap py-2 px-3 border-2 border-lime-400 rounded-lg font-semibold text-sm flex items-center"><i data-lucide="sparkles" class="w-4 h-4 mr-2"></i>AUTOMAÇÕES COM IA</button>
                        </nav>
                    </div>

                    <div class="mt-6 min-h-[250px]">
                        <!-- Aba Venda -->
                        <div x-show="modalTab === 'venda'" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm" x-cloak>
                            <template x-for="[key, value] in Object.entries(getSaleDetails())" :key="key">
                                <div class="flex justify-between border-b border-gray-700 py-2">
                                    <span class="font-medium text-gray-400" x-text="key"></span>
                                    <span class="text-right break-all" x-text="value"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Aba Produtos -->
                        <div x-show="modalTab === 'produtos'" x-cloak>
                            <div class="space-y-4">
                                <div class="grid grid-cols-3 gap-4 text-xs font-bold text-gray-400 uppercase">
                                    <span>Produto</span>
                                    <span class="text-center">Preço de Venda</span>
                                    <span class="text-right">Tipo de Venda</span>
                                </div>
                                <div class="grid grid-cols-3 gap-4 items-center bg-gray-800/50 p-4 rounded-lg">
                                    <div class="flex items-center">
                                        <img :src="sale.product ? sale.product.image_url : (sale.plan ? sale.plan.image_url : 'https://via.placeholder.com/150' )" class="w-12 h-12 rounded-md mr-4 object-cover" alt="Imagem do Produto/Plano">
                                        <div>
                                            <p class="font-medium" x-text="sale.product ? sale.product.name : (sale.plan ? sale.plan.name : 'Item não encontrado')"></p>
                                            <p class="text-xs text-gray-400">Oferta: <span x-text="sale.product ? sale.product.offer_hash_goat : (sale.plan ? sale.plan.offer_hash : 'N/A')"></span></p>
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <span x-text="'R$ ' + parseFloat(sale.total_price).toFixed(2)"></span>
                                    </div>
                                    <div class="text-right">
                                        <span class="bg-yellow-600 text-white px-3 py-1 rounded-full text-xs font-semibold">Venda Principal</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Aba Cliente -->
                        <div x-show="modalTab === 'cliente'" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm" x-cloak>
                            <template x-for="[key, value] in Object.entries(getClientDetails())" :key="key">
                                <div class="flex justify-between border-b border-gray-700 py-2">
                                    <span class="font-medium text-gray-400" x-text="key"></span>
                                    <span class="text-right break-all" x-text="value"></span>
                                </div>
                            </template>
                        </div>

                        <!-- ABA ENVOLVIDOS -->
                        <div x-show="modalTab === 'envolvidos'" class="space-y-4 text-sm" x-cloak>
                            <div class="flex justify-between border-b border-gray-700 py-2">
                                <span class="font-medium text-gray-400">Valor da Transação</span>
                                <span class="text-right" x-text="'R$ ' + parseFloat(sale.total_price).toFixed(2)"></span>
                            </div>
                            <div class="flex justify-between border-b border-gray-700 py-2">
                                <span class="font-medium text-gray-400">Taxas</span>
                                <span class="text-right text-red-400" x-text="'- R$ ' + parseFloat(sale.fee_amount).toFixed(2)"></span>
                            </div>
                            <div class="font-bold text-gray-400 uppercase pt-4">Envolvidos</div>
                            <div class="flex items-center justify-between bg-gray-800/50 p-3 rounded-lg">
                                <div class="flex items-center">
                                    <img :src="sale.association.creator_profile ? sale.association.creator_profile.profile_image_url : 'https://ui-avatars.com/api/?name=P&background=22c55e&color=fff&size=200'" class="w-10 h-10 rounded-full mr-3">
                                    <div>
                                        <p class="font-semibold" x-text="sale.association.nome"></p>
                                        <p class="text-xs text-gray-500" x-text="sale.association.documento_formatado"></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="bg-yellow-600 text-white px-2 py-0.5 rounded-full text-xs font-semibold">Produtor</span>
                                    <p class="font-semibold mt-1" x-text="'R$ ' + (sale.total_price - sale.fee_amount ).toFixed(2)"></p>
                                </div>
                            </div>
                        </div>

                        <!-- ABA UTM'S -->
                        <div x-show="modalTab === 'utms'" class="grid grid-cols-1 gap-y-2 text-sm" x-cloak>
                            <template x-for="[key, value] in Object.entries(getUtmDetails())" :key="key">
                                <div class="flex justify-between border-b border-gray-700 py-2">
                                    <span class="font-medium text-gray-400 uppercase" x-text="key"></span>
                                    <span class="text-right break-all" x-text="value || 'N/A'"></span>
                                </div>
                            </template>
                        </div>

                        <!-- ABA NOTIFICAÇÕES -->
                        <div x-show="modalTab === 'notificacoes'" class="flex flex-col items-center justify-center text-center h-full pt-8" x-cloak>
                            <i data-lucide="wrench" class="w-16 h-16 text-gray-500"></i>
                            <h4 class="mt-4 text-lg font-bold">Em Desenvolvimento</h4>
                            <p class="text-gray-400">Ainda estamos trabalhando no histórico de notificações. Volte em breve!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- MODAL DA FUNCIONALIDADE DE IA -->
    <!-- =================================================================== -->
    <div x-show="isIaModalOpen" @keydown.escape.window="closeIaModal()" class="fixed inset-0 z-[60] overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="isIaModalOpen" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-80" @click="closeIaModal()"></div>
            <div x-show="isIaModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" 
                 class="relative modal-ia-gradient border border-lime-400/30 rounded-2xl shadow-2xl shadow-lime-500/20 w-full max-w-lg p-8 text-center">
                <div class="absolute -top-12 left-1/2 -translate-x-1/2 bg-lime-400 text-slate-900 rounded-full p-4 border-4 border-slate-800">
                    <i data-lucide="sparkles" class="w-10 h-10 animate-pulse"></i>
                </div>
                <h2 class="text-3xl font-bold text-lime-300 mt-8">Automações com IA</h2>
                <p class="mt-2 text-lg font-semibold text-white">Uma nova era na recuperação de vendas.</p>
                <p class="mt-4 text-gray-300">
                    Esta funcionalidade está em fase **Beta Fechado**. Em breve, você poderá ativar um vendedor com Inteligência Artificial que cuidará de todo o processo de recuperação de vendas perdidas, como carrinhos abandonados e pagamentos recusados, de forma automática e personalizada.
                </p>
                <button @click="closeIaModal()" class="mt-8 w-full bg-lime-400 hover:bg-lime-300 text-slate-900 font-bold py-3 px-4 rounded-lg transition-transform transform hover:scale-105">
                    Entendido, mal posso esperar!
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function salesPage() {
        return {
            isModalOpen: false,
            isIaModalOpen: false, // Estado para o novo modal
            loading: false,
            sale: null,
            openModal(url) {
                this.loading = true;
                this.isModalOpen = true;
                document.body.style.overflow = 'hidden';

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        this.sale = data;
                        this.loading = false;
                        this.$nextTick(() => {
                            lucide.createIcons(); // Recria os ícones dentro do modal
                        });
                    })
                    .catch(error => {
                        console.error('Erro ao buscar detalhes da venda:', error);
                        this.loading = false;
                        this.closeModal();
                    });
            },
            closeModal() {
                this.isModalOpen = false;
                this.sale = null;
                document.body.style.overflow = 'auto';
            },
            openIaModal() {
                this.isIaModalOpen = true;
                this.$nextTick(() => { lucide.createIcons(); });
            },
            closeIaModal() {
                this.isIaModalOpen = false;
            },
            getSaleDetails() {
                if (!this.sale) return {};
                return {
                    'Venda': this.sale.transaction_hash,
                    'Status': this.sale.status,
                    'Método de pagamento': this.sale.payment_method ? this.sale.payment_method.replace(/_/g, ' ') : 'N/A',
                    'Código Copia e Cola PIX': this.sale.transactions[0]?.details?.qr_code || 'N/A',
                    'Parcelas': '1',
                    'Tipo de Operação': 'Pagamento Único',
                    'Data da criação': new Date(this.sale.created_at).toLocaleString('pt-BR'),
                    'Líquido Total': 'R$ ' + parseFloat(this.sale.total_price).toFixed(2),
                };
            },
            getClientDetails() {
                if (!this.sale || !this.sale.user) return {};
                return {
                    'ID': this.sale.user.id,
                    'Nome': this.sale.user.name,
                    'E-mail': this.sale.user.email,
                    'Celular': this.sale.user.telefone_formatado || 'N/A',
                    'CPF': this.sale.user.documento_formatado || 'N/A',
                    'Endereço': this.sale.user.endereco || 'N/A',
                };
            },
            getUtmDetails() {
                if (!this.sale || !this.sale.utm_params) return {
                    'SRC': 'N/A', 'UTM Source': 'N/A', 'UTM Medium': 'N/A', 
                    'UTM Campaign': 'N/A', 'UTM Term': 'N/A', 'UTM Content': 'N/A'
                };
                return {
                    'SRC': this.sale.utm_params.src,
                    'UTM Source': this.sale.utm_params.utm_source,
                    'UTM Medium': this.sale.utm_params.utm_medium,
                    'UTM Campaign': this.sale.utm_params.utm_campaign,
                    'UTM Term': this.sale.utm_params.utm_term,
                    'UTM Content': this.sale.utm_params.utm_content,
                };
            }
        }
    }
    
    document.addEventListener('alpine:init', () => {
        Alpine.magic('cloak', el => {
            el.setAttribute('x-cloak', true)
        })
    });
</script>
@endsection
