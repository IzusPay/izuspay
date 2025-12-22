<?php

namespace App\Http\Controllers\Associacao;

use App\Http\Controllers\Controller;
use App\Models\ApiToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ApiKeysController extends Controller
{
    /**
     * Lista as chaves
     */
    public function index()
    {
        $apiKeys = Auth::user()
            ->apiTokens()
            ->latest()
            ->paginate(10);

        return view('associacao.api-keys.index', compact('apiKeys'));
    }

    /**
     * Form de criação
     */
    public function create()
    {
        return view('associacao.api-keys.create');
    }

    /**
     * Cria nova chave
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'environment' => 'required|in:production,sandbox',
        ]);

        $plainToken = 'sk_'.Str::random(60);

        ApiToken::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'environment' => $request->environment,
            'token' => Crypt::encryptString($plainToken),
            'active' => true,
        ]);

        return redirect()
            ->route('api-keys.index')
            ->with('success', 'Chave de API criada com sucesso!')
            ->with('newApiKey', $plainToken); // 👈 mostrar 1x
    }

    /**
     * Revogar / desativar
     */
    public function toggle(ApiToken $apiToken)
    {
        $this->authorizeToken($apiToken);

        $apiToken->update([
            'active' => ! $apiToken->active,
        ]);

        return back()->with('success', 'Status da chave atualizado.');
    }

    /**
     * Excluir chave
     */
    public function destroy(ApiToken $apiToken)
    {
        $this->authorizeToken($apiToken);

        $apiToken->delete();

        return back()->with('success', 'Chave removida com sucesso.');
    }

    /**
     * Segurança: garante que a chave é do usuário logado
     */
    private function authorizeToken(ApiToken $apiToken)
    {
        abort_if($apiToken->user_id !== Auth::id(), 403);
    }

    public function reveal(ApiToken $apiToken)
    {
        abort_if($apiToken->user_id !== auth()->id(), 403);

        return response()->json([
            'token' => Crypt::decryptString($apiToken->token),
        ]);
    }
}
