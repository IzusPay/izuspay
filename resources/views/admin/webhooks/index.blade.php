@extends('layouts.app')

@section('title', 'Webhooks')

@section('content')
<div x-data="{ openConfig: false, scope: 'global' }" class="px-4 sm:px-6 lg:px-8">
    <!-- Cabeçalho -->
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Webhooks</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Listagem de webhooks recebidos e ações manuais de aprovação.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <button @click="openConfig = true" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <i data-lucide="settings" class="w-5 h-5 mr-2"></i>
                Configuração
            </button>
        </div>
    </div>

    <!-- Tabela -->
    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow border border-gray-200 dark:border-white/5 md:rounded-lg bg-white dark:bg-black">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-white/10">
                        <thead class="bg-white dark:bg-black">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider sm:pl-6">ID</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cliente</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Evento</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Recebido em</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Payload</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-white/10 bg-white dark:bg-black">
                            @forelse($webhooks as $hook)
                                <tr class="hover:bg-black/5 dark:hover:bg-white/5">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6 text-slate-900 dark:text-white">{{ $hook['id'] }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ $hook['cliente'] }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-700 dark:text-slate-300"><code>{{ $hook['event'] }}</code></td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        @php
                                            $status = $hook['status'];
                                        @endphp
                                        @if($status === 'aprovado')
                                            <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:text-green-300">Aprovado</span>
                                        @elseif($status === 'rejeitado')
                                            <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/30 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:text-red-300">Rejeitado</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 dark:bg-yellow-900/30 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:text-yellow-300">Pendente</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $hook['received_at'] }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-xs text-slate-600 dark:text-slate-400 max-w-xs truncate">{{ $hook['payload_excerpt'] }}</td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <div class="flex items-center justify-end gap-x-3">
                                            <button type="button" class="inline-flex items-center gap-2 text-green-700 hover:text-green-900 dark:text-green-300 dark:hover:text-green-200">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                                Aprovar manualmente
                                            </button>
                                            <button type="button" class="inline-flex items-center gap-2 text-red-700 hover:text-red-900 dark:text-red-300 dark:hover:text-red-200">
                                                <i data-lucide="x-circle" class="w-4 h-4"></i>
                                                Rejeitar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">Nenhum webhook recebido.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Configuração -->
    <div x-cloak x-show="openConfig" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/60"></div>
        <div class="relative bg-white dark:bg-black w-full max-w-2xl rounded-xl shadow-2xl border border-gray-200 dark:border-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Configuração de Envio Automático</h2>
                <button @click="openConfig = false" class="p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-black/5 dark:hover:bg-white/5">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="px-6 py-5 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Escopo</label>
                    <div class="mt-2 flex items-center gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="radio" name="scope" value="global" x-model="scope" class="text-blue-600 border-gray-300 dark:border-white/10">
                            Global
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="radio" name="scope" value="cliente" x-model="scope" class="text-blue-600 border-gray-300 dark:border-white/10">
                            Cliente específico
                        </label>
                    </div>
                    <div class="mt-3" x-show="scope === 'cliente'">
                        <label for="cliente" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Cliente</label>
                        <select id="cliente" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                            <option value="">Selecione um cliente</option>
                            <option>Loja Alpha</option>
                            <option>Loja Beta</option>
                            <option>Loja Gamma</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">A cada N vendas do cliente</label>
                        <div class="flex items-center gap-3">
                            <input type="number" min="1" placeholder="Ex: 5" class="block w-32 rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                            <span class="text-sm text-slate-700 dark:text-slate-300">pular envio da próxima</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Neste caso, o sistema envia o webhook de confirmação de compra manualmente.</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">A cada N reais faturados</label>
                        <div class="flex items-center gap-3">
                            <input type="number" min="1" placeholder="Ex: 1000" class="block w-32 rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                            <span class="text-sm text-slate-700 dark:text-slate-300">reservar</span>
                            <input type="number" min="0" step="0.01" placeholder="Ex: 200,00" class="block w-32 rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                            <span class="text-sm text-slate-700 dark:text-slate-300">reais</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Define um valor resguardado com base no faturamento acumulado.</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10 flex items-center justify-end gap-3">
                <button @click="openConfig = false" class="inline-flex items-center justify-center rounded-lg bg-black/5 dark:bg-white/5 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-black/10 dark:hover:bg-white/10">
                    Cancelar
                </button>
                <button class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Salvar
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
