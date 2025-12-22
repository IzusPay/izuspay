<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Removido: use Illuminate\Validation\ValidationException; (não é mais necessário)

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Cria uma nova transação de pagamento via API.
     */
    public function create(Request $request): JsonResponse
    {
        // 1. O usuário autenticado é o seu CLIENTE (o dono do token).
        $apiUser = Auth::user();

        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'customer' => 'required|array',
            'customer.name' => 'required|string|max:255',
            'customer.email' => 'required|email|max:255',
            'customer.phone' => 'required|string|max:20',
            'customer.document' => 'required|string|max:20',
            'method' => 'sometimes|in:PIX,pix',
            'items' => 'sometimes|array',
            'metadata' => 'sometimes|array',
        ]);

        try {
            $paymentData = $this->paymentService->createTransactionFromApi($apiUser, $validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Transação iniciada com sucesso!',
                'transaction_id' => $paymentData['transaction_hash'],
                'pix_copy_paste' => $paymentData['pix_qr_code'] ?? null,
                'total_price' => (float) $validatedData['amount'],
            ]);

        } catch (\Exception $e) {
            Log::error("Falha ao criar transação via API para o usuário {$apiUser->id}: ".$e->getMessage(), [
                'trace' => $e->getTraceAsString(), // Adiciona o stack trace ao log para facilitar a depuração
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ocorreu um erro interno ao processar sua solicitação.', // Mensagem genérica para o usuário
            ], 500);
        }
    }

    /**
     * VERSÃO SIMPLIFICADA: Apenas consulta e retorna o status de uma transação.
     *
     * @param  string  $transactionId  O ID da transação retornado no momento da criação.
     */
    public function show(string $transactionId): JsonResponse
    {
        // 1. O usuário autenticado é o seu CLIENTE (o dono do token).
        $apiUser = Auth::user();

        // 2. Encontrar a venda e verificar se pertence ao cliente autenticado.
        //    Isso impede que um cliente veja as vendas de outro.
        $sale = Sale::where('transaction_hash', $transactionId)
            ->with(['product:id,name', 'user:id,name,email']) // Otimiza a consulta, pegando só o que precisa
            ->first();

        if (! $sale || $sale->association_id !== $apiUser->association_id) {
            return response()->json([
                'success' => false,
                'message' => 'Transação não encontrada ou não pertence a você.',
            ], 404);
        }

        // 3. Formatar e retornar a resposta com os dados do nosso banco.
        return response()->json([
            'success' => true,
            'transaction_id' => $sale->transaction_hash,
            'status' => $sale->status, // 'awaiting_payment', 'paid', 'expired', etc.
            'created_at' => $sale->created_at->toIso8601String(),
            'updated_at' => $sale->updated_at->toIso8601String(), // Informa quando foi a última atualização
            'product' => $sale->product ? ['name' => $sale->product->name] : null,
            'customer' => [
                'name' => $sale->user->name,
                'email' => $sale->user->email,
            ],
            'total_price' => $sale->total_price,
        ]);
    }
}
