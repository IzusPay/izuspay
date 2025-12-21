@extends('layouts.app')

@section('title', 'Vendas')

@section('content')
<div class="space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    
    {{-- TÍTULO --}}
    <div>
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Transações</h1>
    </div>

    {{-- CARDS DE RESUMO --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800 border-l-4 border-green-500">
            <div>
                <p class="text-sm text-slate-600 dark:text-slate-300">Transações aprovadas</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                    R$ {{ number_format($totalRevenue, 2, ',', '.') }}
                </p>
            </div>
            <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
        </div>

        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800 border-l-4 border-yellow-400">
            <div>
                <p class="text-sm text-slate-600 dark:text-slate-300">Transações pendentes</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                    R$ {{ number_format($pendingRevenue, 2, ',', '.') }}
                </p>
            </div>
            <i data-lucide="clock" class="w-6 h-6 text-yellow-400"></i>
        </div>

        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800">
            <div>
                <p class="text-sm text-slate-600 dark:text-slate-300">Aprovadas</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                    1
                </p>
            </div>
        </div>
    </div>

    {{-- FILTROS --}}
    <form method="GET" class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-4 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Buscar por código da transação..."
                   class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0">

            <select name="status" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0">
                <option value="">Todos os status</option>
                <option value="paid" @selected(request('status') == 'paid')>Aprovado</option>
                <option value="awaiting_payment" @selected(request('status') == 'awaiting_payment')>Pendente</option>
                <option value="failed" @selected(request('status') == 'failed')>Falhou</option>
            </select>

            <select name="payment_method" class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0">
                <option value="">Todos os métodos</option>
                <option value="pix" @selected(request('payment_method') == 'pix')>PIX</option>
                <option value="credit_card" @selected(request('payment_method') == 'credit_card')>Cartão</option>
                <option value="boleto" @selected(request('payment_method') == 'boleto')>Boleto</option>
            </select>

            <div class="flex gap-2">
                <button class="px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg text-sm hover:bg-gray-800 dark:hover:bg-black/10 transition-colors">
                    Aplicar filtros
                </button>
                <a href="{{ route('associacao.vendas.index') }}"
                   class="px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                    Limpar
                </a>
            </div>
        </div>
    </form>

    {{-- TABELA --}}
    <div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-slate-800">
            <thead class="bg-gray-50 dark:bg-slate-900 text-gray-500 dark:text-slate-300">
                <tr>
                    <th class="px-6 py-3 text-left">ID</th>
                    <th class="px-6 py-3 text-left">Cliente</th>
                    <th class="px-6 py-3 text-left">Método</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Valor Bruto</th>
                    <th class="px-6 py-3 text-left">Taxa</th>
                    <th class="px-6 py-3 text-left">Valor Líquido</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-black divide-y divide-gray-200 dark:divide-slate-800">
                @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-900">
                        <td class="px-6 py-4 font-mono text-xs text-slate-700 dark:text-slate-300">
                            {{ Str::limit($sale->id, 12) }}
                        </td>

                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-900 dark:text-white">{{ $sale->user->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-slate-400">{{ $sale->user->email }}</div>
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-2 py-1 border border-slate-300 dark:border-slate-700 rounded-full text-xs text-slate-700 dark:text-slate-300">
                                {{ strtoupper($sale->payment_method) }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            {!! $sale->getStatusBadge() !!}
                        </td>

                        <td class="px-6 py-4">
                            R$ {{ number_format($sale->total_price, 2, ',', '.') }}
                        </td>

                        <td class="px-6 py-4 text-red-500 dark:text-red-400">
                            R$ {{ number_format($sale->fee_amount ?? 0, 2, ',', '.') }}
                        </td>

                        <td class="px-6 py-4 text-green-600 dark:text-green-400 font-medium">
                            R$ {{ number_format($sale->net_amount ?? ($sale->total_price - ($sale->fee_amount ?? 0)), 2, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-10 text-center text-gray-400 dark:text-slate-400">
                            Nenhuma transação encontrada
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINAÇÃO --}}
    <div>
        {{ $sales->links() }}
    </div>

</div>
@endsection
