<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebhooksController extends Controller
{
    public function index(Request $request)
    {
        $associationId = Auth::user()->association_id;
        $webhooks = WebhookEndpoint::where('association_id', $associationId)->orderBy('created_at', 'desc')->get();
        $activeCount = $webhooks->where('is_active', true)->count();
        $inactiveCount = $webhooks->where('is_active', false)->count();

        return view('associacao.webhooks.index', compact('inactiveCount', 'activeCount', 'webhooks'));
    }

    public function store(Request $request)
    {
        $associationId = Auth::user()->association_id;
        $data = $request->validate([
            'url' => ['required', 'url', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
        $data['association_id'] = $associationId;
        $data['is_active'] = true;
        WebhookEndpoint::create($data);

        return redirect()->route('associacao.webhooks.index')->with('success', 'Webhook cadastrado com sucesso.');
    }

    public function destroy(WebhookEndpoint $webhookEndpoint)
    {
        $associationId = Auth::user()->association_id;
        if ($webhookEndpoint->association_id !== $associationId) {
            abort(403);
        }
        $webhookEndpoint->delete();

        return redirect()->route('associacao.webhooks.index')->with('success', 'Webhook removido com sucesso.');
    }

    public function toggle(WebhookEndpoint $webhookEndpoint)
    {
        $associationId = Auth::user()->association_id;
        if ($webhookEndpoint->association_id !== $associationId) {
            abort(403);
        }
        $webhookEndpoint->is_active = ! $webhookEndpoint->is_active;
        $webhookEndpoint->save();

        return redirect()->route('associacao.webhooks.index')->with('success', 'Webhook atualizado.');
    }
}
