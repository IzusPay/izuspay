<?php

namespace App\Services\Gateways;

use App\Models\Product;

interface GatewayInterface
{
    /**
     * Cria uma cobrança no gateway de pagamento.
     *
     * @param  Product  $product  O produto sendo vendido.
     * @param  array  $customerData  Dados do cliente final.
     * @param  array  $credentials  Credenciais do vendedor para este gateway.
     * @return array Deve retornar um array com dados da transação, como ['transaction_id' => '...', 'pix_qr_code' => '...'].
     */
    public function createCharge(Product $product, array $customerData, array $credentials): array;

    /**
     * Consulta o status de uma transação no gateway.
     * (Opcional por agora, mas bom já ter no contrato)
     *
     * @return string Retorna o status (ex: 'PAID', 'PENDING').
     */
    public function getTransactionStatus(string $transactionId, array $credentials): string;

    public function createWithdrawal(array $params, array $credentials): array;

    public function listWithdrawals(array $credentials): array;

    public function getWithdrawal(string $withdrawalId, array $credentials): array;

    public function searchWithdrawals(string $metadataKey, string $metadataValue, array $credentials): array;

    public function listDisputes(array $credentials): array;

    public function getDispute(string $disputeId, array $credentials): array;

    public function appealDispute(string $disputeId, string $appealReason, array $credentials): array;
}
