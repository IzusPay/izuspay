<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use App\Models\ProductFavoriteModel;
use App\Models\ProductModel;
use App\Models\StoreModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = CategoryModel::all(); // Pega todas as categorias

        $stores = StoreModel::all();

        // Filtra os produtos por categoria e/ou nome
        $products = ProductModel::query()
            ->when($request->category, function ($query) use ($request) {
                return $query->where('category', $request->category);
            })
            ->when($request->search, function ($query) use ($request) {
                return $query->where('name', 'like', '%'.$request->search.'%');
            })
            ->when($request->store, function ($query) use ($request) {
                return $query->where('id_store', $request->store);
            })
            ->paginate(10); // Paginação com 12 produtos por página

        return view('products.index', compact('products', 'categories', 'stores'));
    }

    public function detail($id)
    {

        $product = ProductModel::with(['store', 'categoryR'])->findOrFail($id);

        return view('products.detail', compact('product'));
    }

    // No controlador ProductController.php
    public function toggleFavorite($id)
    {
        $user = Auth::user();  // Obtém o usuário autenticado

        // Verifica se o produto já está nos favoritos
        $favorite = ProductFavoriteModel::where('id_user', $user->id)
            ->where('id_product', $id)
            ->first();

        if ($favorite) {
            // Se o produto já for favorito, exclui o registro
            $favorite->delete();

            return back()->with('message', 'Produto removido dos favoritos.');
        } else {
            // Caso contrário, cria um novo registro de favorito
            ProductFavoriteModel::create([
                'id_user' => $user->id,
                'id_product' => $id,
            ]);

            return back()->with('message', 'Produto adicionado aos favoritos.');
        }
    }
}
