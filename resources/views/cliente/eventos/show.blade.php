@extends('layouts.app')

@section('title', $evento->title)
@section('page-title', $evento->title)

@section('content')
<div class="space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 space-y-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-slate-900 dark:text-white">{{ $evento->title }}</h1>
                @if($evento->brand_color)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300">
                        {{ $association->nome ?? 'Evento' }}
                    </span>
                @endif
            </div>
            <p class="text-slate-700 dark:text-slate-300">{{ $evento->description }}</p>
            <div class="text-sm text-slate-700 dark:text-slate-300">
                <i data-lucide="map-pin" class="inline w-4 h-4 mr-1"></i>
                {{ $evento->location ?: 'Local a definir' }}
            </div>
            <div class="text-sm text-slate-700 dark:text-slate-300">
                <i data-lucide="calendar" class="inline w-4 h-4 mr-1"></i>
                {{ optional($evento->starts_at)->format('d/m/Y H:i') }} - {{ optional($evento->ends_at)->format('d/m/Y H:i') }}
            </div>
        </div>
        <div class="space-y-4">
            <div class="bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-xl p-4">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-3">Comprar Ingresso</h2>
                <form method="POST" action="{{ route('cliente.eventos.buy', $evento) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Tipo de Ingresso</label>
                        <select name="ticket_type_id" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                            @foreach($types as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} • R$ {{ number_format($t->price, 2, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Quantidade</label>
                        <input type="number" min="1" name="quantity" value="1" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Nome</label>
                        <input type="text" name="name" value="{{ old('name', Auth::user()->name ?? '') }}" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email</label>
                        <input type="email" name="email" value="{{ old('email', Auth::user()->email ?? '') }}" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Telefone</label>
                        <input type="text" name="phone" value="{{ old('phone', Auth::user()->phone ?? '') }}" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Documento</label>
                        <input type="text" name="document" value="{{ old('document', Auth::user()->documento ?? '') }}" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                    </div>
                    <button class="w-full inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Prosseguir</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
