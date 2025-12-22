@extends('layouts.app')

@section('title', 'Webhooks')

@section('content')
<div class="space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div>
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Webhooks</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 border border-slate-200 dark:border-slate-800">
            <p class="text-sm text-slate-600 dark:text-slate-300">Inativos</p>
            <p class="text-3xl font-semibold text-slate-900 dark:text-white mt-1">{{ $inactiveCount }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 border border-slate-200 dark:border-slate-800">
            <p class="text-sm text-slate-600 dark:text-slate-300">Ativos</p>
            <p class="text-3xl font-semibold text-slate-900 dark:text-white mt-1">{{ $activeCount }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 border border-slate-200 dark:border-slate-800 flex items-center justify-between">
            <p class="text-sm text-slate-700 dark:text-slate-300">API de Webhooks – Receba notificações em tempo real dos eventos.</p>
            <a href="#" class="px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg text-sm hover:bg-gray-800 dark:hover:bg-black/10 transition">Acessar documentação</a>
        </div>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <button id="open-webhook-modal" class="inline-flex items-center gap-2 px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg text-sm hover:bg-gray-800 dark:hover:bg-black/10 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Novo Webhook</span>
        </button>
        <div class="bg-slate-50 dark:bg-slate-900 rounded-xl p-3 border border-slate-200 dark:border-slate-800 flex items-center gap-2">
            <i data-lucide="info" class="w-4 h-4 text-slate-500 dark:text-slate-400"></i>
            <p class="text-sm text-slate-700 dark:text-slate-300">Configure os endpoints para receber notificação dos eventos</p>
        </div>
    </div>

    <div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-slate-800">
            <thead class="bg-gray-50 dark:bg-slate-900 text-gray-500 dark:text-slate-300">
                <tr>
                    <th class="px-6 py-3 text-left">URL</th>
                    <th class="px-6 py-3 text-left">Descrição</th>
                    <th class="px-6 py-3 text-left">Evento</th>
                    <th class="px-6 py-3 text-left">Status</th>
                    <th class="px-6 py-3 text-left">Criado em</th>
                    <th class="px-6 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-black divide-y divide-gray-200 dark:divide-slate-800">
                @forelse($webhooks as $wh)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-900">
                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300">{{ $wh->url }}</td>
                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300">{{ $wh->description }}</td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">Todos os Eventos</span>
                        </td>
                        <td class="px-6 py-4">
                            @if($wh->is_active)
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">Ativo</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300">Inativo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300">{{ $wh->created_at->format('d/m/Y, H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <form method="POST" action="{{ route('associacao.webhooks.toggle', $wh) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="px-3 py-1 text-xs rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800">
                                        {{ $wh->is_active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('associacao.webhooks.destroy', $wh) }}" onsubmit="return confirm('Remover este webhook?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-slate-500 dark:text-slate-400">Nenhum webhook cadastrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="webhook-modal" class="fixed inset-0 bg-black/50 dark:bg-black/70 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white dark:bg-black rounded-xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-md overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Criar Novo Webhook</h3>
            <button id="close-webhook-modal" class="text-gray-400 dark:text-slate-300 hover:text-gray-600 dark:hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('associacao.webhooks.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">URL do Endpoint *</label>
                <input type="url" name="url" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0" placeholder="https://seu-site.com/webhook" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-slate-300 mb-1">Descrição *</label>
                <input type="text" name="description" class="w-full px-3 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0" placeholder="Descrição do webhook">
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" id="cancel-webhook-modal" class="px-4 py-2 text-sm text-gray-600 dark:text-slate-300 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800">Cancelar</button>
                <button type="submit" class="px-4 py-2 text-sm bg-black dark:bg-white text-white dark:text-black rounded-lg hover:bg-gray-800 dark:hover:bg-black/10">Criar</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const openBtn = document.getElementById('open-webhook-modal');
    const closeBtn = document.getElementById('close-webhook-modal');
    const cancelBtn = document.getElementById('cancel-webhook-modal');
    const modal = document.getElementById('webhook-modal');
    if (openBtn && modal) openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
    if (closeBtn && modal) closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
    if (cancelBtn && modal) cancelBtn.addEventListener('click', () => modal.classList.add('hidden'));
    if (modal) modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.add('hidden'); });
});
</script>
@endpush
@endsection
