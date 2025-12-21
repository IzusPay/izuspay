<div class="space-y-8">
    
    {{-- CARDS DE SAQUE E CONVERSÃO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {{-- Saque Pix Card --}}
        <div class="bg-white dark:bg-black p-6 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Saque Pix</h3>
                <button id="open-pix-modal" class="bg-black dark:bg-white text-white dark:text-black text-sm font-medium px-4 py-2 rounded-lg hover:bg-gray-800 dark:hover:bg-black/10 transition-colors">
                    Efetuar Saque
                </button>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                {{-- Saldo Disponível --}}
                <div class="bg-gray-50 dark:bg-slate-800 p-4 rounded-lg border border-gray-200 dark:border-slate-700">
                    <p class="text-sm text-gray-500 dark:text-slate-300 flex items-center">
                        <i data-lucide="wallet" class="w-4 h-4 mr-1.5 text-gray-400 dark:text-slate-400"></i>
                        Saldo Disponível
                    </p>
                    {{-- Usando o saldo disponível do objeto $wallet, se existir --}}
                    <p class="text-2xl font-bold text-slate-900 dark:text-white mt-1">R$ {{ number_format($wallet->balance ?? 0.73, 2, ',', '.') }}</p>
                </div>
            </div>
        </div>

        {{-- Saque Cartão Card --}}
    </div>

    {{-- Histórico de saques e Taxas --}}
    <div class="space-y-6">
        {{-- Sub-abas Histórico de saques e Taxas --}}
        <div class="border-b border-gray-200 dark:border-slate-800">
            <nav class="-mb-px flex space-x-8" aria-label="Sub Tabs">
                <button class="sub-tab-button inline-flex items-center py-4 px-1 border-b-2 font-medium text-base transition-colors duration-200 border-black dark:border-white text-black dark:text-white" data-sub-tab="history">
                    Histórico de saques
                </button>
                <button class="sub-tab-button inline-flex items-center py-4 px-1 border-b-2 font-medium text-base transition-colors duration-200 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600" data-sub-tab="fees">
                    Taxas
                </button>
            </nav>
        </div>

        {{-- Conteúdo das Sub-abas --}}
        <div class="sub-tab-content" id="history">
            <div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-800">
                        <thead class="bg-gray-50 dark:bg-slate-900">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">Data</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">Valor</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">Método</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">Chave PIX</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">Tipo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">Processado</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">Ref. Externa</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-300 uppercase tracking-wider">ID</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-black divide-y divide-gray-200 dark:divide-slate-800">
                            @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $withdrawal->created_at->format('d/m/y, H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 dark:text-white">R$ {{ number_format($withdrawal->amount, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $withdrawal->method ?? 'PIX' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $withdrawal->key ?? $withdrawal->bankAccount->email ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $withdrawal->type ?? 'E-mail' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @php
                                        $statusText = ucfirst($withdrawal->status);
                                        $statusClass = [
                                            'Pending' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
                                            'Completed' => 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
                                            'Failed' => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
                                        ][$withdrawal->status] ?? 'bg-gray-100 dark:bg-slate-800 text-gray-800 dark:text-slate-200';
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $withdrawal->processed_at ? $withdrawal->processed_at->format('d/m/y, H:i') : '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $withdrawal->external_ref ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-slate-400">{{ $withdrawal->id }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center text-gray-500 dark:text-slate-400">
                                    Nenhum saque encontrado.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Paginação --}}
                <div class="px-6 py-4 bg-white dark:bg-black border-t border-gray-200 dark:border-slate-800 flex items-center justify-between">
                    <p class="text-sm text-gray-500 dark:text-slate-400">Página {{ $withdrawals->currentPage() }} de {{ $withdrawals->lastPage() }} ({{ $withdrawals->total() }} saques)</p>
                    <div class="flex items-center space-x-2">
                        {{ $withdrawals->links() }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Conteúdo da Sub-aba Taxas --}}
        <div class="sub-tab-content hidden" id="fees">
            @include('associacao.financeiro._fees')
        </div>
    </div>

    {{-- MODAL DE SAQUE PIX --}}
    {{-- MODAL DE SAQUE PIX --}}
<div id="pix-withdrawal-modal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-black rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-md overflow-hidden">
        
        {{-- Cabeçalho --}}
        <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Realizar Saque • PIX</h3>
            <button id="close-pix-modal" class="text-gray-400 dark:text-slate-300 hover:text-gray-600 dark:hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        {{-- Form --}}
        <form action="{{ route('associacao.financeiro.withdrawals.store') }}" method="POST" class="p-5 space-y-4">
            @csrf

            {{-- Alerta --}}
            <div class="bg-gray-50 dark:bg-slate-800 px-3 py-2 rounded-md border border-gray-200 dark:border-slate-700 flex items-center space-x-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-yellow-500"></i>
                <p class="text-xs text-gray-700 dark:text-slate-300">
                    Saldo disponível:
                    <span class="font-semibold">
                        R$ {{ number_format($wallet->balance ?? 0.73, 2, ',', '.') }}
                    </span>
                </p>
            </div>

            {{-- Valor --}}
            <div>
                <label for="amount" class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">
                    Valor do saque
                </label>
                <input
                    type="number"
                    name="amount"
                    id="amount"
                    step="0.01"
                    min="0.01"
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0"
                    placeholder="0,00"
                >
            </div>

            {{-- Método --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">
                    Método
                </label>
                <select
                    disabled
                    class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-gray-50 dark:bg-slate-800 cursor-not-allowed text-slate-900 dark:text-slate-200"
                >
                    <option>PIX</option>
                </select>
                <input type="hidden" name="method" value="pix">
            </div>

            {{-- Tipo de Chave --}}
            <div>
                <label for="pix_key_type" class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">
                    Tipo de chave PIX
                </label>
                <select
    name="pix_key_type"
    id="pix_key_type"
    required
    class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0"
>
    <option value="">Selecione</option>
    <option value="cpf">CPF</option>
    <option value="cnpj">CNPJ</option>
    <option value="email">E-mail</option>
    <option value="phone">Telefone</option>
    <option value="random">Chave aleatória</option>
</select>

            </div>

            {{-- Chave --}}
            <div>
                <label for="pix_key" class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">
                    Chave PIX
                </label>
               <input
                    type="text"
                    name="pix_key"
                    id="pix_key"
                    required
                    inputmode="numeric"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0"
                    placeholder="Digite a chave PIX"
                />


            </div>

            {{-- Ações --}}
            <div class="flex justify-end gap-3 pt-2">
                <button
                    type="button"
                    id="cancel-pix-modal"
                    class="px-4 py-2 text-sm text-gray-600 dark:text-slate-300 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800"
                >
                    Cancelar
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 text-sm bg-black dark:bg-white text-white dark:text-black rounded-lg hover:bg-gray-800 dark:hover:bg-black/10"
                >
                    Continuar
                </button>
            </div>
        </form>
    </div>
</div>


    {{-- Script para as Sub-abas e Modal --}}
    @push('scripts')
    <script>
        // Script para as Sub-abas (Mantido)

        document.addEventListener('DOMContentLoaded', function() {
            // --- Script para as Sub-abas ---
            const subTabButtons = document.querySelectorAll('.sub-tab-button');
            const subTabContents = document.querySelectorAll('.sub-tab-content');

            subTabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const targetTabId = button.dataset.subTab;

                    // 1. Atualiza a aparência dos BOTÕES
                    subTabButtons.forEach(btn => {
                        if (btn.dataset.subTab === targetTabId) {
                            // ATIVA o botão clicado
                            btn.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400', 'hover:text-slate-700', 'dark:hover:text-white', 'hover:border-slate-300', 'dark:hover:border-slate-600');
                            btn.classList.add('border-black', 'dark:border-white', 'text-black', 'dark:text-white');
                        } else {
                            // DESATIVA os outros botões
                            btn.classList.remove('border-black', 'dark:border-white', 'text-black', 'dark:text-white');
                            btn.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400', 'hover:text-slate-700', 'dark:hover:text-white', 'hover:border-slate-300', 'dark:hover:border-slate-600');
                        }
                    });

                    // 2. Esconde e mostra o CONTEÚDO correspondente
                    subTabContents.forEach(content => {
                        if (content.id === targetTabId) {
                            content.classList.remove('hidden'); // Mostra o conteúdo da aba clicada
                        } else {
                            content.classList.add('hidden'); // Esconde os outros
                        }
                    });
                });
            });

            // --- Script para a Modal de Saque PIX ---
            const openModalBtn = document.getElementById('open-pix-modal');
            const closeModalBtn = document.getElementById('close-pix-modal');
            const cancelModalBtn = document.getElementById('cancel-pix-modal');
            const modal = document.getElementById('pix-withdrawal-modal');

            if (openModalBtn && modal) {
                openModalBtn.addEventListener('click', () => {
                    modal.classList.remove('hidden');
                });
            }

            if (closeModalBtn && modal) {
                closeModalBtn.addEventListener('click', () => {
                    modal.classList.add('hidden');
                });
            }

            if (cancelModalBtn && modal) {
                cancelModalBtn.addEventListener('click', () => {
                    modal.classList.add('hidden');
                });
            }

            // Fechar modal ao clicar fora
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                    }
                });
            }
        });

        
    </script>
   <script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('pix_key_type');
    const pixInput = document.getElementById('pix_key');

    function allowOnlyNumbers(maxLength = null) {
        pixInput.addEventListener('input', () => {
            pixInput.value = pixInput.value.replace(/\D/g, '');
            if (maxLength) {
                pixInput.value = pixInput.value.slice(0, maxLength);
            }
        });
    }

    function resetInput() {
        pixInput.value = '';
        pixInput.removeAttribute('maxlength');
        pixInput.removeEventListener('input', allowOnlyNumbers);
    }

    typeSelect.addEventListener('change', () => {
        resetInput();

        const type = typeSelect.value;

        if (type === 'cpf') {
            pixInput.placeholder = 'CPF (somente números)';
            pixInput.setAttribute('maxlength', 11);
            allowOnlyNumbers(11);
        }

        if (type === 'cnpj') {
            pixInput.placeholder = 'CNPJ (somente números)';
            pixInput.setAttribute('maxlength', 14);
            allowOnlyNumbers(14);
        }

        if (type === 'phone') {
            pixInput.placeholder = 'Telefone';
            pixInput.removeAttribute('maxlength');
        }

        if (type === 'email') {
            pixInput.placeholder = 'email@dominio.com';
            pixInput.removeAttribute('maxlength');
        }

        if (type === 'random') {
            pixInput.placeholder = 'Chave aleatória';
            pixInput.removeAttribute('maxlength');
        }
    });
});
</script>


    @endpush
</div>
