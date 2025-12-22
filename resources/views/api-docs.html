<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>IzusPay • Documentação da API</title>
  <style>
    :root {
      --bg: #ffffff;
      --fg: #0f172a;
      --muted: #475569;
      --border: #e2e8f0;
      --code-bg: #0b1220;
      --code-fg: #e2e8f0;
      --accent: #111827;
      --pill: #f1f5f9;
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Apple Color Emoji", "Segoe UI Emoji";
      background: var(--bg);
      color: var(--fg);
      line-height: 1.6;
    }
    .wrap { max-width: 980px; padding: 0 16px; }
    h1 { font-size: 32px; margin: 0 0 6px; }
    h2 { font-size: 22px; margin: 28px 0 10px; }
    h3 { font-size: 18px; margin: 22px 0 8px; }
    p { margin: 10px 0; color: var(--muted); }
    .section { background: #fafafa; border: 1px solid var(--border); border-radius: 12px; padding: 18px; margin: 18px 0; }
    .pill { display: inline-block; padding: 2px 8px; background: var(--pill); border: 1px solid var(--border); border-radius: 999px; font-size: 12px; color: var(--muted); }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .card { border: 1px solid var(--border); border-radius: 12px; padding: 16px; background: #fff; }
    ul { margin: 8px 0 8px 20px; }
    code { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    pre { background: var(--code-bg); color: var(--code-fg); border-radius: 12px; padding: 14px; overflow: auto; font-size: 13px; }
    .endpoint { display: inline-flex; align-items: center; gap: 10px; margin: 6px 0; }
    .verb { font-weight: 600; color: #059669; }
    .path { font-family: ui-monospace, monospace; background: var(--pill); border: 1px solid var(--border); padding: 2px 8px; border-radius: 999px; }
    .table { width: 100%; border-collapse: collapse; font-size: 14px; }
    .table th, .table td { border: 1px solid var(--border); padding: 8px 10px; text-align: left; }
    .table th { background: #f8fafc; font-weight: 600; }
    .muted { color: var(--muted); }
    .kbd { font-family: ui-monospace, monospace; background: #eef2ff; border: 1px solid #e5e7eb; padding: 2px 6px; border-radius: 6px; }
    .block { border-left: 3px solid #0ea5e9; padding-left: 12px; margin: 10px 0; }
    .layout { display: grid; grid-template-columns: 220px 1fr; gap: 12px; align-items: start; }
    .sidebar { position: sticky; top: 24px; border: 1px solid var(--border); border-radius: 12px; background: #fff; padding: 12px; }
    .sidebar nav a { display: block; padding: 8px 10px; border-radius: 8px; text-decoration: none; color: var(--fg); font-size: 14px; }
    .sidebar nav a:hover { background: var(--pill); }
    .sidebar nav a.active { background: #eef2ff; border: 1px solid #e5e7eb; }
    .subnav { display: none; margin: 6px 0 10px 6px; padding-left: 8px; border-left: 2px solid var(--border); }
    .subnav.open { display: block; }
    .subnav a { font-size: 13px; color: var(--muted); }
    .method { display: inline-block; font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 999px; margin-right: 6px; color: #fff; }
    .method.get { background: #2563eb; }
    .method.post { background: #059669; }
    .method.patch { background: #7c3aed; }
    .method.delete { background: #dc2626; }
  </style>
  <meta name="robots" content="noindex,nofollow">
</head>
<body>
  <div class="wrap">
    <div>
      <h1>IzusPay • Documentação da API</h1>
      <p>Guia de integração com rotas protegidas e públicas, filtros de listagem e formato de autenticação.</p>
      <span class="pill">Versão: Beta</span>
    </div>

    <div class="layout">
      <aside class="sidebar">
        <nav>
          <a href="#introducao">Introdução</a>
          <a href="#transacoes">Transações</a>
          <div class="subnav">
            <a href="#ep-transactions-index">Listar Transações <span class="method get">GET</span></a>
            <a href="#ep-transactions-create">Criar Transação <span class="method post">POST</span></a>
            <a href="#ep-transactions-show">Consultar Transação <span class="method get">GET</span></a>
          </div>
          <a href="#carteira">Carteira do Vendedor</a>
          <div class="subnav">
            <a href="#ep-wallet-gestao">Saldo do Vendedor <span class="method get">GET</span></a>
          </div>
          <a href="#webhooks">Webhooks</a>
          <div class="subnav">
            <a href="#ep-webhooks-index">Listar Webhooks <span class="method get">GET</span></a>
            <a href="#ep-webhooks-store">Criar Webhook <span class="method post">POST</span></a>
            <a href="#ep-webhooks-show">Consultar Webhook <span class="method get">GET</span></a>
            <a href="#ep-webhooks-update">Atualizar Webhook <span class="method patch">PATCH</span></a>
            <a href="#ep-webhooks-delete">Remover Webhook <span class="method delete">DELETE</span></a>
          </div>
          <a href="#saques">Saques</a>
          <div class="subnav">
            <a href="#ep-withdrawals-index">Listar Saques <span class="method get">GET</span></a>
            <a href="#ep-withdrawals-store">Solicitar Saque <span class="method post">POST</span></a>
            <a href="#ep-withdrawals-show">Consultar Saque <span class="method get">GET</span></a>
          </div>
          <a href="#erros">Erros Comuns</a>
        </nav>
      </aside>
      <div>
    <div class="section" id="introducao">
      <h2>Introdução</h2>
      <p>Base da API: <code>https://seu-dominio.com</code></p>
      <div class="block">
        <p>A API usa autenticação via Bearer Token. Inclua o cabeçalho <span class="kbd">Authorization: Bearer &lt;seu_token&gt;</span> em todas as rotas protegidas, além de <span class="kbd">Accept: application/json</span>.</p>
      </div>
      <pre><code>curl -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json" \
https://seu-dominio.com/api/transactions</code></pre>
    </div>

    <div class="section" id="transacoes">
      <h2>Transações</h2>
      <div class="endpoint" id="ep-transactions-index"><span class="verb">GET</span> <span class="path">/api/transactions</span></div>
      <p class="muted">Lista transações do vendedor autenticado com paginação e filtros.</p>
      <table class="table">
        <thead><tr><th>Parâmetro</th><th>Tipo</th><th>Valores</th><th>Padrão</th><th>Descrição</th></tr></thead>
        <tbody>
          <tr><td>page</td><td>integer</td><td>≥ 1</td><td>1</td><td>Número da página</td></tr>
          <tr><td>limit</td><td>integer</td><td>1–100</td><td>10</td><td>Registros por página</td></tr>
          <tr><td>status</td><td>string</td><td>pending, paid, failed, refunded, expired</td><td>—</td><td>Filtro por status</td></tr>
          <tr><td>paymentMethod</td><td>string</td><td>PIX</td><td>—</td><td>Filtro por método de pagamento</td></tr>
          <tr><td>startDate</td><td>date</td><td>YYYY-MM-DD</td><td>—</td><td>Data inicial (created_at ≥)</td></tr>
          <tr><td>endDate</td><td>date</td><td>YYYY-MM-DD</td><td>—</td><td>Data final (created_at ≤)</td></tr>
        </tbody>
      </table>
      <pre><code>curl -H "Authorization: Bearer SEU_TOKEN" \
"https://seu-dominio.com/api/transactions?status=paid&amp;paymentMethod=PIX&amp;startDate=2025-12-01&amp;endDate=2025-12-31&amp;page=1&amp;limit=20"</code></pre>
      <pre><code>{
  "data": [
    {
      "transaction_id": "abcd-1234",
      "status": "paid",
      "amount": 100.0,
      "method": "pix",
      "created_at": "2025-12-22T10:00:00Z",
      "updated_at": "2025-12-22T10:05:00Z"
    }
  ],
  "meta": {
    "page": 1,
    "limit": 20,
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 50
  }
}</code></pre>

      <div class="endpoint" id="ep-transactions-create"><span class="verb">POST</span> <span class="path">/api/transactions</span></div>
      <p class="muted">Cria uma transação PIX.</p>
      <pre><code>{
  "amount": 99.90,
  "customer": {
    "name": "Cliente",
    "email": "cliente@example.com",
    "phone": "11999999999",
    "document": "12345678900"
  },
  "method": "PIX",
  "items": [
    { "title": "Item", "unitPrice": 99.90, "quantity": 1 }
  ],
  "metadata": { "ref": "ABC123" }
}</code></pre>
      <pre><code>{
  "success": true,
  "message": "Transação iniciada com sucesso!",
  "transaction_id": "abcd-1234",
  "pix_copy_paste": "000201...",
  "total_price": 99.9
}</code></pre>

      <div class="endpoint" id="ep-transactions-show"><span class="verb">GET</span> <span class="path">/api/transactions/{transactionId}</span></div>
      <p class="muted">Consulta detalhes e status da transação.</p>
      <pre><code>{
  "success": true,
  "transaction_id": "abcd-1234",
  "status": "paid",
  "created_at": "2025-12-22T10:00:00Z",
  "updated_at": "2025-12-22T10:05:00Z",
  "product": { "name": "Produto X" },
  "customer": { "name": "Cliente", "email": "cliente@example.com" },
  "total_price": 99.9
}</code></pre>
    </div>

    <div class="section" id="carteira">
      <h2>Carteira do Vendedor</h2>
      <div class="endpoint" id="ep-wallet-gestao"><span class="verb">GET</span> <span class="path">/api/seller-wallet/gestao</span></div>
      <p class="muted">Retorna saldo e bloqueios (saques pendentes/disputas).</p>
      <pre><code>{
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

    <div class="section" id="webhooks">
      <h2>Webhooks (CRUD)</h2>
      <div class="endpoint" id="ep-webhooks-index"><span class="verb">GET</span> <span class="path">/api/webhooks</span></div>
      <p class="muted">Lista endpoints de webhook da associação (paginação opcional com <code>page</code>, <code>limit</code>).</p>
      <pre><code>curl -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json" \
https://seu-dominio.com/api/webhooks?page=1&amp;limit=15</code></pre>
      <pre><code>{
  "data": [
    { "id": 1, "url": "https://example.com/webhook", "description": "Principal", "is_active": true }
  ],
  "meta": {
    "page": 1, "limit": 15, "current_page": 1, "last_page": 1, "per_page": 15, "total": 1
  }
}</code></pre>

      <div class="endpoint" id="ep-webhooks-store"><span class="verb">POST</span> <span class="path">/api/webhooks</span></div>
      <p class="muted">Cria um endpoint de webhook.</p>
      <pre><code>curl -X POST -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json" -H "Content-Type: application/json" \
-d '{
  "url": "https://example.com/webhook",
  "description": "Principal",
  "is_active": true
}' \
https://seu-dominio.com/api/webhooks</code></pre>
      <pre><code>{
  "url": "https://example.com/webhook",
  "description": "Principal",
  "is_active": true
}</code></pre>
      <pre><code>{
  "data": { "id": 1, "url": "https://example.com/webhook", "description": "Principal", "is_active": true }
}</code></pre>

      <div class="endpoint" id="ep-webhooks-show"><span class="verb">GET</span> <span class="path">/api/webhooks/{webhook}</span></div>
      <p class="muted">Consulta um endpoint.</p>
      <pre><code>curl -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json" \
https://seu-dominio.com/api/webhooks/1</code></pre>
      <pre><code>{
  "data": { "id": 1, "url": "https://example.com/webhook", "description": "Principal", "is_active": true }
}</code></pre>

      <div class="endpoint" id="ep-webhooks-update"><span class="verb">PUT</span> <span class="path">/api/webhooks/{webhook}</span> • <span class="verb">PATCH</span> <span class="path">/api/webhooks/{webhook}</span></div>
      <p class="muted">Atualiza um endpoint.</p>
      <pre><code>curl -X PATCH -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json" -H "Content-Type: application/json" \
-d '{
  "description": "Secundário",
  "is_active": false
}' \
https://seu-dominio.com/api/webhooks/1</code></pre>
      <pre><code>{
  "description": "Secundário",
  "is_active": false
}</code></pre>
      <pre><code>{
  "data": { "id": 1, "url": "https://example.com/webhook", "description": "Secundário", "is_active": false }
}</code></pre>

      <div class="endpoint" id="ep-webhooks-delete"><span class="verb">DELETE</span> <span class="path">/api/webhooks/{webhook}</span></div>
      <p class="muted">Remove um endpoint.</p>
      <pre><code>curl -X DELETE -H "Authorization: Bearer SEU_TOKEN" -H "Accept: application/json" \
https://seu-dominio.com/api/webhooks/1</code></pre>
      <pre><code>204 No Content</code></pre>
    </div>

    <div class="section" id="saques">
      <h2>Saques</h2>
      <div class="endpoint" id="ep-withdrawals-index"><span class="verb">GET</span> <span class="path">/api/withdrawals</span></div>
      <p class="muted">Lista saques com paginação.</p>
      <pre><code>curl -H "Authorization: Bearer SEU_TOKEN" \
https://seu-dominio.com/api/withdrawals</code></pre>
      <pre><code>{
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
      <div class="endpoint" id="ep-withdrawals-store"><span class="verb">POST</span> <span class="path">/api/withdrawals</span></div>
      <p class="muted">Solicita saque PIX ou via conta bancária (valida saldo disponível).</p>
      <pre><code>curl -X POST -H "Authorization: Bearer SEU_TOKEN" -H "Content-Type: application/json" \
-d '{
  "method": "pix",
  "amount": 150.00,
  "pix_key": "11999999999",
  "pix_key_type": "phone"
}' \
https://seu-dominio.com/api/withdrawals</code></pre>
      <pre><code>{
  "id": 101,
  "amount": 150,
  "fee_amount": 5,
  "net_amount": 145,
  "status": "pending",
  "pix_key": "11999999999",
  "pix_key_type": "phone",
  "created_at": "2025-12-22T10:00:00Z"
}</code></pre>
      <p class="muted">Exemplo usando conta bancária:</p>
      <pre><code>{
  "method": "pix",
  "amount": 200.00,
  "bank_account_id": 12
}</code></pre>
      <p class="muted">Erros comuns:</p>
      <ul>
        <li><b>422</b> valor mínimo: <code>{"message":"O valor mínimo para saque é de R$ 10,00."}</code></li>
        <li><b>422</b> saldo insuficiente: <code>{"message":"Saldo insuficiente para cobrir o valor do saque e a taxa."}</code></li>
      </ul>
      <div class="endpoint" id="ep-withdrawals-show"><span class="verb">GET</span> <span class="path">/api/withdrawals/{withdrawal}</span></div>
      <p class="muted">Consulta detalhes do saque (taxa e líquido).</p>
      <pre><code>curl -H "Authorization: Bearer SEU_TOKEN" \
https://seu-dominio.com/api/withdrawals/101</code></pre>
      <pre><code>{
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


    <div class="section" id="erros">
      <h2>Erros Comuns</h2>
      <ul>
        <li><b>401</b> Não autorizado: token ausente ou inválido.</li>
        <li><b>404</b> Não encontrado: transação não pertence ao vendedor.</li>
        <li><b>422</b> Validação: parâmetros inválidos ou saldo insuficiente.</li>
        <li><b>500</b> Erro interno.</li>
      </ul>
    </div>
      </div>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const links = document.querySelectorAll('.sidebar nav a');
      const rootLinks = document.querySelectorAll('.sidebar nav > a');
      const subnavs = document.querySelectorAll('.subnav');
      const map = new Map([...links].map(l => [l.getAttribute('href'), l]));

      // Collapse endpoint contents by default
      const endpointEls = Array.from(document.querySelectorAll('.endpoint'));
      const groups = new Map();
      endpointEls.forEach(ep => {
        const group = [ep];
        let sib = ep.nextElementSibling;
        while (sib && !sib.classList.contains('endpoint') && !sib.classList.contains('section')) {
          group.push(sib);
          sib = sib.nextElementSibling;
        }
        if (ep.id) {
          groups.set('#' + ep.id, group);
        }
      });
      function collapseAll() {
        groups.forEach(g => { g.forEach(n => n.style.display = 'none'); });
      }
      function showOnlySection(hash) {
        document.querySelectorAll('.section[id]').forEach(sec => {
          sec.style.display = ('#' + sec.id === hash) ? '' : 'none';
        });
      }
      function expand(hash) {
        collapseAll();
        const g = groups.get(hash);
        if (g) {
          g.forEach(n => n.style.display = '');
          const targetEp = g[0];
          const parentSection = targetEp.closest('.section[id]');
          if (parentSection) showOnlySection('#' + parentSection.id);
          targetEp.scrollIntoView({ behavior: 'smooth', block: 'start' });
          links.forEach(a => a.classList.toggle('active', a.getAttribute('href') === hash));
          const epLink = map.get(hash);
          const parentSubnav = epLink ? epLink.closest('.subnav') : null;
          if (parentSubnav) {
            subnavs.forEach(s => s.classList.toggle('open', s === parentSubnav));
          }
        }
      }
      collapseAll();
      showOnlySection('#introducao');

      // Root menu: toggle subnavs collapse and scroll to section
      rootLinks.forEach(a => {
        a.addEventListener('click', e => {
          const next = a.nextElementSibling;
          if (next && next.classList.contains('subnav')) {
            e.preventDefault();
            subnavs.forEach(s => s.classList.toggle('open', s === next));
            const href = a.getAttribute('href');
            const section = document.querySelector(href);
            if (section) {
              showOnlySection(href);
              // Expand first endpoint in this section, if any
              const firstEp = section.querySelector('.endpoint[id]');
              if (firstEp) {
                const epHash = '#' + firstEp.id;
                expand(epHash);
              } else {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                collapseAll();
              }
            }
          } else {
            // Non-subnav root links: show only the section and expand first endpoint if exists
            e.preventDefault();
            const href = a.getAttribute('href');
            const section = document.querySelector(href);
            if (section) {
              showOnlySection(href);
              const firstEp = section.querySelector('.endpoint[id]');
              if (firstEp) {
                expand('#' + firstEp.id);
              } else {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
              }
            }
          }
        });
      });

      // Submenu navigation: show only selected endpoint content
      links.forEach(a => {
        a.addEventListener('click', e => {
          const href = a.getAttribute('href');
          if (href.startsWith('#ep-')) {
            e.preventDefault();
            expand(href);
          }
        });
      });

      // Observer to highlight section titles on scroll
      const observer = new IntersectionObserver(entries => {
        entries.forEach(e => {
          const id = '#' + e.target.id;
          const link = map.get(id);
          if (!link) return;
          if (e.isIntersecting) {
            links.forEach(a => a.classList.toggle('active', a === link));
          }
        });
      }, { rootMargin: '-20% 0px -70% 0px' });
      document.querySelectorAll('.section[id]').forEach(sec => observer.observe(sec));
    });
  </script>
</body>
</html>
