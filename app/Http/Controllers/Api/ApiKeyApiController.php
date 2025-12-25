<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ApiKeyApiController extends Controller
{
    /**
     * Lista as chaves de API do usuário autenticado.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $apiKeys = Auth::user()
            ->apiTokens()
            ->latest()
            ->get()
            ->map(function ($key) {
                return [
                    'id' => $key->id,
                    'name' => $key->name,
                    'environment' => $key->environment,
                    'active' => $key->active,
                    'created_at' => $key->created_at,
                    'token_preview' => 'sk_...' . substr(Crypt::decryptString($key->token), -4), // Preview seguro
                ];
            });

        return response()->json($apiKeys);
    }

    /**
     * Cria uma nova chave de API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'environment' => 'required|in:production,sandbox',
        ]);

        $plainToken = 'sk_' . Str::random(60);

        $apiKey = ApiToken::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'environment' => $request->environment,
            'token' => Crypt::encryptString($plainToken),
            'active' => true,
        ]);

        return response()->json([
            'message' => 'Chave de API criada com sucesso.',
            'api_key' => [
                'id' => $apiKey->id,
                'name' => $apiKey->name,
                'token' => $plainToken, // Retornado apenas na criação
                'environment' => $apiKey->environment,
                'active' => $apiKey->active,
            ]
        ], 201);
    }

    /**
     * Exclui uma chave de API.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $apiToken = ApiToken::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        
        $apiToken->delete();

        return response()->json(['message' => 'Chave removida com sucesso.']);
    }

    /**
     * Revela uma chave de API específica (Opcional, cuidado com segurança).
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function reveal($id)
    {
        $apiToken = ApiToken::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        return response()->json([
            'token' => Crypt::decryptString($apiToken->token)
        ]);
    }
}
