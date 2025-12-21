@extends('layouts.app')

@section('title', 'Disputas')

@section('content')
<div class="space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Disputas</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 border border-slate-200 dark:border-slate-800">
            <p class="text-sm text-slate-600 dark:text-slate-300">Porcentagem em disputa</p>
            <p class="text-3xl font-semibold text-slate-900 dark:text-white mt-1">
                {{ number_format($disputePercentage, 2, ',', '.') }}%
            </p>
        </div>
    </div>

    <div class="border-b border-slate-200 dark:border-slate-800">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button class="tab-button group inline-flex items-center py-4 px-1 border-b-2 font-medium text-base transition-colors duration-200 border-black dark:border-white text-black dark:text-white" data-tab="disputes">
                Disputas
            </button>
            <button class="tab-button group inline-flex items-center py-4 px-1 border-b-2 font-medium text-base transition-colors duration-200 border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-white hover:border-slate-300 dark:hover:border-slate-600" data-tab="chargebacks">
                Chargeback
            </button>
        </nav>
    </div>

    <form method="GET" class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-3 flex items-center gap-2">
                <i data-lucide="search" class="w-4 h-4 text-slate-500 dark:text-slate-400"></i>
                <input type="text"
                       name="search"
                       value="{{ $search }}"
                       placeholder="Digite o código da transação"
                       class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0">
            </div>
            <div class="flex gap-2">
                <button class="px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg text-sm hover:bg-gray-800 dark:hover:bg-black/10 transition-colors">
                    Aplicar filtros
                </button>
            </div>
        </div>
    </form>

    <div class="tab-content" id="disputes">
        <div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-slate-800">
                <thead class="bg-gray-50 dark:bg-slate-900 text-gray-500 dark:text-slate-300">
                    <tr>
                        <th class="px-6 py-3 text-left">ID</th>
                        <th class="px-6 py-3 text-left">Cliente</th>
                        <th class="px-6 py-3 text-left">Motivo</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Criado em</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-black divide-y divide-gray-200 dark:divide-slate-800">
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400 dark:text-slate-400">
                            Nenhuma disputa encontrada
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="tab-content hidden" id="chargebacks">
        <div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-slate-800">
                <thead class="bg-gray-50 dark:bg-slate-900 text-gray-500 dark:text-slate-300">
                    <tr>
                        <th class="px-6 py-3 text-left">ID</th>
                        <th class="px-6 py-3 text-left">Cliente</th>
                        <th class="px-6 py-3 text-left">Método</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Criado em</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-black divide-y divide-gray-200 dark:divide-slate-800">
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400 dark:text-slate-400">
                            Nenhum chargeback encontrado
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTabId = button.dataset.tab;
            tabButtons.forEach(btn => {
                if (btn.dataset.tab === targetTabId) {
                    btn.classList.remove('border-transparent', 'text-slate-500', 'dark:text-slate-400', 'hover:text-slate-700', 'dark:hover:text-white', 'hover:border-slate-300', 'dark:hover:border-slate-600');
                    btn.classList.add('border-black', 'dark:border-white', 'text-black', 'dark:text-white');
                } else {
                    btn.classList.remove('border-black', 'dark:border-white', 'text-black', 'dark:text-white');
                    btn.classList.add('border-transparent', 'text-slate-500', 'dark:text-slate-400', 'hover:text-slate-700', 'dark:hover:text-white', 'hover:border-slate-300', 'dark:hover:border-slate-600');
                }
            });
            tabContents.forEach(content => {
                if (content.id === targetTabId) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });
        });
    });
});
</script>
@endpush
@endsection
