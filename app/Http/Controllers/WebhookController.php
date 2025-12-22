<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionModel;
use App\Models\User;
use App\Models\UserPerfilModel;
use App\Models\WebhookLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Armazena o log do webhook
        $log = WebhookLog::create([
            'event' => $request->event ?? 'unknown',
            'payload' => $request->all(),
        ]);

        try {
            // Verifica o evento
            if ($request->event === 'purchase_approved') {
                $this->processPixPago($request->all());
                $log->update(['status' => 'processed']);
            } else {
                $log->update(['status' => 'ignored']);
            }

            return response()->json(['message' => 'Webhook received'], 200);
        } catch (\Exception $e) {
            // Atualiza o log com o erro
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }

    protected function processPixPago(array $data)
    {
        // Verifica se o usuário já existe
        $user = User::create([
            'name' => $data['data']['customer']['email'],
            'email' => $data['data']['customer']['email'],
            'password' => Hash::make(123456789),
        ]);

        // Cria uma assinatura
        SubscriptionModel::create([
            'user_id' => $user->id,
            'offer_id' => $data['data']['offer']['id'],
            'status' => 'active',
            'price' => $data['data']['offer']['price'],
            'payment_method' => $data['data']['paymentMethodName'],
            'paid_at' => now(),
        ]);

        UserPerfilModel::create([
            'user_id' => $user->id,
            'perfil_id' => 2,
            'is_atual' => 1,
            'status' => 1,
        ]);
    }

    public function kiwify(Request $request)
    {
        // Armazena o log do webhook
        $log = WebhookLog::create([
            'event' => $request->order_status ?? 'unknown',
            'payload' => $request->all(),
        ]);

        try {
            // Verifica o evento
            if ($request->order_status === 'paid') {

                // Verifica se o usuário já existe
                $user = User::where('email', $request['Customer']['email'])->first();

                // Se o usuário não existir, cria o usuário, a assinatura e o perfil
                if (! $user) {
                    $user = User::create([
                        'name' => $request['Customer']['full_name'],
                        'email' => $request['Customer']['email'],
                        'password' => Hash::make(123456789),
                    ]);

                    // Cria o perfil para o novo usuário
                    UserPerfilModel::create([
                        'user_id' => $user->id,
                        'perfil_id' => 2,
                        'is_atual' => 1,
                        'status' => 1,
                    ]);
                }

                // Cria ou atualiza a assinatura
                SubscriptionModel::create([
                    'user_id' => $user->id,
                    'offer_id' => 1,
                    'status' => 'active',
                    'price' => 10,
                    'payment_method' => $request['payment_method'],
                    'paid_at' => now(),
                ]);

                // Atualiza o log como processado
                $log->update(['status' => 'processed']);
            } else {
                // Caso o status não seja "paid", marca como ignorado
                $log->update(['status' => 'ignored']);
            }

            return response()->json(['message' => 'Webhook received'], 200);
        } catch (\Exception $e) {
            // Atualiza o log com o erro
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }
}
