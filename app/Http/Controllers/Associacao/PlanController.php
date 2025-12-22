<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlanRequest;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Exibe a lista de planos da associação.
     */
    public function index()
    {
        // Filtra os planos para mostrar apenas os da associação do usuário logado
        $plans = Plan::where('association_id', auth()->user()->association_id)
            ->with('products')
            ->paginate(10);

        return view('associacao.plans.index', compact('plans'));
    }

    /**
     * Exibe o formulário de criação de um novo plano.
     */
    public function create()
    {
        // Filtra os produtos para mostrar apenas os da associação do usuário logado
        $products = Product::where('association_id', auth()->user()->association_id)
            ->where('is_active', true)
            ->get();

        return view('associacao.plans.create_edit', compact('products'));
    }

    /**
     * Salva um novo plano no banco de dados.
     */
    public function store(Request $request) // Use o PlanRequest
    {

        $plan = Plan::create([
            'association_id' => auth()->user()->association_id,
            'name' => $request['name'],
            'client_type' => $request['client_type'], // Adicione esta linha
            'description' => $request['description'],
            'recurrence' => $request['recurrence'],
            'image' => $request->file('image')->store('plans', 'public'),
            'is_active' => $request['is_active'] ?? false,
            'offer_hash' => 'jft8thz09y',
            'product_hash' => 'prvz27ifhw',

        ]);

        $plan->products()->sync($request['product_ids']);

        return redirect()->route('associacao.plans.index')
            ->with('success', 'Plano criado com sucesso!');
    }

    /**
     * Exibe o formulário de edição de um plano.
     */
    public function edit(Plan $plan)
    {
        // Verificação de segurança
        if ($plan->association_id !== auth()->user()->association_id) {
            abort(403, 'Acesso negado.');
        }

        // Filtra os produtos para edição
        $products = Product::where('association_id', auth()->user()->association_id)
            ->where('is_active', true)
            ->get();
        $plan->load('products');

        return view('associacao.plans.create_edit', compact('plan', 'products'));
    }

    public function update(Request $request, Plan $plan)
    {
        // Verificação de segurança para garantir que o usuário pertence à associação do plano
        if ($plan->association_id !== auth()->user()->association_id) {
            abort(403, 'Acesso negado.');
        }

        // Inicializa um array para armazenar os dados que serão atualizados
        $updateData = [
            'name' => $request->name,
            'client_type' => $request->client_type,
            'description' => $request->description,
            'recurrence' => $request->recurrence,
            'is_active' => $request->is_active ?? false,
            'offer_hash' => 'jft8thz09y',
            'product_hash' => 'prvz27ifhw',
        ];

        // Lógica para o upload da nova imagem
        if ($request->hasFile('image')) {
            // Se houver uma imagem antiga, a remove do storage
            if ($plan->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($plan->image);
            }
            // Armazena a nova imagem e adiciona o caminho ao array de dados
            $updateData['image'] = $request->file('image')->store('plans', 'public');
        }

        // Atualiza os dados do plano com o array preparado
        $plan->update($updateData);

        // Sincroniza os produtos associados ao plano
        $plan->products()->sync($request->product_ids);

        return redirect()->route('associacao.plans.index')
            ->with('success', 'Plano atualizado com sucesso!');
    }

    /**
     * Exclui um plano do banco de dados.
     */
    public function destroy(Plan $plan)
    {
        // Verificação de segurança
        if ($plan->association_id !== auth()->user()->association_id) {
            abort(403, 'Acesso negado.');
        }

        $plan->delete();

        return redirect()->route('associacao.plans.index')
            ->with('success', 'Plano excluído com sucesso!');
    }
}
