@extends('layouts.app')

@section('title', 'Criar chave de API')

@section('content')
<div class="max-w-3xl space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">

    {{-- CABEÇALHO --}}
    <div>
        <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Criar chave de API</h1>
       
    </div>

    {{-- ALERTA IMPORTANTE --}}
  

    {{-- FORM --}}
    <form method="POST" action="{{ route('api-keys.store') }}" class="space-y-6">
        @csrf

        {{-- NOME --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">
                Nome da chave
            </label>
            <input
                type="text"
                name="name"
                value="{{ old('name') }}"
                placeholder="Ex: Integração Lux Secrets"
                class="w-full px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-200 focus:border-black dark:focus:border-white focus:ring-0"
                required
            >

            @error('name')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- AMBIENTE --}}
        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">
                Ambiente
            </label>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- PRODUÇÃO --}}
                <label class="flex items-start gap-3 p-4 border border-gray-300 dark:border-slate-700 rounded-xl cursor-pointer hover:border-black dark:hover:border-white transition">
                    <input
                        type="radio"
                        name="environment"
                        value="production"
                        class="mt-1"
                        checked
                    >

                    <div>
                        <p class="font-medium text-slate-900 dark:text-white">Produção</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Usar em ambiente real, com dados e pagamentos válidos.
                        </p>
                    </div>
                </label>

                {{-- SANDBOX --}}
                <label class="flex items-start gap-3 p-4 border border-gray-300 dark:border-slate-700 rounded-xl cursor-pointer hover:border-black dark:hover:border-white transition">
                    <input
                        type="radio"
                        name="environment"
                        value="sandbox"
                        class="mt-1"
                    >

                    <div>
                        <p class="font-medium text-slate-900 dark:text-white">Sandbox</p>
                        <p class="text-sm text-slate-600 dark:text-slate-300">
                            Ambiente de testes, sem impacto real.
                        </p>
                    </div>
                </label>
            </div>

            @error('environment')
                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- AÇÕES --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <a href="{{ route('api-keys.index') }}"
               class="px-4 py-2 border border-gray-300 dark:border-slate-700 rounded-lg text-sm text-slate-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                Cancelar
            </a>

            <button
                type="submit"
                class="px-5 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg text-sm hover:bg-gray-900 dark:hover:bg-black/10 transition">
                Criar chave
            </button>
        </div>

    </form>

</div>
@endsection
