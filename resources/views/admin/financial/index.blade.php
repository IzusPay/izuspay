@extends('layouts.app')

@section('title', 'Financeiro - Administração')

@section('content')
<div class="space-y-6" x-data="financialModule()">
    <!-- Header e Abas (sem alterações) -->
    <div class="bg-white dark:bg-black rounded-xl shadow-sm p-6 border border-gray-200 dark:border-white/5">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center shadow-md">
                <i data-lucide="dollar-sign" class="w-7 h-7 text-white"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Módulo Financeiro</h1>
                <p class="text-gray-500 dark:text-gray-400">Gerencie saques, aprovações e movimentações da plataforma.</p>
            </div>
        </div>
    </div>
    <div class="border-b border-gray-200 dark:border-white/10">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button @click="tab = 'aprovacao'" :class="{ 'border-green-500 text-green-600': tab === 'aprovacao', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'aprovacao' }" class="relative whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Aguardando Aprovação
                @if($pendingWithdrawals->count() > 0)
                    <span class="absolute top-3 -right-4 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-red-100 bg-red-600 rounded-full">{{ $pendingWithdrawals->count() }}</span>
                @endif
            </button>
            <button @click="tab = 'saques'" :class="{ 'border-green-500 text-green-600': tab === 'saques', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'saques' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Últimos Saques
            </button>
            <button @click="tab = 'movimentacao'" :class="{ 'border-green-500 text-green-600': tab === 'movimentacao', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': tab !== 'movimentacao' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Movimentação do Saldo
            </button>
        </nav>
    </div>

    <!-- Conteúdo das Abas -->
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-white/5 overflow-hidden">
        <div x-show="tab === 'aprovacao'" x-cloak>
            @include('admin.financial._pending_withdrawals_table', ['withdrawals' => $pendingWithdrawals])
        </div>
        <div x-show="tab === 'saques'" x-cloak>
            @include('admin.financial._processed_withdrawals_table', ['withdrawals' => $processedWithdrawals])
        </div>
        <div x-show="tab === 'movimentacao'" x-cloak>
            @include('admin.financial._movements_table', ['movements' => $movements])
        </div>
    </div>

    <!-- ================================================== -->
    <!-- MODAL DE ANÁLISE DE SAQUE (VERSÃO MELHORADA) -->
    <!-- ================================================== -->
    <div x-show="isModalOpen" @keydown.escape.window="closeModal()" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="isModalOpen" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-60" @click="closeModal()"></div>
            
            <div x-show="isModalOpen" x-transition class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl transform transition-all">
                <!-- Header do Modal -->
                <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Analisar Solicitação de Saque <span class="text-green-500" x-text="'#' + withdrawal.id"></span></h3>
                    <button @click="closeModal()" class="p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600">
                        <i data-lucide="x" class="w-5 h-5 text-gray-500 dark:text-gray-400"></i>
                    </button>
                </div>

                <!-- Corpo do Modal -->
                <div class="p-6 space-y-6">
                    <!-- Detalhes do Saque -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                            <div class="text-gray-500 dark:text-gray-400">Recebedor</div>
                            <div class="font-semibold text-gray-800 dark:text-gray-200" x-text="withdrawal.recipient_name"></div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                            <div class="text-gray-500 dark:text-gray-400">Valor Solicitado</div>
                            <div class="font-bold text-lg text-green-600 dark:text-green-400" x-text="'R$ ' + withdrawal.amount"></div>
                        </div>
                        <div class="md:col-span-2 bg-gray-50 dark:bg-gray-700/50 p-3 rounded-lg">
                            <div class="text-gray-500 dark:text-gray-400">Dados Bancários (Chave Pix)</div>
                            <div class="font-semibold text-gray-800 dark:text-gray-200" x-text="withdrawal.pix_key + ' (' + withdrawal.pix_key_type + ')'"></div>
                        </div>
                    </div>

                    <!-- Formulário de Ação -->
                    <div class="border-t dark:border-gray-700 pt-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Escolha uma ação:</label>
                        
                        <!-- Seletor de Ação Visual -->
                        <div class="grid grid-cols-2 gap-4">
                            <button @click="action = 'approve'" :class="{ 'ring-2 ring-green-500 bg-green-50 dark:bg-green-900/30': action === 'approve', 'hover:bg-gray-50 dark:hover:bg-gray-700/50': action !== 'approve' }" class="p-4 rounded-lg border dark:border-gray-600 text-left transition-all duration-200">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 flex items-center justify-center rounded-full bg-green-100 dark:bg-green-900/50 text-green-600 dark:text-green-400">
                                        <i data-lucide="check" class="w-5 h-5"></i>
                                    </div>
                                    <span class="ml-3 font-semibold text-gray-800 dark:text-gray-200">Aprovar Saque</span>
                                </div>
                            </button>
                            <button @click="action = 'reject'" :class="{ 'ring-2 ring-red-500 bg-red-50 dark:bg-red-900/30': action === 'reject', 'hover:bg-gray-50 dark:hover:bg-gray-700/50': action !== 'reject' }" class="p-4 rounded-lg border dark:border-gray-600 text-left transition-all duration-200">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 flex items-center justify-center rounded-full bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400">
                                        <i data-lucide="x" class="w-5 h-5"></i>
                                    </div>
                                    <span class="ml-3 font-semibold text-gray-800 dark:text-gray-200">Reprovar Saque</span>
                                </div>
                            </button>
                        </div>

                        <!-- Seção de Reprovação (condicional) -->
                        <div x-show="action === 'reject'" x-transition class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-500/30 space-y-3">
                            <form :action="rejectionUrl" method="POST" id="rejectForm">
                                @csrf
                                <label for="rejection_reason" class="block text-sm font-medium text-red-800 dark:text-red-200">Motivo da Reprovação</label>
                                <select id="rejection_reason" name="rejection_reason" x-model="rejectionReason" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                    <option value="">-- Selecione um motivo --</option>
                                    <option value="Dados bancários inválidos ou incorretos">Dados bancários inválidos ou incorretos</option>
                                    <option value="Suspeita de atividade fraudulenta na conta">Suspeita de atividade fraudulenta na conta</option>
                                    <option value="Titularidade da conta bancária não confere">Titularidade da conta bancária não confere</option>
                                    <option value="Violação dos termos de serviço">Violação dos termos de serviço</option>
                                    <option value="other">Outro (especificar abaixo)</option>
                                </select>
                                
                                <div x-show="rejectionReason === 'other'" class="mt-3">
                                    <label for="rejection_details" class="block text-sm font-medium text-red-800 dark:text-red-200">Detalhes</label>
                                    <textarea id="rejection_details" name="rejection_details" rows="3" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm" placeholder="Descreva o motivo da reprovação..."></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Footer do Modal com Botões de Ação -->
                <div class="flex justify-end space-x-3 p-4 bg-gray-50 dark:bg-gray-700/50 border-t dark:border-gray-700">
                    <button @click="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white dark:bg-gray-600 dark:text-gray-200 border rounded-md hover:bg-gray-50 dark:hover:bg-gray-500">Cancelar</button>
                    
                    <!-- Botão de Aprovar (condicional) -->
                    <form :action="approvalUrl" method="POST" x-show="action === 'approve'">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 shadow-sm">
                            <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i> Confirmar Aprovação
                        </button>
                    </form>

                    <!-- Botão de Reprovar (condicional) -->
                    <button type="button" @click="document.getElementById('rejectForm').submit()" x-show="action === 'reject'" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 shadow-sm">
                        <i data-lucide="x-circle" class="w-5 h-5 mr-2"></i> Confirmar Reprovação
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function financialModule() {
    return {
        tab: 'aprovacao',
        isModalOpen: false,
        action: 'approve', // Ação padrão é 'aprovar'
        withdrawal: {},
        approvalUrl: '',
        rejectionUrl: '',
        rejectionReason: '',

        openModal(withdrawalData) {
            this.action = 'approve'; // Reseta para 'approve' toda vez que abre
            this.withdrawal = {
                id: withdrawalData.id,
                amount: parseFloat(withdrawalData.amount).toFixed(2),
                recipient_name: withdrawalData.wallet.association.nome,
                pix_key: withdrawalData.pix_key ?? (withdrawalData.bank_account ? withdrawalData.bank_account.pix_key : ''),
                pix_key_type: withdrawalData.pix_key_type ?? (withdrawalData.bank_account ? withdrawalData.bank_account.pix_key_type : ''),
            };
            this.approvalUrl = `/admin/financeiro/saque/${this.withdrawal.id}/aprovar`;
            this.rejectionUrl = `/admin/financeiro/saque/${this.withdrawal.id}/rejeitar`;
            this.isModalOpen = true;
        },

        closeModal() {
            this.isModalOpen = false;
            this.rejectionReason = '';
        }
    }
}
// É importante que o lucide.createIcons() seja chamado novamente se o conteúdo é dinâmico.
// Uma forma simples é chamar dentro do openModal ou garantir que ele observe mutações no DOM.
// Para simplificar, vamos garantir que ele seja chamado sempre que o script for carregado.
document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
});
</script>
@endpush
@endsection
