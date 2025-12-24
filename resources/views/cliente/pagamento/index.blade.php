@extends('layouts.app')

@section('title', 'Pagamentos')
@section('page-title', 'Pagamentos e Ingressos')

@section('content')
<div class="max-w-xl mx-auto space-y-6">
    @php $hasMembershipSale = !empty($pendingSale) && $pendingSale && $pendingSale->plan; @endphp
    @if($hasMembershipSale)
        <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-gray-800 dark:to-gray-700 rounded-xl p-6 border border-red-100 dark:border-gray-600">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center">
                    <i data-lucide="credit-card" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Pagamento Pendente</h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Sua documentação foi aprovada! Agora, realize o pagamento para ativar sua associação.
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-4 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    @if($hasMembershipSale)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 text-center">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Resumo da sua Compra</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">Plano: <strong>{{ data_get($pendingSale, 'plan.name') }}</strong></p>
            <p class="text-4xl font-extrabold text-green-600 dark:text-green-400 mb-6">
                R$ {{ number_format($pendingSale->total_price, 2, ',', '.') }}
            </p>

            <a href="{{ route('checkout.show', ['hash_id' => data_get($pendingSale, 'plan.hash_id')]) }}"
               class="inline-flex items-center space-x-2 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
                <span>Ir para o Pagamento</span>
            </a>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Meus ingressos pendentes</h3>
            <a href="{{ route('cliente.eventos.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Ver eventos</a>
        </div>
        <div class="space-y-4">
            @forelse($pendingOrders ?? [] as $order)
                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify.center">
                            <i data-lucide="ticket" class="w-6 h-6 text-blue-600 dark:text-blue-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ data_get($order, 'event.title', 'Evento') }}
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                {{ data_get($order, 'ticketType.name', 'Ingresso') }} • {{ $order->quantity }}x
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                            R$ {{ number_format(($order->unit_price ?? 0) * ($order->quantity ?? 0), 2, ',', '.') }}
                        </p>
                        <div class="mt-2 inline-flex items-center px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-medium">
                            Aguardando pagamento
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="calendar" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum ingresso pendente no momento</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Meus ingressos</h3>
            <a href="{{ route('cliente.eventos.index') }}" class="text-sm text-blue-600 hover:text-blue-700">Explorar eventos</a>
        </div>
        <div class="space-y-4">
            @forelse($myTickets ?? [] as $ticket)
                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                            <i data-lucide="ticket-check" class="w-6 h-6 text-green-600 dark:text-green-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text.white">
                                {{ data_get($ticket, 'ticketType.event.title', 'Evento') }}
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                {{ data_get($ticket, 'ticketType.name', 'Ingresso') }}
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="mt-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            {{ ($ticket->status ?? '') === 'used' ? 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-300' : 'bg-emerald-100 text-emerald-700' }}">
                            {{ ($ticket->status ?? '') === 'used' ? 'Utilizado' : 'Emitido' }}
                        </div>
                        <div class="mt-2">
                            @php $qr = $ticket->qr_token ?? null; @endphp
                            @if($qr)
                                <a href="{{ 'https://api.qrserver.com/v1/create-qr-code/?size=256x256&data='.urlencode($qr) }}"
                                   target="_blank"
                                   class="inline-flex items-center space-x-1 text-xs text-blue-600 hover:text-blue-700">
                                    <i data-lucide="qr-code" class="w-4 h-4"></i>
                                    <span>Ver QR</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-2">
                        <i data-lucide="ticket" class="w-6 h-6 text-gray-400"></i>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum ingresso emitido ainda</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
@endpush
