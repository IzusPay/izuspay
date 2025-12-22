<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentTypeRequest;
use App\Models\DocumentType;

class DocumentTypeController extends Controller
{
    public function index()
    {
        $documentTypes = DocumentType::where('association_id', auth()->user()->association_id)->get();

        return view('associacao.document_types.index', compact('documentTypes'));
    }

    public function create()
    {
        return view('associacao.document_types.create_edit');
    }

    public function store(DocumentTypeRequest $request)
    {
        $data = $request->validated();
        $data['association_id'] = auth()->user()->association_id;
        DocumentType::create($data);

        return redirect()->route('associacao.document-types.index')->with('success', 'Tipo de documento criado com sucesso!');
    }

    public function edit(DocumentType $documentType)
    {
        abort_if($documentType->association_id !== auth()->user()->association_id, 403);

        return view('associacao.document_types.create_edit', compact('documentType'));
    }

    public function update(DocumentTypeRequest $request, DocumentType $documentType)
    {
        abort_if($documentType->association_id !== auth()->user()->association_id, 403);
        $documentType->update($request->validated());

        return redirect()->route('associacao.document-types.index')->with('success', 'Tipo de documento atualizado com sucesso!');
    }

    public function destroy(DocumentType $documentType)
    {
        abort_if($documentType->association_id !== auth()->user()->association_id, 403);
        $documentType->delete();

        return redirect()->back()->with('success', 'Tipo de documento excluído com sucesso!');
    }
}
