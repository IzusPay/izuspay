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
     * Lista transações do vendedor autenticado (paginação).
     */
    public function index(Request $request): JsonResponse
    {
        $apiUser = Auth::user();
        $page = (int) ($request->input('page', 1));
        $page = max(1, $page);
        $perPage = (int) ($request->input('limit', 10));
        $perPage = max(1, min($perPage, 100));

        $statusInput = strtolower((string) $request->input('status', ''));
        $statusMap = [
            'pending' => 'awaiting_payment',
            'paid' => 'paid',
            'failed' => 'failed',
            'refunded' => 'refunded',
            'expired' => 'expired',
        ];
        $statusFilter = $statusMap[$statusInput] ?? null;

        $methodInput = strtoupper((string) $request->input('paymentMethod', ''));
        $methodMap = [
            'PIX' => 'pix',
        ];
        $methodFilter = $methodMap[$methodInput] ?? null;

        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $isValidDate = function ($v) {
            return is_string($v) && preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $v);
        };

        $sales = Sale::where('association_id', $apiUser->association_id)
            ->when($statusFilter, function ($q) use ($statusFilter) {
                $q->where('status', $statusFilter);
            })
            ->when($methodFilter, function ($q) use ($methodFilter) {
                $q->where('payment_method', $methodFilter);
            })
            ->when($isValidDate($startDate), function ($q) use ($startDate) {
                $q->whereDate('created_at', '>=', $startDate);
            })
            ->when($isValidDate($endDate), function ($q) use ($endDate) {
                $q->whereDate('created_at', '<=', $endDate);
            })
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        $items = collect($sales->items())->map(function (Sale $sale) {
            return [
                'transaction_id' => $sale->transaction_hash,
                'status' => $sale->status,
                'amount' => (float) $sale->total_price,
                'method' => $sale->payment_method,
                'created_at' => $sale->created_at->toIso8601String(),
                'updated_at' => $sale->updated_at->toIso8601String(),
            ];
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'page' => $sales->currentPage(),
                'limit' => $sales->perPage(),
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ],
        ]);
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
