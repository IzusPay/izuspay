@extends('layouts.app')

@section('title', 'Documentação da API')

@section('content')
<div class="space-y-8 p-6 bg-white dark:bg-black rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
    <div class="space-y-2">
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Documentação da API</h1>
        <p class="text-slate-600 dark:text-slate-300">Introdução e rotas disponíveis para integração.</p>
    </div>

    <div class="space-y-4">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Autenticação</h2>
        <p class="text-slate-700 dark:text-slate-300">A API utiliza Bearer Token. Inclua o cabeçalho <span class="font-mono bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">Authorization: Bearer {seu_token}</span> em todas as requisições protegidas.</p>
        <p class="text-slate-700 dark:text-slate-300">Recomenda-se definir <span class="font-mono bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded">Accept: application/json</span>.</p>
        <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-auto"><code>curl -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json" https://seu-dominio.com/api/transactions</code></pre>
    </div>

    <div class="space-y-6">
        <h2 class="text-xl font-semibold text-slate-900 dark:text-white">Rotas Protegidas</h2>

        <div class="space-y-2">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Listar Transações</h3>
            <p class="text-slate-700 dark:text-slate-300"><span class="font-mono">GET /api/transactions</span></p>
            <p class="text-slate-700 dark:text-slate-300">Parâmetros de consulta:</p>
            <ul class="list-disc pl-6 text-slate-700 dark:text-slate-300">
                <li><span class="font-mono">page</span> integer ≥ 1. Padrão: 1.</li>
                <li><span class="font-mono">limit</span> integer 1–100. Padrão: 10.</li>
                <li><span class="font-mono">status</span> enum: <span class="font-mono">pending</span>, <span class="font-mono">paid</span>, <span class="font-mono">failed</span>, <span class="font-mono">refunded</span>, <span class="font-mono">expired</span>.</li>
                <li><span class="font-mono">paymentMethod</span> enum: <span class="font-mono">PIX</span>.</li>
                <li><span class="font-mono">startDate</span> data <span class="font-mono">YYYY-MM-DD</span>.</li>
                <li><span class="font-mono">endDate</span> data <span class="font-mono">YYYY-MM-DD</span>.</li>
            </ul>
            <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-auto"><code>curl -H "Authorization: Bearer SEU_TOKEN" \
"https://seu-dominio.com/api/transactions?status=paid&amp;paymentMethod=PIX&amp;startDate=2025-12-01&amp;endDate=2025-12-31&amp;page=1&amp;limit=20"</code></pre>
        </div>

        <div class="space-y-2">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Criar Transação</h3>
            <p class="text-slate-700 dark:text-slate-300"><span class="font-mono">POST /api/transactions</span></p>
            <p class="text-slate-700 dark:text-slate-300">Corpo JSON:</p>
            <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-auto"><code>{
  "amount": 99.90,
  "customer": {
    "name": "Cliente",
    "email": "cliente@example.com",
    "phone": "11999999999",
    "document": "12345678900"
  },
  "method": "PIX",
  "items": [
    {"title": "Item", "unitPrice": 99.90, "quantity": 1}
  ],
  "metadata": {"ref": "ABC123"}
}</code></pre>
        </div>

        <div class="space-y-2">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Consultar Transação</h3>
            <p class="text-slate-700 dark:text-slate-300"><span class="font-mono">GET /api/transactions/{transactionId}</span></p>
            <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-auto"><code>curl -H "Authorization: Bearer SEU_TOKEN" \
"https://seu-dominio.com/api/transactions/abcd-1234"</code></pre>
        </div>

        <div class="space-y-2">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Saldo do Vendedor</h3>
            <p class="text-slate-700 dark:text-slate-300"><span class="font-mono">GET /api/seller-wallet/gestao</span></p>
            <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-auto"><code>{
  "status": true,
  "data": {
    "id": "42",
    "sellerId": "7",
    "balance": 0,
    "blockedBalance": 0,
    "createdAt": "2025-12-22T10:00:00Z",
    "updatedAt": "2025-12-22T10:05:00Z"
  }
}</code></pre>
        </div>

        <div class="space-y-2">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Saques</h3>
            <p class="text-slate-700 dark:text-slate-300"><span class="font-mono">GET /api/withdrawals</span></p>
            <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-auto"><code>curl -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json" \
"https://seu-dominio.com/api/withdrawals?page=1&amp;limit=15"</code></pre>
            <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-auto"><code>{
  "data": [
    {
      "id": 101,
      "wallet_id": 42,
      "amount": 150.0,
      "status": "pending",
      "pix_key": "11999999999",
      "pix_key_type": "phone",
      "created_at": "2025-12-22T10:00:00Z",
      "updated_at": "2025-12-22T10:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
  }
}</code></pre>
            <p class="text-slate-700 dark:text-slate-300"><span class="font-mono">POST /api/withdrawals</span></p>
            <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-auto"><code>curl -X POST -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json" -H "Content-Type: application/json" \
-d '{
  "method": "pix",
  "amount": 150.00,
  "pix_key": "11999999999",
  "pix_key_type": "phone"
}' \
https://seu-dominio.com/api/withdrawals</code></pre>
            <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-auto"><code>{
  "id": 101,
  "amount": 150,
  "fee_amount": 5,
  "net_amount": 145,
  "status": "pending",
  "pix_key": "11999999999",
  "pix_key_type": "phone",
  "created_at": "2025-12-22T10:00:00Z"
}</code></pre>
            <p class="text-slate-700 dark:text-slate-300"><span class="font-mono">GET /api/withdrawals/{withdrawal}</span></p>
            <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-auto"><code>curl -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json" \
"https://seu-dominio.com/api/withdrawals/101"</code></pre>
            <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-auto"><code>{
  "id": 101,
  "amount": 150,
  "fee_amount": 5,
  "net_amount": 145,
  "status": "pending",
  "pix_key": "11999999999",
  "pix_key_type": "phone",
  "created_at": "2025-12-22T10:00:00Z"
}</code></pre>
        </div>
    </div>
</div>
@endsection
