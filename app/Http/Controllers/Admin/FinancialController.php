<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Withdrawal; // Assumindo que você tenha um model para movimentações
use App\Services\FinancialServices;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; // Para futuras chamadas de API
// Para transações de banco de dados
use Illuminate\Support\Facades\Log;

class FinancialController extends Controller
{
    protected $financialService;

    public function __construct(FinancialServices $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(Request $request)
    {
        // --- Abas 1 e 2 (Saques) ---
        $pendingWithdrawals = Withdrawal::with('wallet.association', 'bankAccount')->where('status', 'pending')->latest()->get();

        $processedQuery = Withdrawal::with('wallet.association', 'bankAccount')->whereIn('status', ['completed', 'failed', 'processing']);
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $processedQuery->whereBetween('updated_at', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $processedQuery->whereHas('wallet.association', function ($query) use ($searchTerm) {
                $query->where('nome', 'like', "%{$searchTerm}%");
            });
        }
        $processedWithdrawals = $processedQuery->latest()->paginate(10)->withQueryString();

        // --- Aba 3: Movimentação do Saldo ---

        // 1. Define o período a partir do request
        $movStartDate = Carbon::parse($request->input('mov_start_date', now()->startOfMonth()));
        $movEndDate = Carbon::parse($request->input('mov_end_date', now()->endOfMonth()));

        // 2. Usa o serviço injetado, passando as datas para os métodos
        $kpiData = $this->financialService->getKpiData($movStartDate, $movEndDate);
        $movements = $this->financialService->getMovements($movStartDate, $movEndDate);

        // 3. Envia para a view
        return view('admin.financial.index', compact(
            'pendingWithdrawals',
            'processedWithdrawals',
            'kpiData',
            'movements',
            'movStartDate',
            'movEndDate'
        ));
    }

    /**
     * Aprova uma solicitação de saque e inicia o processo de pagamento.
     */
    /**
     * Aprova uma solicitação de saque e inicia o processo de pagamento.
     */
    public function approve(Withdrawal $withdrawal)
    {
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Este saque não está mais pendente de aprovação.');
        }

        if (empty($withdrawal->pix_key) && ! $withdrawal->bankAccount) {
            return back()->with('error', 'Dados de destino ausentes. Informe uma chave PIX.');
        }

        $withdrawal->update(['status' => 'processing']);

        try {
            // LÓGICA DA API DE PIX OUT (COMO NO EXEMPLO ANTERIOR)
            // $response = Http::withToken(...)->post(...);
            // if ($response->successful()) { ... }

            // **CÓDIGO DE SIMULAÇÃO (REMOVER EM PRODUÇÃO)**
            $withdrawal->update(['status' => 'completed']);

            return back()->with('success', "Saque #{$withdrawal->id} aprovado e enviado com sucesso (Simulação).");

        } catch (\Exception $e) {
            $withdrawal->update(['status' => 'failed']);
            // Estornar valor
            $this->refundWallet($withdrawal, 'Erro de API');
            Log::error("Falha na aprovação do saque #{$withdrawal->id}: ".$e->getMessage());

            return back()->with('error', 'Ocorreu um erro inesperado ao tentar processar o saque.');
        }
    }

    /**
     * Reprova uma solicitação de saque com um motivo.
     */
    public function reject(Request $request, Withdrawal $withdrawal)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
            'rejection_details' => 'nullable|string|max:1000',
        ]);

        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Este saque não está mais pendente de aprovação.');
        }

        $reason = $request->rejection_reason;
        if ($reason === 'other' && $request->filled('rejection_details')) {
            $reason = $request->rejection_details;
        }

        // 1. Atualiza o saque com o status de falha e o motivo
        $withdrawal->update([
            'status' => 'failed',
            'notes' => $reason, // Salva o motivo no banco de dados
        ]);

        // 2. Estorna o valor para a carteira do vendedor
        $this->refundWallet($withdrawal, $reason);

        return back()->with('success', "Saque #{$withdrawal->id} reprovado com sucesso. O saldo foi estornado.");
    }

    /**
     * Função auxiliar para estornar o saldo na carteira.
     */
    private function refundWallet(Withdrawal $withdrawal, string $reason)
    {
        $wallet = $withdrawal->wallet;
        $amountToRefund = $withdrawal->amount + 5.00;

        $wallet->balance += $amountToRefund;
        $wallet->save();

        // Opcional: Registrar uma transação de estorno para o extrato do vendedor
        // Transaction::create([...]);
    }
}
