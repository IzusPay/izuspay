<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LedgerEntry;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserPerfilModel;
use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Importação do novo modelo de livro-razão

class GoatPaymentController extends Controller
{
    private $apiToken;

    private $apiUrl;

    public function __construct()
    {
        $this->apiToken = env('GOAT_API_TOKEN', 'POZyVZ3yHrnHXZ9RNaKcsRpS4wcjWf6A3StWKXz07HlGVXs0xz5aCBAgB4QC');
        $this->apiUrl = env('GOAT_API_URL', 'https://api.goatpayments.com.br/api/public/v1/transactions');
    }

    /**
     * Inicia uma nova transação Pix para um Plano.
     */
    public function createPixTransaction(Request $request)
    {
        $plan = Plan::find($request->plan_id);
        if (! $plan) {
            return response()->json(['error' => 'Plano não encontrado.'], 404);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->telefone,
                'tipo' => 'cliente',
                'password' => Hash::make($request->password),
                'association_id' => $plan->association_id,
                'documento' => $request->cpf,
                'status' => 'active',
            ]);

            UserPerfilModel::create(['user_id' => $user->id, 'perfil_id' => 3, 'is_atual' => 1, 'status' => 1]);

            $data = [
                'amount' => ((int) round($plan->getTotalPriceAttribute() * 100)),
                'offer_hash' => $plan->offer_hash,
                'payment_method' => 'pix',
                'customer' => [
                    'name' => $request->nome,
                    'email' => $request->email,
                    'phone_number' => $request->telefone,
                    'document' => $request->cpf,
                    'street_name' => 'Rua Exemplo', 'number' => '123', 'complement' => '', 'neighborhood' => 'Bairro Teste', 'city' => 'Cidade Exemplo', 'state' => 'SP', 'zip_code' => '00000000',
                ],
                'cart' => [[
                    'product_hash' => $plan->product_hash, 'title' => $plan->name, 'price' => ((int) round($plan->getTotalPriceAttribute() * 100)), 'quantity' => 1, 'operation_type' => 1, 'tangible' => true,
                ]],
                'tracking' => [
                    'utm_source' => $request->query('utm_source', ''), 'utm_medium' => $request->query('utm_medium', ''), 'utm_campaign' => $request->query('utm_campaign', ''), 'utm_term' => $request->query('utm_term', ''), 'utm_content' => $request->query('utm_content', ''),
                ],
                'installments' => 1, 'expire_in_days' => 1, 'postback_url' => route('api.goat.postback'), // Rota para o postback
            ];

            $response = Http::withHeaders(['Accept' => 'application/json', 'Content-Type' => 'application/json'])->post($this->apiUrl.'?api_token='.$this->apiToken, $data);
            $responseData = $response->json();

            if ($response->successful()) {
                Log::info('Transação Pix criada com sucesso na Goat Payments:', $responseData);
                Sale::create([
                    'user_id' => $user->id, 'plan_id' => $plan->id, 'transaction_hash' => $responseData['hash'], 'status' => 'awaiting_payment', 'total_price' => $plan->getTotalPriceAttribute(), 'payment_method' => 'pix', 'association_id' => $plan->association_id,
                ]);
                DB::commit();

                return response()->json($responseData);
            } else {
                DB::rollBack();
                Log::error('Erro ao criar transação Pix na Goat Payments:', ['status' => $response->status(), 'response' => $responseData]);

                return response()->json(['error' => 'Falha ao se comunicar com a API de pagamento.', 'details' => $responseData], $response->status());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exceção ao chamar API da Goat Payments ou salvar dados:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json(['error' => 'Erro interno ao processar a requisição.'], 500);
        }
    }

    /**
     * Inicia uma nova transação Pix para um Produto.
     */
    public function createProductPixTransaction(Request $request)
    {
        $product = Product::where('hash_id', $request->hash_id)->first();
        if (! $product) {
            return response()->json(['error' => 'Produto não encontrado.'], 404);
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name, 'email' => $request->email, 'phone' => $request->telefone, 'tipo' => 'cliente', 'password' => Hash::make($request->password), 'association_id' => $product->association_id, 'documento' => $request->cpf, 'status' => 'active',
            ]);
            UserPerfilModel::create(['user_id' => $user->id, 'perfil_id' => 3, 'is_atual' => 1, 'status' => 1]);

            $data = [
                'amount' => ((int) round($product->price * 100)), 'offer_hash' => $product->offer_hash_goat, 'payment_method' => 'pix',
                'customer' => [
                    'name' => $request->nome, 'email' => $request->email, 'phone_number' => $request->telefone, 'document' => $request->cpf, 'street_name' => 'Rua Exemplo', 'number' => '123', 'complement' => '', 'neighborhood' => 'Bairro Teste', 'city' => 'Cidade Exemplo', 'state' => 'SP', 'zip_code' => '00000000',
                ],
                'cart' => [[
                    'product_hash' => $product->product_hash_goat, 'title' => $product->name, 'price' => ((int) round($product->price * 100)), 'quantity' => 1, 'operation_type' => 1, 'tangible' => true,
                ]],
                'tracking' => [
                    'utm_source' => $request->query('utm_source', ''), 'utm_medium' => $request->query('utm_medium', ''), 'utm_campaign' => $request->query('utm_campaign', ''), 'utm_term' => $request->query('utm_term', ''), 'utm_content' => $request->query('utm_content', ''),
                ],
                'installments' => 1, 'expire_in_days' => 1, 'postback_url' => route('api.goat.postback'),
            ];

            $response = Http::withHeaders(['Accept' => 'application/json', 'Content-Type' => 'application/json'])->post($this->apiUrl.'?api_token='.$this->apiToken, $data);
            $responseData = $response->json();

            if ($response->successful()) {
                Sale::create([
                    'user_id' => $user->id, 'product_id' => $product->id, 'transaction_hash' => $responseData['hash'], 'status' => 'awaiting_payment', 'total_price' => $product->price, 'payment_method' => 'pix', 'association_id' => $product->association_id,
                ]);
                DB::commit();

                return response()->json($responseData);
            } else {
                DB::rollBack();
                Log::error('Erro ao criar transação Pix para produto:', ['status' => $response->status(), 'response' => $responseData]);

                return response()->json(['error' => 'Falha ao se comunicar com a API de pagamento.', 'details' => $responseData], $response->status());
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exceção ao processar produto:', ['message' => $e->getMessage()]);

            return response()->json(['error' => 'Erro interno ao processar a requisição.'], 500);
        }
    }

    /**
     * Verifica o status de uma transação.
     */
    /**
     * Verifica o status de uma transação Pix na Goat Payments.
     *
     * @param  Request  $request  Espera 'transaction_hash' na URL
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkTransactionStatus(Request $request)
    {
        $request->validate([
            'transaction_hash' => 'required|string',
        ]);

        $transactionHash = $request->transaction_hash;
        $url = $this->apiUrl.'/'.$transactionHash.'?api_token='.$this->apiToken;

        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
            ])->get($url);

            $responseData = $response->json();

            if ($response->successful()) {
                $sale = Sale::where('transaction_hash', $transactionHash)->first();

                if (! $sale) {
                    return response()->json(['error' => 'Venda não encontrada para este transaction_hash.'], 404);
                }

                // Se a venda já está paga no nosso banco, não fazemos nada para evitar duplicidade.
                if ($sale->status === 'paid') {
                    return response()->json(['status' => 'paid', 'message' => 'Venda já estava confirmada.']);
                }

                if (isset($responseData['payment_status'])) {

                    // ===================================================================
                    // LÓGICA DE PRODUÇÃO ATIVADA
                    // ===================================================================
                    // A linha que forçava o status foi removida.
                    // Agora, lemos o status real que a API da Goat Payments nos enviou.

                    $paymentStatus = $responseData['payment_status'];

                    // ===================================================================

                    // O fluxo continua, mas agora depende do status REAL.
                    // Este bloco só será executado se a Goat Payments confirmar "paid".
                    if ($paymentStatus === 'paid') {
                        DB::beginTransaction();
                        try {
                            $sale->status = 'paid';
                            $sale->save();
                            Log::info("Status da venda {$sale->id} atualizado para 'paid' via verificação de status.");

                            // Se for um plano, cria/atualiza a assinatura
                            if ($sale->plan_id) {
                                $this->createOrUpdateSubscription($sale);
                            }

                            // Registra as movimentações financeiras no livro-razão
                            $this->registerFinancialEntries($sale);

                            DB::commit();

                            return response()->json([
                                'status' => 'paid',
                                'message' => 'Pagamento confirmado e processado com sucesso.',
                            ]);

                        } catch (\Exception $e) {
                            DB::rollBack();
                            Log::error("Erro ao processar a venda #{$sale->id} após confirmação de pagamento: ".$e->getMessage());

                            return response()->json(['error' => 'Erro interno ao finalizar a venda.'], 500);
                        }
                    }
                }

                // Se a API retornar um status diferente de 'paid' (ex: 'awaiting_payment'),
                // apenas retornamos esse status para o front-end.
                return response()->json(['status' => $responseData['payment_status'] ?? 'waiting_payment']);

            } else {
                Log::error('Erro ao verificar status da transação:', [
                    'status' => $response->status(),
                    'response' => $responseData,
                ]);

                return response()->json([
                    'error' => 'Erro ao verificar o status do pagamento.',
                    'details' => $responseData,
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Exceção ao verificar status da transação:', ['message' => $e->getMessage()]);

            return response()->json(['error' => 'Erro interno ao verificar o status.'], 500);
        }
    }

    /**
     * Endpoint para receber postbacks da Goat Payments.
     */
    public function handlePostback(Request $request)
    {
        Log::info('Postback da Goat Payments recebido:', $request->all());
        $transactionHash = $request->input('transaction_hash');
        $paymentStatus = $request->input('payment_status');

        if (! $transactionHash || ! $paymentStatus) {
            Log::warning('Postback inválido: falta transaction_hash ou payment_status.', $request->all());

            return response('Bad Request: Missing parameters', 400);
        }

        try {
            DB::beginTransaction();
            $sale = Sale::where('transaction_hash', $transactionHash)->first();

            if ($sale) {
                if ($sale->status === 'paid') {
                    Log::info("Venda {$transactionHash} já estava como 'paid'. Postback ignorado.");
                    DB::commit();

                    return response('OK - Already processed', 200);
                }

                $sale->status = $paymentStatus;
                $sale->save();
                Log::info("Status da venda {$transactionHash} atualizado para {$paymentStatus}.");

                if ($paymentStatus === 'paid') {
                    if ($sale->plan_id) {
                        $this->createOrUpdateSubscription($sale);
                    }
                    $this->registerFinancialEntries($sale);

                    $amountCents = (int) round($sale->total_price * 100);
                    $payload = [
                        'id' => $sale->transaction_hash,
                        'type' => 'transaction',
                        'event' => 'paid',
                        'metadata' => [
                            'sale_id' => $sale->id,
                        ],
                        'amount' => $amountCents,
                        'method' => strtoupper($sale->payment_method),
                        'created_at' => $sale->created_at ? $sale->created_at->toIso8601String() : now()->toIso8601String(),
                        'updated_at' => $sale->updated_at ? $sale->updated_at->toIso8601String() : now()->toIso8601String(),
                        'status' => 'paid',
                        'customer' => $sale->user ? [
                            'name' => $sale->user->name,
                            'email' => $sale->user->email,
                            'phone' => preg_replace('/\D/', '', $sale->user->phone ?? ''),
                            'document' => preg_replace('/\D/', '', $sale->user->documento ?? ''),
                        ] : null,
                        'items' => [[
                            'title' => $sale->product ? $sale->product->name : 'Item',
                            'amount' => $amountCents,
                            'quantity' => 1,
                            'tangible' => false,
                        ]],
                    ];
                    $this->forwardWebhooks($sale, $payload);
                }

                DB::commit();

                return response('OK', 200);
            } else {
                DB::rollBack();
                Log::warning("Venda com hash {$transactionHash} não encontrada.");

                return response('Not Found', 404);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar postback:', ['transaction_hash' => $transactionHash, 'error' => $e->getMessage()]);

            return response('Internal Server Error', 500);
        }
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
                    'is_manual' => false,
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
                    'is_manual' => false,
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
        return false;
    }

    /**
     * Cria ou atualiza uma assinatura após o pagamento.
     */
    private function createOrUpdateSubscription(Sale $sale)
    {
        if ($sale->user && $sale->plan) {
            $subscription = Subscription::updateOrCreate(
                ['user_id' => $sale->user_id, 'plan_id' => $sale->plan_id],
                [
                    'association_id' => $sale->association_id,
                    'sale_id' => $sale->id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'renews_at' => now()->addMonths($sale->plan->duration_in_months ?? 1),
                    'external_transaction_id' => $sale->transaction_hash,
                ]
            );
            Log::info("Assinatura para o usuário {$sale->user_id} e plano {$sale->plan_id} criada/atualizada.");
        } else {
            Log::warning('Não foi possível criar/atualizar assinatura: venda, usuário ou plano ausente na venda.');
        }
    }

    /**
     * Registra as movimentações financeiras no livro-razão (Ledger) após um pagamento.
     */
    private function registerFinancialEntries(Sale $sale)
    {
        // ===================================================================
        // CORREÇÃO DO CÁLCULO DA TAXA
        // ===================================================================

        // Defina a taxa como um valor percentual (ex: 0.1 para 0.1%)
        $platformFeeAsPercentage = 9.0;

        // Converta a porcentagem para o formato decimal correto para cálculo
        $platformFeeAsDecimal = $platformFeeAsPercentage / 100; // 0.1 / 100 = 0.001

        $acquirerFixedCost = -1.0; // Custo fixo de R$1,00 por transação

        // 1. Entrada da Receita Bruta (sem alterações)
        LedgerEntry::create([
            'association_id' => $sale->association_id,
            'related_type' => get_class($sale),
            'related_id' => $sale->id,
            'type' => 'sale_revenue',
            'description' => "Receita da Venda #{$sale->id}",
            'amount' => $sale->total_price,
        ]);
        Log::info("Ledger: Registrada receita bruta de {$sale->total_price} para a venda #{$sale->id}.");

        // 2. Saída da Taxa da Plataforma (cálculo e descrição corrigidos)
        $platformFeeAmount = $sale->total_price * $platformFeeAsDecimal; // Ex: 500 * 0.001 = 0.50

        LedgerEntry::create([
            'association_id' => $sale->association_id,
            'related_type' => get_class($sale),
            'related_id' => $sale->id,
            'type' => 'platform_fee',
            // Descrição agora mostra a porcentagem correta dinamicamente
            'description' => "Taxa da Plataforma ({$platformFeeAsPercentage}%) sobre Venda #{$sale->id}",
            'amount' => -$platformFeeAmount, // Valor negativo
        ]);
        Log::info("Ledger: Registrada taxa da plataforma de -{$platformFeeAmount} para a venda #{$sale->id}.");

        // 3. Saída do Custo Fixo do Adquirente (sem alterações)
        LedgerEntry::create([
            'association_id' => $sale->association_id,
            'related_type' => get_class($sale),
            'related_id' => $sale->id,
            'type' => 'acquirer_fixed_cost',
            'description' => "Custo Fixo do Adquirente sobre Venda #{$sale->id}",
            'amount' => $acquirerFixedCost,
        ]);
        Log::info("Ledger: Registrado custo fixo de {$acquirerFixedCost} para a venda #{$sale->id}.");
    }
}
