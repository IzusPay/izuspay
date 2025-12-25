<?php

use App\Http\Controllers\Api\ApiKeyApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GoatPaymentController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SellerWalletController;
use App\Http\Controllers\Api\WebhookEndpointApiController;
use App\Http\Controllers\Api\WithdrawalApiController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::post('me', [AuthController::class, 'me']);
    });
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::apiResource('api-keys', ApiKeyApiController::class);
});

Route::post('/webhook-cakto', [WebhookController::class, 'handle'])->name('webhook.handle');
Route::post('/webhook-kiwify', [WebhookController::class, 'kiwify'])->name('webhook.kiwify');

Route::get('/goat-payments/check-transaction-status', [CheckoutController::class, 'checkTransactionStatus'])->name('api.goat.check_status');

// Certifique-se também que a rota para criar a transação Pix está presente:
Route::post('/goat-payments/create-pix-transaction', [GoatPaymentController::class, 'createPixTransaction'])->name('api.goat.create_pix');

// E a rota de postback da Goat Payments:
Route::post('/goat-payments/postback', [GoatPaymentController::class, 'handlePostback'])->name('api.goat.postback');

Route::post('/witetec/postback', [CheckoutController::class, 'handlePostback'])->name('api.witetec.postback');
Route::post('/brpagg/postback', [CheckoutController::class, 'handleBrPaggPostback'])->name('api.brpagg.postback');

Route::middleware('auth.api_token')->group(function () {
    Route::get('/transactions', [PaymentController::class, 'index']);
    Route::post('/transactions', [PaymentController::class, 'create']);
    Route::get('/transactions/{transactionId}', [PaymentController::class, 'show']);

    Route::get('/withdrawals', [WithdrawalApiController::class, 'index']);
    Route::post('/withdrawals', [WithdrawalApiController::class, 'store']);
    Route::get('/withdrawals/{withdrawal}', [WithdrawalApiController::class, 'show']);

    Route::get('/seller-wallet/gestao', [SellerWalletController::class, 'gestao']);

    Route::get('/webhooks', [WebhookEndpointApiController::class, 'index']);
    Route::post('/webhooks', [WebhookEndpointApiController::class, 'store']);
    Route::get('/webhooks/{webhook}', [WebhookEndpointApiController::class, 'show']);
    Route::put('/webhooks/{webhook}', [WebhookEndpointApiController::class, 'update']);
    Route::delete('/webhooks/{webhook}', [WebhookEndpointApiController::class, 'destroy']);
});
