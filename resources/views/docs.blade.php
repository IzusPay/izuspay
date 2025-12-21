<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentação da API - Izus Payment</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* --- Tema de Cores (Variáveis CSS ) --- */
        :root {
            --bg-color: #FFFFFF;
            --text-color: #1A202C;
            --text-color-light: #718096;
            --border-color: #E2E8F0;
            --sidebar-bg: #F7FAFC;
            --sidebar-active-bg: #EBF8FF;
            --sidebar-active-text: #2B6CB0;
            --link-color: #2B6CB0;
            --header-bg: #FFFFFF;
            --code-header-bg: #EDF2F7;
            --code-body-bg: #F7FAFC;
            --btn-secondary-hover-bg: #EDF2F7;
        }

        html.dark {
            --bg-color: #1A202C;
            --text-color: #E2E8F0;
            --text-color-light: #A0AEC0;
            --border-color: #2D3748;
            --sidebar-bg: #2D3748;
            --sidebar-active-bg: #2B6CB0;
            --sidebar-active-text: #FFFFFF;
            --link-color: #63B3ED;
            --header-bg: #1A202C;
            --code-header-bg: #2D3748;
            --code-body-bg: #1A202C;
            --btn-secondary-hover-bg: #2D3748;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.7;
            margin: 0;
            padding: 0;
            transition: background-color 0.3s, color 0.3s;
        }

        /* --- Estrutura Principal --- */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 32px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--header-bg);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .header .logo { font-size: 1.5rem; font-weight: 700; }
        .header .logo span { color: var(--link-color); }
        .header-right-actions { display: flex; align-items: center; gap: 16px; }
        .header-actions button {
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 6px;
            border: 1px solid var(--link-color);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .header-actions .btn-primary { background-color: var(--link-color); color: white; }
        .header-actions .btn-secondary { background-color: transparent; color: var(--link-color); }
        .header-actions .btn-primary:hover { opacity: 0.8; }
        .header-actions .btn-secondary:hover { background-color: var(--btn-secondary-hover-bg); }
        
        #theme-toggle { background: none; border: none; cursor: pointer; color: var(--text-color-light); padding: 4px; }
        #theme-toggle:hover { color: var(--text-color); }
        #theme-toggle .icon { width: 22px; height: 22px; }
        .sun-icon { display: none; }
        .moon-icon { display: block; }
        html.dark .sun-icon { display: block; }
        html.dark .moon-icon { display: none; }

        .main-container { display: flex; }

        /* --- Menu Lateral (Sidebar) --- */
        .sidebar {
            width: 260px; /* Reduzido */
            flex-shrink: 0;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            padding: 16px;
            height: calc(100vh - 73px);
            position: sticky;
            top: 73px;
            overflow-y: auto;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .sidebar-nav h4 {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--text-color-light);
            text-transform: uppercase;
            margin: 20px 0 8px 12px;
            letter-spacing: 0.05em;
        }
        .sidebar-nav ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 12px; /* Reduzido */
            font-size: 0.875rem; /* Reduzido */
            font-weight: 500;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .sidebar-nav li a .icon {
            width: 16px;
            height: 16px;
            stroke-width: 2;
            color: var(--text-color-light);
            transition: color 0.2s ease;
        }
        .sidebar-nav li a:hover { background-color: var(--border-color); }
        .sidebar-nav li a.active {
            background-color: var(--sidebar-active-bg);
            color: var(--sidebar-active-text);
            font-weight: 600;
        }
        .sidebar-nav li a.active .icon { color: var(--sidebar-active-text); }

        /* --- Conteúdo Principal --- */
        .content { flex-grow: 1; padding: 48px 64px; max-width: 800px; }
        .content h1 { font-size: 2.25rem; font-weight: 700; margin-top: 0; margin-bottom: 16px; }
        .content h2 { font-size: 1.75rem; font-weight: 600; margin-top: 48px; margin-bottom: 24px; padding-bottom: 8px; border-bottom: 1px solid var(--border-color); }
        .content h3 { font-size: 1.25rem; font-weight: 600; margin-top: 32px; margin-bottom: 16px; }
        .content p, .content li { color: var(--text-color-light); font-size: 1rem; }
        .content code {
            background-color: var(--code-body-bg);
            border: 1px solid var(--border-color);
            color: #D6336C;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .endpoint { display: flex; align-items: center; gap: 12px; background-color: var(--sidebar-bg); border: 1px solid var(--border-color); padding: 12px; border-radius: 8px; margin: 24px 0; }
        .endpoint-method { font-weight: 700; padding: 4px 8px; border-radius: 6px; color: white; }
        .endpoint-method.post { background-color: #38A169; }
        .endpoint-method.get { background-color: #3182CE; }
        .endpoint-url { font-family: 'Menlo', 'Consolas', monospace; font-size: 1em; }

        /* --- ESTILO VS CODE PARA BLOCOS DE CÓDIGO --- */
        .code-block {
            border: 1px solid #2D3748;
            border-radius: 8px;
            margin: 24px 0;
            overflow: hidden;
            background-color: #1E1E1E;
        }
        .code-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333742;
            padding: 8px 16px;
        }
        .code-header span {
            font-size: 0.8rem;
            font-weight: 500;
            color: #A0AEC0;
            text-transform: uppercase;
        }
        .copy-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: none;
            border: none;
            color: #A0AEC0;
            cursor: pointer;
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background-color 0.2s;
        }
        .copy-btn:hover { background-color: #4A5568; }
        .code-body { position: relative; }
        .code-body pre { margin: 0; border: none; border-radius: 0; background-color: #1E1E1E !important; padding: 16px; }

        /* Tema de Cores "VS Code Dark+" para Prism.js */
        code[class*="language-"], pre[class*="language-"] {
            color: #D4D4D4;
            font-family: 'Fira Code', 'Menlo', 'Consolas', monospace;
            font-size: 14px;
            line-height: 1.5;
            text-align: left;
            white-space: pre;
            word-spacing: normal;
            word-break: normal;
            word-wrap: normal;
            -moz-tab-size: 4;
            -o-tab-size: 4;
            tab-size: 4;
            -webkit-hyphens: none; -moz-hyphens: none; -ms-hyphens: none; hyphens: none;
        }
        .token.comment, .token.prolog, .token.doctype, .token.cdata { color: #6A9955; }
        .token.punctuation { color: #D4D4D4; }
        .token.property, .token.tag, .token.boolean, .token.number, .token.constant, .token.symbol, .token.deleted { color: #B5CEA8; }
        .token.selector, .token.attr-name, .token.string, .token.char, .token.builtin, .token.inserted { color: #CE9178; }
        .token.operator, .token.entity, .token.url { color: #D4D4D4; }
        .token.atrule, .token.attr-value, .token.keyword { color: #C586C0; }
        .token.function, .token.class-name { color: #DCDCAA; }
        .token.regex, .token.important, .token.variable { color: #9CDCFE; }
        .token.key { color: #9CDCFE; } /* Específico para chaves JSON */

        /* --- Mobile --- */
        .mobile-menu-button { display: none; background: none; border: none; cursor: pointer; }
        @media (max-width: 1024px) {
            .sidebar { position: fixed; left: -100%; top: 0; height: 100%; z-index: 2000; transition: left 0.3s ease-in-out; box-shadow: 4px 0px 15px rgba(0,0,0,0.1); }
            .sidebar.open { left: 0; }
            .mobile-menu-button { display: block; }
            .header-actions { display: none; }
            .content { padding: 32px; }
            .overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1999; }
            .overlay.open { display: block; }
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="logo">{{ config('app.name') }}</div>
        <div class="header-right-actions">
            <div class="header-actions">
                <a href="{{ route('login') }}" class="btn btn-secondary">Entrar no Portal</a>
                <a href="{{ route('association.register') }}" class="btn btn-primary">Criar Conta</a>
            </div>
            <button id="theme-toggle" title="Alternar tema">
                <svg class="icon sun-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                <svg class="icon moon-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </button>
        </div>
        <button class="mobile-menu-button" id="mobile-menu-btn">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12H21M3 6H21M3 18H21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </header>

    <div class="main-container">
        <div class="overlay" id="overlay"></div>
        <aside class="sidebar" id="sidebar">
            <nav class="sidebar-nav">
                <h4>Introdução</h4>
                <ul>
                    <li><a href="#primeiros-passos" class="active">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg>
                        Primeiros passos
                    </a></li>
                    <li><a href="#autenticacao">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                        Autenticação
                    </a></li>
                </ul>
                <h4>API</h4>
                <ul>
                    <li><a href="#account">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"><path d="M4 4h16v16H4z"></path></svg>
                        Conta
                    </a></li>
                    <li><a href="#user">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"><circle cx="12" cy="8" r="4"></circle><path d="M6 20c0-3.3137 2.6863-6 6-6s6 2.6863 6 6"></path></svg>
                        Usuário
                    </a></li>
                    <li><a href="#apikeys">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"><path d="M6 12l6-6 6 6-6 6-6-6z"></path></svg>
                        Chaves de API
                    </a></li>
                    <li><a href="#webhooks">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path></svg>
                        Webhooks
                    </a></li>
                    <li><a href="#two-factor-auth">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M7 9h10M7 13h6"></path></svg>
                        Autenticação 2FA
                    </a></li>
                    <li><a href="#seller">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"><path d="M3 6h18M3 10h18M3 14h18M3 18h18"></path></svg>
                        Vendedores
                    </a></li>
                    <li><a href="#transactions">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"><path d="M12 20v-6M6 20v-2M18 20v-4"></path></svg>
                        Transações
                    </a></li>
                    <li><a href="#withdrawals">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"><path d="M12 3v10M5 12l7 7 7-7"></path></svg>
                        Saques
                    </a></li>
                    <li><a href="#seller-wallet">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="M16 10h4v4h-4z"></path></svg>
                        Carteira do Vendedor
                    </a></li>
                    <li><a href="#disputes">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v8M8 12h8"></path></svg>
                        Disputas
                    </a></li>
                    <li><a href="#health">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"><path d="M4 12h4l2-3 2 6 2-3h4"></path></svg>
                        Status de Saúde
                    </a></li>
                </ul>
                <h4>Guias</h4>
                <ul>
                    <li><a href="#codigos-de-erro">
                        <svg class="icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        Códigos de Erro
                    </a></li>
                </ul>
            </nav>
        </aside>

        <main class="content">
            <section id="primeiros-passos">
                <h1>Primeiros passos</h1>
                <p>Para começar a utilizar as APIs da {{ config('app.name') }}, é essencial compreender os processos de integração dos serviços disponíveis e os requisitos para sua utilização.</p>
                <h3>1. Obtenha sua Chave de API</h3>
                <p>Acesse sua conta na plataforma, navegue até a seção <strong>Configurações > API</strong> e gere sua chave. Ela será usada para autenticar todas as suas requisições.</p>
            </section>

            <section id="autenticacao">
                <h2>Autenticação</h2>
                <p>A API utiliza o método <strong>Bearer Token</strong>. Inclua sua chave de API no cabeçalho <code>Authorization</code>. Requisições não autenticadas retornarão um erro <code>401 Unauthorized</code>.</p>
                <div class="code-block">
                    <div class="code-header"><span>Exemplo de Cabeçalho</span></div>
                    <div class="code-body">
                        <pre><code class="language-http">Authorization: Bearer SUA_CHAVE_SECRETA_DE_API
Accept: application/json
Content-Type: application/json</code></pre>
                    </div>
                </div>
            </section>

            <section id="account">
                <h2>Conta</h2>
                <div class="endpoint"><span class="endpoint-method post">POST</span><span class="endpoint-url">@{{BASE_URL}}/login</span></div>
                <div class="code-block"><div class="code-header"><span>REQUEST BODY</span></div><div class="code-body"><pre><code class="language-json">{
  "email": "seuemail@gmail.com",
  "password": "suasenha"
}</code></pre></div></div>
                <div class="endpoint"><span class="endpoint-method post">POST</span><span class="endpoint-url">@{{BASE_URL}}/login</span></div>
                <div class="code-block"><div class="code-header"><span>REQUEST BODY</span></div><div class="code-body"><pre><code class="language-json">{
  "email": "seuemail@witete.com",
  "password": "suasenha@123"
}</code></pre></div></div>
            </section>

            <section id="user">
                <h2>Usuário</h2>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/users/me</span></div>
                <div class="endpoint"><span class="endpoint-method post">PUT</span><span class="endpoint-url">@{{BASE_URL}}/users</span></div>
                <div class="code-block"><div class="code-header"><span>REQUEST BODY</span></div><div class="code-body"><pre><code class="language-json">{
  "name": "Gomes"
}</code></pre></div></div>
            </section>

            <section id="apikeys">
                <h2>Chaves de API</h2>
                <div class="endpoint"><span class="endpoint-method post">POST</span><span class="endpoint-url">@{{BASE_URL}}/apikeys</span></div>
                <div class="code-block"><div class="code-header"><span>REQUEST BODY</span></div><div class="code-body"><pre><code class="language-json">{
  "name": "John Doe 2"
}</code></pre></div></div>
                <div class="endpoint"><span class="endpoint-method get">DELETE</span><span class="endpoint-url">@{{BASE_URL}}/apikeys/:idApiKey</span></div>
            </section>

            <section id="webhooks">
                <h2>Webhooks</h2>
                <div class="endpoint"><span class="endpoint-method post">POST</span><span class="endpoint-url">@{{BASE_URL}}/webhooks</span></div>
                <div class="code-block"><div class="code-header"><span>REQUEST BODY</span></div><div class="code-body"><pre><code class="language-json">{
  "url": "https://seller.example.com/webhook-endpoint",
  "description": "Production webhook for transaction events",
  "eventType": "WITHDRAWAL"
}</code></pre></div></div>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/webhooks/{id}</span></div>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/webhooks</span></div>
                <div class="endpoint"><span class="endpoint-method get">DELETE</span><span class="endpoint-url">@{{BASE_URL}}/webhooks/{id}</span></div>
            </section>

            <section id="two-factor-auth">
                <h2>Autenticação 2FA</h2>
                <div class="endpoint"><span class="endpoint-method post">POST</span><span class="endpoint-url">@{{BASE_URL}}/two-factor-auth/active</span></div>
                <div class="endpoint"><span class="endpoint-method post">POST</span><span class="endpoint-url">@{{BASE_URL}}/two-factor-auth/validate</span></div>
                <div class="code-block"><div class="code-header"><span>REQUEST BODY</span></div><div class="code-body"><pre><code class="language-json">{
  "token": "John Doe"
}</code></pre></div></div>
            </section>

            <section id="seller">
                <h2>Vendedores</h2>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/sellers/:idSeller</span></div>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/sellers/:idSeller</span></div>
            </section>

            <section id="transactions">
                <h2>Transações</h2>
                <div class="endpoint"><span class="endpoint-method post">POST</span><span class="endpoint-url">@{{BASE_URL}}/transactions</span></div>
                <div class="code-block"><div class="code-header"><span>PIX</span></div><div class="code-body"><pre><code class="language-json">{
  "amount": 500,
  "method": "PIX",
  "customer": {
    "name": "John",
    "email": "john@example.com",
    "phone": "1234567890",
    "documentType": "CPF",
    "document": "70443438056"
  },
  "items": [
    {
      "title": "Wireless Mouse",
      "amount": 5999,
      "quantity": 1,
      "tangible": true,
      "externalRef": "item_abc123"
    }
  ]
}</code></pre></div></div>
                <div class="code-block"><div class="code-header"><span>BOLETO</span></div><div class="code-body"><pre><code class="language-json">{
  "amount": 500,
  "method": "BOLETO",
  "customer": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "documentType": "CPF",
    "document": "70443438056"
  },
  "items": [
    {
      "title": "Wireless Mouse",
      "amount": 500,
      "quantity": 1,
      "tangible": true,
      "externalRef": "item_abc123"
    }
  ]
}</code></pre></div></div>
                <div class="code-block"><div class="code-header"><span>CARTÃO DE CRÉDITO</span></div><div class="code-body"><pre><code class="language-json">{
  "amount": 500,
  "method": "CREDIT_CARD",
  "installments": 1,
  "customer": {
    "name": "Luiz Jordão",
    "email": "luiz@hotmail.com",
    "phone": "1234567890",
    "documentType": "CPF",
    "document": "36188196841"
  },
  "card": {
    "number": "4576010058016473 ",
    "holderName": "LUIZ J R SIMAO",
    "holderDocument": "36188196841",
    "expirationMonth": 7,
    "expirationYear": 2030,
    "cvv": "813"
  },
  "items": [
    {
      "title": "Wireless Mouse",
      "amount": 500,
      "quantity": 1,
      "tangible": true,
      "externalRef": "item_abc123"
    }
  ]
}</code></pre></div></div>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/transactions</span></div>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/transactions/:id</span></div>
            </section>

            <section id="withdrawals">
                <h2>Saques</h2>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/withdrawals</span></div>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/withdrawals/{id}</span></div>
                <div class="endpoint"><span class="endpoint-method post">POST</span><span class="endpoint-url">@{{BASE_URL}}/withdrawals</span></div>
                <div class="code-block"><div class="code-header"><span>REQUEST BODY</span></div><div class="code-body"><pre><code class="language-json">{
  "amount": 10000,
  "pixKey": "111111111111",
  "pixKeyType": "CNPJ",
  "method": "PIX",
  "metadata": {
    "sellerExternalRef": ""
  }
}</code></pre></div></div>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/withdrawals/search?metadataKey=sellerExternalRef&amp;metadataValue=VALOR</span></div>
            </section>

            <section id="seller-wallet">
                <h2>Carteira do Vendedor</h2>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/seller-wallet/balance</span></div>
            </section>

            <section id="disputes">
                <h2>Disputas</h2>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/disputes</span></div>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/disputes/:id</span></div>
                <div class="endpoint"><span class="endpoint-method post">POST</span><span class="endpoint-url">@{{BASE_URL}}/disputes/appeal</span></div>
                <div class="code-block"><div class="code-header"><span>REQUEST BODY</span></div><div class="code-body"><pre><code class="language-json">{
  "disputeId": "UUID",
  "appealReason": "Motivo do recurso"
}</code></pre></div></div>
            </section>

            <section id="health">
                <h2>Status de Saúde</h2>
                <div class="endpoint"><span class="endpoint-method get">GET</span><span class="endpoint-url">@{{BASE_URL}}/health</span></div>
            </section>

            <section id="criar-transacao">
                <h2>Criar Transação</h2>
                <p>Este endpoint permite iniciar uma nova transação de pagamento.</p>
                <div class="endpoint">
                    <span class="endpoint-method post">POST</span>
                    <span class="endpoint-url">/api/transactions</span>
                </div>
                <h3>Corpo da Requisição</h3>
                <div class="code-block">
                    <div class="code-header"><span>REQUEST BODY</span><button class="copy-btn">Copiar</button></div>
                    <div class="code-body">
                        <pre><code class="language-json">{
  <span class="token-key">"product_hash_id"</span>: <span class="token-string">"prod_a1b2c3d4e5f6"</span>,
  <span class="token-key">"customer"</span>: {
    <span class="token-key">"name"</span>: <span class="token-string">"João da Silva"</span>,
    <span class="token-key">"email"</span>: <span class="token-string">"joao.silva@email.com"</span>,
    <span class="token-key">"phone"</span>: <span class="token-string">"11999998888"</span>,
    <span class="token-key">"document"</span>: <span class="token-string">"12345678900"</span>
  }
}</code></pre>
                    </div>
                </div>
                <h3>Resposta de Sucesso</h3>
                <div class="code-block">
                    <div class="code-header"><span>RESPONSE (200 OK )</span><button class="copy-btn">Copiar</button></div>
                    <div class="code-body">
                        <pre><code class="language-json">{
  <span class="token-key">"success"</span>: <span class="token-boolean">true</span>,
  <span class="token-key">"message"</span>: <span class="token-string">"Transação iniciada com sucesso!"</span>,
  <span class="token-key">"transaction_id"</span>: <span class="token-string">"txn_a1b2c3d4e5"</span>,
  <span class="token-key">"pix_copy_paste"</span>: <span class="token-string">"00020126..."</span>,
  <span class="token-key">"product_name"</span>: <span class="token-string">"Nome do Produto Exemplo"</span>,
  <span class="token-key">"total_price"</span>: <span class="token-number">99.90</span>
}</code></pre>
                    </div>
                </div>
            </section>

            <section id="consultar-transacao">
                <h2>Consultar Transação</h2>
                <p>Use este endpoint para verificar o status de uma transação criada anteriormente.</p>
                <div class="endpoint">
                    <span class="endpoint-method get">GET</span>
                    <span class="endpoint-url">/api/transactions/{transactionId}</span>
                </div>
                <h3>Resposta de Sucesso</h3>
                <div class="code-block">
                    <div class="code-header"><span>RESPONSE (200 OK)</span><button class="copy-btn">Copiar</button></div>
                    <div class="code-body">
                        <pre><code class="language-json">{
  <span class="token-key">"success"</span>: <span class="token-boolean">true</span>,
  <span class="token-key">"transaction_id"</span>: <span class="token-string">"txn_a1b2c3d4e5"</span>,
  <span class="token-key">"status"</span>: <span class="token-string">"paid"</span>,
  <span class="token-key">"created_at"</span>: <span class="token-string">"2025-09-24T10:00:00Z"</span>,
  <span class="token-key">"updated_at"</span>: <span class="token-string">"2025-09-24T10:01:30Z"</span>,
  <span class="token-key">"product"</span>: { <span class="token-key">"name"</span>: <span class="token-string">"Nome do Produto Exemplo"</span> },
  <span class="token-key">"customer"</span>: { <span class="token-key">"name"</span>: <span class="token-string">"João da Silva"</span>, <span class="token-key">"email"</span>: <span class="token-string">"joao.silva@email.com"</span> },
  <span class="token-key">"total_price"</span>: <span class="token-number">99.90</span>
}</code></pre>
                    </div>
                </div>
            </section>

            <section id="codigos-de-erro">
                <h2>Códigos de Erro</h2>
                <p>A API utiliza códigos de status HTTP padrão para indicar o sucesso ou a falha de uma requisição.</p>
                <div class="code-block">
                    <div class="code-header"><span>Exemplos de Erros</span><button class="copy-btn">Copiar</button></div>
                    <div class="code-body">
                        <pre><code class="language-json"><span class="token-comment">// 401 Unauthorized</span>
{ <span class="token-key">"message"</span>: <span class="token-string">"Token de autenticação não fornecido."</span> }

<span class="token-comment">// 404 Not Found</span>
{ <span class="token-key">"success"</span>: <span class="token-boolean">false</span>, <span class="token-key">"message"</span>: <span class="token-string">"Transação não encontrada."</span> }

<span class="token-comment">// 422 Unprocessable Entity</span>
{
  <span class="token-key">"message"</span>: <span class="token-string">"Dados inválidos."</span>,
  <span class="token-key">"errors"</span>: {
    <span class="token-key">"product_hash_id"</span>: [<span class="token-string">"O campo product hash id é obrigatório."</span>]
  }
}</code></pre>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function ( ) {
            // --- Lógica do Tema Dark/Light ---
            const themeToggle = document.getElementById('theme-toggle');
            const html = document.documentElement;

            // Aplica o tema salvo ou o do sistema
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                html.classList.toggle('dark', savedTheme === 'dark');
            } else {
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                html.classList.toggle('dark', prefersDark);
            }

            // Alterna o tema ao clicar no botão
            themeToggle.addEventListener('click', () => {
                html.classList.toggle('dark');
                localStorage.setItem('theme', html.classList.contains('dark') ? 'dark' : 'light');
            });

            // --- Lógica do Menu Mobile ---
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const navLinks = document.querySelectorAll('.sidebar-nav a');
            function toggleMenu() {
                sidebar.classList.toggle('open');
                overlay.classList.toggle('open');
            }
            mobileMenuBtn.addEventListener('click', toggleMenu);
            overlay.addEventListener('click', toggleMenu);
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (sidebar.classList.contains('open')) toggleMenu();
                });
            });

            // --- Lógica do Scrollspy ---
            const sections = document.querySelectorAll('main section');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.getAttribute('id');
                        navLinks.forEach(link => {
                            link.classList.toggle('active', link.getAttribute('href') === '#' + id);
                        });
                    }
                });
            }, { rootMargin: '-20% 0px -70% 0px' });
            sections.forEach(section => observer.observe(section));

            // --- Lógica do Botão Copiar ---
            document.querySelectorAll('.copy-btn').forEach(button => {
                button.addEventListener('click', () => {
                    const pre = button.closest('.code-block').querySelector('pre');
                    const text = pre.innerText;
                    navigator.clipboard.writeText(text).then(() => {
                        button.innerHTML = 'Copiado!';
                        setTimeout(() => { button.innerHTML = 'Copiar'; }, 2000);
                    }).catch(err => { console.error('Falha ao copiar: ', err); });
                });
            });
        });
    </script>
</body>
</html>
