<?php

namespace App\Http\Controllers\associacao;

use App\Enums\CategoriaProduto;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // Exibir a lista de produtos
    public function index(Request $request)
    {
        $associationId = Auth::user()->association_id;
        $query = Product::where('association_id', $associationId);

        if ($search = $request->get('q')) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($status = $request->get('status')) {
            if ($status === 'ativo') {
                $query->where('is_active', 1);
            } elseif ($status === 'inativo') {
                $query->where('is_active', 0);
            }
        }

        if ($categoria = $request->get('categoria')) {
            $query->where('categoria_produto', $categoria);
        }

        if ($from = $request->get('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($request->get('export') === 'csv') {
            $filename = 'links_pagamento_'.now()->format('Ymd_His').'.csv';
            $rows = $query->orderBy('created_at', 'desc')->get(['id', 'name', 'price', 'is_active', 'categoria_produto', 'created_at']);

            return response()->streamDownload(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['ID', 'Nome', 'Preço', 'Status', 'Categoria', 'Criado em'], ';');
                foreach ($rows as $p) {
                    fputcsv($out, [
                        $p->id,
                        $p->name,
                        number_format($p->price, 2, ',', '.'),
                        $p->is_active ? 'Ativo' : 'Inativo',
                        CategoriaProduto::all()[$p->categoria_produto] ?? 'N/A',
                        $p->created_at->format('d/m/Y H:i'),
                    ], ';');
                }
                fclose($out);
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
            ]);
        }

        $products = $query->paginate(12);

        $totalLinks = Product::where('association_id', $associationId)->count();
        $activeLinks = Product::where('association_id', $associationId)->where('is_active', 1)->count();
        $paidSales = Sale::where('association_id', $associationId)->where('status', 'paid')->count();
        $totalSales = Sale::where('association_id', $associationId)->count();
        $totalRevenue = Sale::where('association_id', $associationId)->where('status', 'paid')->sum('total_price');
        $avgTicket = $paidSales > 0 ? $totalRevenue / $paidSales : 0;
        $conversionRate = $totalSales > 0 ? round(($paidSales / $totalSales) * 100, 2) : 0;

        $traffic = Product::where('association_id', $associationId)->sum('traffic');
        $metrics = [
            'total_links' => $totalLinks,
            'active_links' => $activeLinks,
            'total_revenue' => $totalRevenue,
            'avg_ticket' => $avgTicket,
            'traffic' => $traffic,
            'conversion' => $conversionRate,
        ];

        $categorias = CategoriaProduto::all();

        return view('associacao.products.index', compact('products', 'metrics', 'categorias'));
    }

    public function show($id)
    {
        $product = Product::where('id', $id)
            ->where('association_id', Auth::user()->association_id)
            ->firstOrFail();

        return view('associacao.products.show', compact('product'));
    }

    // Exibir o formulário de criação de produto
    public function create()
    {
        $categorias = CategoriaProduto::all();

        return view('associacao.products.create_edit', compact('categorias')); // Retorna para a view de criação de produto
    }

    public function store(Request $request)
    {
        $price = str_replace(',', '.', $request->input('price'));

        $data = [
            'association_id' => Auth::user()->association_id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $price,
            'sales_count' => $request->input('sales_count'),
            'is_active' => $request->input('is_active', 1),
            'tipo_produto' => $request->input('tipo_produto'),
            'entrega_produto' => $request->input('entrega_produto'),
            'categoria_produto' => $request->input('categoria_produto'),
            'url_venda' => $request->input('url_venda'),
            'nome_sac' => $request->input('nome_sac'),
            'email_sac' => $request->input('email_sac'),
            'offer_hash_goat' => 'jft8thz09y',
            'product_hash_goat' => 'prvz27ifhw',
        ];

        // Upload da imagem, se houver
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('associacao.products.index')->with('success', 'Produto criado com sucesso!');
    }

    // Exibir o formulário de edição do produto
    public function edit($id)
    {
        $product = Product::where('id', $id)->first();

        return view('associacao.products.create_edit', compact('product')); // Retorna para a view de edição do produto
    }

    // Atualizar as informações do produto no banco de dados
    public function update(Request $request, Product $product)
    {
        $price = str_replace(',', '.', $request->input('price'));
        $possible_profit = str_replace(',', '.', $request->input('possible_profit'));

        $updateData = [
            'association_id' => Auth::user()->association_id,
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'price' => $price,
            'sales_count' => $request->input('sales_count'),
            'is_active' => $request->input('is_active', 1),
            'tipo_produto' => $request->input('tipo_produto'),
            'entrega_produto' => $request->input('entrega_produto'),
            'categoria_produto' => $request->input('categoria_produto'),
            'url_venda' => $request->input('url_venda'),
            'nome_sac' => $request->input('nome_sac'),
            'email_sac' => $request->input('email_sac'),
            'offer_hash_goat' => 'jft8thz09y',
            'product_hash_goat' => 'prvz27ifhw',
        ];

        // Upload da imagem, se houver
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $updateData['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($updateData);

        return redirect()->route('associacao.products.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    // Excluir um produto
    public function destroy(Product $product)
    {
        $product->delete(); // Deleta o produto do banco de dados

        return redirect()->route('associacao.products.index')->with('success', 'Produto excluído com sucesso!'); // Redireciona para a lista de produtos
    }
}
