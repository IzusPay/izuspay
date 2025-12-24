@extends('layouts.app')

@section('title', 'Webhooks')

@section('content')
<div x-data="webhooksPage()" class="px-4 sm:px-6 lg:px-8">
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

    <!-- Indicadores -->
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-slate-600 dark:text-slate-400">Total</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-slate-600 dark:text-slate-400">Manuais</p>
            <p class="mt-1 text-2xl font-semibold text-indigo-700 dark:text-indigo-300">{{ $stats['manual'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-slate-600 dark:text-slate-400">Automáticos</p>
            <p class="mt-1 text-2xl font-semibold text-slate-900 dark:text-white">{{ $stats['automatic'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-slate-600 dark:text-slate-400">Pendentes</p>
            <p class="mt-1 text-2xl font-semibold text-yellow-700 dark:text-yellow-300">{{ $stats['pending'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-slate-600 dark:text-slate-400">Enviados</p>
            <p class="mt-1 text-2xl font-semibold text-blue-700 dark:text-blue-300">{{ $stats['sent'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-slate-600 dark:text-slate-400">Falhos</p>
            <p class="mt-1 text-2xl font-semibold text-red-700 dark:text-red-300">{{ $stats['failed'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-slate-600 dark:text-slate-400">Aprovados</p>
            <p class="mt-1 text-2xl font-semibold text-green-700 dark:text-green-300">{{ $stats['approved'] ?? 0 }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-slate-600 dark:text-slate-400">Rejeitados</p>
            <p class="mt-1 text-2xl font-semibold text-red-700 dark:text-red-300">{{ $stats['rejected'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm p-4 border border-gray-200 dark:border-white/10">
        <form method="GET" action="{{ route('admin.webhooks.index') }}" class="flex items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Evento</label>
                <select name="event" class="rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                    <option value="">Todos</option>
                    <option value="waiting_payment" @if(request('event')==='waiting_payment') selected @endif>Aguardando pagamento</option>
                    <option value="paid" @if(request('event')==='paid') selected @endif>Pago</option>
                    <option value="refused" @if(request('event')==='refused') selected @endif>Recusado</option>
                    <option value="canceled" @if(request('event')==='canceled') selected @endif>Cancelado</option>
                    <option value="refunded" @if(request('event')==='refunded') selected @endif>Estornado</option>
                    <option value="chargeback" @if(request('event')==='chargeback') selected @endif>Chargeback</option>
                    <option value="failed" @if(request('event')==='failed') selected @endif>Falhou</option>
                    <option value="expired" @if(request('event')==='expired') selected @endif>Expirado</option>
                    <option value="in_analysis" @if(request('event')==='in_analysis') selected @endif>Em análise</option>
                    <option value="in_protest" @if(request('event')==='in_protest') selected @endif>Em protesto</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1">Status</label>
                <select name="status" class="rounded-lg bg-white dark:bg.black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                    <option value="">Todos</option>
                    <option value="pending" @if(request('status')==='pending') selected @endif>Pendente</option>
                    <option value="sent" @if(request('status')==='sent') selected @endif>Enviado</option>
                    <option value="failed" @if(request('status')==='failed') selected @endif>Falhou</option>
                    <option value="approved" @if(request('status')==='approved') selected @endif>Aprovado</option>
                    <option value="rejected" @if(request('status')==='rejected') selected @endif>Rejeitado</option>
                </select>
            </div>
            <div>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                    Filtrar
                </button>
            </div>
        </form>
        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Observação: eventos de “aguardando pagamento” são enviados automaticamente e geralmente aparecem com status “Enviado”.</p>
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
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Origem</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Recebido em</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-white/10 bg-white dark:bg-black">
                            @forelse($webhooks as $hook)
                                <tr class="hover:bg-black/5 dark:hover:bg-white/5">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6 text-slate-900 dark:text-white">{{ $hook['id'] }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-slate-900 dark:text-white">
                                        @php $url = $hook['endpoint_url'] ?? $hook['cliente']; @endphp
                                        <a href="{{ $url }}" target="_blank" class="text-blue-600 hover:underline"><code>{{ $hook['cliente'] }}</code></a>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-700 dark:text-slate-300"><code>{{ $hook['event'] }}</code></td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        @php $status = $hook['status']; @endphp
                                        @if(in_array($status, ['approved']))
                                            <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:text-green-300">Aprovado</span>
                                        @elseif(in_array($status, ['rejected']))
                                            <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/30 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:text-red-300">Rejeitado</span>
                                        @elseif(in_array($status, ['sent']))
                                            <span class="inline-flex items-center rounded-full bg-blue-100 dark:bg-blue-900/30 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:text-blue-300">Enviado</span>
                                        @elseif(in_array($status, ['failed']))
                                            <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/30 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:text-red-300">Falhou</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 dark:bg-yellow-900/30 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:text-yellow-300">Pendente</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        @if($hook['is_manual'] ?? false)
                                            <span class="inline-flex items-center rounded-full bg-indigo-100 dark:bg-indigo-900/30 px-2.5 py-0.5 text-xs font-medium text-indigo-800 dark:text-indigo-300" title="{{ $hook['moderation_reason'] ?? '' }}">Manual</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-white/10 px-2.5 py-0.5 text-xs font-medium text-gray-800 dark:text-white">Automático</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $hook['received_at'] }}</td>
                                    
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <div class="flex items-center justify-end gap-x-2">
                                            <button @click="openPayload({{ json_encode($hook['payload']) }})" title="Ver payload" class="p-2 rounded-md text-slate-700 dark:text-slate-300 hover:bg-black/5 dark:hover:bg-white/10">
                                                <i data-lucide="eye" class="w-4 h-4"></i>
                                            </button>
                                            <button @click="openApprove({ id: {{ $hook['id'] }}, event: '{{ $hook['event'] }}', cliente: '{{ addslashes($hook['cliente']) }}' })" title="Aprovar" class="p-2 rounded-md text-green-700 hover:bg-green-50 dark:text-green-300 dark:hover:bg-green-900/20">
                                                <i data-lucide="check-circle" class="w-4 h-4"></i>
                                            </button>
                                            <button @click="openReject({ id: {{ $hook['id'] }}, event: '{{ $hook['event'] }}', cliente: '{{ addslashes($hook['cliente']) }}' })" title="Rejeitar" class="p-2 rounded-md text-red-700 hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-900/20">
                                                <i data-lucide="x-circle" class="w-4 h-4"></i>
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

            <form method="POST" action="{{ route('admin.webhooks.config.save') }}">
            @csrf
            <div class="px-6 py-5 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Escopo</label>
                    <div class="mt-2 flex items-center gap-4">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="radio" name="scope" value="global" x-model="scope" class="text-blue-600 border-gray-300 dark:border-white/10" checked>
                            Global
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                            <input type="radio" name="scope" value="cliente" x-model="scope" class="text-blue-600 border-gray-300 dark:border-white/10">
                            Cliente específico
                        </label>
                    </div>
                    <div class="mt-3" x-show="scope === 'cliente'">
                        <label for="cliente" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Cliente</label>
                        <select id="cliente" name="association_id" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                            <option value="">Selecione um cliente</option>
                            @foreach($associations as $assoc)
                                <option value="{{ $assoc->id }}">{{ $assoc->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">A cada N vendas do cliente</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="skip_every_n_sales" min="1" placeholder="Ex: 5" class="block w-32 rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                            <span class="text-sm text-slate-700 dark:text-slate-300">pular envio da próxima</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Neste caso, o sistema envia o webhook de confirmação de compra manualmente.</p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">A cada N reais faturados</label>
                        <div class="flex items-center gap-3">
                            <input type="number" name="revenue_threshold_cents" min="1" placeholder="Ex: 100000 (R$ 1000,00)" class="block w-48 rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                            <span class="text-sm text-slate-700 dark:text-slate-300">reservar</span>
                            <input type="number" name="reserve_amount_cents" min="0" placeholder="Ex: 20000 (R$ 200,00)" class="block w-48 rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
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
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Salvar
                </button>
            </div>
            </form>
        </div>
    </div>

    <!-- Modal Visualizar Payload -->
    <div x-cloak x-show="payloadModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/60" @click="payloadModal=false"></div>
        <div class="relative bg-white dark:bg-black w-full max-w-2xl rounded-xl shadow-2xl border border-gray-200 dark:border-white/10">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Payload</h2>
                <button @click="payloadModal=false" class="p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-black/5 dark:hover:bg.white/5">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-6">
                <pre class="text-xs overflow-x-auto bg-gray-900 text-gray-100 p-4 rounded-md"><code x-text="prettyPayload"></code></pre>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10 flex items-center justify-end gap-3">
                <button @click="copyPayload()" class="inline-flex items-center justify-center rounded-lg bg-black/5 dark:bg-white/5 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-black/10 dark:hover:bg-white/10">
                    Copiar
                </button>
                <button @click="payloadModal=false" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Fechar</button>
            </div>
        </div>
    </div>

    <!-- Modal Aprovar -->
    <div x-cloak x-show="approveModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/60" @click="approveModal=false"></div>
        <div class="relative bg-white dark:bg-black w-full max-w-md rounded-xl shadow-2xl border border-gray-200 dark:border-white/10">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Confirmar aprovação</h2>
                <button @click="approveModal=false" class="p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-black/5 dark:hover:bg.white/5">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="p-5 text-sm text-slate-700 dark:text-slate-300">
                <p>Você está prestes a aprovar o webhook <strong>#<span x-text="selected?.id"></span></strong> para <span x-text="selected?.cliente"></span>.</p>
            </div>
            <div class="px-5 py-4 border-t border-gray-200 dark:border-white/10 flex items-center justify-end gap-3">
                <button @click="approveModal=false" class="inline-flex items-center justify-center rounded-lg bg-black/5 dark:bg-white/5 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-black/10 dark:hover:bg-white/10">Cancelar</button>
                <form :action="approveUrl()" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">
                        Confirmar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Rejeitar -->
    <div x-cloak x-show="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="fixed inset-0 bg-black/60" @click="rejectModal=false"></div>
        <div class="relative bg-white dark:bg-black w-full max-w-md rounded-xl shadow-2xl border border-gray-200 dark:border-white/10">
            <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Rejeitar webhook</h2>
                <button @click="rejectModal=false" class="p-2 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-black/5 dark:hover:bg.white/5">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <form :action="rejectUrl()" method="POST">
                @csrf
                @method('PATCH')
                <div class="p-5 space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Status</label>
                        <select name="status" class="w-full rounded-md border-gray-300 text-sm dark:bg-black dark:text-white">
                            <option value="refused">Recusada</option>
                            <option value="canceled">Cancelada</option>
                            <option value="refunded">Estornada</option>
                            <option value="chargeback">Chargeback</option>
                            <option value="failed">Falhou</option>
                            <option value="expired">Expirada</option>
                            <option value="in_analysis">Em análise</option>
                            <option value="in_protest">Em protesto</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Motivo</label>
                        <input type="text" name="reason" placeholder="Descreva o motivo" class="w-full rounded-md border-gray-300 text-sm dark:bg-black dark:text-white" />
                    </div>
                </div>
                <div class="px-5 py-4 border-t border-gray-200 dark:border-white/10 flex items-center justify-end gap-3">
                    <button type="button" @click="rejectModal=false" class="inline-flex items-center justify-center rounded-lg bg-black/5 dark:bg白/5 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-black/10 dark:hover:bg-white/10">Cancelar</button>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Confirmar Rejeição</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function webhooksPage() {
    return {
        openConfig: false,
        scope: 'global',
        payloadModal: false,
        approveModal: false,
        rejectModal: false,
        selected: null,
        prettyPayload: '',
        openPayload(payload) {
            try { this.prettyPayload = JSON.stringify(payload, null, 2); } catch (e) { this.prettyPayload = 'Payload inválido'; }
            this.payloadModal = true;
        },
        copyPayload() {
            navigator.clipboard.writeText(this.prettyPayload);
        },
        openApprove(hook) {
            this.selected = hook;
            this.approveModal = true;
        },
        openReject(hook) {
            this.selected = hook;
            this.rejectModal = true;
        },
        approveUrl() {
            return `/admin/webhooks/${this.selected.id}/approve`;
        },
        rejectUrl() {
            return `/admin/webhooks/${this.selected.id}/reject`;
        }
    }
}
document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') lucide.createIcons();
});
</script>
@endpush
@endsection
