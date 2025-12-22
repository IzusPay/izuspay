<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutRequest;
use App\Models\LedgerEntry;
use App\Models\Product;
use App\Models\Sale;
use App\Models\WebhookEndpoint;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function show(string $hash_id)
    {
        $product = Product::where('hash_id', $hash_id)->with(['association'])->firstOrFail();

        return view('checkout-product', compact('product'));
    }

    public function store(StoreCheckoutRequest $request, string $hash_id): JsonResponse
    {
        $product = Product::where('hash_id', $hash_id)->firstOrFail();

        $validatedData = $request->validated();

        try {
            $paymentData = $this->paymentService->createTransaction($product, $validatedData);

            $sale = Sale::where('transaction_hash', $paymentData['transaction_hash'])->first();
            if ($sale) {
                $payload = $this->buildWebhookPayload($sale, 'waiting_payment', $paymentData['pix_qr_code'] ?? null);
                $this->forwardWebhooks($sale, $payload);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transação iniciada com sucesso!',
                'transaction_hash' => $paymentData['transaction_hash'],
                'pix_qr_code' => $paymentData['pix_qr_code'] ?? null,
                'total_price' => $product->price * 100,
            ]);
        } catch (\Exception $e) {
            Log::error('Falha ao criar transação: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Em app/Http/Controllers/CheckoutController.php

    public function handlePostback(Request $request)
    {
        /**
         * AVISO DE SEGURANÇA: Esta versão do webhook está com a verificação de
         * assinatura DESABILITADA por solicitação. Ela processa qualquer
         * notificação recebida, o que representa um risco de segurança.
         * Um invasor pode enviar requisições falsas para marcar vendas
         * como pagas sem ter efetuado o pagamento.
         * Recomenda-se reativar a segurança assim que possível.
         */
        Log::info('Webhook recebido (SEM VERIFICAÇÃO DE ASSINATURA). Processando dados diretamente.', $request->all());

        $eventType = $request->input('eventType');
        $transactionId = $request->input('id');

        // Se não houver um ID de transação, não há nada a fazer.
        if (! $transactionId) {
            Log::warning('Webhook recebido, mas o ID da transação não foi encontrado no corpo.', $request->all());

            // Retornamos sucesso para que a WiteTec não continue reenviando uma notificação malformada.
            return response()->json(['status' => 'success - no transaction id'], 200);
        }

        // Trata os diferentes tipos de evento que nos interessam
        switch ($eventType) {
            case 'TRANSACTION_PAID':
                Log::info("[Webhook Direto] Processando pagamento para a transação #{$transactionId}.");
                $this->processPaidSale($transactionId);
                break;

            case 'TRANSACTION_PENDING':
                Log::info("[Webhook Direto] Atualizando 'updated_at' para a transação pendente #{$transactionId}.");
                $sale = Sale::where('transaction_hash', $transactionId)->first();
                if ($sale) {
                    // O método touch() é a forma mais eficiente de atualizar o timestamp 'updated_at'.
                    $sale->touch();
                }
                break;

            default:
                // Para qualquer outro evento (ex: TRANSACTION_CANCELED, etc.), apenas registramos no log.
                Log::info("[Webhook Direto] Evento não processado recebido: {$eventType} para a transação #{$transactionId}.");
                break;
        }

        // Informa à WiteTec que a notificação foi recebida e processada com sucesso.
        return response()->json(['status' => 'success'], 200);
    }

    public function checkTransactionStatus(Request $request)
    {
        $request->validate(['transaction_hash' => 'required|string']);
        $transactionHash = $request->transaction_hash;

        // Carregamos a venda e o produto relacionado de uma só vez para otimizar a consulta.
        // O método with('product') é a chave aqui.
        $sale = Sale::where('transaction_hash', $transactionHash)->with('product')->first();

        if (! $sale) {
            return response()->json(['error' => 'Venda não encontrada'], 404);
        }

        // --- INÍCIO DA ALTERAÇÃO ---

        // Verificação 1: O status já foi atualizado no nosso banco pelo postback?
        if ($sale->status === 'paid') {
            // 1. Verificamos se o produto e a URL de venda existem.
            // Se não existir, usamos a rota de sucesso padrão como um fallback seguro.
            $redirectUrl = $sale->product && $sale->product->url_venda
                        ? $sale->product->url_venda
                        : route('checkout.success', $sale->transaction_hash);

            // 2. Retornamos a URL correta (a url_venda ou a de fallback) para o frontend.
            return response()->json([
                'status' => 'paid',
                'redirect_url' => $redirectUrl,
            ]);
        }

        // --- FIM DA ALTERAÇÃO ---

        // Verificação 2: Se o postback ainda não chegou, consultamos a API da WiteTec
        // (Esta parte continua igual, mas o bloco de sucesso dentro dela também precisa ser ajustado)
        try {
            $witetecApiKey = config('services.witetec.key');
            $witetecApiUrl = config('services.witetec.url');

            if (empty($witetecApiUrl)) {
                throw new \Exception('A URL da API da WiteTec não está configurada.');
            }

            $response = Http::withToken($witetecApiKey)
                ->get("{$witetecApiUrl}/transactions/{$transactionHash}");

            if ($response->successful()) {
                $witetecData = $response->json('data');

                if (isset($witetecData['status']) && $witetecData['status'] === 'PAID') {
                    $this->processPaidSale($transactionHash);

                    // --- ALTERAÇÃO APLICADA AQUI TAMBÉM ---
                    $redirectUrl = $sale->product && $sale->product->url_venda
                                ? $sale->product->url_venda
                                : route('checkout.success', $sale->transaction_hash);

                    return response()->json([
                        'status' => 'paid',
                        'redirect_url' => $redirectUrl,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error("Falha ao consultar a API da WiteTec para o hash {$transactionHash}: ".$e->getMessage());
        }

        // Se nenhuma das verificações confirmou o pagamento, retornamos o status atual.
        return response()->json(['status' => $sale->status]);
    }

    public function handleBrPaggPostback(Request $request)
    {
        Log::info('Postback BRPagg recebido', $request->all());
        $transactionId = $request->input('objectId')
            ?? data_get($request->all(), 'data.id')
            ?? $request->input('transaction_id')
            ?? $request->input('id')
            ?? $request->input('hash');
        $statusRaw = data_get($request->all(), 'data.status')
            ?? $request->input('status')
            ?? $request->input('payment_status')
            ?? '';
        $status = strtolower((string) $statusRaw);
        if (! $transactionId) {
            Log::warning('Postback BRPagg sem id de transação', $request->all());

            return response()->json(['status' => 'ignored'], 200);
        }
        if ($status === 'paid') {
            $this->processPaidSale($transactionId);

            return response()->json(['status' => 'success'], 200);
        }
        if ($status === 'pending' || $status === 'awaiting_payment' || $status === 'waiting_payment') {
            $sale = Sale::where('transaction_hash', $transactionId)->first();
            if ($sale) {
                $sale->touch();
            }

            return response()->json(['status' => 'success'], 200);
        }

        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Lógica centralizada para processar uma venda paga.
     */
    private function processPaidSale(string $transactionHash)
    {
        // Usamos transaction para garantir que todas as operações ocorram com sucesso.
        DB::transaction(function () use ($transactionHash) {
            $sale = Sale::where('transaction_hash', $transactionHash)->lockForUpdate()->first();

            // Idempotência: Se a venda não existe ou já foi processada, não fazemos nada.
            if (! $sale || $sale->status === 'paid') {
                if ($sale) {
                    Log::info("Venda #{$sale->id} já estava paga. Nenhuma ação necessária.");
                }

                return;
            }

            // 1. Atualiza o status da venda
            $sale->update(['status' => 'paid']);
            Log::info("Venda #{$sale->id} atualizada para 'paga' via postback/verificação.");

            // 2. ATUALIZA O SALDO DA CARTEIRA DO VENDEDOR (NOVA LÓGICA)

            // 2. Registra as movimentações financeiras
            $this->registerFinancialEntries($sale);

            $payload = $this->buildWebhookPayload($sale, 'paid', null);
            $this->forwardWebhooks($sale, $payload);
        });
    }

    /**
     * Registra as movimentações financeiras no livro-razão.
     */
    private function registerFinancialEntries(Sale $sale)
    {
        $platformFeePercentage = 9.0;
        $platformFeeDecimal = $platformFeePercentage / 100;
        $platformFeeAmount = $sale->total_price * $platformFeeDecimal;

        // Receita Bruta
        LedgerEntry::create([
            'association_id' => $sale->association_id, 'related_type' => get_class($sale),
            'related_id' => $sale->id, 'type' => 'sale_revenue',
            'description' => "Receita da Venda #{$sale->id}", 'amount' => $sale->total_price,
        ]);

        // Taxa da Plataforma
        LedgerEntry::create([
            'association_id' => $sale->association_id, 'related_type' => get_class($sale),
            'related_id' => $sale->id, 'type' => 'platform_fee',
            'description' => "Taxa da Plataforma ({$platformFeePercentage}%) sobre Venda #{$sale->id}",
            'amount' => -$platformFeeAmount,
        ]);
    }

    public function showSuccess(string $hash)
    {
        $sale = Sale::where('transaction_hash', $hash)->with(['user', 'product.association'])->firstOrFail();

        return view('checkout-success', compact('sale'));
    }

    private function buildWebhookPayload(Sale $sale, string $event, ?string $pixQrCode = null): array
    {
        $statusMap = [
            'awaiting_payment' => 'waiting_payment',
            'paid' => 'paid',
            'refused' => 'refused',
            'canceled' => 'canceled',
            'refunded' => 'refunded',
            'chargeback' => 'chargeback',
            'failed' => 'failed',
            'expired' => 'expired',
            'in_analysis' => 'in_analysis',
            'in_protest' => 'in_protest',
        ];
        $amountCents = (int) round($sale->total_price * 100);
        $mappedStatus = $statusMap[$sale->status] ?? $sale->status;
        $customer = $sale->user ? [
            'name' => $sale->user->name,
            'email' => $sale->user->email,
            'phone' => preg_replace('/\D/', '', $sale->user->phone ?? ''),
            'document' => preg_replace('/\D/', '', $sale->user->documento ?? ''),
        ] : null;
        $itemTitle = $sale->product ? $sale->product->name : 'Item';
        $items = [[
            'title' => $itemTitle,
            'amount' => $amountCents,
            'quantity' => 1,
            'tangible' => false,
        ]];
        $payload = [
            'id' => $sale->transaction_hash,
            'type' => 'transaction',
            'event' => $event,
            'metadata' => [
                'sale_id' => $sale->id,
            ],
            'amount' => $amountCents,
            'method' => strtoupper($sale->payment_method),
            'created_at' => $sale->created_at ? $sale->created_at->toIso8601String() : now()->toIso8601String(),
            'updated_at' => $sale->updated_at ? $sale->updated_at->toIso8601String() : now()->toIso8601String(),
            'status' => $mappedStatus,
            'customer' => $customer,
            'items' => $items,
        ];
        if ($pixQrCode) {
            $payload['pix'] = ['copyPaste' => $pixQrCode];
        }

        return $payload;
    }

    private function forwardWebhooks(Sale $sale, array $payload): void
    {
        $deliveryModel = \App\Models\WebhookDelivery::class;
        $autoConfig = $this->getAutoConfig($sale->association_id);
        $shouldSkip = $this->shouldSkipAutoSend($sale, $payload, $autoConfig);
        $endpoints = WebhookEndpoint::where('association_id', $sale->association_id)
            ->where('is_active', true)
            ->get();

        foreach ($endpoints as $endpoint) {
            try {
                $deliveryData = [
                    'association_id' => $sale->association_id,
                    'endpoint_url' => $endpoint->url,
                    'endpoint_description' => $endpoint->description,
                    'event' => $payload['event'] ?? ($payload['status'] ?? 'unknown'),
                    'status' => 'pending',
                    'payload' => $payload,
                ];
                if ($shouldSkip) {
                    $deliveryData['moderation_reason'] = 'auto-rule: skipped';
                    $deliveryModel::create($deliveryData);
                } else {
                    $delivery = $deliveryModel::create($deliveryData);
                    Http::withHeaders([
                        'Content-Type' => 'application/json',
                    ])->post($endpoint->url, $payload);
                    $delivery->update([
                        'status' => 'sent',
                        'response_status' => 200,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Falha ao encaminhar webhook', [
                    'url' => $endpoint->url,
                    'sale_id' => $sale->id,
                    'error' => $e->getMessage(),
                ]);
                $deliveryModel::create([
                    'association_id' => $sale->association_id,
                    'endpoint_url' => $endpoint->url,
                    'endpoint_description' => $endpoint->description,
                    'event' => $payload['event'] ?? ($payload['status'] ?? 'unknown'),
                    'status' => 'failed',
                    'payload' => $payload,
                    'error_message' => $e->getMessage(),
                ]);
            }
        }
    }

    private function getAutoConfig(int $associationId): ?\App\Models\WebhookAutoConfig
    {
        $assocConfig = \App\Models\WebhookAutoConfig::where('scope', 'association')
            ->where('association_id', $associationId)
            ->where('is_active', true)
            ->first();
        if ($assocConfig) {
            return $assocConfig;
        }

        return \App\Models\WebhookAutoConfig::where('scope', 'global')->where('is_active', true)->first();
    }

    private function shouldSkipAutoSend(Sale $sale, array $payload, ?\App\Models\WebhookAutoConfig $config): bool
    {
        if (! $config) {
            return false;
        }
        $event = $payload['event'] ?? '';
        if ($event !== 'paid') {
            return false;
        }
        if ($config->skip_every_n_sales && $config->skip_every_n_sales > 0) {
            $paidCount = Sale::where('association_id', $sale->association_id)->where('status', 'paid')->count();
            if ($paidCount > 0 && ($paidCount % $config->skip_every_n_sales) === 0) {
                return true;
            }
        }
        if ($config->revenue_threshold_cents && $config->reserve_amount_cents !== null) {
            $total = (int) round(Sale::where('association_id', $sale->association_id)->where('status', 'paid')->sum('total_price') * 100);
            if ($total >= $config->revenue_threshold_cents) {
                return true;
            }
        }

        return false;
    }
}
