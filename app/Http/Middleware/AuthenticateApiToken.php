<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response; // Importe o seu model User

class AuthenticateApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Pegar o token do cabeçalho da requisição.
        // O padrão é usar o cabeçalho 'Authorization' com o formato 'Bearer SEU_TOKEN'.
        $token = $request->bearerToken();

        // Se não houver token, retorne um erro 401 (Não Autorizado).
        if (! $token) {
            return response()->json(['message' => 'Token de autenticação não fornecido.'], 401);
        }

        // 2. Buscar o usuário pelo hash do token.
        // Lembre-se: no banco, guardamos o HASH do token, não o token em si.
        $hashedToken = hash('sha256', $token);
        $user = User::where('api_token', $hashedToken)->first();

        // 3. Verificar se o usuário foi encontrado e se está ativo.
        if (! $user || $user->status !== 'ativo') {
            // Se não encontrar o usuário ou ele estiver inativo, retorne um erro 401.
            return response()->json(['message' => 'Não autorizado. Token inválido ou usuário inativo.'], 401);
        }
        // 4. Autenticar o usuário para esta requisição.
        // Isso permite que você use Auth::user() nos seus controllers de API.
        auth()->login($user);

        // 5. Se tudo estiver OK, permita que a requisição continue.
        return $next($request);
    }
}
