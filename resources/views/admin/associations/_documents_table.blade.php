<div class="space-y-6">
    @if ($association->associationDocuments->isEmpty())
        <div class="p-6 text-center bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
            <i class="fas fa-folder-open text-4xl text-gray-400 mb-3"></i>
            <p class="text-gray-600 dark:text-gray-300 font-medium">Nenhum documento foi enviado para esta associação ainda.</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Peça ao cliente para completar o envio dos documentos de cadastro.</p>
        </div>
    @else
        <div class="overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">
                            Documento
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Usuário (Responsável)
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Status
                        </th>
                        <th scope="col" class="px-6 py-3">
                            Data de Envio
                        </th>
                        <th scope="col" class="px-6 py-3 text-right">
                            Ações
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($association->associationDocuments as $document)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ $document->documentType->name ?? 'Tipo Desconhecido' }}
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ basename($document->file_path) }}</p>
                            </th>
                            <td class="px-6 py-4">
                                {{ $document->user->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4">
    @php
        $statusClass = match($document->status) {
            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            'missing' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
        };

        $statusLabel = match($document->status) {
            'approved' => 'Aprovado',
            'pending' => 'Pendente',
            'rejected' => 'Rejeitado',
            'missing' => 'Pendente de envio',
            default => 'Desconhecido',
        };
    @endphp

    <span class="text-xs font-medium px-2.5 py-0.5 rounded {{ $statusClass }}">
        {{ $statusLabel }}
    </span>
</td>

                            <td class="px-6 py-4">
                                {{ $document->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 text-right space-x-2 flex justify-end">
                                <a href="{{ Storage::url($document->file_path) }}" target="_blank" class="text-white bg-primary-blue hover:bg-dark-blue font-medium rounded-lg text-sm px-3 py-1.5 dark:bg-secondary-blue dark:hover:bg-primary-blue transition-colors flex items-center" title="Visualizar Documento">
                                    <i class="fas fa-eye w-4 h-4"></i>
                                </a>
                                
                                <form action="{{ route('admin.documents.approve', $document->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja APROVAR este documento?')" class="inline-block">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="text-white bg-green-600 hover:bg-green-700 font-medium rounded-lg text-sm px-3 py-1.5 transition-colors flex items-center" title="Aprovar">
                                        <i class="fas fa-check w-4 h-4"></i>
                                    </button>
                                </form>
                                
                                <button type="button" onclick="rejectDocument({{ $document->id }})" class="text-white bg-red-600 hover:bg-red-700 font-medium rounded-lg text-sm px-3 py-1.5 transition-colors flex items-center" title="Rejeitar">
                                    <i class="fas fa-times w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                        @if ($document->status === 'rejected' && $document->rejection_reason)
                            <tr class="bg-white dark:bg-gray-800">
                                <td colspan="5" class="px-6 py-2 text-sm text-red-600 dark:text-red-400 border-t border-red-200 dark:border-red-800">
                                    <i class="fas fa-exclamation-circle mr-2"></i> **Motivo da Rejeição:** {{ $document->rejection_reason }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    
    {{-- Você precisará implementar a lógica de backend para as rotas admin.documents.approve/reject --}}
</div>