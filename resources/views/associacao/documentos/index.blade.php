@extends('layouts.app')

@section('title', 'Envio de Documentos')
@section('page-title', 'Documentação Pendente')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-gradient-to-r from-red-50 to-orange-50 dark:from-gray-800 dark:to-gray-700 rounded-xl p-6 border border-red-100 dark:border-gray-600">
        <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-6 h-6 text-white"></i>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Atenção!</h2>
                <p class="text-gray-600 dark:text-gray-400">
                    Sua documentação está pendente. Envie os documentos obrigatórios abaixo.
                </p>
            </div>
        </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Documentos Obrigatórios</h3>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($requiredTypes as $type)
            @php
                $doc = $submittedDocs->get($type->id);
            @endphp
            <li class="py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $type->name }}</p>
                        <div class="mt-1">
                            @if($doc && $doc->status === 'approved')
                                <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">Aprovado</span>
                            @elseif($doc && $doc->status === 'rejected')
                                <span class="px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">Reprovado</span>
                                @if($doc->rejection_reason)
                                    <p class="text-xs text-red-500 dark:text-red-400 mt-1">Motivo: {{ $doc->rejection_reason }}</p>
                                @endif
                            @elseif($doc && $doc->status === 'pending')
                                <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">Em análise</span>
                            @else
                                <span class="px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full">Não enviado</span>
                            @endif
                        </div>
                    </div>
                    @if($doc && $doc->file_path)
                        <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="text-blue-600 hover:text-blue-900">
                            <i data-lucide="eye" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
                <form action="{{ route('associacao.documentos.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 flex items-center gap-3">
                    @csrf
                    <input type="hidden" name="document_type_id" value="{{ $type->id }}">
                    <input type="file" name="document_file" required
                           class="flex-1 text-sm text-gray-600 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 @error('document_file') border-red-500 @enderror">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Enviar
                    </button>
                </form>
            </li>
            @empty
            <li class="py-4 text-center text-gray-500 dark:text-gray-400">Nenhum documento obrigatório configurado.</li>
            @endforelse
        </ul>
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
