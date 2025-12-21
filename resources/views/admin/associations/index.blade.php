@extends('layouts.app')

@section('title', 'Contas - Administração')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white dark:bg-black rounded-xl p-6 border border-gray-200 dark:border-white/5 shadow-lg">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center shadow-md">
                        <i data-lucide="building" class="w-6 h-6 text-white"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Contas Cadastradas</h2>
                </div>
                <p class="text-gray-600 dark:text-gray-400">Gerencie todas as contas (associações) da plataforma.</p>
            </div>
        </div>
    </div>

    <!-- ================================================== -->
    <!-- FILTROS FUNCIONAIS -->
    <!-- ================================================== -->
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-white/5 p-6">
        <form action="{{ route('admin.contas.index') }}" method="GET">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                {{-- Filtro de Data Início --}}
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">De</label>
                    <input type="date" id="start_date" name="start_date" value="{{ request('start_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-white/10 rounded-lg focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent dark:bg-black dark:text-white">
                </div>

                {{-- Filtro de Data Fim --}}
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Até</label>
                    <input type="date" id="end_date" name="end_date" value="{{ request('end_date') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-white/10 rounded-lg focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent dark:bg-black dark:text-white">
                </div>

                {{-- Filtro de Busca por Texto --}}
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar Conta</label>
                    <input type="text" id="search" name="search" placeholder="Nome, email, documento..." value="{{ request('search') }}"
                           class="w-full px-3 py-2 border border-gray-300 dark:border-white/10 rounded-lg focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent dark:bg-black dark:text-white">
                </div>

                {{-- Filtro por Status --}}
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-white/10 rounded-lg focus:ring-2 focus:ring-black dark:focus:ring-white focus:border-transparent dark:bg-black dark:text-white">
                        <option value="">Todos</option>
                        <option value="ativa" @selected(request('status') == 'ativa')>Ativo</option>
                        <option value="inativa" @selected(request('status') == 'inativa')>Inativo</option>
                        <option value="pendente" @selected(request('status') == 'pendente')>Pendente</option>
                    </select>
                </div>
            </div>
            
            {{-- Botões de Ação do Formulário --}}
            <div class="mt-4 flex justify-end space-x-3">
                <a href="{{ route('admin.contas.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-lg transition-colors">
                    Limpar Filtros
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i data-lucide="filter" class="w-4 h-4 mr-2"></i>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Tabela de Contas -->
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white dark:bg-black">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Titular da Conta</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Taxas por M. Pagamento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Saldo Global</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cadastro</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-black divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($associations as $association)
                        @php
                            $balanceDetails = $association->balance_details;
                        @endphp
                        <tr class="hover:bg-black/5 dark:hover:bg-white/5 transition-colors duration-200">
                            {{-- Coluna Titular da Conta --}}
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.contas.show', $association) }}" class="hover:underline">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full" src="{{ $association->creatorProfile->profile_image_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($association->nome ) . '&background=22c55e&color=fff&size=200' }}" alt="Logo">
                                        </div>
                                        <div class="ml-4 min-w-0">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $association->nome }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ $association->documento_formatado }}</div>
                                        </div>
                                    </div>
                                </a>
                            </td>

                            {{-- Coluna de Taxas --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col space-y-1 text-sm">
                                    @forelse($association->fees as $fee)
                                        <div class="flex justify-between items-center">
                                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ ucfirst($fee->payment_method) }}:</span>
                                            <span class="text-gray-600 dark:text-gray-400 text-right">
                                                {{ rtrim(rtrim(number_format($fee->percentage_fee, 2, ',', '.'), '0'), ',') }}% + R$ {{ number_format($fee->fixed_fee, 2, ',', '.') }}
                                            </span>
                                        </div>
                                    @empty
                                        <span class="text-xs text-red-500">Nenhuma taxa configurada.</span>
                                    @endforelse
                                </div>
                            </td>

                            {{-- Coluna Saldo Global --}}
                            <td class="px-6 py-4">
                                <div class="text-sm space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Disponível:</span>
                                        <span class="font-medium text-green-600 dark:text-green-400">R$ {{ number_format($balanceDetails['available'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Aguard. Liberação:</span>
                                        <span>R$ {{ number_format($balanceDetails['pending_release'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Aguard. Saque:</span>
                                        <span>R$ {{ number_format($balanceDetails['pending_withdrawal'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Transferido:</span>
                                        <span>R$ {{ number_format($balanceDetails['total_withdrawn'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Retido:</span>
                                        <span class="text-red-600 dark:text-red-400">R$ {{ number_format($balanceDetails['retained'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-2">
                                        Total Bruto: R$ {{ number_format($balanceDetails['total_gross'], 2, ',', '.') }}
                                    </div>
                                </div>
                            </td>

                            {{-- Coluna Status --}}
                            <td class="px-6 py-4 whitespace-nowrap">{!! $association->getBadgeStatus() !!}</td>
                            {{-- Coluna Cadastro --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $association->created_at->format('d/m/Y') }}</td>
                            {{-- Coluna Ações --}}
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('admin.contas.show', $association) }}" class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">Detalhes</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i data-lucide="search-x" class="w-12 h-12 text-gray-400 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Nenhuma conta encontrada</h3>
                                    <p class="text-gray-500 dark:text-gray-400">Tente ajustar os filtros ou cadastre uma nova conta.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginação que mantém os filtros --}}
        @if($associations->hasPages())
        <div class="bg-white dark:bg-black px-6 py-3 border-t border-gray-200 dark:border-white/5">
            {{ $associations->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Initialize Lucide icons
    lucide.createIcons();
</script>
@endpush
@endsection
