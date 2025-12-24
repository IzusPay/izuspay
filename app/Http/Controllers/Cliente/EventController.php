<?php

namespace App\Http\Controllers\Cliente;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Sale;
use App\Models\TicketOrder;
use App\Models\TicketType;
use App\Models\WebhookEndpoint;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::where('status', 'published')->orderBy('starts_at', 'asc')->get();

        return view('cliente.eventos.index', compact('events'));
    }

    public function show(Event $evento)
    {
        $types = $evento->ticketTypes()->where('is_active', true)->orderBy('price')->get();
        $association = $evento->association;

        return view('cliente.eventos.show', compact('evento', 'types', 'association'));
    }

    public function buy(Request $request, Event $evento, PaymentService $payments)
    {
        $data = $request->validate([
            'ticket_type_id' => ['required', 'exists:ticket_types,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string'],
            'document' => ['nullable', 'string'],
        ]);
        $type = TicketType::where('event_id', $evento->id)->findOrFail($data['ticket_type_id']);
        if (! $type->is_active) {
            return back()->with('error', 'Tipo de ingresso indisponível.');
        }
        if ($type->per_order_limit && $data['quantity'] > $type->per_order_limit) {
            return back()->with('error', 'Quantidade acima do limite por pedido.');
        }

        $customer = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? '',
            'document' => $data['document'] ?? '',
            'items' => [[
                'title' => $type->name,
                'amount' => (int) round($type->price * 100),
                'quantity' => $data['quantity'],
                'tangible' => false,
                'externalRef' => 'event:'.$evento->id,
            ]],
            'metadata' => [
                'event_id' => $evento->id,
                'ticket_type_id' => $type->id,
                'quantity' => $data['quantity'],
            ],
        ];

        $result = $payments->createEventTransaction($evento, $type, (int) $data['quantity'], $customer);

        $sale = Sale::where('transaction_hash', $result['transaction_hash'])->first();
        if ($sale) {
            $amountCents = (int) round($sale->total_price * 100);
            $payload = [
                'id' => $sale->transaction_hash,
                'type' => 'transaction',
                'event' => 'waiting_payment',
                'metadata' => [
                    'sale_id' => $sale->id,
                ],
                'amount' => $amountCents,
                'method' => strtoupper($sale->payment_method),
                'created_at' => $sale->created_at ? $sale->created_at->toIso8601String() : now()->toIso8601String(),
                'updated_at' => $sale->updated_at ? $sale->updated_at->toIso8601String() : now()->toIso8601String(),
                'status' => 'waiting_payment',
                'customer' => $sale->user ? [
                    'name' => $sale->user->name,
                    'email' => $sale->user->email,
                    'phone' => preg_replace('/\D/', '', $sale->user->phone ?? ''),
                    'document' => preg_replace('/\D/', '', $sale->user->documento ?? ''),
                ] : null,
                'items' => [[
                    'title' => $type->name,
                    'amount' => $amountCents,
                    'quantity' => (int) $data['quantity'],
                    'tangible' => false,
                ]],
            ];
            $endpoints = WebhookEndpoint::where('association_id', $sale->association_id)
                ->where('is_active', true)
                ->get();
            foreach ($endpoints as $endpoint) {
                try {
                    $delivery = \App\Models\WebhookDelivery::create([
                        'association_id' => $sale->association_id,
                        'endpoint_url' => $endpoint->url,
                        'endpoint_description' => $endpoint->description,
                        'event' => $payload['event'],
                        'status' => 'pending',
                        'is_manual' => false,
                        'payload' => $payload,
                    ]);
                    Http::withHeaders(['Content-Type' => 'application/json'])->post($endpoint->url, $payload);
                    $delivery->update(['status' => 'sent', 'response_status' => 200]);
                } catch (\Throwable $e) {
                    Log::error('Falha ao encaminhar webhook de waiting_payment (evento)', [
                        'url' => $endpoint->url,
                        'sale_id' => $sale->id,
                        'error' => $e->getMessage(),
                    ]);
                    \App\Models\WebhookDelivery::create([
                        'association_id' => $sale->association_id,
                        'endpoint_url' => $endpoint->url,
                        'endpoint_description' => $endpoint->description,
                        'event' => $payload['event'],
                        'status' => 'failed',
                        'is_manual' => false,
                        'payload' => $payload,
                        'error_message' => $e->getMessage(),
                    ]);
                }
            }
        }

        return redirect()->route('checkout.success', $result['transaction_hash'])->with('success', 'Pedido criado. Complete o pagamento para emitir os ingressos.');
    }
}
