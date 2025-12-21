@extends('layouts.app') {{-- Assumindo que este arquivo é envolvido por um layout principal --}}

@section('content')
<div class="space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    
    {{-- Título da Seção (simulando o breadcrumb/título superior) --}}
    <h1 class="text-slate-600 dark:text-slate-300 text-sm font-medium">Financeiro</h1>

    <div class="border-b border-slate-200 dark:border-slate-800">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            @php
                // Mapeamento para o novo visual: Saque e Saldo retido
                $tabs = [
                    ['id' => 'saque', 'label' => 'Saque'],
                    ['id' => 'saldo-retido', 'label' => 'Saldo retido'],
                ];
            @endphp

            @foreach ($tabs as $index => $tab)
                <button class="tab-button group inline-flex items-center py-4 px-1 border-b-2 font-medium text-base transition-colors duration-200
                    {{ $index === 0 ? 'border-black dark:border-white text-black dark:text-white' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600' }}"
                    data-tab="{{ $tab['id'] }}">
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- CONTEÚDO DAS ABAS --}}
    <div>
        {{-- A aba 'saque' irá conter o conteúdo de _withdrawals.blade.php, que será refatorado na próxima fase --}}
        <div class="tab-content" id="saque">
            @include('associacao.financeiro._withdrawals')
        </div>
        
        {{-- A aba 'saldo-retido' será um placeholder por enquanto --}}
        <div class="tab-content hidden" id="saldo-retido">
            <div class="p-6 text-slate-600 dark:text-slate-300">
                <h3 class="text-xl font-semibold mb-4 text-slate-900 dark:text-white">Conteúdo de Saldo Retido</h3>
                <p class="text-slate-700 dark:text-slate-300">Esta seção será implementada para exibir informações sobre o saldo retido, conforme o novo visual.</p>
            </div>
        </div>
        
        {{-- Mantendo os includes originais para referência, mas escondidos, caso o usuário precise do conteúdo original.
             No entanto, para o novo visual, o conteúdo de 'overview', 'bank-accounts' e 'fees' será movido ou adaptado.
             Por enquanto, vamos focar em 'saque' (que é o 'withdrawals' refatorado).
        --}}
       
    </div>
</div>

{{-- SCRIPT FUNCIONAL PARA AS ABAS - ADAPTADO PARA O NOVO NOME DAS ABAS --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetTabId = button.dataset.tab;

                // 1. Atualiza a aparência dos BOTÕES
                tabButtons.forEach(btn => {
                    if (btn.dataset.tab === targetTabId) {
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
                tabContents.forEach(content => {
                    if (content.id === targetTabId) {
                        content.classList.remove('hidden'); // Mostra o conteúdo da aba clicada
                    } else {
                        content.classList.add('hidden'); // Esconde os outros
                    }
                });
            });
        });
    });
</script>
@endpush
@endsection
