<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebhookEndpoint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class WebhookEndpointApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $associationId = Auth::user()->association_id;
        $perPage = (int) ($request->input('limit', 15));
        $perPage = max(1, min($perPage, 100));
        $page = max(1, (int) ($request->input('page', 1)));
        $isActive = $request->has('is_active') ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

        $query = WebhookEndpoint::where('association_id', $associationId)
            ->when($isActive !== null, function ($q) use ($isActive) {
                $q->where('is_active', $isActive);
            })
            ->latest();

        $endpoints = $query->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $endpoints->items(),
            'meta' => [
                'page' => $endpoints->currentPage(),
                'limit' => $endpoints->perPage(),
                'current_page' => $endpoints->currentPage(),
                'last_page' => $endpoints->lastPage(),
                'per_page' => $endpoints->perPage(),
                'total' => $endpoints->total(),
            ],
        ]);
    }

    public function show(Request $request, WebhookEndpoint $webhook): JsonResponse
    {
        if ($webhook->association_id !== Auth::user()->association_id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        return response()->json([
            'data' => $webhook,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => ['required', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $webhook = WebhookEndpoint::create([
            'association_id' => Auth::user()->association_id,
            'url' => $validated['url'],
            'description' => $validated['description'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return response()->json(['data' => $webhook], 201);
    }

    public function update(Request $request, WebhookEndpoint $webhook): JsonResponse
    {
        if ($webhook->association_id !== Auth::user()->association_id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $validated = $request->validate([
            'url' => ['sometimes', 'url', 'max:2048'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $webhook->fill($validated);
        $webhook->save();

        return response()->json(['data' => $webhook]);
    }

    public function destroy(Request $request, WebhookEndpoint $webhook): JsonResponse
    {
        if ($webhook->association_id !== Auth::user()->association_id) {
            return response()->json(['message' => 'Não autorizado'], 403);
        }

        $webhook->delete();

        return response()->json([], 204);
    }
}

