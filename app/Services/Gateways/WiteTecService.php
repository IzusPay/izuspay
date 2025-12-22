<?php

namespace App\Services\Gateways;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WiteTecService implements GatewayInterface
{
    private function getApiUrl(array $credentials): string
    {
        $apiUrl = $credentials['WITETEC_API_URL'] ?? null;
        if (! $apiUrl) {
            throw new \Exception('URL da WiteTec não encontrada nas credenciais do gateway.');
        }

        return rtrim($apiUrl, '/');
    }

    private function getApiKey(array $credentials): string
    {
        $apiKey = $credentials['WITETEC_API_KEY'] ?? null;
        if (! $apiKey) {
            throw new \Exception('Chave de API da WiteTec não encontrada nas credenciais do gateway.');
        }

        return $apiKey;
    }

    private function getAccessToken(array $credentials): ?string
    {
        return $credentials['WITETEC_ACCESS_TOKEN'] ?? null;
    }

    private function buildHeaders(array $credentials, bool $requireBearer = false, bool $includeApiKey = true): array
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];
        if ($includeApiKey) {
            $headers['x-api-key'] = $this->getApiKey($credentials);
        }
        $bearer = $this->getAccessToken($credentials) ?? $this->getApiKey($credentials);
        if ($requireBearer && ! $bearer) {
            throw new \Exception('Token de autorização da WiteTec não encontrado nas credenciais para este endpoint.');
        }
        if ($bearer) {
            $headers['Authorization'] = "Bearer {$bearer}";
        }

        return $headers;
    }

    public function createCharge(Product $product, array $customerData, array $credentials): array
    {
        $apiUrl = $this->getApiUrl($credentials);

        $document = preg_replace('/\D/', '', $customerData['document'] ?? '');
        $documentType = strlen($document) === 11 ? 'CPF' : 'CNPJ';

        $amount = isset($customerData['amount'])
            ? (int) $customerData['amount']
            : (int) ($product->price * 100);

        $items = $customerData['items'] ?? null;
        if (! $items) {
            $items = [[
                'title' => $product->name,
                'amount' => (int) ($product->price * 100),
                'quantity' => 1,
                'tangible' => $product->tipo_produto == 0,
                'externalRef' => (string) $product->id,
            ]];
        } else {
            $normalized = [];
            foreach ($items as $it) {
                $amt = $it['amount'] ?? 0;
                if (! is_int($amt)) {
                    $amt = (int) round(((float) $amt));
                }
                $normalized[] = [
                    'title' => $it['title'] ?? $product->name,
                    'amount' => $amt,
                    'quantity' => (int) ($it['quantity'] ?? 1),
                    'tangible' => (bool) ($it['tangible'] ?? ($product->tipo_produto == 0)),
                    'externalRef' => (string) ($it['externalRef'] ?? (string) $product->id),
                ];
            }
            $items = $normalized;
        }

        $method = strtoupper($customerData['method'] ?? 'PIX');

        $payload = [
            'amount' => $amount,
            'method' => $method,
            'customer' => [
                'name' => $customerData['name'] ?? '',
                'email' => $customerData['email'] ?? '',
                'phone' => preg_replace('/\D/', '', $customerData['phone'] ?? ''),
                'documentType' => $documentType,
                'document' => $document,
            ],
            'items' => $items,
            'postbackUrl' => route('api.witetec.postback'),
        ];
        if (isset($customerData['metadata']) && is_array($customerData['metadata'])) {
            $payload['metadata'] = $customerData['metadata'];
        }

        if ($method === 'CREDIT_CARD') {
            $payload['installments'] = (int) ($customerData['installments'] ?? 1);
            $payload['card'] = [
                'number' => preg_replace('/\s+/', '', $customerData['card']['number'] ?? ''),
                'holderName' => $customerData['card']['holderName'] ?? '',
                'holderDocument' => preg_replace('/\D/', '', $customerData['card']['holderDocument'] ?? ''),
                'expirationMonth' => (int) ($customerData['card']['expirationMonth'] ?? 0),
                'expirationYear' => (int) ($customerData['card']['expirationYear'] ?? 0),
                'cvv' => (string) ($customerData['card']['cvv'] ?? ''),
            ];
        }

        $response = Http::withHeaders($this->buildHeaders($credentials, false, true))
            ->post("{$apiUrl}/transactions", $payload);

        if ($response->failed()) {
            $errorData = $response->json() ?? ['message' => $response->body()];
            Log::error('WiteTec API Error:', [
                'payload' => $payload,
                'status' => $response->status(),
                'response' => $errorData,
            ]);
            throw new \Exception($errorData['message'] ?? 'Falha na comunicação com o gateway de pagamento.');
        }

        $responseData = $response->json('data') ?? $response->json();

        $result = [
            'transaction_id' => $responseData['id'] ?? ($responseData['data']['id'] ?? null),
        ];

        if (isset($responseData['pix']['copyPaste'])) {
            $result['pix_qr_code'] = $responseData['pix']['copyPaste'];
        }
        if (isset($responseData['boleto']['link'])) {
            $result['boleto_url'] = $responseData['boleto']['link'];
        }
        if (isset($responseData['status'])) {
            $result['status'] = strtoupper($responseData['status']);
        }

        return $result;
    }

    public function getTransactionStatus(string $transactionId, array $credentials): string
    {
        $apiUrl = $this->getApiUrl($credentials);

        $response = Http::withHeaders($this->buildHeaders($credentials, false, true))
            ->get("{$apiUrl}/transactions/{$transactionId}");

        if ($response->failed()) {
            $errorData = $response->json() ?? ['message' => $response->body()];
            Log::error('WiteTec API Error:', [
                'transactionId' => $transactionId,
                'status' => $response->status(),
                'response' => $errorData,
            ]);
            throw new \Exception($errorData['message'] ?? 'Falha ao consultar transação no gateway.');
        }

        $data = $response->json('data') ?? $response->json();
        $status = strtoupper($data['status'] ?? 'PENDING');

        return $status;
    }

    public function createWithdrawal(array $params, array $credentials): array
    {
        $apiUrl = $this->getApiUrl($credentials);

        $payload = [
            'amount' => (int) ($params['amount'] ?? 0),
            'pixKey' => $params['pixKey'] ?? '',
            'pixKeyType' => strtoupper($params['pixKeyType'] ?? 'CPF'),
            'method' => 'PIX',
            'metadata' => $params['metadata'] ?? [],
        ];

        $response = Http::withHeaders($this->buildHeaders($credentials, false, true))
            ->post("{$apiUrl}/withdrawals", $payload);

        if ($response->failed()) {
            $errorData = $response->json() ?? ['message' => $response->body()];
            Log::error('WiteTec Withdrawals API Error:', [
                'payload' => $payload,
                'status' => $response->status(),
                'response' => $errorData,
            ]);
            throw new \Exception($errorData['message'] ?? 'Falha ao solicitar saque PIX.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function listWithdrawals(array $credentials): array
    {
        $apiUrl = $this->getApiUrl($credentials);
        $response = Http::withHeaders($this->buildHeaders($credentials, true, false))
            ->get("{$apiUrl}/withdrawals");

        if ($response->failed()) {
            $errorData = $response->json() ?? ['message' => $response->body()];
            Log::error('WiteTec Withdrawals List Error:', [
                'status' => $response->status(),
                'response' => $errorData,
            ]);
            throw new \Exception($errorData['message'] ?? 'Falha ao listar saques.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function getWithdrawal(string $withdrawalId, array $credentials): array
    {
        $apiUrl = $this->getApiUrl($credentials);
        $response = Http::withHeaders($this->buildHeaders($credentials, true, true))
            ->get("{$apiUrl}/withdrawals/{$withdrawalId}");

        if ($response->failed()) {
            $errorData = $response->json() ?? ['message' => $response->body()];
            Log::error('WiteTec Withdrawal Get Error:', [
                'withdrawalId' => $withdrawalId,
                'status' => $response->status(),
                'response' => $errorData,
            ]);
            throw new \Exception($errorData['message'] ?? 'Falha ao consultar saque.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function searchWithdrawals(string $metadataKey, string $metadataValue, array $credentials): array
    {
        $apiUrl = $this->getApiUrl($credentials);
        $response = Http::withHeaders($this->buildHeaders($credentials, false, true))
            ->get("{$apiUrl}/withdrawals/search", [
                'metadataKey' => $metadataKey,
                'metadataValue' => $metadataValue,
            ]);

        if ($response->failed()) {
            $errorData = $response->json() ?? ['message' => $response->body()];
            Log::error('WiteTec Withdrawals Search Error:', [
                'query' => compact('metadataKey', 'metadataValue'),
                'status' => $response->status(),
                'response' => $errorData,
            ]);
            throw new \Exception($errorData['message'] ?? 'Falha ao buscar saques por metadata.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function listDisputes(array $credentials): array
    {
        $apiUrl = $this->getApiUrl($credentials);
        $response = Http::withHeaders($this->buildHeaders($credentials, true, false))
            ->get("{$apiUrl}/disputes");

        if ($response->failed()) {
            $errorData = $response->json() ?? ['message' => $response->body()];
            Log::error('WiteTec Disputes List Error:', [
                'status' => $response->status(),
                'response' => $errorData,
            ]);
            throw new \Exception($errorData['message'] ?? 'Falha ao listar disputas.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function getDispute(string $disputeId, array $credentials): array
    {
        $apiUrl = $this->getApiUrl($credentials);
        $response = Http::withHeaders($this->buildHeaders($credentials, true, false))
            ->get("{$apiUrl}/disputes/{$disputeId}");

        if ($response->failed()) {
            $errorData = $response->json() ?? ['message' => $response->body()];
            Log::error('WiteTec Dispute Get Error:', [
                'disputeId' => $disputeId,
                'status' => $response->status(),
                'response' => $errorData,
            ]);
            throw new \Exception($errorData['message'] ?? 'Falha ao consultar disputa.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function appealDispute(string $disputeId, string $appealReason, array $credentials): array
    {
        $apiUrl = $this->getApiUrl($credentials);
        $payload = [
            'disputeId' => $disputeId,
            'appealReason' => $appealReason,
        ];
        $response = Http::withHeaders($this->buildHeaders($credentials, true, false))
            ->post("{$apiUrl}/disputes/appeal", $payload);

        if ($response->failed()) {
            $errorData = $response->json() ?? ['message' => $response->body()];
            Log::error('WiteTec Dispute Appeal Error:', [
                'payload' => $payload,
                'status' => $response->status(),
                'response' => $errorData,
            ]);
            throw new \Exception($errorData['message'] ?? 'Falha ao recorrer disputa.');
        }

        return $response->json('data') ?? $response->json();
    }
}
