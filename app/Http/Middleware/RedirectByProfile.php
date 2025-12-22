<?php

namespace App\Http\Middleware;

use App\Services\GamificationService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class RedirectByProfile
{
    protected $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    /**
     * Lida com uma requisição de entrada.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Se não houver usuário autenticado, permite que a requisição continue.
        if (! $user) {
            return $next($request);
        }

        $gamificationData = $this->gamificationService->getGamificationData($user);

        View::share('globalGamificationData', $gamificationData);

        // Rotas que não devem ser redirecionadas (para evitar loops ou rotas públicas essenciais)
        $exemptRoutes = [
            'logout',
            'api/*',
            // Adicione outras rotas que nunca devem ser redirecionadas,
            // independentemente do perfil do usuário, por exemplo:
            // 'password/reset/*',
            // 'register', // Se houver uma rota de registro que não deve redirecionar
        ];

        foreach ($exemptRoutes as $route) {
            if ($request->is($route)) {
                return $next($request);
            }
        }

        // Redireciona com base no perfil do usuário
        $perfilAtual = $this->getPerfilAtual($user);

        // Se o usuário não tem um perfil ativo (por alguma razão de dados), desloga e redireciona para o login.
        if (! $perfilAtual) {
            Auth::logout();

            return redirect('/login')->with('error', 'Usuário sem perfil ativo. Entre em contato com o administrador.');
        }

        $redirectUrl = $this->getRedirectUrlByProfile($perfilAtual->name);

        // Redireciona apenas se o usuário não estiver na área correta para seu perfil.
        // Isso previne redirecionamentos desnecessários e loops.
        if ($redirectUrl && ! $this->isAlreadyInCorrectArea($request, $perfilAtual->name)) {
            return redirect($redirectUrl);
        }

        // Permite que a requisição continue se nenhum redirecionamento for necessário.
        return $next($request);
    }

    /**
     * Obtém o perfil atual do usuário.
     * Assume que 'perfilAtual()' ou uma relação userPerfis existe.
     *
     * @param  \App\Models\User  $user
     * @return \App\Models\Perfil|null
     */
    private function getPerfilAtual($user)
    {
        // Prioriza o método perfilAtual() se ele existir no modelo User
        if (method_exists($user, 'perfilAtual')) {
            return $user->perfilAtual();
        }

        // Fallback: Tenta encontrar o perfil atual através da relação userPerfis
        // Removido o where('status', 1) conforme solicitado, mantendo apenas is_atual
        $userPerfil = $user->userPerfis()
            ->where('is_atual', 1)
            ->with('perfil')
            ->first();

        return $userPerfil ? $userPerfil->perfil : null;
    }

    /**
     * Retorna a URL de redirecionamento baseada no nome do perfil.
     *
     * @param  string  $perfilName
     * @return string|null
     */
    private function getRedirectUrlByProfile($perfilName)
    {
        $redirectMap = [
            'Administrador' => '/admin/dashboard',
            'Cliente' => '/cliente/dashboard',
            'Associacao' => '/associacao/dashboard',
            'Membro' => '/membro/dashboard',
            'Moderador' => '/moderador/dashboard',
        ];

        // Retorna a URL mapeada ou '/dashboard' como um padrão genérico
        return $redirectMap[$perfilName] ?? '/dashboard';
    }

    /**
     * Verifica se o usuário já está na área correta com base no padrão da URL.
     *
     * @param  string  $perfilName
     * @return bool
     */
    private function isAlreadyInCorrectArea(Request $request, $perfilName)
    {
        $areaMap = [
            'Administrador' => 'admin/*',
            'Cliente' => 'cliente/*',
            'Associacao' => 'associacao/*',
            'Membro' => 'membro/*',
            'Moderador' => 'moderador/*',
            // Adicione aqui outros perfis e seus padrões de rota
        ];

        $pattern = $areaMap[$perfilName] ?? null;

        return $pattern ? $request->is($pattern) : false;
    }
}
