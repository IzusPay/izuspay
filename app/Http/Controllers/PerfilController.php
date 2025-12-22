<?php

namespace App\Http\Controllers;

use App\Models\PerfilModel;
use Illuminate\Http\Request;

class PerfilController extends Controller
{
    public function index()
    {
        $perfis = PerfilModel::all();

        return view('perfis.index', compact('perfis'));
    }

    public function create()
    {
        return view('perfis.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        PerfilModel::create($data);

        return redirect()->route('perfis.index')->with('success', 'Perfil criado com sucesso!');
    }

    public function edit(PerfilModel $perfil)
    {
        return view('perfis.edit', compact('perfil'));
    }

    public function update(Request $request, PerfilModel $perfil)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $perfil->update($data);

        return redirect()->route('perfis.index')->with('success', 'Perfil atualizado com sucesso!');
    }

    public function destroy(PerfilModel $perfil)
    {
        $perfil->delete();

        return redirect()->route('perfis.index')->with('success', 'Perfil excluído com sucesso!');
    }
}
