<?php
$path = resource_path('views/movelo01.html');
$html = file_exists($path) ? file_get_contents($path) : '';
$replacements = [
    'Witetec' => 'Izuspay',
    'witetec.com' => 'izuspay.com',
    'app.witetec.com' => 'app.izuspay.com',
    'https://app.witetec.com/login' => route('login'),
    'title>Witetec' => 'title>Izuspay',
];
foreach ($replacements as $search => $replace) {
    $html = str_replace($search, $replace, $html);
}
echo $html;
exit;
?>
<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Izus Pay - Gateway de Pagamentos e Api pix</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                        'mono': ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        'primary': '#0066FF',
                        'primary-dark': '#0052CC',
                        'primary-light': '#E6F2FF',
                        'secondary': '#00D4FF',
                        'accent': '#FF6B35',
                        'gray-50': '#FAFAFA',
                        'gray-100': '#F5F5F5',
                        'gray-200': '#E5E5E5',
                        'gray-300': '#D4D4D4',
                        'gray-400': '#A3A3A3',
                        'gray-500': '#737373',
                        'gray-600': '#525252',
                        'gray-700': '#404040',
                        'gray-800': '#262626',
                        'gray-900': '#171717',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'slide-up': 'slideUp 0.5s ease-out',
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'scale-in': 'scaleIn 0.4s ease-out',
                        'bounce-gentle': 'bounceGentle 2s ease-in-out infinite',
                        'transaction': 'transaction 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' },
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(100px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' },
                        },
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        scaleIn: {
                            '0%': { transform: 'scale(0.9)', opacity: '0' },
                            '100%': { transform: 'scale(1)', opacity: '1' },
                        },
                        bounceGentle: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' },
                        },
                        transaction: {
                            '0%': { transform: 'translateX(-100px)', opacity: '0' },
                            '50%': { transform: 'translateX(0)', opacity: '1' },
                            '100%': { transform: 'translateX(100px)', opacity: '0' },
                        },
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-white text-gray-900 font-inter overflow-x-hidden">
    <nav class="fixed top-0 w-full z-50 bg-white/80 backdrop-blur-xl border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-8">
                    <div class="text-2xl font-bold text-primary">
                        Izus Pay
                    </div>
                    <div class="hidden md:flex space-x-8">
                        <a href="{{ route('show.docs') }}"  class="text-gray-600 hover:text-primary transition-colors">Documentação</a>
                        <a href="#support" class="text-gray-600 hover:text-primary transition-colors">Suporte</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-gray-600 hover:text-primary transition-colors">Login</a>
                    <a href="{{ route('show.docs') }}" class="bg-primary hover:bg-primary-dark text-white px-6 py-2 rounded-lg font-medium transition-all duration-300">
                        Integrar API
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <section class="relative pt-24 pb-20 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-8 leading-tight text-gray-900">
                    Receba seus pagamentos
                    <br>
                    <span class="text-primary">em segundos</span>
                </h1>
                <p class="text-xl md:text-2xl text-gray-600 mb-12 max-w-3xl mx-auto leading-relaxed">
                    PIX instantâneo, cartões sem complicação e taxas transparentes. 
                    Tudo em uma plataforma simples e segura.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16">
                    <a href="{{ route('association.register') }}" class="bg-primary hover:bg-primary-dark text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all duration-300 transform hover:scale-105">
                        Criar conta agora
                    </a>
                    <button onclick="openCalculator()" class="border border-gray-300 hover:border-primary text-gray-700 hover:text-primary px-8 py-4 rounded-lg font-semibold transition-all duration-300">
                        <i class="fas fa-calculator mr-2"></i>
                        Falar com gerente de contas
                    </button>
                </div>

                <div class="bg-gray-50 rounded-2xl p-8 max-w-4xl mx-auto">
                    <h3 class="text-lg font-semibold text-gray-700 mb-6">Transações em tempo real</h3>
                    <div class="space-y-3" id="transactionFeed">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-6 text-gray-900">
                    Integração em <span class="text-primary">5 minutos</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Nossa API é simples e poderosa. Veja como é fácil começar a aceitar pagamentos.
                </p>
            </div>

            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold">Criar Pagamento PIX</h3>
                            <div class="flex space-x-2">
                                <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                                <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                            </div>
                        </div>
                        <pre class="text-sm text-gray-700 font-mono bg-gray-50 p-4 rounded-lg overflow-x-auto"><code>{
  "amount": 10000,
  "description": "Pedido #123",
  "customer": {
    "name": "João Silva",
    "email": "joao@email.com"
  }
}</code></pre>
                        <button onclick="simulateApiCall()" class="mt-4 bg-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary-dark transition-colors">
                            <i class="fas fa-play mr-2"></i>
                            Executar
                        </button>
                    </div>
                </div>

                <div>
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-gray-200">
                        <h3 class="text-lg font-semibold mb-4">Resposta da API</h3>
                        <div id="apiResponse" class="text-sm text-gray-500 font-mono bg-gray-50 p-4 rounded-lg">
                            Clique em "Executar" para ver a resposta
                        </div>
                        <div class="mt-4 flex items-center space-x-4">
                            <div class="flex items-center text-green-600">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span class="text-sm">200 OK</span>
                            </div>
                            <div class="text-sm text-gray-500" id="responseTime">
                                Tempo: --ms
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-6 text-gray-900">
                    Todos os métodos de <span class="text-primary">pagamento</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Ofereça a melhor experiência para seus clientes com múltiplas opções de pagamento.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gradient-to-br from-primary-light to-white border border-primary/20 rounded-2xl p-8 text-center hover:shadow-lg transition-all duration-300">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" width="800px" height="800px" viewBox="0 0 16 16"><path d="M11.917 11.71a2.046 2.046 0 0 1-1.454-.602l-2.1-2.1a.4.4 0 0 0-.551 0l-2.108 2.108a2.044 2.044 0 0 1-1.454.602h-.414l2.66 2.66c.83.83 2.177.83 3.007 0l2.667-2.668h-.253zM4.25 4.282c.55 0 1.066.214 1.454.602l2.108 2.108a.39.39 0 0 0 .552 0l2.1-2.1a2.044 2.044 0 0 1 1.453-.602h.253L9.503 1.623a2.127 2.127 0 0 0-3.007 0l-2.66 2.66h.414z"/><path d="m14.377 6.496-1.612-1.612a.307.307 0 0 1-.114.023h-.733c-.379 0-.75.154-1.017.422l-2.1 2.1a1.005 1.005 0 0 1-1.425 0L5.268 5.32a1.448 1.448 0 0 0-1.018-.422h-.9a.306.306 0 0 1-.109-.021L1.623 6.496c-.83.83-.83 2.177 0 3.008l1.618 1.618a.305.305 0 0 1 .108-.022h.901c.38 0 .75-.153 1.018-.421L7.375 8.57a1.034 1.034 0 0 1 1.426 0l2.1 2.1c.267.268.638.421 1.017.421h.733c.04 0 .079.01.114.024l1.612-1.612c.83-.83.83-2.178 0-3.008z"/></svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">PIX</h3>
                    <p class="text-gray-600 mb-6">Pagamentos instantâneos 24/7 com QR Code automático</p>
                    <div class="text-3xl font-bold text-primary">5,99% + R$0,99</div>
                    <div class="text-sm text-gray-500">por transação</div>
                </div>

                <div class="bg-gradient-to-br from-gray-50 to-white border border-gray-200 rounded-2xl p-8 text-center hover:shadow-lg transition-all duration-300">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-credit-card text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Cartões</h3>
                    <p class="text-gray-600 mb-6">Crédito e débito com parcelamento sem juros</p>
                    <div class="text-3xl font-bold text-gray-700">7,99% + R$1,99</div>
                    <div class="text-sm text-gray-500">débito / 4,9% crédito</div>
                </div>

                <div class="bg-gradient-to-br from-green-50 to-white border border-green-200 rounded-2xl p-8 text-center hover:shadow-lg transition-all duration-300">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-barcode text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Boleto</h3>
                    <p class="text-gray-600 mb-6">Boleto bancário com vencimento em 2 dias</p>
                    <div class="text-3xl font-bold text-green-600">R$ 3,90</div>
                    <div class="text-sm text-gray-500">por boleto pago</div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-6 text-gray-900">
                    Para todos os <span class="text-primary">negócios</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Soluções personalizadas para cada segmento
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="bg-white border border-gray-200 rounded-2xl p-8 hover:shadow-lg transition-all duration-300">
                    <i class="fas fa-shopping-cart text-4xl text-primary mb-6"></i>
                    <h3 class="text-xl font-bold mb-4 text-gray-900">E-commerce</h3>
                    <p class="text-gray-600">Checkout otimizado e recuperação de carrinho abandonado.</p>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-8 hover:shadow-lg transition-all duration-300">
                    <i class="fas fa-utensils text-4xl text-primary mb-6"></i>
                    <h3 class="text-xl font-bold mb-4 text-gray-900">Food & Delivery</h3>
                    <p class="text-gray-600">Pagamentos rápidos e split de comissões automático.</p>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-8 hover:shadow-lg transition-all duration-300">
                    <i class="fas fa-graduation-cap text-4xl text-primary mb-6"></i>
                    <h3 class="text-xl font-bold mb-4 text-gray-900">Educação</h3>
                    <p class="text-gray-600">Assinaturas recorrentes e parcelamento sem juros.</p>
                </div>

                <div class="bg-white border border-gray-200 rounded-2xl p-8 hover:shadow-lg transition-all duration-300">
                    <i class="fas fa-heartbeat text-4xl text-primary mb-6"></i>
                    <h3 class="text-xl font-bold mb-4 text-gray-900">Saúde</h3>
                    <p class="text-gray-600">Conformidade LGPD e integração com sistemas médicos.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="support" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold mb-6 text-gray-900">
                    Suporte <span class="text-primary">excepcional</span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Nossa equipe está sempre pronta para ajudar você
                </p>
            </div>

            <div class="text-center">
                <div class="w-20 h-20 bg-primary rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-headset text-3xl text-white"></i>
                </div>
                <h3 class="text-2xl font-bold mb-4 text-gray-900">Agente de contas</h3>
                <p class="text-gray-600 mb-8">Agente personalizado para seu negócio.</p>

                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <button class="bg-primary hover:bg-primary-dark text-white px-8 py-4 rounded-lg font-semibold transition-all duration-300">
                        <i class="fas fa-comments mr-2"></i>
                        Iniciar Chat
                    </button>
                    <button onclick="openApiModal()" class="border border-gray-300 hover:border-primary text-gray-700 hover:text-primary px-8 py-4 rounded-lg font-semibold transition-all duration-300">
                        <i class="fas fa-book mr-2"></i>
                        Ver Documentação
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-primary">
        <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-8 text-white">
                Pronto para revolucionar
                <br>
                seus pagamentos?
            </h2>
            <p class="text-xl text-blue-100 mb-12 leading-relaxed">
                Junte-se a milhares de empresas que já escolheram o Izus Pay. 
                Comece gratuitamente e veja a diferença em minutos.
            </p>
            <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                <a href="#" class="bg-white hover:bg-gray-100 text-primary px-8 py-4 rounded-lg font-semibold text-lg transition-all duration-300 transform hover:scale-105">
                    Começar Agora
                </a>
            </div>
        </div>
    </section>

    <footer class="bg-gray-50 border-t border-gray-200 py-16">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <div>
                    <div class="text-3xl font-bold text-primary mb-6">
                        Izus Pay
                    </div>
                    <p class="text-gray-600 mb-6">
                        O gateway de pagamentos mais avançado do Brasil. Seguro, rápido e confiável.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center hover:bg-primary hover:text-white transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center hover:bg-primary hover:text-white transition-colors">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center hover:bg-primary hover:text-white transition-colors">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-6 text-gray-900">Produto</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-600 hover:text-primary transition-colors">Recursos</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary transition-colors">Preços</a></li>
                        <li><a href="{{ route('show.docs') }}"  class="text-gray-600 hover:text-primary transition-colors">Integrações</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary transition-colors">Changelog</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-lg font-semibold mb-6 text-gray-900">Empresa</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-600 hover:text-primary transition-colors">Sobre</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary transition-colors">Blog</a></li>
                        <li><a href="#" class="text-gray-600 hover:text-primary transition-colors">Carreiras</a></li>
                        <li><a href="#support" class="text-gray-600 hover:text-primary transition-colors">Contato</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-8 flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-600 mb-4 md:mb-0">
                    © 2025 Izus Pay. Todos os direitos reservados.
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-600 hover:text-primary transition-colors">Privacidade</a>
                    <a href="#" class="text-gray-600 hover:text-primary transition-colors">Termos</a>
                    <a href="#" class="text-gray-600 hover:text-primary transition-colors">Cookies</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Live Transaction Feed
        const transactions = [
            { name: "João Silva", amount: "R$ 150,00", method: "PIX", status: "approved" },
            { name: "Maria Santos", amount: "R$ 89,90", method: "Cartão", status: "approved" },
            { name: "Pedro Costa", amount: "R$ 299,00", method: "PIX", status: "approved" },
            { name: "Ana Oliveira", amount: "R$ 45,50", method: "Boleto", status: "pending" },
            { name: "Carlos Lima", amount: "R$ 199,99", method: "Cartão", status: "approved" },
        ];

        function updateTransactionFeed() {
            const feed = document.getElementById('transactionFeed');
            const randomTransaction = transactions[Math.floor(Math.random() * transactions.length)];
            
            const transactionElement = document.createElement('div');
            transactionElement.className = 'flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200 animate-slide-up';
            transactionElement.innerHTML = `
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white text-sm"></i>
                    </div>
                    <div>
                        <div class="font-medium text-gray-900">${randomTransaction.name}</div>
                        <div class="text-sm text-gray-500">${randomTransaction.method}</div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-semibold text-gray-900">${randomTransaction.amount}</div>
                    <div class="text-sm ${randomTransaction.status === 'approved' ? 'text-green-600' : 'text-yellow-600'}">
                        ${randomTransaction.status === 'approved' ? 'Aprovado' : 'Pendente'}
                    </div>
                </div>
            `;
            
            feed.insertBefore(transactionElement, feed.firstChild);
            
            // Keep only 5 transactions
            while (feed.children.length > 5) {
                feed.removeChild(feed.lastChild);
            }
        }

        // Update transaction feed every 3 seconds
        setInterval(updateTransactionFeed, 3000);
        updateTransactionFeed(); // Initial load

        // Counter Animation
        function animateCounters() {
            const counters = document.querySelectorAll('[data-counter]');
            counters.forEach(counter => {
                const target = parseFloat(counter.getAttribute('data-counter'));
                const increment = target / 100;
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    if (target === 99.9) {
                        counter.textContent = current.toFixed(1);
                    } else {
                        counter.textContent = Math.floor(current).toLocaleString();
                    }
                }, 20);
            });
        }

        // Intersection Observer for animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (entry.target.querySelector('[data-counter]')) {
                        animateCounters();
                    }
                }
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const statsSection = document.querySelector('[data-counter]')?.closest('section');
            if (statsSection) {
                observer.observe(statsSection);
            }
        });

        // API Simulation
        function simulateApiCall() {
            const responseDiv = document.getElementById('apiResponse');
            const timeDiv = document.getElementById('responseTime');
            
            responseDiv.innerHTML = '<div class="text-gray-500">Processando...</div>';
            
            setTimeout(() => {
                const response = {
                    "id": "pix_" + Math.random().toString(36).substr(2, 9),
                    "status": "pending",
                    "amount": 10000,
                    "qr_code": "00020126580014br.gov.bcb.pix...",
                    "qr_code_url": "https://api.izuspay.com/qr/pix_123.png",
                    "expires_at": new Date(Date.now() + 3600000).toISOString()
                };
                
                responseDiv.innerHTML = `<pre class="text-gray-700">${JSON.stringify(response, null, 2)}</pre>`;
                timeDiv.textContent = `Tempo: ${Math.floor(Math.random() * 200 + 50)}ms`;
            }, 1000);
        }

        // Calculator Functions
        function openCalculator() {
            document.getElementById('calculatorModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeCalculator() {
            document.getElementById('calculatorModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function calculateSavings() {
            const volume = parseFloat(document.getElementById('monthlyVolume').value) || 0;
            const currentRate = parseFloat(document.getElementById('currentRate').value) || 0;
            const izusRate = 2.4; // Izus Pay rate
            
            const currentCost = volume * (currentRate / 100);
            const izusCost = volume * (izusRate / 100);
            const monthlySavings = currentCost - izusCost;
            const yearlySavings = monthlySavings * 12;
            const savingsPercentage = currentRate > 0 ? ((monthlySavings / currentCost) * 100) : 0;
            
            document.getElementById('monthlySavings').textContent = `R$ ${monthlySavings.toLocaleString('pt-BR', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`;
            document.getElementById('yearlySavings').textContent = `R$ ${yearlySavings.toLocaleString('pt-BR', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`;
            document.getElementById('savingsPercentage').textContent = `${savingsPercentage.toFixed(1)}%`;
        }

        // API Modal Functions
        function openApiModal() {
            document.getElementById('apiModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeApiModal() {
            document.getElementById('apiModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Show feedback
                const button = event.target;
                const originalIcon = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i>';
                button.classList.add('text-green-600');
                
                setTimeout(() => {
                    button.innerHTML = originalIcon;
                    button.classList.remove('text-green-600');
                    button.classList.add('text-primary');
                }, 1000);
            });
        }

        // Close modals on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeApiModal();
                closeCalculator();
            }
        });

        // Close modals on backdrop click
        document.getElementById('apiModal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeApiModal();
            }
        });

        document.getElementById('calculatorModal').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeCalculator();
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
