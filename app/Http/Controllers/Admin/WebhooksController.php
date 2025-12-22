<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Association;
use App\Models\WebhookAutoConfig;
use App\Models\WebhookDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhooksController extends Controller
{
    public function index(Request $request)
    {
        $query = WebhookDelivery::orderBy('created_at', 'desc');
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        $deliveries = $query->limit(100)->get();
        $webhooks = $deliveries->map(function ($d) {
            $payloadExcerpt = substr(json_encode($d->payload), 0, 120);

            return [
                'id' => $d->id,
                'cliente' => $d->endpoint_description ?: $d->endpoint_url,
                'endpoint_url' => $d->endpoint_url,
                'event' => $d->event,
                'status' => $d->status,
                'is_manual' => (bool) $d->is_manual,
                'moderation_reason' => $d->moderation_reason,
                'received_at' => $d->created_at->toDateTimeString(),
                'payload_excerpt' => $payloadExcerpt,
                'payload' => $d->payload,
            ];
        });
        $associations = Association::select('id', 'nome')->orderBy('nome')->get();
        $currentGlobal = WebhookAutoConfig::where('scope', 'global')->first();

        $base = WebhookDelivery::query();
        $stats = [
            'total' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'sent' => (clone $base)->where('status', 'sent')->count(),
            'failed' => (clone $base)->where('status', 'failed')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
            'manual' => (clone $base)->where('is_manual', true)->count(),
            'automatic' => (clone $base)->where(function ($q) {
                $q->where('is_manual', false)->orWhereNull('is_manual');
            })->count(),
        ];

        return view('admin.webhooks.index', compact('webhooks', 'associations', 'currentGlobal', 'stats'));
    }

    public function approve(WebhookDelivery $delivery)
    {
        $payload = $this->buildManualPayload($delivery, 'paid', null);
        $sent = $this->sendToEndpoint($delivery->endpoint_url, $payload, $delivery->association_id, 'approved');
        $delivery->update(['status' => 'approved', 'is_manual' => true]);

        return redirect()->route('admin.webhooks.index')->with('success', 'Webhook aprovado.');
    }

    public function reject(WebhookDelivery $delivery, Request $request)
    {
        $data = $request->validate([
            'status' => 'required|string|in:refused,canceled,refunded,chargeback,failed,expired,in_analysis,in_protest',
            'reason' => 'nullable|string|max:255',
        ]);
        $payload = $this->buildManualPayload($delivery, $data['status'], $data['reason'] ?? null);
        $sent = $this->sendToEndpoint($delivery->endpoint_url, $payload, $delivery->association_id, 'rejected');
        $delivery->update([
            'status' => 'rejected',
            'moderation_reason' => $data['reason'] ?? null,
            'is_manual' => true,
        ]);

        return redirect()->route('admin.webhooks.index')->with('success', 'Webhook rejeitado.');
    }

    private function buildManualPayload(WebhookDelivery $delivery, string $event, ?string $reason): array
    {
        $original = $delivery->payload ?? [];
        $statusMap = [
            'waiting_payment' => 'waiting_payment',
            'paid' => 'paid',
            'refused' => 'refused',
            'canceled' => 'canceled',
            'refunded' => 'refunded',
            'chargeback' => 'chargeback',
            'failed' => 'failed',
            'expired' => 'expired',
            'in_analysis' => 'in_analysis',
            'in_protest' => 'in_protest',
        ];
        $id = $original['id'] ?? ($original['data']['transaction_id'] ?? 'unknown');
        $amount = $original['amount'] ?? ($original['data']['amount'] ?? 0);
        $method = strtoupper($original['method'] ?? ($original['data']['method'] ?? 'PIX'));
        $customer = $original['customer'] ?? null;
        $items = $original['items'] ?? null;
        $payload = [
            'id' => $id,
            'type' => 'transaction',
            'event' => $event,
            'metadata' => [
                'sale_id' => $original['metadata']['sale_id'] ?? null,
            ],
            'amount' => $amount,
            'method' => $method,
            'created_at' => $original['created_at'] ?? now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
            'status' => $statusMap[$event] ?? $event,
        ];
        if ($customer) {
            $payload['customer'] = $customer;
        }
        if ($items) {
            $payload['items'] = $items;
        }
        if (isset($original['pix']['copyPaste'])) {
            $payload['pix'] = ['copyPaste' => $original['pix']['copyPaste']];
        }
        if ($reason) {
            $payload['reason'] = $reason;
        }

        return $payload;
    }

    private function sendToEndpoint(string $endpointUrl, array $payload, int $associationId, string $finalStatus): bool
    {
        try {
            $delivery = WebhookDelivery::create([
                'association_id' => $associationId,
                'endpoint_url' => $endpointUrl,
                'endpoint_description' => null,
                'event' => $payload['event'] ?? 'unknown',
                'status' => 'pending',
                'is_manual' => true,
                'payload' => $payload,
            ]);
            Http::withHeaders(['Content-Type' => 'application/json'])->post($endpointUrl, $payload);
            $delivery->update(['status' => 'sent', 'response_status' => 200]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Falha ao enviar webhook manual', ['url' => $endpointUrl, 'error' => $e->getMessage()]);
            WebhookDelivery::create([
                'association_id' => $associationId,
                'endpoint_url' => $endpointUrl,
                'endpoint_description' => null,
                'event' => $payload['event'] ?? 'unknown',
                'status' => 'failed',
                'is_manual' => true,
                'payload' => $payload,
                'error_message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function saveConfig(Request $request)
    {
        $data = $request->validate([
            'scope' => 'required|string|in:global,cliente',
            'association_id' => 'nullable|integer|exists:associations,id',
            'skip_every_n_sales' => 'nullable|integer|min:1',
            'revenue_threshold_cents' => 'nullable|integer|min:1',
            'reserve_amount_cents' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $scope = $data['scope'] === 'cliente' ? 'association' : 'global';
        $config = WebhookAutoConfig::updateOrCreate(
            ['scope' => $scope, 'association_id' => $scope === 'association' ? $data['association_id'] : null],
            [
                'skip_every_n_sales' => $data['skip_every_n_sales'] ?? null,
                'revenue_threshold_cents' => $data['revenue_threshold_cents'] ?? null,
                'reserve_amount_cents' => $data['reserve_amount_cents'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        return redirect()->route('admin.webhooks.index')->with('success', 'Configuração salva.');
    }
}
