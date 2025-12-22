<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    /**
     * Exibe a lista de banners da associação.
     */
    public function index()
    {
        $banners = Banner::where('association_id', auth()->user()->association_id)
            ->paginate(10);

        return view('associacao.banners.index', compact('banners'));
    }

    /**
     * Exibe o formulário de criação.
     */
    public function create()
    {
        return view('associacao.banners.create_edit');
    }

    /**
     * Armazena um novo banner.
     */
    public function store(Request $request)
    {
        $request['association_id'] = auth()->user()->association_id;

        Banner::create($request->all());

        return redirect()->route('associacao.banners.index')
            ->with('success', 'Banner criado com sucesso!');
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit(Banner $banner)
    {
        if ($banner->association_id !== auth()->user()->association_id) {
            abort(403, 'Acesso negado.');
        }

        return view('associacao.banners.create_edit', compact('banner'));
    }

    /**
     * Atualiza um banner.
     */
    public function update(Request $request, Banner $banner)
    {
        if ($banner->association_id !== auth()->user()->association_id) {
            abort(403, 'Acesso negado.');
        }

        $banner->update($request->all());

        return redirect()->route('associacao.banners.index')
            ->with('success', 'Banner atualizado com sucesso!');
    }

    /**
     * Exclui um banner.
     */
    public function destroy(Banner $banner)
    {
        if ($banner->association_id !== auth()->user()->association_id) {
            abort(403, 'Acesso negado.');
        }

        $banner->delete();

        return redirect()->route('associacao.banners.index')
            ->with('success', 'Banner excluído com sucesso!');
    }
}
