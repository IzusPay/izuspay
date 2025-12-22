<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AssociationController as AdminAssociationController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\FinancialController;
use App\Http\Controllers\Admin\GatewayController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SaleController as AdminSaleController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Associacao\ApiKeysController;
use App\Http\Controllers\Associacao\BankAccountController;
use App\Http\Controllers\Associacao\BannerController;
use App\Http\Controllers\Associacao\ConfiguracoesController;
use App\Http\Controllers\Associacao\ConversationController;
use App\Http\Controllers\Associacao\DashboardController;
use App\Http\Controllers\Associacao\DashboardSettingsController;
use App\Http\Controllers\Associacao\DocumentationController;
use App\Http\Controllers\Associacao\DocumentTypeController;
use App\Http\Controllers\Associacao\FinanceiroController;
use App\Http\Controllers\Associacao\MessageController;
use App\Http\Controllers\Associacao\NewsController;
use App\Http\Controllers\Associacao\PlanController as AssociacaoPlanController;
use App\Http\Controllers\Associacao\ProductController;
use App\Http\Controllers\Associacao\RelatoriosController;
use App\Http\Controllers\Associacao\SaleController;
use App\Http\Controllers\Associacao\UserController as AssociacaoUserController;
use App\Http\Controllers\Associacao\WithdrawalController;
use App\Http\Controllers\AssociationController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\Cliente\DocumentosController;
use App\Http\Controllers\Cliente\NewsController as ClienteNewsController;
use App\Http\Controllers\Cliente\PagamentoController;
use App\Http\Controllers\CreatorProfileController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PublicCreatorController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\RedirectByProfile;
use Illuminate\Support\Facades\Route;

Route::get('/criador/{username}', [PublicCreatorController::class, 'show'])->name('public.creator.profile');
Route::post('/criador/{username}/assinar/{planId}', [PublicCreatorController::class, 'subscribe'])->name('public.creator.subscribe');

Route::get('/page/{slug}', [PublicPageController::class, 'showAssociationLp'])->name('lp.show');

Route::redirect('/', '/login')->name('home');

Route::get('/termos', function () {
    return view('terms');
})->name('termos');

Route::get('/privacidade', function () {
    return view('privacy');
})->name('privacidade');

Route::get('/cookies', function () {
    return view('cookies');
});

Route::get('/docs', [PublicPageController::class, 'docs'])->name('show.docs');

// Rotas de Checkout

Route::get('/checkout/{hash_id}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout/{hash_id}', [CheckoutController::class, 'store'])->name('checkout.store');

Route::get('/checkout/success/{sale}', [CheckoutController::class, 'showSuccess'])->name('checkout.success');

Route::get('/checkout/product/{hash_id}', [CheckoutController::class, 'show'])->name('checkout.product.show');
Route::post('/checkout/product/{hash_id}', [CheckoutController::class, 'store'])->name('checkout.product.store');

Route::get('/cadastro-associacao', [AssociationController::class, 'showRegistrationForm'])->name('association.register.form');
Route::post('/cadastro-associacao', [AssociationController::class, 'register'])->name('association.register');

Route::get('/membro/login', function () {
    return view('auth.member-login');
})->name('member.login');

Route::middleware('auth')->group(function () {

    Route::middleware('admin')->group(function () {
        Route::get('/admin/associacoes', [AssociationController::class, 'index'])->name('admin.associations.index');
        Route::get('/admin/associacoes/{association}', [AssociationController::class, 'show'])->name('admin.associations.show');
        Route::post('/admin/associacoes/{association}/aprovar', [AssociationController::class, 'approve'])->name('admin.associations.approve');
        Route::post('/admin/associacoes/{association}/rejeitar', [AssociationController::class, 'reject'])->name('admin.associations.reject');
    });

    Route::put('/admin/documentos/{documentation}/approve', [DocumentationController::class, 'approve'])->name('admin.documents.approve');
    Route::put('/admin/documentos/{documentation}/reject', [DocumentationController::class, 'reject'])->name('admin.documents.reject');
    Route::get('/admin/documentos/usuario/{user}', [DocumentationController::class, 'showDocs'])->name('admin.documentos.show');
    Route::get('/admin/documentos/pendentes', [DocumentationController::class, 'pendingDocs'])->name('admin.documentos.pending');
});

Route::middleware(['auth', RedirectByProfile::class])->prefix('associacao')->group(function () {

    Route::post('/configuracoes/documentos/{documentType}/upload', [ConfiguracoesController::class, 'upload'])
        ->name('associacao.documents.upload');

    Route::get('/integracoes', [ApiKeysController::class, 'index'])->name('api-keys.index');
    Route::get('/integracoes/api-keys/create', [ApiKeysController::class, 'create'])->name('api-keys.create');
    Route::post('/integracoes/api-keys', [ApiKeysController::class, 'store'])->name('api-keys.store');
    Route::delete('/integracoes/api-keys/{apiToken}', [ApiKeysController::class, 'destroy'])->name('api-keys.destroy');
    Route::patch('/integracoes/api-keys/{apiToken}/toggle', [ApiKeysController::class, 'toggle'])->name('api-keys.toggle');

    Route::get(
        '/integracoes/api-keys/{apiToken}/reveal',
        [ApiKeysController::class, 'reveal']
    )->name('api-keys.reveal');

    Route::post('/dashboard/save-layout', [DashboardSettingsController::class, 'saveLayout'])->name('dashboard.saveLayout');

    Route::get('/inicio', [DashboardController::class, 'index'])->name('associacao.dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('usuarios', [AssociacaoUserController::class, 'index'])->name('associacao.users.index');
    Route::get('usuarios/cadastrar', [AssociacaoUserController::class, 'create'])->name('associacao.users.create');
    Route::post('usuarios/cadastrar', [AssociacaoUserController::class, 'store'])->name('associacao.users.store');
    Route::get('usuarios/editar/{user}', [AssociacaoUserController::class, 'edit'])->name('associacao.users.edit');
    Route::post('usuarios/editar/{user}', [AssociacaoUserController::class, 'update'])->name('associacao.users.update');
    Route::delete('usuario/excluir/{user}', [AssociacaoUserController::class, 'destroy'])->name('associacao.users.destroy');

    Route::get('/relatorios', [RelatoriosController::class, 'index'])->name('associacao.relatorios.index');

    Route::get('usuarios', [AssociacaoUserController::class, 'index'])->name('associacao.users.index');

    Route::prefix('messages')->name('associacao.messages.')->group(function () {
        // Página principal de mensagens
        Route::get('/', function () {
            return view('associacao.messages.index');
        })->name('index');

        // Página de conversa específica
        Route::get('/conversation/{conversation}', function ($conversation) {
            return view('associacao.messages.conversation', compact('conversation'));
        })->name('conversation');

        // Página para nova conversa
        Route::get('/new', function () {
            return view('messages.new');
        })->name('new');
    });

    Route::get('/noticias', [NewsController::class, 'index'])->name('associacao.news.index');
    Route::get('/noticias/create', [NewsController::class, 'create'])->name('associacao.news.create');
    Route::post('/noticias', [NewsController::class, 'store'])->name('associacao.news.store');
    Route::get('/noticias/{news}', [NewsController::class, 'show'])->name('associacao.news.show');
    Route::get('/noticias/{news}/edit', [NewsController::class, 'edit'])->name('associacao.news.edit');
    Route::put('/noticias/{news}', [NewsController::class, 'update'])->name('associacao.news.update');
    Route::delete('/noticias/{news}', [NewsController::class, 'destroy'])->name('associacao.news.destroy');
    Route::patch('/noticias/{news}/toggle-publish', [NewsController::class, 'togglePublish'])->name('associacao.news.toggle-publish');
    Route::patch('/noticias/{news}/toggle-featured', [NewsController::class, 'toggleFeatured'])->name('associacao.news.toggle-featured');

    Route::get('/products', [ProductController::class, 'index'])->name('associacao.products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('associacao.products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('associacao.products.store');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('associacao.products.show');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('associacao.products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('associacao.products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('associacao.products.destroy');

    Route::get('/plans', [AssociacaoPlanController::class, 'index'])->name('associacao.plans.index');
    Route::get('/plans/create', [AssociacaoPlanController::class, 'create'])->name('associacao.plans.create');
    Route::post('/plans', [AssociacaoPlanController::class, 'store'])->name('associacao.plans.store');
    Route::get('/plans/{plan}/edit', [AssociacaoPlanController::class, 'edit'])->name('associacao.plans.edit');
    Route::put('/plans/{plan}', [AssociacaoPlanController::class, 'update'])->name('associacao.plans.update');
    Route::delete('/plans/{plan}', [AssociacaoPlanController::class, 'destroy'])->name('associacao.plans.destroy');

    Route::get('/vendas', [SaleController::class, 'index'])->name('associacao.vendas.index');
    Route::get('/disputas', [\App\Http\Controllers\Associacao\DisputeController::class, 'index'])->name('associacao.disputas.index');
    Route::get('/webhooks', [\App\Http\Controllers\Associacao\WebhooksController::class, 'index'])->name('associacao.webhooks.index');
    Route::post('/webhooks', [\App\Http\Controllers\Associacao\WebhooksController::class, 'store'])->name('associacao.webhooks.store');
    Route::delete('/webhooks/{webhookEndpoint}', [\App\Http\Controllers\Associacao\WebhooksController::class, 'destroy'])->name('associacao.webhooks.destroy');
    Route::patch('/webhooks/{webhookEndpoint}/toggle', [\App\Http\Controllers\Associacao\WebhooksController::class, 'toggle'])->name('associacao.webhooks.toggle');
    Route::get('/vendas/create', [SaleController::class, 'create'])->name('associacao.vendas.create');
    Route::post('/vendas', [SaleController::class, 'store'])->name('associacao.vendas.store');
    Route::get('/vendas/{sale}', [SaleController::class, 'show'])->name('associacao.vendas.show'); // Show já existia
    Route::get('/vendas/{sale}/edit', [SaleController::class, 'edit'])->name('associacao.vendas.edit');
    Route::put('/vendas/{sale}', [SaleController::class, 'update'])->name('associacao.vendas.update');
    Route::delete('/vendas/{sale}', [SaleController::class, 'destroy'])->name('associacao.vendas.destroy');
    Route::patch('/vendas/{sale}/status', [SaleController::class, 'updateStatus'])->name('associacao.vendas.update-status'); // Já existia

    Route::get('/banners', [BannerController::class, 'index'])->name('associacao.banners.index');
    Route::get('/banners/create', [BannerController::class, 'create'])->name('associacao.banners.create');
    Route::post('/banners', [BannerController::class, 'store'])->name('associacao.banners.store');
    Route::get('/banners/{banner}/edit', [BannerController::class, 'edit'])->name('associacao.banners.edit');
    Route::put('/banners/{banner}', [BannerController::class, 'update'])->name('associacao.banners.update');
    Route::delete('/banners/{banner}', [BannerController::class, 'destroy'])->name('associacao.banners.destroy');

    Route::get('/configuracoes', [ConfiguracoesController::class, 'edit'])->name('associacao.configuracoes.edit');
    Route::put('/configuracoes', [ConfiguracoesController::class, 'update'])->name('associacao.configuracoes.update');

    Route::post('configuracoes/regenerate-api-token', [ConfiguracoesController::class, 'regenerateApiToken'])->name('associacao.configuracoes.regenerateApiToken');

    Route::prefix('financeiro')->name('associacao.financeiro.')->group(function () {
        // Dashboard Financeiro Principal
        Route::get('/', [FinanceiroController::class, 'index'])->name('index');

        // Gestão de Contas Bancárias
        Route::prefix('contas-bancarias')->name('bank-accounts.')->group(function () {
            Route::get('/', [BankAccountController::class, 'index'])->name('index');
            Route::get('/create', [BankAccountController::class, 'create'])->name('create');
            Route::post('/', [BankAccountController::class, 'store'])->name('store');
            Route::get('/{bankAccount}/edit', [BankAccountController::class, 'edit'])->name('edit');
            Route::put('/{bankAccount}', [BankAccountController::class, 'update'])->name('update');
            Route::delete('/{bankAccount}', [BankAccountController::class, 'destroy'])->name('destroy');
        });

        // Gestão de Saques
        Route::prefix('saques')->name('withdrawals.')->group(function () {
            Route::get('/', [WithdrawalController::class, 'index'])->name('index');
            Route::post('/', [WithdrawalController::class, 'store'])->name('store');
            Route::patch('/{withdrawal}/status', [WithdrawalController::class, 'updateStatus'])->name('update-status');
        });
    });

    Route::get('/documentos', [DocumentationController::class, 'index'])->name('associacao.documentos.index');
    Route::post('/documentos', [DocumentationController::class, 'store'])->name('associacao.documentos.store');
    Route::get('/documentos/revisao', [DocumentationController::class, 'review'])->name('associacao.documentos.review');

    Route::prefix('documentos/tipos')->name('associacao.document-types.')->group(function () {
        Route::get('/', [DocumentTypeController::class, 'index'])->name('index');
        Route::get('/create', [DocumentTypeController::class, 'create'])->name('create');
        Route::post('/', [DocumentTypeController::class, 'store'])->name('store');
        Route::get('/{documentType}/edit', [DocumentTypeController::class, 'edit'])->name('edit');
        Route::put('/{documentType}', [DocumentTypeController::class, 'update'])->name('update');
        Route::delete('/{documentType}', [DocumentTypeController::class, 'destroy'])->name('destroy');
    });

    // Rotas de Aprovação/Reprovação ajustadas para os novos parâmetros
    // Route::patch('/documentos/usuario/{user}/aprovar/{documentType}', [DocumentationController::class, 'approve'])->name('associacao.documentos.approve');
    // Route::patch('/documentos/usuario/{user}/reprovar/{documentType}', [DocumentationController::class, 'reject'])->name('associacao.documentos.reject');

});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::post('/create-subscription', [SubscriptionController::class, 'create'])->name('sunscription.create');

Route::domain('{subdomain}.copywave.com.br')->group(function () {
    Route::get('/', function ($subdomain) {
        return "Você acessou o subdomínio: $subdomain";
    });
});

Route::middleware(['auth', RedirectByProfile::class])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/webhooks', [\App\Http\Controllers\Admin\WebhooksController::class, 'index'])->name('admin.webhooks.index');
    Route::patch('/webhooks/{delivery}/approve', [\App\Http\Controllers\Admin\WebhooksController::class, 'approve'])->name('admin.webhooks.approve');
    Route::patch('/webhooks/{delivery}/reject', [\App\Http\Controllers\Admin\WebhooksController::class, 'reject'])->name('admin.webhooks.reject');
    Route::post('/webhooks/config', [\App\Http\Controllers\Admin\WebhooksController::class, 'saveConfig'])->name('admin.webhooks.config.save');

    Route::get('/organizacoes', [OrganizationController::class, 'index'])->name('admin.organizacoes.index');
    Route::get('/organizacoes/create', [OrganizationController::class, 'create'])->name('admin.organizacoes.create');
    Route::post('/organizacoes', [OrganizationController::class, 'store'])->name('admin.organizacoes.store');
    Route::get('/organizacoes/{organizacao}/edit', [OrganizationController::class, 'edit'])->name('admin.organizacoes.edit');
    Route::put('/organizacoes/{organizacao}', [OrganizationController::class, 'update'])->name('admin.organizacoes.update');
    Route::delete('/organizacoes/{organizacao}', [OrganizationController::class, 'destroy'])->name('admin.organizacoes.destroy');

    Route::get('/gateways', [GatewayController::class, 'index'])->name('admin.gateways.index');
    Route::get('/gateways/criar', [GatewayController::class, 'create'])->name('admin.gateways.create');
    Route::post('/gateways/criar', [GatewayController::class, 'store'])->name('admin.gateways.store');
    Route::get('/gateways/editar/{gateway}', [GatewayController::class, 'edit'])->name('admin.gateways.edit');
    Route::put('/gateways/editar/{gateway}', [GatewayController::class, 'update'])->name('admin.gateways.update');
    Route::delete('/gateways/remover/{id}', [GatewayController::class, 'destroy'])->name('admin.gateways.destroy');

    Route::get('/perfis', [PerfilController::class, 'index'])->name('admin.perfis.index');
    Route::get('/perfis/create', [PerfilController::class, 'create'])->name('admin.perfis.create');
    Route::post('/perfis', [PerfilController::class, 'store'])->name('admin.perfis.store');
    Route::get('/perfis/{perfil}/edit', [PerfilController::class, 'edit'])->name('admin.perfis.edit');
    Route::put('/perfis/{perfil}', [PerfilController::class, 'update'])->name('admin.perfis.update');
    Route::delete('/perfis/{perfil}', [PerfilController::class, 'destroy'])->name('admin.perfis.destroy');

    Route::prefix('/lojas')->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('admin.stores.index');
        Route::get('/criar', [StoreController::class, 'create'])->name('admin.stores.create');
        Route::post('/', [StoreController::class, 'store'])->name('admin.stores.store');
        Route::get('{id}', [StoreController::class, 'show'])->name('admin.stores.show');
        Route::get('/edit/{id}', [StoreController::class, 'edit'])->name('admin.stores.edit');
        Route::put('{id}', [StoreController::class, 'update'])->name('admin.stores.update');
        Route::delete('{id}', [StoreController::class, 'destroy'])->name('admin.stores.destroy');
    });

    Route::get('/financeiro', [FinancialController::class, 'index'])->name('admin.financial.index');
    Route::post('/financeiro/saque/{withdrawal}/aprovar', [FinancialController::class, 'approve'])->name('admin.financial.withdrawals.approve');
    Route::post('/financeiro/saque/{withdrawal}/rejeitar', [FinancialController::class, 'reject'])->name('admin.financial.withdrawals.reject');

    Route::prefix('/produtos')->group(function () {
        Route::get('/', [AdminProductController::class, 'index'])->name('admin.produtos.index');
        Route::get('{id}', [AdminProductController::class, 'show'])->name('admin.produtos.show');
        Route::delete('{id}', [AdminProductController::class, 'destroy'])->name('admin.produtos.destroy');
    });
    Route::get('/planos', [PlanController::class, 'index'])->name('admin.planos.index');

    Route::get('/vendas', [AdminSaleController::class, 'index'])->name('admin.sales.index');
    Route::get('/vendas/{sale}', [AdminSaleController::class, 'show'])->name('admin.sales.show');

    Route::get('/contas', [AdminAssociationController::class, 'index'])->name('admin.contas.index');
    Route::get('/contas/{association}', [AdminAssociationController::class, 'show'])->name('admin.contas.show');
    Route::put('/contas/{association}/settings', [AdminAssociationController::class, 'updateSettings'])->name('admin.associations.updateSettings');

    Route::get('users', [AdminUserController::class, 'index'])->name('admin.users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('admin.users.create');
    Route::post('users', [UserController::class, 'store'])->name('admin.users.store');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('admin.users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    Route::get('domains', [DomainController::class, 'index'])->name('domains.index');
    Route::get('domains/create', [DomainController::class, 'create'])->name('domains.create');
    Route::post('domains', [DomainController::class, 'store'])->name('domains.store');
    Route::get('domains/{domain}/edit', [DomainController::class, 'edit'])->name('domains.edit');
    Route::put('domains/{domain}', [DomainController::class, 'update'])->name('domains.update');
    Route::delete('domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');
    Route::post('domains/{domain}/attach-page', [DomainController::class, 'attachPage'])->name('domains.attachPage');

    Route::prefix('categorias')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('admin.categories.index');
        Route::get('/criar', [CategoryController::class, 'create'])->name('admin.categories.create');
        Route::post('/', [CategoryController::class, 'store'])->name('admin.categories.store');
        Route::get('{id}', [CategoryController::class, 'show'])->name('admin.categories.show');
        Route::get('/edit/{id}', [CategoryController::class, 'edit'])->name('admin.categories.edit');
        Route::put('{id}', [CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('{id}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
    });

    Route::prefix('plans')->group(function () {
        Route::get('/', [PlanController::class, 'index'])->name('admin.plans.index');
        Route::get('/create', [PlanController::class, 'create'])->name('admin.plans.create');
        Route::post('/create', [PlanController::class, 'store'])->name('admin.plans.store');
        Route::get('/edit/{plan}', [PlanController::class, 'edit'])->name('admin.plans.edit');
        Route::put('/edit/{plan}', [PlanController::class, 'update'])->name('admin.plans.update');
        Route::delete('{plan}', [PlanController::class, 'destroy'])->name('admin.plans.destroy');
        // Route::get('/criar', [CategoryController::class, 'create'])->name('admin.categories.create');
        // Route::post('/', [CategoryController::class, 'store'])->name('admin.categories.store');
    });

    Route::prefix('subscription')->group(function () {
        Route::get('/', [AdminSubscriptionController::class, 'index'])->name('admin.subscriptions.index');
    });

});

Route::middleware(['auth', RedirectByProfile::class])->prefix('cliente')->group(function () {
    Route::get('dashboard', [ClienteNewsController::class, 'index'])->name('cliente.dashboard');

    // Tela de espera para status não tratados
    Route::get('/aguarde', [ClienteController::class, 'wait'])->name('cliente.wait');

    // Módulo de Documentação (o cliente envia documentos)
    Route::prefix('documentos')->name('cliente.documentos.')->group(function () {
        Route::get('/', [DocumentosController::class, 'index'])->name('index');
        Route::post('/', [DocumentosController::class, 'store'])->name('store');
        Route::patch('/enviar-analise', [DocumentosController::class, 'submitForReview'])->name('submit-for-review');

    });

    // Módulo de Documentação e Pagamento (Tela Unificada)
    Route::prefix('documentos')->name('documentos.')->group(function () {
        // Esta rota é o ponto de entrada para o cliente nos status de documentação e pagamento
        Route::get('/', [DocumentosController::class, 'index'])->name('index');
        // Ações dentro da tela unificada
        Route::post('/upload', [DocumentosController::class, 'store'])->name('store');
        Route::patch('/enviar-analise', [DocumentosController::class, 'submitForReview'])->name('submit-for-review');
    });

    Route::prefix('pagamento')->name('pagamento.')->group(function () {
        // A rota 'store' para processar a mudança de método de pagamento
        Route::post('/store', [PagamentoController::class, 'store'])->name('store');
    });

    // Módulo de Pagamento
    Route::prefix('pagamento')->name('cliente.pagamento.')->group(function () {
        Route::get('/', [PagamentoController::class, 'index'])->name('index');
        // Ação de pagamento, que levará ao checkout
        Route::post('/', [PagamentoController::class, 'store'])->name('store');
    });

    // Módulo de Contrato

    Route::get('/profile', [ClienteNewsController::class, 'profile'])->name('cliente.profile');

    // Notícias
    Route::prefix('noticias')->name('cliente.news.')->group(function () {
        Route::get('/', [NewsController::class, 'all'])->name('index');
        Route::get('/{id}', [NewsController::class, 'show'])->name('show');
    });

    // Novas rotas para criadores de conteúdo
    Route::prefix('criadores')->name('cliente.creators.')->group(function () {
        // Explorar criadores
        Route::get('/explorar', [CreatorProfileController::class, 'explore'])->name('explore');

        // Buscar criadores (AJAX)
        Route::get('/buscar', [CreatorProfileController::class, 'search'])->name('search');

        // Feed personalizado
        Route::get('/feed', [CreatorProfileController::class, 'feed'])->name('feed');

        // Criadores seguidos
        Route::get('/seguindo', [CreatorProfileController::class, 'following'])->name('following');

        // Perfil do criador
        Route::get('/{username}', [CreatorProfileController::class, 'show'])->name('profile');

        // Seguir/deixar de seguir (AJAX)
        Route::post('/{username}/toggle-follow', [CreatorProfileController::class, 'toggleFollow'])->name('toggle-follow');
    });

});

Route::middleware(['auth'])->prefix('api')->group(function () {

    // API para conversas
    Route::apiResource('conversations', ConversationController::class);
    Route::get('conversations/{conversation}/messages', [MessageController::class, 'index']);
    Route::post('conversations/{conversation}/messages', [MessageController::class, 'store']);
    Route::put('conversations/{conversation}/messages/{message}', [MessageController::class, 'update']);
    Route::delete('conversations/{conversation}/messages/{message}', [MessageController::class, 'destroy']);
    Route::post('conversations/{conversation}/messages/mark-read', [MessageController::class, 'markAsRead']);
    Route::get('conversations/{conversation}/messages/search', [MessageController::class, 'search']);

    // Buscar usuários
    Route::get('users/search', [ConversationController::class, 'searchUsers']);
});

require __DIR__.'/auth.php';
