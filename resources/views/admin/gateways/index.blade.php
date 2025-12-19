@extends('layouts.app')

@section('title', 'Gerenciamento de Gateways')

@section('content')
<div class="px-4 sm:px-6 lg:px-8">
    <!-- Cabeçalho da Página -->
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Gerenciamento de Gateways</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">Liste, crie e edite os gateways de pagamento disponíveis na plataforma.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
            <a href="{{ route('admin.gateways.create') }}" class="inline-flex items-center justify-center rounded-lg border border-transparent bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                Novo Gateway
            </a>
        </div>
    </div>

    <!-- Mensagens de Feedback -->
    @if(session('success' ))
        <div class="mt-4 rounded-md bg-green-50 p-4 dark:bg-green-900/20">
            <div class="flex">
                <div class="flex-shrink-0"><svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg></div>
                <div class="ml-3"><p class="text-sm font-medium text-green-800 dark:text-green-300">{{ session('success' ) }}</p></div>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="mt-4 rounded-md bg-red-50 p-4 dark:bg-red-900/20">
            <div class="flex">
                <div class="flex-shrink-0"><svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg></div>
                <div class="ml-3"><p class="text-sm font-medium text-red-800 dark:text-red-300">{{ session('error' ) }}</p></div>
            </div>
        </div>
    @endif

    <!-- Tabela de Gateways -->
    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-gray-700">
                        <thead class="bg-slate-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider sm:pl-6">Logo</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nome</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Slug</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider">Prioridade</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @forelse($gateways as $gateway)
                                <tr class="hover:bg-slate-50 dark:hover:bg-gray-700/50">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                        <img class="h-10 w-auto" src="{{ $gateway->logo_url }}" alt="{{ $gateway->name }}">
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-slate-900 dark:text-white">{{ $gateway->name }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500 dark:text-slate-400"><code>{{ $gateway->slug }}</code></td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        @if($gateway->is_active)
                                            <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/30 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:text-green-300">Ativo</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-red-100 dark:bg-red-900/30 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:text-red-300">Inativo</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500 dark:text-slate-400"><code>{{ $gateway->order }}</code></td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <div class="flex items-center justify-end gap-x-4">
                                            <a href="{{ route('admin.gateways.edit', $gateway) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Editar</a>
                                            <form action="{{ route('admin.gateways.destroy', $gateway) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este gateway?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Excluir</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Nenhum gateway cadastrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $gateways->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
