<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use App\Models\Sale;
use Illuminate\Http\Request;

class DocumentosController extends Controller
{
    /**
     * Exibe a tela de envio de documentos para o cliente.
     */
    public function index()
    {
        $user = auth()->user();

        $submittedDocs = $user->documentations()->with('documentType')->get()->keyBy('document_type_id');
        $activeDocumentTypes = DocumentType::where('association_id', $user->association_id)
            ->where('is_active', true)
            ->get();

        $requiredTypesCount = DocumentType::where('association_id', $user->association_id)
            ->where('is_required', true)
            ->count();
        $submittedRequiredCount = $user->documentations()->whereHas('documentType', function ($query) {
            $query->where('is_required', true);
        })->count();

        $allRequiredSubmitted = ($submittedRequiredCount >= $requiredTypesCount) && ($requiredTypesCount > 0);

        $pendingSale = null;
        if ($user->status === 'payment_pending') {
            $pendingSale = Sale::where('user_id', $user->id)
                ->where('status', 'awaiting_payment')
                ->with('plan')
                ->first();
        }

        return view('cliente.documentos.index', compact('submittedDocs', 'activeDocumentTypes', 'allRequiredSubmitted', 'pendingSale'));
    }

    /**
     * Armazena o documento enviado pelo cliente.
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $file = $request->file('document_file');
        $path = $file->store('public/documentos/'.$user->id);

        $user->documentations()->create([
            'document_type_id' => $request->document_type_id,
            'file_path' => $path,
            'status' => 'pending',
        ]);

        // O status do usuário não é atualizado aqui. O usuário permanece 'documentation_pending'
        // até que ele clique no botão "Enviar para Análise".

        return redirect()->route('cliente.documentos.index')->with('success', 'Documento enviado com sucesso!');
    }

    /**
     * Novo método: Atualiza o status do usuário para análise.
     */
    public function submitForReview(Request $request)
    {
        $user = auth()->user();
        $requiredTypesCount = DocumentType::where('association_id', $user->association_id)
            ->where('is_required', true)
            ->count();
        $submittedRequiredCount = $user->documentations()->whereHas('documentType', function ($query) {
            $query->where('is_required', true);
        })->count();

        if ($submittedRequiredCount >= $requiredTypesCount && $requiredTypesCount > 0) {
            $user->status = 'docs_under_review';
            $user->save();

            return redirect()->route('cliente.documentos.index')->with('success', 'Documentos enviados para análise com sucesso! Aguarde a aprovação.');
        }

        return redirect()->back()->with('error', 'Por favor, envie todos os documentos obrigatórios antes de submeter para análise.');
    }
}
