@extends('layouts.app')

@section('title', 'Marketing: Banners - Admin')
@section('page-title', 'Marketing: Banners')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-black rounded-xl p-6 border border-gray-200 dark:border-white/5">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-3 mb-2">
                    <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                        <i data-lucide="megaphone" class="w-6 h-6 text-white"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Banners</h2>
                </div>
                <p class="text-gray-600 dark:text-gray-400">Gerencie os banners que aparecem no dashboard das associações.</p>
            </div>
            <a href="{{ route('admin.marketing.banners.create') }}" 
               class="inline-flex items-center space-x-2 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200">
                <i data-lucide="plus" class="w-5 h-5"></i>
                <span>Novo Banner</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($banners as $banner)
            <div class="bg-white dark:bg-black rounded-xl p-5 border border-gray-200 dark:border-white/5">
                <div class="aspect-[16/9] bg-gray-100 dark:bg-white/5 rounded-lg overflow-hidden mb-4">
                    <img src="{{ $banner->image_url }}" alt="{{ $banner->name }}" class="w-full h-full object-cover">
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $banner->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Associação: {{ $banner->association->nome ?? '—' }}</p>
                    <p class="text-xs">
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                            {{ $banner->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-900/20 dark:text-gray-300' }}">
                            {{ $banner->status === 'active' ? 'Ativo' : 'Inativo' }}
                        </span>
                    </p>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <a href="{{ route('admin.marketing.banners.edit', $banner) }}" class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5">
                        <i data-lucide="edit" class="w-4 h-4 mr-2"></i> Editar
                    </a>
                    <form action="{{ route('admin.marketing.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Excluir este banner?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-2 rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/10">
                            <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Excluir
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-black rounded-xl p-6 border border-gray-200 dark:border-white/5">
                <div class="text-center">
                    <i data-lucide="image-off" class="w-10 h-10 text-gray-400 mx-auto mb-2"></i>
                    <p class="text-gray-600 dark:text-gray-400">Nenhum banner cadastrado.</p>
                </div>
            </div>
        @endforelse
    </div>

    @if($banners->hasPages())
        <div class="bg-white dark:bg-black rounded-xl p-4 border border-gray-200 dark:border-white/5">
            {{ $banners->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
    });
</script>
@endpush
@endsection
