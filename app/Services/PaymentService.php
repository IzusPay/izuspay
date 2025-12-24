<?php

namespace App\Services;

use App\Models\Gateway;
use App\Models\Event;
use App\Models\Product;
use App\Models\TicketOrder;
use App\Models\TicketType;
use App\Models\Sale;
use App\Models\User;
use App\Services\Gateways\BrPaggService;
use App\Services\Gateways\WiteTecService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Orquestra a criação da venda e da transação de pagamento.
     */
    public function createTransaction(Product $product, array $customerData): array
    {
        return DB::transaction(function () use ($product, $customerData) {

            $association = $product->association()->with('wallet.gateway')->first();
            $wallet = $association->wallet;

            if (! $wallet || ! $wallet->gateway) {
                throw new \Exception('Nenhum gateway de pagamento configurado para o vendedor.');
            }

            $candidates = $this->getGatewayCandidates($association);
            $gatewayResponse = null;
            $usedGateway = null;
            $email = $customerData['email'] ?? '';
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $customerData['email'] = 'cliente@email.com';
            }
            foreach ($candidates as $g) {
                try {
                    $gatewayService = $this->getGatewayService($g->slug);
                    $credentials = $this->buildGatewayCredentials($wallet, $g);
                    if (empty($credentials)) {
                        throw new \Exception("Credenciais não configuradas para o gateway '{$g->name}'.");
                    }
                    $gatewayResponse = $gatewayService->createCharge($product, $customerData, $credentials);
                    $usedGateway = $g;
                    break;
                } catch (\Throwable $e) {
                    Log::warning("Falha ao criar cobrança no gateway '{$g->slug}': ".$e->getMessage());

                    continue;
                }
            }
            if (! $gatewayResponse) {
                throw new \Exception('Nenhum gateway disponível conseguiu processar a cobrança.');
            }

            $user = User::firstOrCreate(
                ['email' => $customerData['email']],
                [
                    'name' => $customerData['name'],
                    'phone' => preg_replace('/\D/', '', $customerData['phone']),
                    'documento' => preg_replace('/\D/', '', $customerData['document']),
                    'password' => Hash::make(Str::random(16)),
                    'association_id' => $product->association_id,
                    'tipo' => 'cliente',
                    'status' => 'active',
                ]
            );

            $feeConfig = $association->fees()->where('payment_method', 'pix')->first();
            $percentageFee = $feeConfig->percentage_fee ?? 4.99;
            $fixedFee = $feeConfig->fixed_fee ?? 0.40;

            $totalFee = ($product->price * ($percentageFee / 100)) + $fixedFee;
            $netAmount = $product->price - $totalFee;

            $sale = Sale::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'association_id' => $product->association_id,
                'status' => 'awaiting_payment',
                'total_price' => $product->price,
                'payment_method' => 'pix',
                'transaction_hash' => $gatewayResponse['transaction_id'],
                'fee_percentage' => $percentageFee,
                'fee_fixed' => $fixedFee,
                'fee_total' => $totalFee,
                'net_amount' => $netAmount,
            ]);

            if ($wallet) {
                // 'increment' é uma forma segura e atômica de adicionar valor a uma coluna.
                // Ele previne condições de corrida (race conditions).
                $wallet->increment('balance', $sale->net_amount);
                Log::info("Creditado R$ {$sale->net_amount} na carteira #{$wallet->id} referente à venda #{$sale->id}.");
            } else {
                // Log de erro crítico se, por algum motivo, o vendedor não tiver uma carteira.
                Log::critical("Venda #{$sale->id} paga, mas a association #{$sale->association_id} não possui uma carteira para creditar o saldo.");
            }

            return [
                'transaction_hash' => $gatewayResponse['transaction_id'],
                'pix_qr_code' => $gatewayResponse['pix_qr_code'] ?? null,
            ];
        });
    }

    public function createEventTransaction(Event $event, TicketType $type, int $quantity, array $customerData): array
    {
        return DB::transaction(function () use ($event, $type, $quantity, $customerData) {
            $association = $event->association()->with('wallet.gateway')->first();
            $wallet = $association->wallet;
            if (! $wallet || ! $wallet->gateway) {
                throw new \Exception('Nenhum gateway de pagamento configurado para o vendedor.');
            }
            $totalAmount = $type->price * $quantity;
            $candidates = $this->getGatewayCandidates($association);
            $gatewayResponse = null;
            $email = $customerData['email'] ?? '';
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $customerData['email'] = 'cliente@email.com';
            }
            $tmpProduct = new Product([
                'name' => $event->title.' - '.$type->name,
                'price' => $totalAmount,
                'association_id' => $association->id,
                'tipo_produto' => 1,
            ]);
            foreach ($candidates as $g) {
                try {
                    $gatewayService = $this->getGatewayService($g->slug);
                    $credentials = $this->buildGatewayCredentials($wallet, $g);
                    if (empty($credentials)) {
                        throw new \Exception("Credenciais não configuradas para o gateway '{$g->name}'.");
                    }
                    $gatewayResponse = $gatewayService->createCharge($tmpProduct, $customerData, $credentials);
                    break;
                } catch (\Throwable $e) {
                    Log::warning("Falha ao criar cobrança no gateway '{$g->slug}': ".$e->getMessage());
                    continue;
                }
            }
            if (! $gatewayResponse) {
                throw new \Exception('Nenhum gateway disponível conseguiu processar a cobrança.');
            }
            $user = User::firstOrCreate(
                ['email' => $customerData['email']],
                [
                    'name' => $customerData['name'],
                    'phone' => preg_replace('/\D/', '', $customerData['phone'] ?? ''),
                    'documento' => preg_replace('/\D/', '', $customerData['document'] ?? ''),
                    'password' => Hash::make(Str::random(16)),
                    'association_id' => $association->id,
                    'tipo' => 'cliente',
                    'status' => 'active',
                ]
            );
            $feeConfig = $association->fees()->where('payment_method', 'pix')->first();
            $percentageFee = $feeConfig->percentage_fee ?? 4.99;
            $fixedFee = $feeConfig->fixed_fee ?? 0.40;
            $totalFee = ($totalAmount * ($percentageFee / 100)) + $fixedFee;
            $netAmount = $totalAmount - $totalFee;
            $sale = Sale::create([
                'user_id' => $user->id,
                'product_id' => null,
                'association_id' => $association->id,
                'status' => 'awaiting_payment',
                'total_price' => $totalAmount,
                'payment_method' => 'pix',
                'transaction_hash' => $gatewayResponse['transaction_id'],
                'fee_percentage' => $percentageFee,
                'fee_fixed' => $fixedFee,
                'fee_total' => $totalFee,
                'net_amount' => $netAmount,
            ]);
            if ($wallet) {
                $wallet->increment('balance', $sale->net_amount);
                Log::info("Creditado R$ {$sale->net_amount} na carteira #{$wallet->id} referente à venda #{$sale->id}.");
            } else {
                Log::critical("Venda #{$sale->id} paga, mas a association #{$sale->association_id} não possui uma carteira para creditar o saldo.");
            }
            TicketOrder::create([
                'association_id' => $association->id,
                'event_id' => $event->id,
                'ticket_type_id' => $type->id,
                'sale_id' => $sale->id,
                'user_id' => $user->id,
                'quantity' => $quantity,
                'unit_price' => $type->price,
                'status' => 'awaiting_payment',
            ]);

            return [
                'transaction_hash' => $gatewayResponse['transaction_id'],
                'pix_qr_code' => $gatewayResponse['pix_qr_code'] ?? null,
            ];
        });
    }

    public function createTransactionFromApi(User $apiUser, array $payload): array
    {
        return DB::transaction(function () use ($apiUser, $payload) {
            $association = $apiUser->association()->with('wallet.gateway')->first();
            $wallet = $association->wallet;
            if (! $wallet || ! $wallet->gateway) {
                throw new \Exception('Nenhum gateway de pagamento configurado para o vendedor.');
            }
            $amount = (float) ($payload['amount'] ?? 0);
            $amountCents = (int) round($amount * 100);
            $method = 'PIX';
            $customerData = [
                'name' => $payload['customer']['name'] ?? '',
                'email' => $payload['customer']['email'] ?? '',
                'phone' => $payload['customer']['phone'] ?? '',
                'document' => $payload['customer']['document'] ?? '',
                'amount' => $amountCents,
                'method' => $method,
            ];
            if (isset($payload['items']) && is_array($payload['items'])) {
                $items = [];
                foreach ($payload['items'] as $item) {
                    $itemAmount = $item['amount'] ?? $amount;
                    if (! is_int($itemAmount)) {
                        $itemAmount = (int) round(((float) $itemAmount) * 100);
                    }
                    $items[] = [
                        'title' => $item['title'] ?? 'Item',
                        'amount' => $itemAmount,
                        'quantity' => (int) ($item['quantity'] ?? 1),
                        'tangible' => (bool) ($item['tangible'] ?? false),
                        'externalRef' => (string) ($item['externalRef'] ?? ''),
                    ];
                }
                $customerData['items'] = $items;
            }
            if (isset($payload['metadata']) && is_array($payload['metadata'])) {
                $customerData['metadata'] = $payload['metadata'];
            }
            $tmpProduct = new Product([
                'name' => $payload['items'][0]['title'] ?? 'Venda API',
                'price' => $amount,
                'association_id' => $association->id,
                'tipo_produto' => 1,
            ]);
            $candidates = $this->getGatewayCandidates($association);
            $gatewayResponse = null;
            $email = $customerData['email'] ?? '';
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $customerData['email'] = 'cliente@email.com';
            }
            foreach ($candidates as $g) {
                try {
                    $gatewayService = $this->getGatewayService($g->slug);
                    $credentials = $this->buildGatewayCredentials($wallet, $g);
                    if (empty($credentials)) {
                        throw new \Exception("Credenciais não configuradas para o gateway '{$g->name}'.");
                    }
                    $gatewayResponse = $gatewayService->createCharge($tmpProduct, $customerData, $credentials);
                    break;
                } catch (\Throwable $e) {
                    Log::warning("Falha ao criar cobrança (API) no gateway '{$g->slug}': ".$e->getMessage());

                    continue;
                }
            }
            if (! $gatewayResponse) {
                throw new \Exception('Nenhum gateway disponível conseguiu processar a cobrança (API).');
            }
            $user = User::firstOrCreate(
                ['email' => $customerData['email']],
                [
                    'name' => $customerData['name'],
                    'phone' => preg_replace('/\D/', '', $customerData['phone']),
                    'documento' => preg_replace('/\D/', '', $customerData['document']),
                    'password' => Hash::make(Str::random(16)),
                    'association_id' => $association->id,
                    'tipo' => 'cliente',
                    'status' => 'active',
                ]
            );
            $feeConfig = $association->fees()->where('payment_method', 'pix')->first();
            $percentageFee = $feeConfig->percentage_fee ?? 4.99;
            $fixedFee = $feeConfig->fixed_fee ?? 0.40;
            $totalFee = ($amount * ($percentageFee / 100)) + $fixedFee;
            $netAmount = $amount - $totalFee;
            $sale = Sale::create([
                'user_id' => $user->id,
                'product_id' => null,
                'association_id' => $association->id,
                'status' => 'awaiting_payment',
                'total_price' => $amount,
                'payment_method' => 'pix',
                'transaction_hash' => $gatewayResponse['transaction_id'],
                'fee_percentage' => $percentageFee,
                'fee_fixed' => $fixedFee,
                'fee_total' => $totalFee,
                'net_amount' => $netAmount,
            ]);
            if ($wallet) {
                $wallet->increment('balance', $sale->net_amount);
                Log::info("Creditado R$ {$sale->net_amount} na carteira #{$wallet->id} referente à venda #{$sale->id}.");
            } else {
                Log::critical("Venda #{$sale->id} paga, mas a association #{$sale->association_id} não possui uma carteira para creditar o saldo.");
            }

            return [
                'transaction_hash' => $gatewayResponse['transaction_id'],
                'pix_qr_code' => $gatewayResponse['pix_qr_code'] ?? null,
            ];
        });
    }

    /**
     * Factory para obter a instância do serviço de gateway.
     */
    protected function getGatewayService(string $gatewaySlug)
    {
        switch ($gatewaySlug) {
            case 'witetec': // Este slug deve corresponder ao que você cadastrou no banco
                return new WiteTecService;
            case 'brpagg':
                return new BrPaggService;
            case 'mercado-pago':
                // return new MercadoPagoService(); // Exemplo para o futuro
            default:
                throw new \Exception("Gateway '{$gatewaySlug}' não suportado.");
        }
    }

    protected function buildGatewayCredentials($wallet, $gateway): array
    {
        if (isset($gateway->credentials_schema['fields']) && is_array($gateway->credentials_schema['fields'])) {
            $fromSchema = [];
            foreach ($gateway->credentials_schema['fields'] as $field) {
                $name = $field['name'] ?? null;
                $value = $field['default'] ?? ($field['label'] ?? null);
                if (is_string($value)) {
                    $value = trim($value);
                    $value = preg_replace('/^[`\"\\\']+|[`\"\\\']+$/', '', $value);
                }
                if ($name && $name !== '' && $value !== null && $value !== '') {
                    $fromSchema[$name] = $value;
                }
            }
            if (! empty($fromSchema)) {
                return $fromSchema;
            }
        }
        switch ($gateway->slug) {
            case 'brpagg':
                return [
                    'BRPAGG_API_URL' => config('services.brpagg.url'),
                    'BRPAGG_USERNAME' => config('services.brpagg.username'),
                    'BRPAGG_PASSWORD' => config('services.brpagg.password'),
                    'BRPAGG_API_KEY' => config('services.brpagg.api_key'),
                    'BRPAGG_SECRET_KEY' => config('services.brpagg.secret_key'),
                    'BRPAGG_COMPANY_ID' => config('services.brpagg.company_id'),
                ];
            case 'witetec':
                return [
                    'WITETEC_API_URL' => config('services.witetec.url'),
                    'WITETEC_API_KEY' => config('services.witetec.key'),
                    'WITETEC_ACCESS_TOKEN' => env('WITETEC_ACCESS_TOKEN'),
                ];
            default:
                return [];
        }
    }

    protected function getGatewayCandidates($association): array
    {
        $active = Gateway::where('is_active', true)->orderBy('order')->get();
        $preferred = $association->wallet && $association->wallet->gateway ? $association->wallet->gateway : null;
        if (! $preferred) {
            return $active->all();
        }
        $result = [$preferred];
        foreach ($active as $g) {
            if ($preferred->id !== $g->id) {
                $result[] = $g;
            }
        }

        return $result;
    }
}
