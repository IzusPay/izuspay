<?php

namespace App\Services\Gateways;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrPaggService implements GatewayInterface
{
    private function getBaseUrl(array $credentials): string
    {
        $url = $credentials['BRPAGG_API_URL'] ?? 'https://api.brpagg.com.br';

        return rtrim($url, '/');
    }

    private function getAuthUser(array $credentials): string
    {
        $u = $credentials['BRPAGG_USERNAME'] ?? ($credentials['BRPAGG_SECRET_KEY'] ?? null);
        if (! $u) {
            throw new \Exception('Credencial BRPagg ausente: usuário ou secretKey.');
        }

        return $u;
    }

    private function getAuthPass(array $credentials): string
    {
        $p = $credentials['BRPAGG_PASSWORD'] ?? ($credentials['BRPAGG_COMPANY_ID'] ?? null);
        if (! $p) {
            throw new \Exception('Credencial BRPagg ausente: senha ou companyId.');
        }

        return $p;
    }

    private function buildHeaders(array $credentials, bool $includeApiKey = true): array
    {
        $basic = base64_encode($this->getAuthUser($credentials).':'.$this->getAuthPass($credentials));
        $headers = [
            'Authorization' => "Basic {$basic}",
            'Content-Type' => 'application/json',
        ];
        if ($includeApiKey && isset($credentials['BRPAGG_API_KEY'])) {
            $headers['x-api-key'] = $credentials['BRPAGG_API_KEY'];
        }

        return $headers;
    }

    public function createCharge(Product $product, array $customerData, array $credentials): array
    {
        $base = $this->getBaseUrl($credentials);

        $document = preg_replace('/\D/', '', $customerData['document'] ?? '');
        $documentType = strlen($document) === 11 ? 'CPF' : 'CNPJ';
        $amount = isset($customerData['amount']) ? (int) $customerData['amount'] : (int) ($product->price * 100);
        $items = $customerData['items'] ?? null;
        if (! $items) {
            $items = [[
                'title' => $product->name,
                'unitPrice' => (int) ($product->price * 100),
                'quantity' => 1,
                'tangible' => $product->tipo_produto == 0,
                'externalRef' => (string) $product->id,
            ]];
        } else {
            $converted = [];
            foreach ($items as $it) {
                $unitPrice = $it['unitPrice'] ?? ($it['amount'] ?? 0);
                if (! is_int($unitPrice)) {
                    $unitPrice = (int) round((float) $unitPrice);
                }
                $converted[] = [
                    'title' => $it['title'] ?? $product->name,
                    'unitPrice' => $unitPrice,
                    'quantity' => (int) ($it['quantity'] ?? 1),
                    'tangible' => (bool) ($it['tangible'] ?? ($product->tipo_produto == 0)),
                    'externalRef' => (string) ($it['externalRef'] ?? (string) $product->id),
                ];
            }
            $items = $converted;
        }

        $payload = [
            'amount' => $amount,
            'paymentMethod' => 'PIX',
            'customer' => [
                'name' => $customerData['name'] ?? '',
                'email' => $customerData['email'] ?? '',
                'phone' => preg_replace('/\D/', '', $customerData['phone'] ?? ''),
                'documentType' => $documentType,
                'document' => $document,
            ],
            'items' => $items,
        ];
        if (isset($customerData['metadata']) && is_array($customerData['metadata'])) {
            $payload['metadata'] = $customerData['metadata'];
        }

        $payload['postbackUrl'] = $credentials['BRPAGG_POSTBACK_URL'] ?? route('api.brpagg.postback');

        $response = Http::withHeaders($this->buildHeaders($credentials, true))
            ->post($base.'/functions/v1/transactions', $payload);

        if ($response->failed()) {
            $error = $response->json() ?? ['message' => $response->body()];
            Log::error('BRPagg Create Charge Error', ['payload' => $payload, 'status' => $response->status(), 'response' => $error]);
            throw new \Exception($error['message'] ?? 'Falha na criação de transação BRPagg.');
        }

        $data = $response->json('data') ?? $response->json();
        $result = [
            'transaction_id' => $data['id'] ?? null,
        ];
        $qr = null;
        if (isset($data['pix']) && is_array($data['pix'])) {
            $pix = $data['pix'];
            $qr = $pix['copyPaste'] ?? ($pix['code'] ?? ($pix['copy_paste'] ?? ($pix['qrCode'] ?? ($pix['qrcode'] ?? ($pix['emv'] ?? null)))));
        }
        if (! $qr && isset($data['pix_qr_code'])) {
            $qr = $data['pix_qr_code'];
        }
        if ($qr) {
            $result['pix_qr_code'] = $qr;
        }
        if (isset($data['status'])) {
            $result['status'] = strtoupper($data['status']);
        }

        return $result;
    }

    public function getTransactionStatus(string $transactionId, array $credentials): string
    {
        $base = $this->getBaseUrl($credentials);
        $response = Http::withHeaders($this->buildHeaders($credentials, true))
            ->get($base.'/functions/v1/transactions/'.$transactionId);

        if ($response->failed()) {
            $error = $response->json() ?? ['message' => $response->body()];
            Log::error('BRPagg Get Transaction Error', ['transactionId' => $transactionId, 'status' => $response->status(), 'response' => $error]);
            throw new \Exception($error['message'] ?? 'Falha ao consultar transação BRPagg.');
        }

        $data = $response->json('data') ?? $response->json();

        return strtoupper($data['status'] ?? 'PENDING');
    }

    public function createWithdrawal(array $params, array $credentials): array
    {
        $base = $this->getBaseUrl($credentials);
        $payload = [
            'amount' => (int) ($params['amount'] ?? 0),
            'pixKey' => $params['pixKey'] ?? '',
            'pixKeyType' => strtoupper($params['pixKeyType'] ?? 'CPF'),
            'method' => 'PIX',
            'metadata' => $params['metadata'] ?? [],
        ];

        $response = Http::withHeaders($this->buildHeaders($credentials, true))
            ->post($base.'/cash-out/v1/withdrawals', $payload);

        if ($response->failed()) {
            $error = $response->json() ?? ['message' => $response->body()];
            Log::error('BRPagg Create Withdrawal Error', ['payload' => $payload, 'status' => $response->status(), 'response' => $error]);
            throw new \Exception($error['message'] ?? 'Falha ao criar saque BRPagg.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function listWithdrawals(array $credentials): array
    {
        $base = $this->getBaseUrl($credentials);
        $response = Http::withHeaders($this->buildHeaders($credentials, true))
            ->get($base.'/cash-out/v1/withdrawals');

        if ($response->failed()) {
            $error = $response->json() ?? ['message' => $response->body()];
            Log::error('BRPagg List Withdrawals Error', ['status' => $response->status(), 'response' => $error]);
            throw new \Exception($error['message'] ?? 'Falha ao listar saques BRPagg.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function searchWithdrawals(string $metadataKey, string $metadataValue, array $credentials): array
    {
        $base = $this->getBaseUrl($credentials);
        $response = Http::withHeaders($this->buildHeaders($credentials, true))
            ->get($base.'/cash-out/v1/withdrawals/search', [
                'metadataKey' => $metadataKey,
                'metadataValue' => $metadataValue,
            ]);
        if ($response->failed()) {
            $error = $response->json() ?? ['message' => $response->body()];
            Log::error('BRPagg Search Withdrawals Error', ['query' => compact('metadataKey', 'metadataValue'), 'status' => $response->status(), 'response' => $error]);
            throw new \Exception($error['message'] ?? 'Falha ao buscar saques BRPagg.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function getWithdrawal(string $withdrawalId, array $credentials): array
    {
        $base = $this->getBaseUrl($credentials);
        $response = Http::withHeaders($this->buildHeaders($credentials, true))
            ->get($base.'/cash-out/v1/withdrawals/'.$withdrawalId);

        if ($response->failed()) {
            $error = $response->json() ?? ['message' => $response->body()];
            Log::error('BRPagg Get Withdrawal Error', ['withdrawalId' => $withdrawalId, 'status' => $response->status(), 'response' => $error]);
            throw new \Exception($error['message'] ?? 'Falha ao consultar saque BRPagg.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function listDisputes(array $credentials): array
    {
        $base = $this->getBaseUrl($credentials);
        $response = Http::withHeaders($this->buildHeaders($credentials, true))
            ->get($base.'/functions/v1/disputes');

        if ($response->failed()) {
            $error = $response->json() ?? ['message' => $response->body()];
            Log::error('BRPagg List Disputes Error', ['status' => $response->status(), 'response' => $error]);
            throw new \Exception($error['message'] ?? 'Falha ao listar disputas BRPagg.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function getDispute(string $disputeId, array $credentials): array
    {
        $base = $this->getBaseUrl($credentials);
        $response = Http::withHeaders($this->buildHeaders($credentials, true))
            ->get($base.'/functions/v1/disputes/'.$disputeId);

        if ($response->failed()) {
            $error = $response->json() ?? ['message' => $response->body()];
            Log::error('BRPagg Get Dispute Error', ['disputeId' => $disputeId, 'status' => $response->status(), 'response' => $error]);
            throw new \Exception($error['message'] ?? 'Falha ao consultar disputa BRPagg.');
        }

        return $response->json('data') ?? $response->json();
    }

    public function appealDispute(string $disputeId, string $appealReason, array $credentials): array
    {
        $base = $this->getBaseUrl($credentials);
        $payload = [
            'disputeId' => $disputeId,
            'appealReason' => $appealReason,
        ];
        $response = Http::withHeaders($this->buildHeaders($credentials, true))
            ->post($base.'/functions/v1/disputes/appeal', $payload);

        if ($response->failed()) {
            $error = $response->json() ?? ['message' => $response->body()];
            Log::error('BRPagg Appeal Dispute Error', ['payload' => $payload, 'status' => $response->status(), 'response' => $error]);
            throw new \Exception($error['message'] ?? 'Falha ao recorrer disputa BRPagg.');
        }

        return $response->json('data') ?? $response->json();
    }
}
