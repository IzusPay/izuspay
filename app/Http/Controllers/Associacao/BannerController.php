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
        $banners = Banner::where('created_by_admin', true)
            ->paginate(10);

        return view('associacao.banners.index', compact('banners'));
    }

    /**
     * Exibe o formulário de criação.
     */
    public function create()
    {
        abort(403);
    }

    /**
     * Armazena um novo banner.
     */
    public function store(Request $request)
    {
        abort(403);
    }

    /**
     * Exibe o formulário de edição.
     */
    public function edit(Banner $banner)
    {
        abort(403);
    }

    /**
     * Atualiza um banner.
     */
    public function update(Request $request, Banner $banner)
    {
        abort(403);
    }

    /**
     * Exclui um banner.
     */
    public function destroy(Banner $banner)
    {
        abort(403);
    }
}
