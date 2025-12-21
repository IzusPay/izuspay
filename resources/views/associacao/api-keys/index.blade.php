@extends('layouts.app')

@section('title', 'Chaves de API')

@section('content')
<div class="space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">

    {{-- ALERTA: NOVA CHAVE GERADA --}}
    @if(session('newApiKey'))
        <div class="p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-300 text-sm">
            <p class="font-medium">Chave de API criada com sucesso</p>

            <div class="mt-2 flex items-center gap-2 font-mono text-xs break-all">
                <span>{{ session('newApiKey') }}</span>

                <button
                    type="button"
                    data-key="{{ session('newApiKey') }}"
                    class="copy-new-api-key text-green-700 dark:text-green-300 hover:text-green-900 dark:hover:text-green-200"
                    title="Copiar chave">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                </button>
            </div>

            <p class="mt-2 text-xs text-green-700 dark:text-green-300">
                ⚠️ Esta chave não será exibida novamente.
            </p>
        </div>
    @endif

    {{-- CABEÇALHO --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Chaves de API</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                Utilize estas chaves para integrar sua operação. Não compartilhe com terceiros.
            </p>
        </div>

        <a href="{{ route('api-keys.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg text-sm hover:bg-gray-900 dark:hover:bg-black/10 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Adicionar
        </a>
    </div>

    {{-- TABELA --}}
    <div class="bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
        <table class="min-w-full text-sm divide-y divide-gray-200 dark:divide-slate-800">
            <thead class="bg-gray-50 dark:bg-slate-900 text-gray-500 dark:text-slate-300">
                <tr>
                    <th class="px-6 py-3 text-left font-medium">Nome</th>
                    <th class="px-6 py-3 text-left font-medium">Chave</th>
                    <th class="px-6 py-3 text-left font-medium">Criado em</th>
                    <th class="px-6 py-3 text-left font-medium">Status</th>
                    <th class="px-6 py-3 text-left font-medium">Ambiente</th>
                    <th class="px-6 py-3 text-right font-medium">Ações</th>
                </tr>
            </thead>

            <tbody class="bg-white dark:bg-black divide-y divide-gray-200 dark:divide-slate-800">
                @forelse($apiKeys as $key)
                    <tr class="hover:bg-gray-50 dark:hover:bg-slate-900">

                        {{-- NOME --}}
                        <td class="px-6 py-4 font-medium text-slate-900 dark:text-white">
                            {{ $key->name }}
                        </td>

                        <td class="px-6 py-4 font-mono text-xs text-gray-500 dark:text-slate-400 flex items-center gap-2">
                            <span>sk_••••••••••••••••</span>

                            <button
                                type="button"
                                data-id="{{ $key->id }}"
                                class="reveal-api-key text-gray-400 dark:text-slate-400 hover:text-gray-700"
                                title="Copiar chave">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                            </button>
                        </td>


                        {{-- DATA --}}
                        <td class="px-6 py-4 text-slate-700 dark:text-slate-300">
                            {{ $key->created_at->format('d/m/Y, H:i') }}
                        </td>

                        {{-- STATUS --}}
                        <td class="px-6 py-4">
                            @if($key->active)
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">
                                    Ativo
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">
                                    Inativo
                                </span>
                            @endif
                        </td>

                        {{-- AMBIENTE --}}
                        <td class="px-6 py-4">
                            @if($key->environment === 'production')
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                                    Produção
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-300">
                                    Sandbox
                                </span>
                            @endif
                        </td>

                        {{-- AÇÕES --}}
                        <td class="px-6 py-4 text-right">
                            <form
                                action="{{ route('api-keys.destroy', $key->id) }}"
                                method="POST"
                                onsubmit="return confirm('Deseja realmente excluir esta chave?')">
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="text-red-500 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-400 dark:text-slate-400">
                            Nenhuma chave de API cadastrada
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINAÇÃO --}}
    <div>
        {{ $apiKeys->links() }}
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.querySelector('.copy-new-api-key');
    if (btn) {
        btn.addEventListener('click', function() {
            const key = btn.getAttribute('data-key') || '';
            if (key) {
                navigator.clipboard.writeText(key);
                alert('Chave copiada com sucesso!');
            }
        });
    }
    document.querySelectorAll('.reveal-api-key').forEach(function(el) {
        el.addEventListener('click', function() {
            const id = el.getAttribute('data-id') || '';
            if (id) {
                copyApiKey(id);
            }
        });
    });
});
function copyApiKey(id) {
    fetch(`/associacao/integracoes/api-keys/${id}/reveal`, {
        method: 'GET',
        credentials: 'same-origin', // 👈 ESSENCIAL
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(res => {
        if (!res.ok) {
            throw new Error('Erro ao buscar a chave')
        }
        return res.json()
    })
    .then(data => {
        navigator.clipboard.writeText(data.token)
        alert('Chave copiada com sucesso!')
    })
    .catch(err => {
        alert('Erro ao copiar a chave')
        console.error(err)
    })
}
</script>

@endpush
