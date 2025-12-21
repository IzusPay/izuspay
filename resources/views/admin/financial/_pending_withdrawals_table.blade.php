<div class="overflow-x-auto">
    <table class="w-full">
        <thead class="bg-white dark:bg-black">
            <tr>
                <th class="p-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Recebedor</th>
                <th class="p-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Dados Bancários</th>
                <th class="p-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Valor Solicitado</th>
                <th class="p-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data Solicitação</th>
                <th class="p-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
            @forelse($withdrawals as $withdrawal)
                <tr class="hover:bg-black/5 dark:hover:bg-white/5">
                    <td class="p-4">
                        <div class="font-medium text-gray-900 dark:text-white">{{ $withdrawal->wallet->association->nome }}</div>
                        <div class="text-sm text-gray-500">ID: {{ $withdrawal->wallet->association->id }}</div>
                    </td>
                    <td class="p-4 text-sm text-gray-500 dark:text-gray-400">
                        <div><strong>Banco:</strong> {{ $withdrawal->bankAccount->bank_name }}</div>
                        <div><strong>Chave Pix:</strong> {{ $withdrawal->bankAccount->pix_key }} ({{ $withdrawal->bankAccount->pix_key_type }})</div>
                    </td>
                    <td class="p-4 font-semibold text-lg text-gray-900 dark:text-white">
                        R$ {{ number_format($withdrawal->amount, 2, ',', '.') }}
                    </td>
                    <td class="p-4 text-sm text-gray-500 dark:text-gray-400">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</td>
                    
                    <td class="p-4 text-right">
                        {{-- Este botão agora abre o modal --}}
                        <button @click='openModal(@json($withdrawal))' class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                            Analisar
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <i data-lucide="check-check" class="w-12 h-12 mx-auto mb-2"></i>
                        Nenhum saque aguardando aprovação.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
