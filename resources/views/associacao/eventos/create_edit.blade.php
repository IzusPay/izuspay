@extends('layouts.app')

@section('title', isset($evento) ? 'Editar Evento' : 'Novo Evento')
@section('page-title', isset($evento) ? 'Editar Evento' : 'Novo Evento')

@section('content')
<div x-data="{ tab: 'detalhes' }" class="space-y-6 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    @unless(isset($dashboardOnly) && $dashboardOnly)
    <form method="POST" action="{{ isset($evento) ? route('associacao.eventos.update', $evento) : route('associacao.eventos.store') }}" enctype="multipart/form-data" class="space-y-6">
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
                <div class="mt-1 rounded-lg border border-gray-300 dark:border-white/10 overflow-hidden">
                    <div class="bg-slate-50 dark:bg-slate-900 border-b border-gray-300 dark:border-white/10 p-2 flex flex-wrap gap-1">
                        <button type="button" onclick="document.execCommand('bold')" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded" title="Negrito">
                            <i data-lucide="bold" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="document.execCommand('italic')" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded" title="Itálico">
                            <i data-lucide="italic" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="document.execCommand('underline')" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded" title="Sublinhado">
                            <i data-lucide="underline" class="w-4 h-4"></i>
                        </button>
                        <div class="w-px bg-gray-300 dark:bg-gray-700 mx-1"></div>
                        <button type="button" onclick="document.execCommand('insertUnorderedList')" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded" title="Lista">
                            <i data-lucide="list" class="w-4 h-4"></i>
                        </button>
                        <button type="button" onclick="document.execCommand('insertOrderedList')" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded" title="Lista numerada">
                            <i data-lucide="list-ordered" class="w-4 h-4"></i>
                        </button>
                        <div class="w-px bg-gray-300 dark:bg-gray-700 mx-1"></div>
                        <button type="button" onclick="(function(){var u=prompt('Digite a URL:'); if(u) document.execCommand('createLink', false, u); })()" class="p-2 hover:bg-gray-200 dark:hover:bg-gray-700 rounded" title="Link">
                            <i data-lucide="link" class="w-4 h-4"></i>
                        </button>
                    </div>
                    <div id="event-description-editor" contenteditable="true" class="min-h-[200px] p-3 bg-white dark:bg-black text-slate-900 dark:text-white">{{ old('description', $evento->description ?? '') }}</div>
                </div>
                <textarea id="event-description" name="description" class="hidden">{{ old('description', $evento->description ?? '') }}</textarea>
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
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Cor da Marca</label>
                <input type="text" name="brand_color" value="{{ old('brand_color', $evento->brand_color ?? '') }}" class="mt-1 block w-full rounded-lg bg-white dark:bg-black border border-gray-300 dark:border-white/10 px-3 py-2 text-sm text-slate-900 dark:text-white" placeholder="#000000">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Imagem do Evento</label>
                <div class="mt-1 border-2 border-dashed border-gray-300 dark:border-white/10 rounded-lg p-4 text-center">
                    <input type="file" id="brand_logo" name="brand_logo" accept="image/*" class="hidden" onchange="(function(input){ if (input.files && input.files[0]) { const reader = new FileReader(); reader.onload = function(e){ const img = document.getElementById('event-image-preview'); img.src = e.target.result; img.classList.remove('hidden'); }; reader.readAsDataURL(input.files[0]); } })(this)">
                    <label for="brand_logo" class="cursor-pointer inline-flex items-center gap-2 px-3 py-2 rounded bg-black/5 dark:bg-white/5 text-slate-700 dark:text-slate-300">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Selecionar imagem</span>
                    </label>
                    <img id="event-image-preview" src="{{ isset($evento) && $evento->brand_logo ? asset('storage/'.$evento->brand_logo) : '' }}" alt="Imagem do evento" class="{{ isset($evento) && $evento->brand_logo ? '' : 'hidden' }} w-full max-h-48 object-cover rounded-lg mt-3">
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between gap-3">
            @isset($evento)
            <div class="flex gap-2">
                <button type="button" @click="tab = 'detalhes'" :class="tab==='detalhes' ? 'bg-slate-900 text-white dark:bg-white dark:text-black' : 'bg-black/5 dark:bg-white/5 text-slate-700 dark:text-slate-300'" class="px-3 py-2 rounded-lg text-sm font-medium">Detalhes</button>
                <button type="button" @click="tab = 'ingressos'" :class="tab==='ingressos' ? 'bg-slate-900 text-white dark:bg.white dark:text-black' : 'bg-black/5 dark:bg.white/5 text-slate-700 dark:text-slate-300'" class="px-3 py-2 rounded-lg text-sm font.medium">Ingressos vendidos</button>
                <button type="button" @click="tab = 'faturamento'" :class="tab==='faturamento' ? 'bg-slate-900 text-white dark:bg.white dark:text-black' : 'bg-black/5 dark:bg.white/5 text-slate-700 dark:text-slate-300'" class="px-3 py-2 rounded-lg text-sm font.medium">Faturamento</button>
            </div>
            @endisset
            <a href="{{ route('associacao.eventos.index') }}" class="inline-flex items-center justify-center rounded-lg bg-black/5 dark:bg.white/5 px-4 py-2 text-sm font-medium text-slate-700 dark:text-slate-300 hover:bg.black/10 dark:hover:bg.white/10">Cancelar</a>
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
    @endunless

    @if(isset($dashboardOnly) && $dashboardOnly && isset($metrics))
    <div class="space-y-4">
        <div class="relative w-full h-44 md:h-64 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-800">
            @if(isset($evento) && $evento->brand_logo)
                <img src="{{ asset('storage/'.$evento->brand_logo) }}" alt="{{ $evento->title }}" class="absolute inset-0 w-full h-full object-cover">
            @else
                <div class="absolute inset-0 w-full h-full bg-gradient-to-br from-slate-200 to-slate-300 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center">
                    <i data-lucide="image" class="w-8 h-8 text-slate-500 dark:text-slate-400"></i>
                </div>
            @endif
            <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent p-4">
                <h2 class="text-xl font-bold text-white">{{ $evento->title }}</h2>
                <p class="text-sm text-white/80">{{ $evento->location ?? 'Local não definido' }}</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Gerado</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        R$ {{ number_format($metrics['total_generated'], 2, ',', '.') }}
                    </p>
                </div>
                <i data-lucide="banknote" class="w-6 h-6 text-slate-500"></i>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800 border-l-4 border-green-500">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Pago</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        R$ {{ number_format($metrics['total_paid'], 2, ',', '.') }}
                    </p>
                </div>
                <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800 border-l-4 border-yellow-400">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Aguardando</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        R$ {{ number_format($metrics['total_awaiting'], 2, ',', '.') }}
                    </p>
                </div>
                <i data-lucide="clock" class="w-6 h-6 text-yellow-400"></i>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Pedidos</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        {{ $metrics['orders_count'] }}
                    </p>
                </div>
                <i data-lucide="list" class="w-6 h-6 text-slate-500"></i>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800 border-l-4 border-green-500">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Pagos</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        {{ $metrics['paid_count'] }}
                    </p>
                </div>
                <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Conversão</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        {{ number_format($metrics['conversion_rate'], 2, ',', '.') }}%
                    </p>
                </div>
                <i data-lucide="percent" class="w-6 h-6 text-indigo-500"></i>
            </div>
        </div>
    </div>
    @endif

    @isset($orders)
    @if(isset($dashboardOnly) && $dashboardOnly)
    <div x-data="{ modalOpen: false, currentOrderId: null }" class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Ingressos vendidos</h2>
            <div class="flex items-center gap-2">
                <input type="text" id="search-sales-dashboard" placeholder="Buscar por nome ou CPF..." class="w-56 md:w-72 px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
                <a href="{{ route('associacao.eventos.edit', $evento) }}" class="inline-flex items-center justify-center rounded-lg bg-slate-900 text-white dark:bg-white dark:text-black px-3 py-2 text-xs font-semibold">Editar evento</a>
            </div>
        </div>
        <div class="overflow-x-auto bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-xl">
            <table id="orders-table-dashboard" class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Pedido</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Cliente</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Qtd</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Unitário</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Total</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-300">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($orders as $o)
                    <tr data-name="{{ $o->user?->name ?? '' }}" data-cpf="{{ preg_replace('/\\D/', '', $o->user?->documento ?? '') }}">
                        <td class="px-4 py-3 text-slate-800 dark:text-slate-200">#{{ $o->id }}</td>
                        <td class="px-4 py-3 text-slate-800 dark:text-slate-200">{{ $o->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-800 dark:text-slate-200">{{ $o->quantity }}</td>
                        <td class="px-4 py-3 text-slate-800 dark:text-slate-200">R$ {{ number_format($o->unit_price, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-slate-800 dark:text-slate-200">R$ {{ number_format($o->unit_price * $o->quantity, 2, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $o->status==='paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' }}">{{ $o->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" @click="currentOrderId = {{ $o->id }}; modalOpen = true" class="inline-flex items-center justify-center rounded-lg bg-black/5 dark:bg-white/5 text-slate-800 dark:text-slate-200 px-3 py-2 text-xs font-semibold mr-2">Detalhes</button>
                            @if($o->status !== 'paid')
                            <form method="POST" action="{{ route('associacao.eventos.mark-order-paid', [$evento, $o]) }}">
                                @csrf
                                <button class="inline-flex items-center justify-center rounded-lg bg-slate-900 dark:bg-white text-white dark:text-black px-3 py-2 text-xs font-semibold">Marcar como pago</button>
                            </form>
                            @else
                            <span class="text-xs text-slate-500">Pago</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr data-empty><td colspan="7" class="px-4 py-6 text-center text-slate-500">Nenhum pedido ainda</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" @click="modalOpen=false"></div>
            <div class="relative w-full max-w-4xl mx-auto bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-lg">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Detalhes do pedido</h3>
                    <button type="button" @click="modalOpen=false" class="px-2 py-1 rounded bg-black/5 dark:bg-white/5 text-slate-700 dark:text-slate-300 text-xs">Fechar</button>
                </div>
                <div class="p-4 max-h-[70vh] overflow-y-auto">
                    @foreach($orders as $o)
                        <div x-show="currentOrderId === {{ $o->id }}">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-xl p-4">
                                    <p class="text-xs text-slate-500">Pedido</p>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">#{{ $o->id }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Criado em: {{ $o->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Tipo: {{ $o->ticketType?->name ?? '—' }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Preço: R$ {{ number_format($o->unit_price, 2, ',', '.') }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Quantidade: {{ $o->quantity }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Total: R$ {{ number_format($o->unit_price * $o->quantity, 2, ',', '.') }}</p>
                                </div>
                                <div class="bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-xl p-4">
                                    <p class="text-xs text-slate-500">Compra</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">ID: {{ $o->sale?->id ?? '—' }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Método: {{ $o->sale?->payment_method ?? '—' }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Status: {{ $o->sale?->status ?? '—' }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Criada em: {{ $o->sale?->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                                </div>
                                <div class="bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-xl p-4">
                                    <p class="text-xs text-slate-500 mb-2">Ingressos (QR)</p>
                                    @if($o->tickets && $o->tickets->count())
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                            @foreach($o->tickets as $t)
                                                <div class="flex flex-col items-center justify-center rounded-lg border border-slate-200 dark:border-slate-800 p-2">
                                                    <div id="qr-{{ $t->id }}" data-qr-url="{{ url('/ingresso') . '/' . $t->qr_token }}" class="w-40 h-40"></div>
                                                    <p class="mt-2 text-[11px] text-slate-500 break-all w-full text-center">{{ url('/ingresso') . '/' . $t->qr_token }}</p>
                                                    <span class="mt-1 text-[10px] px-2 py-0.5 rounded {{ $t->status==='used' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">{{ $t->status }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-xs text-slate-600 dark:text-slate-400">Nenhum ingresso emitido para este pedido.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @else
    <div x-show="tab==='ingressos'" x-data="{ modalOpen: false, currentOrderId: null }" class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Ingressos vendidos</h2>
            <input type="text" id="search-sales-tab" placeholder="Buscar por nome ou CPF..." class="w-56 md:w-72 px-3 py-2 border border-slate-300 dark:border-slate-700 rounded-lg text-sm">
        </div>
        <div class="overflow-x-auto bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-xl">
            <table id="orders-table-tab" class="min-w-full divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Pedido</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Cliente</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Qtd</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Unitário</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Total</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-600 dark:text-slate-300">Status</th>
                        <th class="px-4 py-3 text-right font-medium text-slate-600 dark:text-slate-300">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                    @forelse($orders as $o)
                    <tr data-name="{{ $o->user?->name ?? '' }}" data-cpf="{{ preg_replace('/\\D/', '', $o->user?->documento ?? '') }}">
                        <td class="px-4 py-3 text-slate-800 dark:text-slate-200">#{{ $o->id }}</td>
                        <td class="px-4 py-3 text-slate-800 dark:text-slate-200">{{ $o->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-800 dark:text-slate-200">{{ $o->quantity }}</td>
                        <td class="px-4 py-3 text-slate-800 dark:text-slate-200">R$ {{ number_format($o->unit_price, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-slate-800 dark:text-slate-200">R$ {{ number_format($o->unit_price * $o->quantity, 2, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs {{ $o->status==='paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' }}">{{ $o->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" @click="currentOrderId = {{ $o->id }}; modalOpen = true" class="inline-flex items-center justify-center rounded-lg bg-black/5 dark:bg-white/5 text-slate-800 dark:text-slate-200 px-3 py-2 text-xs font-semibold mr-2">Detalhes</button>
                            @if($o->status !== 'paid')
                            <form method="POST" action="{{ route('associacao.eventos.mark-order-paid', [$evento, $o]) }}">
                                @csrf
                                <button class="inline-flex items-center justify-center rounded-lg bg-slate-900 dark:bg-white text-white dark:text-black px-3 py-2 text-xs font-semibold">Marcar como pago</button>
                            </form>
                            @else
                            <span class="text-xs text-slate-500">Pago</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr data-empty><td colspan="7" class="px-4 py-6 text-center text-slate-500">Nenhum pedido ainda</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/50" @click="modalOpen=false"></div>
            <div class="relative w-full max-w-4xl mx-auto bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-lg">
                <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200 dark:border-slate-800">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Detalhes do pedido</h3>
                    <button type="button" @click="modalOpen=false" class="px-2 py-1 rounded bg-black/5 dark:bg-white/5 text-slate-700 dark:text-slate-300 text-xs">Fechar</button>
                </div>
                <div class="p-4 max-h-[70vh] overflow-y-auto">
                    @foreach($orders as $o)
                        <div x-show="currentOrderId === {{ $o->id }}">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-xl p-4">
                                    <p class="text-xs text-slate-500">Pedido</p>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">#{{ $o->id }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Criado em: {{ $o->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Tipo: {{ $o->ticketType?->name ?? '—' }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Preço: R$ {{ number_format($o->unit_price, 2, ',', '.') }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Quantidade: {{ $o->quantity }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Total: R$ {{ number_format($o->unit_price * $o->quantity, 2, ',', '.') }}</p>
                                </div>
                                <div class="bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-xl p-4">
                                    <p class="text-xs text-slate-500">Compra</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">ID: {{ $o->sale?->id ?? '—' }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Método: {{ $o->sale?->payment_method ?? '—' }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Status: {{ $o->sale?->status ?? '—' }}</p>
                                    <p class="text-xs text-slate-600 dark:text-slate-400">Criada em: {{ $o->sale?->created_at?->format('d/m/Y H:i') ?? '—' }}</p>
                                </div>
                                <div class="bg-white dark:bg-black border border-slate-200 dark:border-slate-800 rounded-xl p-4">
                                    <p class="text-xs text-slate-500 mb-2">Ingressos (QR)</p>
                                    @if($o->tickets && $o->tickets->count())
                                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                            @foreach($o->tickets as $t)
                                                <div class="flex flex-col items-center justify-center rounded-lg border border-slate-200 dark:border-slate-800 p-2">
                                                    <div id="qr-{{ $t->id }}" data-qr-url="{{ url('/ingresso') . '/' . $t->qr_token }}" class="w-40 h-40"></div>
                                                    <p class="mt-2 text-[11px] text-slate-500 break-all w-full text-center">{{ url('/ingresso') . '/' . $t->qr_token }}</p>
                                                    <span class="mt-1 text-[10px] px-2 py-0.5 rounded {{ $t->status==='used' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">{{ $t->status }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-xs text-slate-600 dark:text-slate-400">Nenhum ingresso emitido para este pedido.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
    @endisset

    @isset($metrics)
    @unless(isset($dashboardOnly) && $dashboardOnly)
    <div x-show="tab==='faturamento'" class="space-y-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-white">Faturamento</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Gerado</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        R$ {{ number_format($metrics['total_generated'], 2, ',', '.') }}
                    </p>
                </div>
                <i data-lucide="banknote" class="w-6 h-6 text-slate-500"></i>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800 border-l-4 border-green-500">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Pago</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        R$ {{ number_format($metrics['total_paid'], 2, ',', '.') }}
                    </p>
                </div>
                <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800 border-l-4 border-yellow-400">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Aguardando</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        R$ {{ number_format($metrics['total_awaiting'], 2, ',', '.') }}
                    </p>
                </div>
                <i data-lucide="clock" class="w-6 h-6 text-yellow-400"></i>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Pedidos</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        {{ $metrics['orders_count'] }}
                    </p>
                </div>
                <i data-lucide="list" class="w-6 h-6 text-slate-500"></i>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800 border-l-4 border-green-500">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Pagos</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        {{ $metrics['paid_count'] }}
                    </p>
                </div>
                <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm p-5 flex items-center justify-between border border-slate-200 dark:border-slate-800">
                <div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">Conversão</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-white">
                        {{ number_format($metrics['conversion_rate'], 2, ',', '.') }}%
                    </p>
                </div>
                <i data-lucide="percent" class="w-6 h-6 text-indigo-500"></i>
            </div>
        </div>
    </div>
    @endunless
    @endisset
</div>
@endsection
@push('scripts')
<script src="{{ asset('js/qrcode.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ed = document.getElementById('event-description-editor');
        const ta = document.getElementById('event-description');
        if (ed && ta) {
            ed.addEventListener('input', function () { ta.value = this.innerHTML; });
        }
        const nodes = document.querySelectorAll('[data-qr-url]');
        nodes.forEach(node => {
            const content = node.getAttribute('data-qr-url');
            try {
                new QRCode(node, {
                    text: content,
                    width: 160,
                    height: 160,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: QRCode.CorrectLevel.M
                });
            } catch (e) {}
        });
        function normalizeText(s) {
            return (s || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        }
        function onlyDigits(s) {
            return (s || '').toString().replace(/\D/g, '');
        }
        function attachSearch(inputId, tableId) {
            const input = document.getElementById(inputId);
            const table = document.getElementById(tableId);
            if (!input || !table) return;
            const rows = Array.from(table.querySelectorAll('tbody tr'));
            input.addEventListener('input', function () {
                const termRaw = this.value.trim();
                const termDigits = onlyDigits(termRaw);
                const term = normalizeText(termRaw);
                let visibleCount = 0;
                rows.forEach(row => {
                    const name = normalizeText(row.getAttribute('data-name') || '');
                    const cpf = onlyDigits(row.getAttribute('data-cpf') || '');
                    let match = false;
                    if (termDigits.length >= 3) {
                        match = cpf.includes(termDigits);
                    } else {
                        match = name.includes(term);
                    }
                    row.style.display = match ? '' : 'none';
                    if (match) visibleCount++;
                });
                const emptyRow = table.querySelector('tbody tr[data-empty]');
                if (emptyRow) {
                    emptyRow.style.display = visibleCount === 0 ? '' : 'none';
                }
            });
        }
        attachSearch('search-sales-dashboard', 'orders-table-dashboard');
        attachSearch('search-sales-tab', 'orders-table-tab');
    });
</script>
<style>
#event-description-editor:empty:before {
    content: 'Descreva seu evento aqui...';
    color: #9CA3AF;
    pointer-events: none;
}
.dark #event-description-editor:empty:before { color: #6B7280; }
#event-description-editor:focus:before { content: none; }
</style>
@endpush
