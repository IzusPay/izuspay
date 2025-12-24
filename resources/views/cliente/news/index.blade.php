@extends('layouts.app')

@section('title', 'Dashboard - Cliente')
@section('page-title', 'Dashboard')
@section('page-description', 'Olá, {{ auth()->user()->name }}! Bem-vindo de volta.')

@section('content')
<div class="space-y-6">
    <!-- Mobile Welcome Header -->
    <div class="lg:hidden gradient-green -mx-4 -mt-4 px-4 py-6 mb-6">
        <div class="text-white">
            <h1 class="text-2xl font-bold">Olá, {{ auth()->user()->name }}</h1>
            <p class="text-green-100 mt-1">Bem-vindo de volta ao seu painel</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
            <div class="flex items-center">
                <div class="p-3 bg-primary-100 rounded-lg">
                    <svg class="h-6 w-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Documentos</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $documentsCount ?? 12 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Notícias</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $newsCount ?? 8 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pendências</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $pendingCount ?? 3 }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 card-hover">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Eventos</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $eventsCount ?? 5 }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ações Rápidas</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @php
                use Illuminate\Support\Facades\Route;
                $docsRoute = Route::has('cliente.documentos.index') ? 'cliente.documentos.index' : 'documentos.index';
            @endphp
            <a href="{{ route($docsRoute) }}" class="flex flex-col items-center p-4 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors">
                <div class="w-12 h-12 bg-primary-500 rounded-lg flex items-center justify-center mb-2">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-900">Novo Documento</span>
            </a>

            @php
                $paymentRoute = Route::has('cliente.pagamento.index') ? 'cliente.pagamento.index' : 'pagamento.index';
            @endphp
            <a href="{{ route($paymentRoute) }}" class="flex flex-col items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mb-2">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-900">Pagamento</span>
            </a>

            @php
                $contractsRoute = null;
                foreach (['cliente.contracts.index','cliente.contrato.index','contrato.index'] as $r) {
                    if (Route::has($r)) { $contractsRoute = $r; break; }
                }
                if (!$contractsRoute && Route::has('cliente.dashboard')) { $contractsRoute = 'cliente.dashboard'; }
            @endphp
            <a href="{{ route($contractsRoute) }}" class="flex flex-col items-center p-4 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors">
                <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center mb-2">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-900">Contratos</span>
            </a>

            @php
                $supportRoute = Route::has('cliente.support') ? 'cliente.support' : (Route::has('cliente.dashboard') ? 'cliente.dashboard' : null);
            @endphp
            <a href="{{ route($supportRoute) }}" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mb-2">
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-medium text-gray-900">Suporte</span>
            </a>
        </div>
    </div>

    <!-- Recent Activity & Upcoming Events -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Atividades Recentes</h3>
                @php
                    $activityRoute = Route::has('cliente.activity') ? 'cliente.activity' : (Route::has('cliente.dashboard') ? 'cliente.dashboard' : null);
                @endphp
                <a href="{{ route($activityRoute) }}" class="text-sm text-primary-600 hover:text-primary-700">Ver todas</a>
            </div>
            <div class="space-y-4">
                @forelse($recentActivities ?? [] as $activity)
                <div class="flex items-start space-x-3">
                    <div class="w-2 h-2 bg-primary-500 rounded-full mt-2"></div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                        <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Nenhuma atividade recente</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Upcoming Events -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Próximos Eventos</h3>
                @php
                    $eventsRoute = Route::has('cliente.eventos.index') ? 'cliente.eventos.index' : (Route::has('cliente.dashboard') ? 'cliente.dashboard' : null);
                @endphp
                <a href="{{ route($eventsRoute) }}" class="text-sm text-primary-600 hover:text-primary-700">Ver todos</a>
            </div>
            <div class="space-y-4">
                @forelse($upcomingEvents ?? [] as $event)
                <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex flex-col items-center justify-center">
                            <span class="text-xs font-medium text-primary-600">{{ $event->date->format('M') }}</span>
                            <span class="text-lg font-bold text-primary-700">{{ $event->date->format('d') }}</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-medium text-gray-900">{{ $event->title }}</h4>
                        <p class="text-xs text-gray-500">{{ $event->location }} • {{ $event->date->format('H:i') }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="mt-2 text-sm text-gray-500">Nenhum evento próximo</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Mobile spacing for bottom navigation -->
<div class="lg:hidden h-20"></div>
@endsection
