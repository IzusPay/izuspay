@extends('layouts.app')

@section('title', 'Dashboard - Cliente')

@section('content')
<div class="container mx-auto p-4 lg:p-8 space-y-6 text-slate-800 dark:text-slate-200">
    <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-slate-900 dark:text-white">Últimos eventos</h2>
        <a href="{{ route('cliente.eventos.index') }}" class="text-sm font-semibold text-slate-700 dark:text-slate-300 hover:underline">Ver todos</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($latestEvents as $evento)
            <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-800 bg-white dark:bg-black shadow-sm hover:shadow-md transition-shadow">
                @if($evento->brand_logo)
                    <div class="bg-slate-50 dark:bg-slate-800 flex items-center justify-center h-32 border-b border-slate-100 dark:border-slate-700">
                        <img src="{{ $evento->brand_logo }}" alt="{{ $evento->title }}" class="h-20 object-contain">
                    </div>
                @else
                    <div class="bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-700 h-32 border-b border-slate-100 dark:border-slate-700 flex items-center justify-center">
                        <i data-lucide="calendar" class="w-8 h-8 text-slate-500"></i>
                    </div>
                @endif
                <div class="p-5 space-y-2">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white truncate">{{ $evento->title }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ $evento->location ?? 'Local a definir' }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ optional($evento->starts_at)->format('d/m/Y H:i') }}
                        @if($evento->ends_at)
                            — {{ optional($evento->ends_at)->format('d/m/Y H:i') }}
                        @endif
                    </p>
                    <div class="pt-3 flex items-center justify-between">
                        <a href="{{ route('cliente.eventos.show', $evento) }}" class="inline-flex items-center px-3 py-2 rounded-lg bg-slate-900 dark:bg-white text-white dark:text-black text-xs font-semibold">
                            Ver detalhes
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                        </a>
                        <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                            <i data-lucide="ticket" class="w-4 h-4"></i>
                            {{ $evento->ticketTypes()->count() }} tipos
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-black rounded-2xl border border-slate-200 dark:border-slate-800 p-12 text-center">
                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="image-off" class="w-8 h-8 text-slate-400"></i>
                </div>
                <p class="text-slate-600 dark:text-slate-300">Nenhum evento disponível no momento</p>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Volte mais tarde para conferir novidades.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
