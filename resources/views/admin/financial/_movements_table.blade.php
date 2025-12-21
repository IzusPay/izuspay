<div class="p-6 space-y-6">
    <!-- Painel de Indicadores (KPIs) -->
    <div>
        <!-- Linha 1 de KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <div class="bg-gray-50 dark:bg-black p-4 rounded-lg border border-gray-200 dark:border-white/5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Faturamento Bruto</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">R$ {{ number_format($kpiData['gross_revenue'], 2, ',', '.') }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-black p-4 rounded-lg border border-gray-200 dark:border-white/5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Margem Líquida</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($kpiData['net_margin'], 2, ',', '.') }}%</p>
            </div>
            <div class="col-span-2 bg-green-100 dark:bg-green-900/20 p-4 rounded-lg border border-green-200 dark:border-green-500/30">
                <p class="text-sm text-green-800 dark:text-green-300">Receita da Plataforma (Seu Lucro)</p>
                <p class="text-3xl font-extrabold text-green-700 dark:text-green-400">R$ {{ number_format($kpiData['platform_revenue'], 2, ',', '.') }}</p>
            </div>
        </div>
        <!-- Linha 2 de KPIs -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-gray-50 dark:bg-black p-4 rounded-lg border border-gray-200 dark:border-white/5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Recebimento MDR</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">R$ {{ number_format($kpiData['mdr_received'], 2, ',', '.') }}</p>
            </div>
            <div class="bg-gray-50 dark:bg-black p-4 rounded-lg border border-gray-200 dark:border-white/5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Recebimento Juros</p>
                <p class="text-xl font-semibold text-gray-900 dark:text-white">R$ {{ number_format($kpiData['interest_received'], 2, ',', '.') }}</p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-500/30">
                <p class="text-sm text-red-600 dark:text-red-300">Custo Fixo Adquirente</p>
                <p class="text-xl font-semibold text-red-800 dark:text-red-200">R$ {{ number_format($kpiData['acquirer_fixed_cost'], 2, ',', '.') }}</p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-500/30">
                <p class="text-sm text-red-600 dark:text-red-300">Taxa de Whitelabel</p>
                <p class="text-xl font-semibold text-red-800 dark:text-red-200">R$ {{ number_format($kpiData['whitelabel_fee'], 2, ',', '.') }}</p>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg border border-red-200 dark:border-red-500/30">
                <p class="text-sm text-red-600 dark:text-red-300">Custo MDR</p>
                <p class="text-xl font-semibold text-red-800 dark:text-red-200">R$ {{ number_format($kpiData['mdr_cost'], 2, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Filtros da Tabela -->
    <form action="{{ route('admin.financial.index') }}" method="GET" class="mt-6">
        <input type="hidden" name="tab" value="movimentacao">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- 
            ==================================================
            AQUI ESTÁ A CORREÇÃO: Usando $movStartDate e $movEndDate
            ==================================================
            --}}
            <input type="date" name="mov_start_date" value="{{ $movStartDate->format('Y-m-d') }}" class="form-input w-full rounded-md bg-white dark:bg-black border-gray-300 dark:border-white/10">
            <input type="date" name="mov_end_date" value="{{ $movEndDate->format('Y-m-d') }}" class="form-input w-full rounded-md bg-white dark:bg-black border-gray-300 dark:border-white/10">
            <input type="text" name="mov_search" placeholder="Consultar movimento de uma transação..." class="form-input w-full rounded-md bg-white dark:bg-black border-gray-300 dark:border-white/10">
        </div>
        <div class="flex justify-end space-x-3 mt-3">
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Buscar</button>
            <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700">Exportar Lista</button>
        </div>
    </form>

    <!-- Tabela de Movimentações -->
    <div class="mt-6 overflow-x-auto">
        <table class="w-full">
            <thead class="bg-white dark:bg-black">
                <tr>
                    <th class="p-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Transação</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Valor</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Recebedor</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Operação</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Descrição</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                    <th class="p-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Disponibilidade</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                @forelse($movements as $movement)
                    <tr>
                        <td class="p-4 text-sm text-gray-500 dark:text-gray-400">#{{ $movement->id }}</td>
                        <td class="p-4 font-semibold @if($movement->amount > 0) text-green-600 dark:text-green-400 @else text-red-600 dark:text-red-400 @endif">
                            R$ {{ number_format($movement->amount, 2, ',', '.') }}
                        </td>
                        <td class="p-4 text-sm text-gray-800 dark:text-gray-200">{{ $movement->association->nome ?? 'Plataforma' }}</td>
                        <td class="p-4">
                            @if($movement->amount > 0)
                                <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300 rounded-full">Entrada</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 rounded-full">Saída</span>
                            @endif
                        </td>
                        <td class="p-4 text-sm text-gray-600 dark:text-gray-300">{{ $movement->description }}</td>
                        <td class="p-4 text-sm text-gray-600 dark:text-gray-300">Liquidado</td>
                        <td class="p-4 text-sm text-gray-600 dark:text-gray-300">{{ $movement->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-12 text-gray-500 dark:text-gray-400">
                            Nenhuma movimentação encontrada no período.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
