<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhooksController extends Controller
{
    public function index(Request $request)
    {
        $webhooks = collect([
            [
                'id' => 101,
                'cliente' => 'Loja Alpha',
                'event' => 'payment.success',
                'status' => 'pendente',
                'received_at' => now()->subMinutes(12)->toDateTimeString(),
                'payload_excerpt' => '{ "order_id": "A-1001", "amount": 129.90 }'
            ],
            [
                'id' => 102,
                'cliente' => 'Loja Beta',
                'event' => 'payment.failed',
                'status' => 'rejeitado',
                'received_at' => now()->subHour()->toDateTimeString(),
                'payload_excerpt' => '{ "order_id": "B-2002", "amount": 89.00 }'
            ],
            [
                'id' => 103,
                'cliente' => 'Loja Gamma',
                'event' => 'payment.success',
                'status' => 'aprovado',
                'received_at' => now()->subMinutes(3)->toDateTimeString(),
                'payload_excerpt' => '{ "order_id": "G-3003", "amount": 199.99 }'
            ],
        ]);

        return view('admin.webhooks.index', compact('webhooks'));
    }
}

