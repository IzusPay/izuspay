<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 0. Tentar autenticação via JWT (Guard 'api')
        if (Auth::guard('api')->check()) {
            Auth::shouldUse('api');
            return $next($request);
        }

        // 1. Pegar o token do cabeçalho da requisição.
        // O padrão é usar o cabeçalho 'Authorization' com o formato 'Bearer SEU_TOKEN'.
        $token = $request->bearerToken();
        Log::info('API auth start', [
            'path' => $request->path(),
            'has_token' => (bool) $token,
            'prefix' => $token ? substr($token, 0, 3) : null,
        ]);

        // Se não houver token, retorne um erro 401 (Não Autorizado).
        if (! $token) {
            return response()->json(['message' => 'Token de autenticação não fornecido.'], 401);
        }

        // 2. Buscar o usuário pelo hash do token.
        // Lembre-se: no banco, guardamos o HASH do token, não o token em si.
        $hashedToken = hash('sha256', $token);
        $user = User::where('api_token', $hashedToken)->first();
        Log::info('API auth user via hash', ['found' => (bool) $user]);

        if (! $user) {
            $record = ApiToken::where('active', true)->get()->first(function ($t) use ($token) {
                try {
                    return Crypt::decryptString($t->token) === $token;
                } catch (\Throwable $e) {
                    return false;
                }
            });
            Log::info('API auth via ApiToken', [
                'matched' => (bool) $record,
                'token_count_active' => ApiToken::where('active', true)->count(),
            ]);
            if (! $record) {
                return response()->json(['message' => 'Não autorizado. Token inválido.'], 401);
            }
            $user = $record->user;
        }
        Auth::login($user);
        Log::info('API auth success', ['user_id' => $user->id]);

        // 5. Se tudo estiver OK, permita que a requisição continue.
        return $next($request);
    }
}
