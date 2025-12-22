<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\Fee;
use App\Models\Gateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssociationController extends Controller
{
    public function index(Request $request)
    {
        $query = Association::with(['users', 'plans', 'products', 'wallet', 'creatorProfile', 'sales', 'withdrawals', 'fees']);

        // Filtro por período
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Filtro por busca (nome, email, documento)
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nome', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%")
                    ->orWhere('documento', 'like', "%{$request->search}%");
            });
        }

        // Filtro por status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $associations = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.associations.index', compact('associations'));
    }

    public function show(Association $association)
    {
        // Carrega todos os relacionamentos necessários para a tela de detalhes
        $association->load([
            'users',
            'creatorProfile',
            'plans',
            'products',
            'wallet',
            'bankAccounts',
            'associationDocuments' => function ($query) {
                $query->with('documentType', 'user')->latest();
            },
            'sales' => function ($query) {
                $query->with(['user', 'product', 'plan'])->latest()->take(10); // Pega as 10 últimas vendas
            },
            'withdrawals' => function ($query) {
                $query->with('bankAccount')->latest()->take(10); // Pega os 10 últimos saques
            },
        ]);

        $availableGateways = Gateway::where('is_active', true)->get();

        return view('admin.associations.show', compact('association', 'availableGateways'));
    }

    /**
     * Atualiza as configurações de gateway e taxas para uma associação específica.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettings(Request $request, Association $association)
    {
        $validated = $request->validate([
            'gateway_id' => 'nullable|exists:gateways,id',
            'fees' => 'required|array',
            'fees.*.percentage_fee' => 'required|numeric|min:0|max:100',
            'fees.*.fixed_fee' => 'required|numeric|min:0',
        ], [
            'fees.*.percentage_fee.required' => 'O campo de porcentagem da taxa é obrigatório.',
            'fees.*.fixed_fee.required' => 'O campo de taxa fixa é obrigatório.',
        ]);

        // Bloco 'try-catch' para garantir a integridade dos dados
        try {
            DB::beginTransaction();

            // 1. Atualiza o Gateway na Wallet
            // Usamos firstOrCreate para garantir que a wallet exista, mesmo para contas antigas
            $wallet = $association->wallet()->firstOrCreate(['association_id' => $association->id]);
            $wallet->gateway_id = $validated['gateway_id'];
            $wallet->save();

            // Não há edição de credenciais por associação; valores são definidos no Gateway pelo admin.

            // 2. Atualiza as Taxas
            foreach ($validated['fees'] as $feeId => $feeData) {
                $fee = Fee::find($feeId);
                // Dupla verificação: a taxa existe E pertence à associação correta?
                if ($fee && $fee->association_id == $association->id) {
                    $fee->update([
                        'percentage_fee' => $feeData['percentage_fee'],
                        'fixed_fee' => $feeData['fixed_fee'],
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Configurações da conta atualizadas com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Erro ao atualizar configurações da associação #{$association->id}: ".$e->getMessage());

            return back()->with('error', 'Ocorreu um erro ao salvar as configurações. Tente novamente.');
        }
    }
}
