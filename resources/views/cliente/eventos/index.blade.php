@extends('layouts.app')

@section('title', 'Eventos')
@section('page-title', 'Eventos')

@section('content')
<div class="space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900 dark:text.white">Eventos</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">Explore eventos disponíveis e garanta seus ingressos.</p>
        </div>
    </div>

    @if($events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $evento)
                <div class="bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow">
                    <div class="p-5 space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $evento->title }}</h3>
                            <span class="text-xs px-2 py-1 rounded bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300">Publicado</span>
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
                        <div class="flex items-center justify-end pt-3">
                            <a href="{{ route('cliente.eventos.show', $evento) }}" class="px-3 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 text-sm">
                                Ver Detalhes
                            </a>
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
            <h3 class="text-2xl font-semibold text-slate-900 dark:text-white mb-3">Nenhum evento no momento</h3>
            <p class="text-slate-600 dark:text-slate-300 mb-2 max-w-md mx-auto">Volte em breve para ver novos eventos.</p>
        </div>
    @endif
</div>
@endsection
