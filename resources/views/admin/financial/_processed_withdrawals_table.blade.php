<div class="p-6">
    <!-- Formulário de Filtros -->
    <form action="{{ route('admin.financial.index') }}" method="GET">
        <input type="hidden" name="tab" value="saques"> {{-- Para manter a aba ativa após filtrar --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-input w-full rounded-md bg-white dark:bg-black border-gray-300 dark:border-white/10">
            <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-input w-full rounded-md bg-white dark:bg-black border-gray-300 dark:border-white/10">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nome, e-mail, CPF/CNPJ do seller" class="form-input w-full rounded-md bg-white dark:bg-black border-gray-300 dark:border-white/10">
        </div>
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.financial.index', ['tab' => 'saques']) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 dark:bg-white/10 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-white/20">Limpar</a>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Pesquisar</button>
            <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-md hover:bg-gray-700">Exportar Lista</button>
        </div>
    </form>

    <!-- Cabeçalho da Lista -->
    <div class="mt-6 grid grid-cols-12 gap-4 px-4 py-2 bg-gray-100 dark:bg-black rounded-t-lg text-xs font-bold text-gray-600 dark:text-gray-300 uppercase">
        <div class="col-span-1">ID</div>
        <div class="col-span-4">Recebedor</div>
        <div class="col-span-3">Solicitação e Status</div>
        <div class="col-span-2">Valor Autorizado</div>
        <div class="col-span-2 text-right">Autorizado Em</div>
    </div>

    <!-- Lista de Saques -->
    <div class="space-y-2">
        @forelse($withdrawals as $withdrawal)
            <div class="grid grid-cols-12 gap-4 p-4 border-b dark:border-white/10 hover:bg-black/5 dark:hover:bg-white/5">
                <!-- Coluna ID -->
                <div class="col-span-1 text-sm font-medium text-gray-800 dark:text-gray-200">
                    {{ $withdrawal->id }}
                </div>

                <!-- Coluna Recebedor -->
                <div class="col-span-4 text-sm">
                    <div class="font-bold text-gray-900 dark:text-white">{{ $withdrawal->wallet->association->nome }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">ID Interno: #{{ $withdrawal->wallet->association->id }}</div>
                    <div class="mt-2 font-semibold">Conta/Chave PIX</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Tipo de chave: {{ $withdrawal->bankAccount->pix_key_type }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Chave: {{ $withdrawal->bankAccount->pix_key }}</div>
                </div>

                <!-- Coluna Solicitação e Status -->
                <div class="col-span-3 text-sm">
                    <div class="text-gray-800 dark:text-gray-200">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</div>
                    <div>
                        @if($withdrawal->status == 'completed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                Saque realizado
                            </span>
                        @elseif($withdrawal->status == 'failed')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                Falha no saque
                            </span>
                            @if($withdrawal->notes)
                                <div class="text-xs text-red-500 mt-1" title="Motivo da falha">{{ $withdrawal->notes }}</div>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300">
                                {{ ucfirst($withdrawal->status) }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Coluna Valor Autorizado -->
                <div class="col-span-2 text-sm">
                    <div class="font-bold text-gray-900 dark:text-white">R$ {{ number_format($withdrawal->amount, 2, ',', '.') }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">(Esse saque)</div>
                    <a href="#" class="mt-1 inline-block px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 rounded-md hover:bg-blue-200">
                        VER SALDO TOTAL
                    </a>
                </div>

                <!-- Coluna Autorizado Em -->
                <div class="col-span-2 text-sm text-right text-gray-800 dark:text-gray-200">
                    {{ $withdrawal->updated_at->format('d/m/Y H:i') }}
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-2"></i>
                Nenhum saque encontrado com os filtros aplicados.
            </div>
        @endforelse
    </div>

    <!-- Paginação -->
    @if($withdrawals->hasPages())
        <div class="p-4 border-t dark:border-gray-700">
            {{ $withdrawals->links() }}
        </div>
    @endif
</div>
