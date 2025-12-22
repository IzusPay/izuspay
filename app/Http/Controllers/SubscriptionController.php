<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SubscriptionController extends Controller
{
    public function create(Request $request)
    {
        // Supondo que o JSON seja enviado via POST
        $data = $request->all(); // Dados do JSON

        // Criar o usuário
        $user = User::create([
            'name' => $data['customer']['name'],
            'email' => $data['customer']['email'],
            'password' => Hash::make('123456789'), // Senha temporária ou gerada automaticamente
        ]);

        // Criar a assinatura vinculada ao usuário
        $subscription = SubscriptionModel::create([
            'user_id' => $user->id,
            'offer_id' => 1, // Assumindo que você já tem ofertas cadastradas
            'status' => $data['status'],
            'price' => $data['price'],
            'payment_method' => $data['paymentMethod'],
            'paid_at' => $data['paidAt'] ?? null, // Se estiver pago
        ]);

        // Retornar uma resposta, caso queira confirmar a criação
        return response()->json([
            'message' => 'Usuário e assinatura criados com sucesso!',
            'user' => $user,
            'subscription' => $subscription,
        ], 201);
    }
}
