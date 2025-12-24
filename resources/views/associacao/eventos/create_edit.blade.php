@extends('layouts.app')

@section('title', isset($evento) ? 'Editar Evento' : 'Novo Evento')
@section('page-title', isset($evento) ? 'Editar Evento' : 'Novo Evento')

@section('content')
<div class="space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <form method="POST" action="{{ isset($evento) ? route('associacao.eventos.update', $evento) : route('associacao.eventos.store') }}" class="space-y-6">
        @csrf
        @if(isset($evento))
            @method('PUT')
        @endif
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Título</label>
                <input type="text" name="title" value="{{ old('title', $evento->title ?? '') }}" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
                <select name="status" class="mt-1 block w-full rounded-lg bg.white dark:bg.black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
                    @php $current = old('status', $evento->status ?? 'draft'); @endphp
                    <option value="draft" @selected($current==='draft')>Rascunho</option>
                    <option value="published" @selected($current==='published')>Publicado</option>
                    <option value="archived" @selected($current==='archived')>Arquivado</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Descrição</label>
                <textarea name="description" rows="4" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">{{ old('description', $evento->description ?? '') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Local</label>
                <input type="text" name="location" value="{{ old('location', $evento->location ?? '') }}" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Capacidade</label>
                <input type="number" min="0" name="capacity" value="{{ old('capacity', $evento->capacity ?? 0) }}" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Início</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at', isset($evento) && $evento->starts_at ? $evento->starts_at->format('Y-m-d\\TH:i') : '') }}" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Fim</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at', isset($evento) && $evento->ends_at ? $evento->ends_at->format('Y-m-d\\TH:i') : '') }}" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white">
            </div>
            <div>
                <label class="block text.sm font-medium text-slate-700 dark:text-slate-300">Cor da Marca</label>
                <input type="text" name="brand_color" value="{{ old('brand_color', $evento->brand_color ?? '') }}" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white" placeholder="#000000">
            </div>
        </div>
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('associacao.eventos.index') }}" class="inline-flex items-center justify-center rounded-lg bg-black/5 dark:bg-white/5 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg-black/10 dark:hover:bg-white/10">Cancelar</a>
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Salvar</button>
        </div>
    </form>

    @isset($ticketTypes)
    <div class="border-t border-gray-200 dark:border-white/10 pt-6">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white mb-4">Tipos de Ingresso</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($ticketTypes as $tt)
                <div class="bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ $tt->name }}</p>
                            <p class="text-xs text-slate-600 dark:text-slate-400">Capacidade: {{ $tt->capacity }} • Limite/Pedido: {{ $tt->per_order_limit }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white">R$ {{ number_format($tt->price, 2, ',', '.') }}</p>
                            <span class="text-xs px-2 py-1 rounded {{ $tt->is_active ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $tt->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-slate-600 dark:text-slate-400">Nenhum tipo de ingresso ainda.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('associacao.eventos.add-ticket-type', $evento) }}" class="mt-6 grid grid-cols-1 md:grid-cols-5 gap-4">
            @csrf
            <input type="text" name="name" placeholder="Nome" class="rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm">
            <input type="number" step="0.01" min="0" name="price" placeholder="Preço" class="rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm">
            <input type="number" min="0" name="capacity" placeholder="Capacidade" class="rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm">
            <input type="number" min="1" name="per_order_limit" placeholder="Limite/Pedido" class="rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm">
            <div class="flex items-center gap-3">
                <label class="text-sm text-slate-700 dark:text-slate-300"><input type="checkbox" name="is_active" value="1" class="mr-2"> Ativo</label>
                <button class="inline-flex items-center justify-center rounded-lg bg-black dark:bg-white text-white dark:text-black px-4 py-2 text-sm font-medium">Adicionar</button>
            </div>
        </form>
    </div>
    @endisset
</div>
@endsection
