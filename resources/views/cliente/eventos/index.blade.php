@extends('layouts.app')

@section('title', 'Eventos')
@section('page-title', 'Eventos')

@section('content')
<div class="space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">Encontre eventos</h1>

    @if($events->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($events as $evento)
                <a href="{{ route('cliente.eventos.show', $evento) }}" class="block bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden shadow hover:shadow-md transition-shadow">
                    <div class="p-5 space-y-2">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">{{ $evento->title }}</h3>
                        <p class="text-sm text-slate-700 dark:text-slate-300 flex items-center">
                            <i data-lucide="map-pin" class="inline w-4 h-4 mr-1"></i>
                            {{ $evento->location ?: 'Local a definir' }}
                        </p>
                    </div>
                </a>
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
