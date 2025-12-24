@extends('layouts.app')

@section('title', 'Eventos')
@section('page-title', 'Eventos')

@section('content')
<div class="space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Eventos</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">Gerencie seus eventos e tipos de ingressos.</p>
        </div>
        <a href="{{ route('associacao.eventos.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-black dark:bg-white text-white dark:text-black rounded-lg text-sm hover:bg-gray-900 dark:hover:bg-black/10 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Novo Evento</span>
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded-md">{{ session('success') }}</div>
    @endif

    @if($events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $evento)
                <div class="bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow">
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $evento->title }}</h3>
                            <span class="text-xs px-2 py-1 rounded bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300">{{ $evento->status }}</span>
                        </div>
                        <p class="text-sm text-slate-600 dark:text-slate-400 line-clamp-3">{{ $evento->description }}</p>
                        <div class="text-sm text-slate-700 dark:text-slate-300">
                            <i data-lucide="map-pin" class="inline w-4 h-4 mr-1"></i>
                            {{ $evento->location ?: 'Local a definir' }}
                        </div>
                        <div class="text-sm text-slate-700 dark:text-slate-300">
                            <i data-lucide="calendar" class="inline w-4 h-4 mr-1"></i>
                            {{ optional($evento->starts_at)->format('d/m/Y H:i') }} - {{ optional($evento->ends_at)->format('d/m/Y H:i') }}
                        </div>
                        <div class="flex items-center justify-end gap-2 pt-3">
                            <a href="{{ route('associacao.eventos.show', $evento) }}" class="px-3 py-2 rounded-lg bg-slate-900 text-white dark:bg.white dark:text-black hover:bg-slate-800 dark:hover:bg-black/10 text-sm">
                                Detalhes
                            </a>
                            <a href="{{ route('associacao.eventos.edit', $evento) }}" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 text-sm">
                                Editar
                            </a>
                            <form method="POST" action="{{ route('associacao.eventos.destroy', $evento) }}" onsubmit="return confirm('Excluir este evento?')">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-2 rounded-lg border border-red-300 dark:border-red-700 text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 text-sm">
                                    Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-2xl p-12 text-center shadow">
            <div class="w-20 h-20 bg-gray-100 dark:bg-slate-900 rounded-2xl flex items-center justify-center mx-auto mb-6">
                <i data-lucide="calendar" class="w-10 h-10 text-slate-500 dark:text-slate-300"></i>
            </div>
            <h3 class="text-2xl font-semibold text-slate-900 dark:text-white mb-3">Nenhum evento cadastrado</h3>
            <p class="text-slate-600 dark:text-slate-300 mb-8 max-w-md mx-auto">Crie seu primeiro evento para começar a vender ingressos.</p>
            <a href="{{ route('associacao.eventos.create') }}" class="inline-flex items-center space-x-2 px-8 py-3 bg-black dark:bg.white text-white dark:text-black rounded-xl font-semibold transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Criar Evento</span>
            </a>
        </div>
    @endif
</div>
@endsection
