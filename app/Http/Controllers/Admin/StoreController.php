<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreModel;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function index()
    {
        $stores = StoreModel::all();

        return view('admin.stores.index', compact('stores'));
    }

    public function create()
    {
        return view('admin.stores.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        StoreModel::create($data);

        return redirect()->route('admin.stores.index')->with('success', 'Loja criada com sucesso!');
    }

    public function edit(StoreModel $store)
    {
        return view('admin.stores.edit', compact('category'));
    }

    public function update(Request $request, StoreModel $store)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $store->update($data);

        return redirect()->route('admin.stores.index')->with('success', 'Loja atualizada com sucesso!');
    }

    public function destroy(StoreModel $store)
    {
        $store->delete();

        return redirect()->route('admin.stores.index')->with('success', 'Loja excluída com sucesso!');
    }
}
